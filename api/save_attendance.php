<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "../Includes/dbcon.php";
include "auth.php";

/*
    This file is for ClassTeacher only.
    It saves or updates attendance into tblattendance.

    It accepts:
    1. form-data / x-www-form-urlencoded:
       dateTaken = 2026-04-28
       attendance = [{"admissionNo":"ETMB-001","status":1}]

    2. raw JSON:
       {
          "dateTaken": "2026-04-28",
          "attendance": [
              {"admissionNo":"ETMB-001","status":1},
              {"admissionNo":"ETMB-002","status":0}
          ]
       }

    status:
    1 = Present
    0 = Absent
*/

$user = validateToken($conn);
requireRole($user, ["ClassTeacher"]);

$teacherId = intval($user['user_id']);

$dateTaken = "";
$attendanceList = null;

/* Read raw JSON body */
$rawInput = file_get_contents("php://input");
$jsonData = json_decode($rawInput, true);

if (is_array($jsonData)) {
    if (isset($jsonData["dateTaken"])) {
        $dateTaken = trim($jsonData["dateTaken"]);
    }

    if (isset($jsonData["attendance"]) && is_array($jsonData["attendance"])) {
        $attendanceList = $jsonData["attendance"];
    }
}

/* Read form-data or x-www-form-urlencoded */
if ($dateTaken == "" && isset($_POST["dateTaken"])) {
    $dateTaken = trim($_POST["dateTaken"]);
}

if ($attendanceList === null && isset($_POST["attendance"])) {
    $attendanceList = json_decode($_POST["attendance"], true);
}

/* Convert date if C# sends MM/dd/yyyy */
if (preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $dateTaken)) {
    $dateObj = DateTime::createFromFormat("m/d/Y", $dateTaken);
    if ($dateObj) {
        $dateTaken = $dateObj->format("Y-m-d");
    }
}

/* Validate date */
if ($dateTaken == "") {
    echo json_encode([
        "success" => false,
        "message" => "dateTaken is required."
    ]);
    exit();
}

if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $dateTaken)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid date format. Use YYYY-MM-DD.",
        "receivedDate" => $dateTaken
    ]);
    exit();
}

/* Validate attendance */
if (!is_array($attendanceList) || count($attendanceList) == 0) {
    echo json_encode([
        "success" => false,
        "message" => "attendance is required and must be a JSON array."
    ]);
    exit();
}

/* Get the logged-in class teacher's assigned class and class arm */
$teacherStmt = $conn->prepare("
    SELECT classId, classArmId
    FROM tblclassteacher
    WHERE Id = ?
    LIMIT 1
");

if (!$teacherStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Teacher query prepare failed.",
        "error" => $conn->error
    ]);
    exit();
}

$teacherStmt->bind_param("i", $teacherId);
$teacherStmt->execute();
$teacherResult = $teacherStmt->get_result();

if (!$teacherRow = $teacherResult->fetch_assoc()) {
    echo json_encode([
        "success" => false,
        "message" => "Class teacher assignment not found."
    ]);
    exit();
}

$classId = intval($teacherRow["classId"]);
$classArmId = intval($teacherRow["classArmId"]);

/* Get active session term */
$sessionStmt = $conn->prepare("
    SELECT Id
    FROM tblsessionterm
    WHERE isActive = '1'
    LIMIT 1
");

if (!$sessionStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Session term query prepare failed.",
        "error" => $conn->error
    ]);
    exit();
}

$sessionStmt->execute();
$sessionResult = $sessionStmt->get_result();

if (!$sessionRow = $sessionResult->fetch_assoc()) {
    echo json_encode([
        "success" => false,
        "message" => "No active session term found. Please activate one first."
    ]);
    exit();
}

$sessionTermId = intval($sessionRow["Id"]);

$savedCount = 0;
$skippedCount = 0;
$errors = [];

/* Prepare reusable statements */
$studentCheckStmt = $conn->prepare("
    SELECT Id
    FROM tblstudents
    WHERE admissionNumber = ?
    AND classId = ?
    AND classArmId = ?
    LIMIT 1
");

$existingStmt = $conn->prepare("
    SELECT Id
    FROM tblattendance
    WHERE admissionNo = ?
    AND classId = ?
    AND classArmId = ?
    AND sessionTermId = ?
    AND dateTimeTaken = ?
    LIMIT 1
");

$updateStmt = $conn->prepare("
    UPDATE tblattendance
    SET status = ?
    WHERE Id = ?
");

$insertStmt = $conn->prepare("
    INSERT INTO tblattendance
    (admissionNo, classId, classArmId, sessionTermId, status, dateTimeTaken)
    VALUES (?, ?, ?, ?, ?, ?)
");

if (!$studentCheckStmt || !$existingStmt || !$updateStmt || !$insertStmt) {
    echo json_encode([
        "success" => false,
        "message" => "One or more SQL statements failed to prepare.",
        "error" => $conn->error
    ]);
    exit();
}

/* Save each attendance record */
foreach ($attendanceList as $item) {
    $admissionNo = isset($item["admissionNo"]) ? trim($item["admissionNo"]) : "";
    $status = isset($item["status"]) ? intval($item["status"]) : 0;

    if ($admissionNo == "") {
        $skippedCount++;
        $errors[] = "Skipped one record because admissionNo is empty.";
        continue;
    }

    $status = ($status == 1) ? 1 : 0;

    /* Check if student belongs to this ClassTeacher */
    $studentCheckStmt->bind_param("sii", $admissionNo, $classId, $classArmId);
    $studentCheckStmt->execute();
    $studentResult = $studentCheckStmt->get_result();

    if ($studentResult->num_rows == 0) {
        $skippedCount++;
        $errors[] = "Skipped $admissionNo because student is not assigned to this teacher's class and class arm.";
        continue;
    }

    /* Check if attendance already exists for the same date */
    $existingStmt->bind_param(
        "siiis",
        $admissionNo,
        $classId,
        $classArmId,
        $sessionTermId,
        $dateTaken
    );

    $existingStmt->execute();
    $existingResult = $existingStmt->get_result();

    if ($existingRow = $existingResult->fetch_assoc()) {
        $attendanceId = intval($existingRow["Id"]);

        $updateStmt->bind_param("ii", $status, $attendanceId);

        if ($updateStmt->execute()) {
            $savedCount++;
        } else {
            $errors[] = "Failed to update attendance for $admissionNo: " . $updateStmt->error;
        }
    } else {
        $insertStmt->bind_param(
            "siiiis",
            $admissionNo,
            $classId,
            $classArmId,
            $sessionTermId,
            $status,
            $dateTaken
        );

        if ($insertStmt->execute()) {
            $savedCount++;
        } else {
            $errors[] = "Failed to insert attendance for $admissionNo: " . $insertStmt->error;
        }
    }
}

echo json_encode([
    "success" => $savedCount > 0,
    "message" => $savedCount > 0
        ? "Attendance saved successfully."
        : "No attendance was saved. Check errors.",
    "teacherId" => $teacherId,
    "classId" => $classId,
    "classArmId" => $classArmId,
    "sessionTermId" => $sessionTermId,
    "dateTaken" => $dateTaken,
    "savedCount" => $savedCount,
    "skippedCount" => $skippedCount,
    "errors" => $errors
]);

$conn->close();
?>
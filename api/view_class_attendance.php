<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "../Includes/dbcon.php";
include "auth.php";

$user = validateToken($conn);
requireRole($user, ["ClassTeacher"]);

$teacherId = $user['user_id'];
$dateTaken = isset($_GET['dateTaken']) ? trim($_GET['dateTaken']) : '';

if ($dateTaken == '') {
    echo json_encode([
        "success" => false,
        "message" => "dateTaken is required."
    ]);
    exit();
}

/*
    Accept both:
    2026-04-28
    04/28/2026
*/
if (preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $dateTaken)) {
    $dateObj = DateTime::createFromFormat("m/d/Y", $dateTaken);
    if ($dateObj) {
        $dateTaken = $dateObj->format("Y-m-d");
    }
}

if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $dateTaken)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid date format. Use YYYY-MM-DD."
    ]);
    exit();
}

/* Get teacher assigned class and class arm */
$teacherStmt = $conn->prepare("
    SELECT classId, classArmId 
    FROM tblclassteacher 
    WHERE Id = ?
    LIMIT 1
");

$teacherStmt->bind_param("i", $teacherId);
$teacherStmt->execute();
$teacherResult = $teacherStmt->get_result();

if (!$teacher = $teacherResult->fetch_assoc()) {
    echo json_encode([
        "success" => false,
        "message" => "Class teacher assignment not found."
    ]);
    exit();
}

$classId = $teacher['classId'];
$classArmId = $teacher['classArmId'];

/*
    View attendance by teacher class, class arm, and date.
    This version does not force sessionName join to avoid query error.
*/
$stmt = $conn->prepare("
    SELECT 
        a.Id,
        s.firstName,
        s.lastName,
        s.otherName,
        s.admissionNumber,
        c.className,
        ca.classArmName,
        a.sessionTermId,
        t.termName,
        a.status,
        CASE 
            WHEN a.status = 1 THEN 'Present'
            ELSE 'Absent'
        END AS statusText,
        a.dateTimeTaken
    FROM tblattendance a
    INNER JOIN tblstudents s 
        ON s.admissionNumber = a.admissionNo
    INNER JOIN tblclass c 
        ON c.Id = a.classId
    INNER JOIN tblclassarms ca 
        ON ca.Id = a.classArmId
    LEFT JOIN tblsessionterm st 
        ON st.Id = a.sessionTermId
    LEFT JOIN tblterm t 
        ON t.Id = st.termId
    WHERE a.classId = ?
    AND a.classArmId = ?
    AND a.dateTimeTaken = ?
    ORDER BY s.firstName ASC
");

$stmt->bind_param("iis", $classId, $classArmId, $dateTaken);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "message" => count($data) > 0 
        ? "Attendance records found." 
        : "No attendance record found for the selected date.",
    "teacherId" => $teacherId,
    "classId" => $classId,
    "classArmId" => $classArmId,
    "dateTaken" => $dateTaken,
    "data" => $data
]);

$conn->close();
?>
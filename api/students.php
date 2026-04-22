<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../Includes/dbcon.php";
include "auth.php";

$user = validateToken($conn);

$query = "
SELECT 
    s.Id,
    s.firstName,
    s.lastName,
    s.otherName,
    s.admissionNumber,
    c.className,
    ca.classArmName,
    s.dateCreated
FROM tblstudents s
LEFT JOIN tblclass c ON s.classId = c.Id
LEFT JOIN tblclassarms ca ON s.classArmId = ca.Id
ORDER BY s.Id DESC
";

$result = $conn->query($query);

$students = array();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    echo json_encode([
        "success" => true,
        "message" => "Students fetched successfully",
        "authorized_user" => $user,
        "data" => $students
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch students"
    ]);
}

$conn->close();
?>
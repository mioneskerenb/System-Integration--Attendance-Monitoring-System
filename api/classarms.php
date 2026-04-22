<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../Includes/dbcon.php";
include "auth.php";

validateToken($conn);

$query = "SELECT Id, classArmName FROM tblclassarms ORDER BY classArmName ASC";
$result = $conn->query($query);

$classArms = array();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $classArms[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $classArms
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch class arms"
    ]);
}

$conn->close();
?>
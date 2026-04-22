<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../Includes/dbcon.php";
include "auth.php";

validateToken($conn);

$query = "SELECT Id, className FROM tblclass ORDER BY className ASC";
$result = $conn->query($query);

$classes = array();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $classes
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch classes"
    ]);
}

$conn->close();
?>
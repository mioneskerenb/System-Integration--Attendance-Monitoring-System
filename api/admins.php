<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "../Includes/dbcon.php";

$query = "SELECT Id, firstName, lastName, emailAddress FROM tbladmin ORDER BY Id DESC";
$result = $conn->query($query);

$admins = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $admins[] = [
            "Id" => intval($row["Id"]),
            "firstName" => $row["firstName"],
            "lastName" => $row["lastName"],
            "emailAddress" => $row["emailAddress"]
        ];
    }

    echo json_encode([
        "success" => true,
        "message" => "Admins loaded successfully.",
        "data" => $admins
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to load admins: " . $conn->error,
        "data" => []
    ]);
}

$conn->close();
?>
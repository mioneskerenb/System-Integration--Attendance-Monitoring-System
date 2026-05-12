<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include "../Includes/dbcon.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Admin ID is required."
    ]);
    exit();
}

$stmt = $conn->prepare("DELETE FROM tbladmin WHERE Id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Admin deleted successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to delete admin: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
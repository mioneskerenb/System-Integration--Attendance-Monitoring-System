<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../Includes/dbcon.php";

/*
|--------------------------------------------------------------------------
| DELETE TEACHER API
|--------------------------------------------------------------------------
| This is for your C# Admin system.
| No PHP admin login required.
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$deletedBy = isset($_POST['deletedBy']) ? intval($_POST['deletedBy']) : 2;

if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Teacher ID is required."
    ]);
    exit();
}

/*
|--------------------------------------------------------------------------
| If you added MySQL admin-only triggers, keep this.
|--------------------------------------------------------------------------
*/
$conn->query("SET @app_role = 'Administrator'");
$conn->query("SET @app_user_id = " . $deletedBy);

// Check if teacher exists first
$check = $conn->prepare("SELECT Id FROM tblclassteacher WHERE Id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "success" => false,
        "message" => "Teacher record not found."
    ]);
    exit();
}

$check->close();

// Delete teacher
$stmt = $conn->prepare("DELETE FROM tblclassteacher WHERE Id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Teacher deleted successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to delete teacher: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
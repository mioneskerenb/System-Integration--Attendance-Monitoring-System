<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../Includes/dbcon.php";

/*
    Desktop Admin Version:
    We removed auth.php because your WinForms app is not sending an Authorization token.
    If you want token security later, we can add the token to C# instead.
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
$lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
$emailAddress = isset($_POST['emailAddress']) ? trim($_POST['emailAddress']) : '';
$phoneNo = isset($_POST['phoneNo']) ? trim($_POST['phoneNo']) : '';
$classId = isset($_POST['classId']) ? intval($_POST['classId']) : 0;
$classArmId = isset($_POST['classArmId']) ? intval($_POST['classArmId']) : 0;
$updatedBy = isset($_POST['updatedBy']) ? intval($_POST['updatedBy']) : 2;

if (
    $id <= 0 ||
    $firstName === '' ||
    $lastName === '' ||
    $emailAddress === '' ||
    $phoneNo === '' ||
    $classId <= 0 ||
    $classArmId <= 0
) {
    echo json_encode([
        "success" => false,
        "message" => "Please fill in all required fields."
    ]);
    exit();
}

if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email address."
    ]);
    exit();
}

$check = $conn->prepare("SELECT Id FROM tblclassteacher WHERE emailAddress = ? AND Id != ?");
$check->bind_param("si", $emailAddress, $id);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email address already exists."
    ]);
    exit();
}

$check->close();

/*
    Important if you added admin-only MySQL triggers.
*/
$conn->query("SET @app_role = 'Administrator'");
$conn->query("SET @app_user_id = " . $updatedBy);

$stmt = $conn->prepare("
    UPDATE tblclassteacher 
    SET firstName = ?, 
        lastName = ?, 
        emailAddress = ?, 
        phoneNo = ?, 
        classId = ?, 
        classArmId = ?,
        updatedBy = ?,
        lastUpdated = CURDATE()
    WHERE Id = ?
");

$stmt->bind_param(
    "ssssiiii",
    $firstName,
    $lastName,
    $emailAddress,
    $phoneNo,
    $classId,
    $classArmId,
    $updatedBy,
    $id
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Teacher updated successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update teacher: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
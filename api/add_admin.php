<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../Includes/dbcon.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit();
}

$firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
$lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
$emailAddress = isset($_POST['emailAddress']) ? trim($_POST['emailAddress']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';

if (
    $firstName == '' ||
    $lastName == '' ||
    $emailAddress == '' ||
    $password == '' ||
    $confirmPassword == ''
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

if (strlen($password) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters."
    ]);
    exit();
}

if ($password !== $confirmPassword) {
    echo json_encode([
        "success" => false,
        "message" => "Password and confirm password do not match."
    ]);
    exit();
}

$check = $conn->prepare("SELECT Id FROM tbladmin WHERE emailAddress = ?");
$check->bind_param("s", $emailAddress);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Admin email already exists."
    ]);
    exit();
}

$check->close();

/*
    Your admin login checks strtoupper(md5(password)),
    so we save admin password as uppercase MD5.
*/
$hashedPassword = strtoupper(md5($password));

$stmt = $conn->prepare("
    INSERT INTO tbladmin
    (firstName, lastName, emailAddress, password)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "ssss",
    $firstName,
    $lastName,
    $emailAddress,
    $hashedPassword
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Admin added successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to add admin: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
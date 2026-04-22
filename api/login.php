<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../Includes/dbcon.php";

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$userType = $_POST['userType'] ?? 'Administrator';

if (empty($username) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Username and password are required"
    ]);
    exit();
}

$password = strtoupper(md5($password));

if ($userType === "Administrator") {
    $stmt = $conn->prepare("SELECT Id, firstName, lastName, emailAddress FROM tbladmin WHERE emailAddress = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
} elseif ($userType === "ClassTeacher") {
    $stmt = $conn->prepare("SELECT Id, firstName, lastName, emailAddress, classId, classArmId FROM tblclassteacher WHERE emailAddress = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid user type"
    ]);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $token = bin2hex(random_bytes(32));

    $insertToken = $conn->prepare("INSERT INTO api_tokens (user_id, user_type, token) VALUES (?, ?, ?)");
    $insertToken->bind_param("iss", $row['Id'], $userType, $token);
    $insertToken->execute();
    $insertToken->close();

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "token" => $token,
        "userType" => $userType,
        "data" => $row
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid username or password"
    ]);
}

$stmt->close();
$conn->close();
?>
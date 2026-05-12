<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../Includes/dbcon.php";

/*
|--------------------------------------------------------------------------
| ADD TEACHER API
|--------------------------------------------------------------------------
| This is for your C# Admin system.
| Default teacher password will be: pass123
| Stored in database as MD5 so PHP teacher login can read it.
|--------------------------------------------------------------------------
*/

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
$phoneNo = isset($_POST['phoneNo']) ? trim($_POST['phoneNo']) : '';
$classId = isset($_POST['classId']) ? intval($_POST['classId']) : 0;
$classArmId = isset($_POST['classArmId']) ? intval($_POST['classArmId']) : 0;
$createdBy = isset($_POST['createdBy']) ? intval($_POST['createdBy']) : 2;

if (
    $firstName == '' ||
    $lastName == '' ||
    $emailAddress == '' ||
    $phoneNo == '' ||
    $classId == 0 ||
    $classArmId == 0
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

// Check duplicate email
$check = $conn->prepare("SELECT Id FROM tblclassteacher WHERE emailAddress = ?");
$check->bind_param("s", $emailAddress);
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
|--------------------------------------------------------------------------
| DEFAULT PASSWORD
|--------------------------------------------------------------------------
| Teacher will login using: pass123
| Database will store MD5 value of pass123.
|--------------------------------------------------------------------------
*/
$defaultPassword = md5("pass123");

/*
|--------------------------------------------------------------------------
| For your MySQL admin-only trigger, keep this.
|--------------------------------------------------------------------------
*/
$conn->query("SET @app_role = 'Administrator'");
$conn->query("SET @app_user_id = " . $createdBy);

$stmt = $conn->prepare("
    INSERT INTO tblclassteacher
    (
        firstName,
        lastName,
        emailAddress,
        password,
        phoneNo,
        classId,
        classArmId,
        dateCreated,
        createdBy
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)
");

$stmt->bind_param(
    "sssssiii",
    $firstName,
    $lastName,
    $emailAddress,
    $defaultPassword,
    $phoneNo,
    $classId,
    $classArmId,
    $createdBy
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Teacher added successfully. Default password is pass123."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to add teacher: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
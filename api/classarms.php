<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../Includes/dbcon.php";
include "auth.php";

$user = validateToken($conn);
requireRole($user, ["Administrator"]);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$classId = isset($_GET['classId']) ? intval($_GET['classId']) : 0;

$where = [];

if ($classId > 0) {
    $where[] = "ca.classId = '$classId'";
}

if ($search != '') {
    $search = mysqli_real_escape_string($conn, $search);
    $where[] = "(c.className LIKE '%$search%' OR ca.classArmName LIKE '%$search%')";
}

$whereSql = "";

if (count($where) > 0) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

$query = "
SELECT 
    ca.Id,
    ca.classId,
    c.className,
    ca.classArmName,
    ca.isAssigned
FROM tblclassarms ca
LEFT JOIN tblclass c ON ca.classId = c.Id
$whereSql
ORDER BY ca.Id DESC
";

$result = $conn->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch class arms."
    ]);
}

$conn->close();
?>
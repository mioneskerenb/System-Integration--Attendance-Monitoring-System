<?php
function getBearerToken() {
    $headers = null;

    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    if (!$headers) {
        return null;
    }

    if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
    }

    return null;
}

function validateToken($conn) {
    $token = getBearerToken();

    if (!$token) {
        echo json_encode([
            "success" => false,
            "message" => "Authorization token missing"
        ]);
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, user_type, token FROM api_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row;
    } else {
        $stmt->close();
        echo json_encode([
            "success" => false,
            "message" => "Invalid or expired token"
        ]);
        exit();
    }
}
?>
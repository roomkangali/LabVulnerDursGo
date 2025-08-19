<?php
// File: /autentikasi2/bola/users.php
require_once '../_auth_check.php';

$users = [
    1 => ['id' => 1, 'username' => 'alice', 'email' => 'alice@example.com', 'role' => 'user', 'password_hash' => '$2a$10$FxfIkz3aTYzN0dPQvDw4t.WHbtrhcbMN/3nIQUxEie77KcO0FczP2'],
    2 => ['id' => 2, 'username' => 'bob', 'email' => 'bob@example.com', 'role' => 'user', 'password_hash' => '$2a$10$Q5Kzvw7wGjZfaAqswKl2teZWY4WMy30KHTVQmWEaSVvHid8CNdxpS'],
    3 => ['id' => 3, 'username' => 'admin', 'email' => 'admin@example.com', 'role' => 'admin', 'password_hash' => '$2a$10$Ri4MUB0N9KJGWy/VArxJcuquI/J6rDwWmu0gNCX7/hvjcGyX2HQ5a'],
];

header("Content-Type: application/json");

$current_user_id = 1;

$path_info = $_SERVER['PATH_INFO'] ?? '';
$path_parts = explode('/', $path_info);
$requested_id = intval(end($path_parts));

if ($requested_id > 0) {
    if (isset($users[$requested_id])) {
        echo json_encode($users[$requested_id]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
} else {
    echo json_encode(array_values($users));
}
?>

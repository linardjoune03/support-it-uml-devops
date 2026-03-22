<?php
define('API_BASE', 'http://localhost:3000');

function api_get(string $endpoint): ?array {
    $jwt = $_SESSION['jwt'] ?? '';
    $ctx = stream_context_create(['http' => [
        'method'  => 'GET',
        'header'  => "Authorization: Bearer $jwt\r\nContent-Type: application/json",
        'ignore_errors' => true,
    ]]);
    $result = @file_get_contents(API_BASE . $endpoint, false, $ctx);
    return $result ? json_decode($result, true) : null;
}

function api_post(string $endpoint, array $body): ?array {
    $jwt = $_SESSION['jwt'] ?? '';
    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Authorization: Bearer $jwt\r\nContent-Type: application/json",
        'content' => json_encode($body),
        'ignore_errors' => true,
    ]]);
    $result = @file_get_contents(API_BASE . $endpoint, false, $ctx);
    return $result ? json_decode($result, true) : null;
}

function api_patch(string $endpoint, array $body): ?array {
    $jwt = $_SESSION['jwt'] ?? '';
    $ctx = stream_context_create(['http' => [
        'method'  => 'PATCH',
        'header'  => "Authorization: Bearer $jwt\r\nContent-Type: application/json",
        'content' => json_encode($body),
        'ignore_errors' => true,
    ]]);
    $result = @file_get_contents(API_BASE . $endpoint, false, $ctx);
    return $result ? json_decode($result, true) : null;
}

function require_login(): void {
    if (empty($_SESSION['jwt'])) {
        header('Location: login.php');
        exit;
    }
}

function require_role(string $role): void {
    require_login();
    if (($_SESSION['user_role'] ?? '') !== $role) {
        header('Location: index.php');
        exit;
    }
}

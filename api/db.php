<?php
// api/db.php

$dbPath = __DIR__ . '/../db/db.sqlite';

if (!file_exists($dbPath)) {
    http_response_code(500);
    echo "Database not found: {$dbPath}";
    exit;
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');
} catch (Throwable $e) {
    http_response_code(500);
    echo "DB connection failed: " . $e->getMessage();
    exit;
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

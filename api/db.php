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
    ensure_latest_schema($db);
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

function ensure_latest_schema(PDO $db): void
{
    $stmt = $db->query("PRAGMA table_info(items)");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[$row['name']] = true;
    }
    if (!isset($columns['shape_type'])) {
        $db->exec("ALTER TABLE items ADD COLUMN shape_type TEXT NOT NULL DEFAULT 'box'");
    }
}

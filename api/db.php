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
    $requiredColumns = [
        'shape_type'    => "ALTER TABLE items ADD COLUMN shape_type TEXT NOT NULL DEFAULT 'box'",
        'purchase_date' => "ALTER TABLE items ADD COLUMN purchase_date TEXT",
        'material_type' => "ALTER TABLE items ADD COLUMN material_type TEXT",
        'is_archived'   => "ALTER TABLE items ADD COLUMN is_archived INTEGER NOT NULL DEFAULT 0",
        'depleted_at'   => "ALTER TABLE items ADD COLUMN depleted_at TEXT",
    ];
    $added = [];
    foreach ($requiredColumns as $name => $sql) {
        if (!isset($columns[$name])) {
            $db->exec($sql);
            $added[] = $name;
        }
    }

    if (in_array('is_archived', $added, true) || in_array('depleted_at', $added, true)) {
        $db->exec("UPDATE items SET is_archived = 1 WHERE qty <= 0");
        $db->exec("UPDATE items SET depleted_at = COALESCE(depleted_at, updated_at, created_at) WHERE is_archived = 1 AND depleted_at IS NULL");
    }
}

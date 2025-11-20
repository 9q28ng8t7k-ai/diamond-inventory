<?php
// db/init_db.php
// 從專案根目錄執行：php db/init_db.php

require_once __DIR__ . '/../api/db.php';

try {
    $db = create_database_if_missing($dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');
    ensure_latest_schema($db);
    echo "Database created/updated: {$dbPath}\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Initialization failed: " . $e->getMessage() . "\n");
    exit(1);
}

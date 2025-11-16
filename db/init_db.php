<?php
// db/init_db.php
// 從專案根目錄執行：php db/init_db.php

$dbFile = __DIR__ . '/db.sqlite';

if (file_exists($dbFile)) {
    echo "Database already exists: {$dbFile}\n";
} else {
    echo "Creating database: {$dbFile}\n";
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 開啟外鍵
$pdo->exec('PRAGMA foreign_keys = ON;');

// 建 items 表（原料批次）
$pdo->exec("
CREATE TABLE IF NOT EXISTS items (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor      TEXT NOT NULL,
    size_str    TEXT NOT NULL,
    length      REAL NOT NULL,
    width       REAL NOT NULL,
    height      REAL NOT NULL,
    qty         INTEGER NOT NULL DEFAULT 0,
    unit_price  REAL,
    note        TEXT,
    created_at  TEXT,
    updated_at  TEXT
);
");

// 建 withdrawals 表（領料紀錄）
$pdo->exec("
CREATE TABLE IF NOT EXISTS withdrawals (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id     INTEGER NOT NULL,
    qty         INTEGER NOT NULL,
    purpose     TEXT,
    created_at  TEXT,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);
");

echo "Database created/updated: {$dbFile}\n";

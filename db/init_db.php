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
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor         TEXT NOT NULL,
    size_str       TEXT NOT NULL,
    length         REAL NOT NULL,
    width          REAL NOT NULL,
    height         REAL NOT NULL,
    qty            INTEGER NOT NULL DEFAULT 0,
    unit_price     REAL,
    note           TEXT,
    shape_type     TEXT NOT NULL DEFAULT 'box',
    purchase_date  TEXT,
    material_type  TEXT,
    is_archived    INTEGER NOT NULL DEFAULT 0,
    depleted_at    TEXT,
    created_at     TEXT,
    updated_at     TEXT
);
");

// 若舊版資料庫缺少欄位，就補上
$columns = $pdo->query("PRAGMA table_info(items)")->fetchAll(PDO::FETCH_ASSOC);
$columnMap = [];
foreach ($columns as $col) {
    $columnMap[$col['name']] = true;
}

$requiredColumns = [
    'shape_type'    => "ALTER TABLE items ADD COLUMN shape_type TEXT NOT NULL DEFAULT 'box'",
    'purchase_date' => "ALTER TABLE items ADD COLUMN purchase_date TEXT",
    'material_type' => "ALTER TABLE items ADD COLUMN material_type TEXT",
    'is_archived'   => "ALTER TABLE items ADD COLUMN is_archived INTEGER NOT NULL DEFAULT 0",
    'depleted_at'   => "ALTER TABLE items ADD COLUMN depleted_at TEXT",
];

foreach ($requiredColumns as $name => $sql) {
    if (!isset($columnMap[$name])) {
        $pdo->exec($sql);
    }
}

// 依現有庫存狀態補齊歷史欄位
$pdo->exec("UPDATE items SET is_archived = 1 WHERE qty <= 0");
$pdo->exec("UPDATE items SET depleted_at = COALESCE(depleted_at, updated_at, created_at) WHERE is_archived = 1 AND depleted_at IS NULL");

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

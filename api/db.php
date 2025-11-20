<?php
// api/db.php

$dbPath = __DIR__ . '/../db/db.sqlite';

try {
    $db = create_database_if_missing($dbPath);
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
    if (!table_exists($db, 'items')) {
        create_base_schema($db);
        return;
    }

    $columns = column_map($db, 'items');
    $requiredColumns = [
        'shape_type'    => "ALTER TABLE items ADD COLUMN shape_type TEXT NOT NULL DEFAULT 'box'",
        'purchase_date' => "ALTER TABLE items ADD COLUMN purchase_date TEXT",
        'material_type' => "ALTER TABLE items ADD COLUMN material_type TEXT",
        'is_archived'   => "ALTER TABLE items ADD COLUMN is_archived INTEGER NOT NULL DEFAULT 0",
        'depleted_at'   => "ALTER TABLE items ADD COLUMN depleted_at TEXT",
        'unit_price_foreign' => "ALTER TABLE items ADD COLUMN unit_price_foreign REAL",
        'currency_code' => "ALTER TABLE items ADD COLUMN currency_code TEXT",
        'exchange_rate' => "ALTER TABLE items ADD COLUMN exchange_rate REAL",
        'unit_price_twd'=> "ALTER TABLE items ADD COLUMN unit_price_twd REAL",
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
    if (in_array('unit_price_twd', $added, true)) {
        $db->exec("UPDATE items SET unit_price_twd = unit_price WHERE unit_price_twd IS NULL");
    }

    if (!table_exists($db, 'withdrawals')) {
        create_withdrawals_table($db);
    }
}

function create_database_if_missing(string $dbPath): PDO
{
    $dbDir = dirname($dbPath);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0777, true);
    }

    $isNew = !file_exists($dbPath);
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    if ($isNew) {
        create_base_schema($pdo);
    }

    return $pdo;
}

function create_base_schema(PDO $db): void
{
    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS items (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor         TEXT NOT NULL,
    size_str       TEXT NOT NULL,
    length         REAL NOT NULL,
    width          REAL NOT NULL,
    height         REAL NOT NULL,
    qty            INTEGER NOT NULL DEFAULT 0,
    unit_price     REAL,
    unit_price_foreign REAL,
    currency_code  TEXT,
    exchange_rate  REAL,
    unit_price_twd REAL,
    note           TEXT,
    shape_type     TEXT NOT NULL DEFAULT 'box',
    purchase_date  TEXT,
    material_type  TEXT,
    is_archived    INTEGER NOT NULL DEFAULT 0,
    depleted_at    TEXT,
    created_at     TEXT,
    updated_at     TEXT
);
SQL);

    create_withdrawals_table($db);
}

function create_withdrawals_table(PDO $db): void
{
    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS withdrawals (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id     INTEGER NOT NULL,
    qty         INTEGER NOT NULL,
    purpose     TEXT,
    created_at  TEXT,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);
SQL);
}

function table_exists(PDO $db, string $table): bool
{
    $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :name");
    $stmt->execute([':name' => $table]);
    return (bool)$stmt->fetchColumn();
}

function column_map(PDO $db, string $table): array
{
    $stmt = $db->query("PRAGMA table_info({$table})");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[$row['name']] = true;
    }
    return $columns;
}

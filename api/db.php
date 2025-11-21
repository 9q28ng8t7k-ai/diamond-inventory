<?php
// Database bootstrap and schema management

$dbPath = __DIR__ . '/../db/db.sqlite';

try {
    $db = create_database_if_missing($dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');
    ensure_latest_schema($db);
} catch (Throwable $e) {
    http_response_code(500);
    echo 'DB connection failed: ' . $e->getMessage();
    exit;
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function create_database_if_missing(string $dbPath): PDO
{
    $dir = dirname($dbPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
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

function ensure_latest_schema(PDO $db): void
{
    if (!table_exists($db, 'items')) {
        create_base_schema($db);
        return;
    }

    // incremental columns
    $columns = column_map($db, 'items');
    $addColumns = [
        'size_str'        => "ALTER TABLE items ADD COLUMN size_str TEXT NOT NULL DEFAULT ''",
        'length'          => "ALTER TABLE items ADD COLUMN length REAL NOT NULL DEFAULT 0",
        'width'           => "ALTER TABLE items ADD COLUMN width REAL NOT NULL DEFAULT 0",
        'height'          => "ALTER TABLE items ADD COLUMN height REAL NOT NULL DEFAULT 0",
        'qty'             => "ALTER TABLE items ADD COLUMN qty INTEGER NOT NULL DEFAULT 0",
        'unit_price_twd'  => "ALTER TABLE items ADD COLUMN unit_price_twd REAL",
        'unit_price_foreign' => "ALTER TABLE items ADD COLUMN unit_price_foreign REAL",
        'currency_code'   => "ALTER TABLE items ADD COLUMN currency_code TEXT",
        'exchange_rate'   => "ALTER TABLE items ADD COLUMN exchange_rate REAL",
        'note'            => "ALTER TABLE items ADD COLUMN note TEXT",
        'shape_type'      => "ALTER TABLE items ADD COLUMN shape_type TEXT NOT NULL DEFAULT 'box'",
        'purchase_date'   => "ALTER TABLE items ADD COLUMN purchase_date TEXT",
        'material_type'   => "ALTER TABLE items ADD COLUMN material_type TEXT",
        'is_archived'     => "ALTER TABLE items ADD COLUMN is_archived INTEGER NOT NULL DEFAULT 0",
        'depleted_at'     => "ALTER TABLE items ADD COLUMN depleted_at TEXT",
        'serial_no'       => "ALTER TABLE items ADD COLUMN serial_no TEXT",
        'created_at'      => "ALTER TABLE items ADD COLUMN created_at TEXT",
        'updated_at'      => "ALTER TABLE items ADD COLUMN updated_at TEXT",
    ];

    foreach ($addColumns as $name => $sql) {
        if (!isset($columns[$name])) {
            $db->exec($sql);
        }
    }

    // ensure withdrawals table
    if (!table_exists($db, 'withdrawals')) {
        create_withdrawals_table($db);
    } else {
        $wColumns = column_map($db, 'withdrawals');
        if (!isset($wColumns['updated_at'])) {
            $db->exec("ALTER TABLE withdrawals ADD COLUMN updated_at TEXT");
        }
        if (!isset($wColumns['purpose'])) {
            // older schema might already have it; ignore errors
            try {
                $db->exec("ALTER TABLE withdrawals ADD COLUMN purpose TEXT");
            } catch (Throwable $e) {
                // ignore
            }
        }
    }

    // ensure unique index for serial_no
    $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='index' AND name='idx_items_serial_no'");
    $stmt->execute();
    if (!$stmt->fetchColumn()) {
        $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_items_serial_no ON items(serial_no) WHERE serial_no IS NOT NULL");
    }

    // Backfill timestamps and derived fields
    $now = gmdate('Y-m-d H:i:s');
    $db->exec("UPDATE items SET created_at = COALESCE(created_at, '{$now}')");
    $db->exec("UPDATE items SET updated_at = COALESCE(updated_at, created_at, '{$now}')");
    $db->exec("UPDATE items SET size_str = CASE WHEN size_str = '' OR size_str IS NULL THEN TRIM(length || '×' || width || '×' || height) ELSE size_str END");
    $db->exec("UPDATE items SET unit_price_twd = COALESCE(unit_price_twd, unit_price_foreign * exchange_rate, unit_price_twd)");
    $db->exec("UPDATE items SET is_archived = CASE WHEN qty <= 0 THEN 1 ELSE is_archived END");
    $db->exec("UPDATE items SET depleted_at = CASE WHEN is_archived = 1 AND qty <= 0 THEN COALESCE(depleted_at, updated_at) ELSE depleted_at END");
}

function create_base_schema(PDO $db): void
{
    $db->exec(<<<'SQL'
CREATE TABLE items (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor             TEXT NOT NULL,
    serial_no          TEXT UNIQUE,
    size_str           TEXT NOT NULL,
    length             REAL NOT NULL,
    width              REAL NOT NULL,
    height             REAL NOT NULL,
    qty                INTEGER NOT NULL DEFAULT 0,
    unit_price_twd     REAL,
    unit_price_foreign REAL,
    currency_code      TEXT,
    exchange_rate      REAL,
    note               TEXT,
    shape_type         TEXT NOT NULL DEFAULT 'box',
    purchase_date      TEXT,
    material_type      TEXT,
    is_archived        INTEGER NOT NULL DEFAULT 0,
    depleted_at        TEXT,
    created_at         TEXT,
    updated_at         TEXT
);
SQL);

    $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_items_serial_no ON items(serial_no) WHERE serial_no IS NOT NULL");
    create_withdrawals_table($db);
}

function create_withdrawals_table(PDO $db): void
{
    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS withdrawals (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id    INTEGER NOT NULL,
    qty        INTEGER NOT NULL,
    purpose    TEXT,
    created_at TEXT,
    updated_at TEXT,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);
SQL);
}

function table_exists(PDO $db, string $name): bool
{
    $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:name");
    $stmt->execute([':name' => $name]);
    return (bool)$stmt->fetchColumn();
}

function column_map(PDO $db, string $table): array
{
    $stmt = $db->query("PRAGMA table_info({$table})");
    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $map[$row['name']] = true;
    }
    return $map;
}

function refresh_archive_status(PDO $db, int $itemId): void
{
    $stmt = $db->prepare('SELECT qty, is_archived, depleted_at FROM items WHERE id = :id');
    $stmt->execute([':id' => $itemId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return;
    }
    $now = gmdate('Y-m-d H:i:s');
    if ((int)$row['qty'] <= 0) {
        $depleted = $row['depleted_at'] ?: $now;
        $db->prepare('UPDATE items SET is_archived = 1, depleted_at = :depleted_at, updated_at = :updated_at WHERE id = :id')
           ->execute([':depleted_at' => $depleted, ':updated_at' => $now, ':id' => $itemId]);
    } else {
        $db->prepare('UPDATE items SET is_archived = 0, depleted_at = NULL, updated_at = :updated_at WHERE id = :id')
           ->execute([':updated_at' => $now, ':id' => $itemId]);
    }
}
?>

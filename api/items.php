<?php
// Items CRUD and listing
require __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        list_items($db);
        break;
    case 'POST':
        save_item($db);
        break;
    case 'DELETE':
        delete_item($db);
        break;
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function list_items(PDO $db): void
{
    $sql = <<<SQL
SELECT i.*, IFNULL(SUM(w.qty), 0) AS withdrawn_qty
FROM items i
LEFT JOIN withdrawals w ON w.item_id = i.id
GROUP BY i.id
ORDER BY i.is_archived ASC, i.updated_at DESC, i.id DESC
SQL;
    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_response($rows);
}

function save_item(PDO $db): void
{
    $data = json_decode(file_get_contents('php://input'), true) ?: [];

    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $vendor = trim((string)($data['vendor'] ?? ''));
    $shape = ($data['shape_type'] ?? 'box') === 'cylinder' ? 'cylinder' : 'box';
    $length = (float)($data['length'] ?? 0);
    $width = (float)($data['width'] ?? 0);
    $height = (float)($data['height'] ?? 0);
    $qty = (int)($data['qty'] ?? 0);
    $unitPriceForeign = isset($data['unit_price_foreign']) && $data['unit_price_foreign'] !== '' ? (float)$data['unit_price_foreign'] : null;
    $exchangeRate = isset($data['exchange_rate']) && $data['exchange_rate'] !== '' ? (float)$data['exchange_rate'] : null;
    $unitPriceTwd = isset($data['unit_price_twd']) && $data['unit_price_twd'] !== '' ? (float)$data['unit_price_twd'] : null;
    $currencyCode = array_key_exists('currency_code', $data) ? strtoupper(trim((string)$data['currency_code'])) : null;
    $note = trim((string)($data['note'] ?? ''));
    $purchaseDate = trim((string)($data['purchase_date'] ?? ''));
    $materialType = $data['material_type'] ?? null;
    $serialNo = trim((string)($data['serial_no'] ?? ''));

    $errors = [];
    if ($vendor === '') {
        $errors[] = 'vendor required';
    }
    if ($shape === 'box') {
        if ($length <= 0 || $width <= 0 || $height <= 0) {
            $errors[] = 'invalid dimension';
        }
    } else {
        if ($length <= 0 || $height <= 0) {
            $errors[] = 'invalid dimension';
        }
        $width = $length; // cylinder uses diameter as length/width
    }
    if ($qty < 0) {
        $errors[] = 'qty must be >= 0';
    }
    if ($unitPriceForeign !== null && $unitPriceForeign < 0) {
        $errors[] = 'unit_price_foreign must be >= 0';
    }
    if ($exchangeRate !== null && $exchangeRate < 0) {
        $errors[] = 'exchange_rate must be >= 0';
    }
    if ($unitPriceTwd !== null && $unitPriceTwd < 0) {
        $errors[] = 'unit_price_twd must be >= 0';
    }
    if ($currencyCode !== null && $currencyCode !== '' && !preg_match('/^[A-Z]{2,10}$/', $currencyCode)) {
        $errors[] = 'invalid currency_code';
    }
    if ($purchaseDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $purchaseDate)) {
        $errors[] = 'invalid purchase_date';
    }
    if ($materialType !== null && $materialType !== '') {
        $materialType = strtolower(trim((string)$materialType));
        if (!in_array($materialType, ['hpht', 'cvd'], true)) {
            $errors[] = 'invalid material_type';
        }
    } else {
        $materialType = null;
    }
    if ($serialNo === '') {
        $serialNo = null;
    }

    if ($errors) {
        json_response(['error' => implode('; ', $errors)], 400);
    }

    $existing = null;
    if ($id > 0) {
        $stmt = $db->prepare('SELECT * FROM items WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            json_response(['error' => 'item not found'], 404);
        }
    }

    // Keep previous values when not supplied
    if ($existing) {
        if ($unitPriceForeign === null && array_key_exists('unit_price_foreign', $data) === false) {
            $unitPriceForeign = $existing['unit_price_foreign'];
        }
        if ($exchangeRate === null && array_key_exists('exchange_rate', $data) === false) {
            $exchangeRate = $existing['exchange_rate'];
        }
        if ($unitPriceTwd === null && array_key_exists('unit_price_twd', $data) === false) {
            $unitPriceTwd = $existing['unit_price_twd'];
        }
        if ($currencyCode === null && array_key_exists('currency_code', $data) === false) {
            $currencyCode = $existing['currency_code'];
        }
        if ($serialNo === null && array_key_exists('serial_no', $data) === false) {
            $serialNo = $existing['serial_no'];
        }
    }

    if ($unitPriceTwd === null && $unitPriceForeign !== null && $exchangeRate !== null) {
        $unitPriceTwd = $unitPriceForeign * $exchangeRate;
    }

    $sizeStr = format_size_string($shape, $length, $width, $height);
    $now = gmdate('Y-m-d H:i:s');
    $isArchived = $qty <= 0 ? 1 : 0;
    $depletedAt = $isArchived ? ($existing['depleted_at'] ?? $now) : null;

    // serial no uniqueness
    if ($serialNo !== null) {
        $stmt = $db->prepare('SELECT id FROM items WHERE serial_no = :serial_no AND (:id IS NULL OR id != :id)');
        $stmt->execute([':serial_no' => $serialNo, ':id' => $id ?: null]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            json_response(['error' => 'serial_no already exists'], 409);
        }
    }

    if ($id > 0) {
        $stmt = $db->prepare(<<<'SQL'
UPDATE items
SET vendor = :vendor,
    serial_no = COALESCE(:serial_no, serial_no),
    size_str = :size_str,
    length = :length,
    width = :width,
    height = :height,
    qty = :qty,
    unit_price_twd = :unit_price_twd,
    unit_price_foreign = :unit_price_foreign,
    currency_code = :currency_code,
    exchange_rate = :exchange_rate,
    note = :note,
    shape_type = :shape_type,
    purchase_date = :purchase_date,
    material_type = :material_type,
    is_archived = :is_archived,
    depleted_at = :depleted_at,
    updated_at = :updated_at
WHERE id = :id
SQL);
        $stmt->execute([
            ':vendor' => $vendor,
            ':serial_no' => $serialNo,
            ':size_str' => $sizeStr,
            ':length' => $length,
            ':width' => $width,
            ':height' => $height,
            ':qty' => $qty,
            ':unit_price_twd' => $unitPriceTwd,
            ':unit_price_foreign' => $unitPriceForeign,
            ':currency_code' => $currencyCode,
            ':exchange_rate' => $exchangeRate,
            ':note' => $note,
            ':shape_type' => $shape,
            ':purchase_date' => $purchaseDate !== '' ? $purchaseDate : null,
            ':material_type' => $materialType,
            ':is_archived' => $isArchived,
            ':depleted_at' => $depletedAt,
            ':updated_at' => $now,
            ':id' => $id,
        ]);
    } else {
        $stmt = $db->prepare(<<<'SQL'
INSERT INTO items (vendor, serial_no, size_str, length, width, height, qty, unit_price_twd, unit_price_foreign, currency_code, exchange_rate, note, shape_type, purchase_date, material_type, is_archived, depleted_at, created_at, updated_at)
VALUES (:vendor, :serial_no, :size_str, :length, :width, :height, :qty, :unit_price_twd, :unit_price_foreign, :currency_code, :exchange_rate, :note, :shape_type, :purchase_date, :material_type, :is_archived, :depleted_at, :created_at, :updated_at)
SQL);
        $stmt->execute([
            ':vendor' => $vendor,
            ':serial_no' => $serialNo,
            ':size_str' => $sizeStr,
            ':length' => $length,
            ':width' => $width,
            ':height' => $height,
            ':qty' => $qty,
            ':unit_price_twd' => $unitPriceTwd,
            ':unit_price_foreign' => $unitPriceForeign,
            ':currency_code' => $currencyCode,
            ':exchange_rate' => $exchangeRate,
            ':note' => $note,
            ':shape_type' => $shape,
            ':purchase_date' => $purchaseDate !== '' ? $purchaseDate : null,
            ':material_type' => $materialType,
            ':is_archived' => $isArchived,
            ':depleted_at' => $depletedAt,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
        $id = (int)$db->lastInsertId();
    }

    refresh_archive_status($db, $id);

    $item = fetch_item($db, $id);
    json_response($item);
}

function delete_item(PDO $db): void
{
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        json_response(['error' => 'id required'], 400);
    }

    $db->beginTransaction();
    try {
        $db->prepare('DELETE FROM withdrawals WHERE item_id = :id')->execute([':id' => $id]);
        $stmt = $db->prepare('DELETE FROM items WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $db->commit();
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        $db->rollBack();
        json_response(['error' => $e->getMessage()], 500);
    }
}

function fetch_item(PDO $db, int $id): array
{
    $stmt = $db->prepare(<<<'SQL'
SELECT i.*, IFNULL(SUM(w.qty), 0) AS withdrawn_qty
FROM items i
LEFT JOIN withdrawals w ON w.item_id = i.id
WHERE i.id = :id
GROUP BY i.id
SQL);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: [];
}

function format_size_string(string $shape, float $length, float $width, float $height): string
{
    $fmt = fn($num) => rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
    if ($shape === 'cylinder') {
        return 'Ø' . $fmt($length) . '×' . $fmt($height);
    }
    return $fmt($length) . '×' . $fmt($width) . '×' . $fmt($height);
}
?>

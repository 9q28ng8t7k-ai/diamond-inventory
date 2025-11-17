<?php
// api/items.php
require __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET：列出全部批次 + 已領出總數
if ($method === 'GET') {
    $stmt = $db->query("
        SELECT i.*,
               IFNULL(SUM(w.qty), 0) AS withdrawn_qty
        FROM items i
        LEFT JOIN withdrawals w ON w.item_id = i.id
        GROUP BY i.id
        ORDER BY i.is_archived ASC, i.updated_at DESC, i.id DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_response($rows);
}

// POST：新增或更新一個批次
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_response(['error' => 'Invalid JSON'], 400);
    }

    $id        = isset($data['id']) ? (int)$data['id'] : 0;
    $vendor    = trim($data['vendor'] ?? '');
    $sizeStr   = trim($data['size_str'] ?? '');
    $length    = (float)($data['length'] ?? 0);
    $width     = (float)($data['width'] ?? 0);
    $height    = (float)($data['height'] ?? 0);
    $qty       = (int)($data['qty'] ?? 0);
    $hasUnitPriceLegacy = array_key_exists('unit_price', $data);
    $unitPriceLegacy = null;
    if ($hasUnitPriceLegacy && $data['unit_price'] !== '') {
        $unitPriceLegacy = (float)$data['unit_price'];
    }
    $hasUnitPriceForeign = array_key_exists('unit_price_foreign', $data);
    $unitPriceForeign = null;
    if ($hasUnitPriceForeign && $data['unit_price_foreign'] !== '') {
        $unitPriceForeign = (float)$data['unit_price_foreign'];
    }
    $hasCurrencyCode = array_key_exists('currency_code', $data);
    $currencyCode = null;
    if ($hasCurrencyCode) {
        $currencyCode = strtoupper(trim((string)$data['currency_code']));
        if ($currencyCode === '') {
            $currencyCode = null;
        }
    }
    $hasExchangeRate = array_key_exists('exchange_rate', $data);
    $exchangeRate = null;
    if ($hasExchangeRate && $data['exchange_rate'] !== '') {
        $exchangeRate = (float)$data['exchange_rate'];
    }
    $hasUnitPriceTwd = array_key_exists('unit_price_twd', $data);
    $unitPriceTwd = null;
    if ($hasUnitPriceTwd && $data['unit_price_twd'] !== '') {
        $unitPriceTwd = (float)$data['unit_price_twd'];
    }
    $note      = trim($data['note'] ?? '');
    $shapeType = $data['shape_type'] ?? 'box';
    $purchaseDate = trim($data['purchase_date'] ?? '');
    $materialType = $data['material_type'] ?? null;

    $errors = [];
    if ($vendor === '') $errors[] = 'vendor required';
    if ($sizeStr === '') $errors[] = 'size_str required';
    if (!in_array($shapeType, ['box', 'cylinder'], true)) $shapeType = 'box';
    if ($shapeType === 'box') {
        if ($length <= 0 || $width <= 0 || $height <= 0) $errors[] = 'invalid dimension';
    } else {
        if ($length <= 0 || $height <= 0) $errors[] = 'invalid dimension';
        // 圓柱時 width 與 length 同值
        $width = $length;
    }
    if ($qty < 0) $errors[] = 'qty must be >= 0';
    if ($unitPriceForeign !== null && $unitPriceForeign < 0) $errors[] = 'unit_price_foreign must be >= 0';
    if ($exchangeRate !== null && $exchangeRate <= 0) $errors[] = 'exchange_rate must be > 0';
    if ($unitPriceTwd !== null && $unitPriceTwd < 0) $errors[] = 'unit_price_twd must be >= 0';
    if ($currencyCode !== null && !preg_match('/^[A-Z]{2,5}$/', $currencyCode)) {
        $errors[] = 'invalid currency_code';
    }
    if ($purchaseDate !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $purchaseDate)) {
            $errors[] = 'invalid purchase_date';
        }
    } else {
        $purchaseDate = null;
    }
    if ($materialType !== null) {
        $materialType = strtolower(trim((string)$materialType));
        if ($materialType === '') {
            $materialType = null;
        } elseif (!in_array($materialType, ['hpht', 'cvd'], true)) {
            $errors[] = 'invalid material_type';
        }
    }

    if ($errors) {
        json_response(['error' => implode('; ', $errors)], 400);
    }

    $now = gmdate('Y-m-d H:i:s');

    $existing = null;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT * FROM items WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            json_response(['error' => 'item not found'], 404);
        }
    }

    $isArchived = 0;
    $depletedAt = null;
    if ($existing) {
        $isArchived = (int)$existing['is_archived'];
        $depletedAt = $existing['depleted_at'] ?? null;

        $preserveCurrencyField = function ($hasField, $value, $column) use ($existing) {
            if ($hasField) {
                return $value;
            }
            return array_key_exists($column, $existing) ? $existing[$column] : null;
        };

        $unitPriceForeign = $preserveCurrencyField($hasUnitPriceForeign, $unitPriceForeign, 'unit_price_foreign');
        $currencyCode     = $preserveCurrencyField($hasCurrencyCode, $currencyCode, 'currency_code');
        $exchangeRate     = $preserveCurrencyField($hasExchangeRate, $exchangeRate, 'exchange_rate');
        $unitPriceTwd     = $preserveCurrencyField($hasUnitPriceTwd, $unitPriceTwd, 'unit_price_twd');
    }
    if ($qty <= 0) {
        $isArchived = 1;
        if (!$depletedAt) {
            $depletedAt = $now;
        }
    } else {
        $isArchived = 0;
        $depletedAt = null;
    }

    if ($unitPriceTwd === null && $unitPriceLegacy !== null) {
        $unitPriceTwd = $unitPriceLegacy;
    }
    if ($unitPriceTwd === null && $unitPriceForeign !== null && $exchangeRate !== null) {
        $unitPriceTwd = $unitPriceForeign * $exchangeRate;
    }
    if ($unitPriceTwd === null && $existing && !$hasUnitPriceLegacy && !$hasUnitPriceForeign && !$hasUnitPriceTwd) {
        $unitPriceTwd = $existing['unit_price_twd'] ?? $existing['unit_price'];
    }
    $unitPrice = $unitPriceTwd;

    if ($id > 0) {
        // 更新
        $stmt = $db->prepare("
            UPDATE items
            SET vendor        = :vendor,
                size_str      = :size_str,
                length        = :length,
                width         = :width,
                height        = :height,
                qty           = :qty,
                unit_price    = :unit_price,
                unit_price_foreign = :unit_price_foreign,
                currency_code = :currency_code,
                exchange_rate = :exchange_rate,
                unit_price_twd = :unit_price_twd,
                note          = :note,
                shape_type    = :shape_type,
                purchase_date = :purchase_date,
                material_type = :material_type,
                is_archived   = :is_archived,
                depleted_at   = :depleted_at,
                updated_at    = :updated_at
            WHERE id = :id
        ");
        $stmt->execute([
            ':vendor'        => $vendor,
            ':size_str'      => $sizeStr,
            ':length'        => $length,
            ':width'         => $width,
            ':height'        => $height,
            ':qty'           => $qty,
            ':unit_price'    => $unitPrice,
            ':unit_price_foreign' => $unitPriceForeign,
            ':currency_code' => $currencyCode,
            ':exchange_rate' => $exchangeRate,
            ':unit_price_twd'=> $unitPriceTwd,
            ':note'          => $note,
            ':shape_type'    => $shapeType,
            ':purchase_date' => $purchaseDate,
            ':material_type' => $materialType,
            ':is_archived'   => $isArchived,
            ':depleted_at'   => $depletedAt,
            ':updated_at'    => $now,
            ':id'            => $id,
        ]);
    } else {
        // 新增
        $stmt = $db->prepare("
            INSERT INTO items
            (vendor, size_str, length, width, height, qty, unit_price, unit_price_foreign, currency_code, exchange_rate, unit_price_twd, note, shape_type, purchase_date, material_type, is_archived, depleted_at, created_at, updated_at)
            VALUES
            (:vendor, :size_str, :length, :width, :height, :qty, :unit_price, :unit_price_foreign, :currency_code, :exchange_rate, :unit_price_twd, :note, :shape_type, :purchase_date, :material_type, :is_archived, :depleted_at, :created_at, :updated_at)
        ");
        $stmt->execute([
            ':vendor'        => $vendor,
            ':size_str'      => $sizeStr,
            ':length'        => $length,
            ':width'         => $width,
            ':height'        => $height,
            ':qty'           => $qty,
            ':unit_price'    => $unitPrice,
            ':unit_price_foreign' => $unitPriceForeign,
            ':currency_code' => $currencyCode,
            ':exchange_rate' => $exchangeRate,
            ':unit_price_twd'=> $unitPriceTwd,
            ':note'          => $note,
            ':shape_type'    => $shapeType,
            ':purchase_date' => $purchaseDate,
            ':material_type' => $materialType,
            ':is_archived'   => $isArchived,
            ':depleted_at'   => $depletedAt,
            ':created_at'    => $now,
            ':updated_at'    => $now,
        ]);
        $id = (int)$db->lastInsertId();
    }

    $stmt = $db->prepare("SELECT * FROM items WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    json_response($item);
}

// DELETE：刪除批次（會連同領料紀錄一起刪）
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) json_response(['error' => 'invalid id'], 400);

    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM withdrawals WHERE item_id = :id");
    $stmt->execute([':id' => $id]);

    $stmt = $db->prepare("DELETE FROM items WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $db->commit();
    json_response(['ok' => true]);
}

    json_response(['error' => 'Method not allowed'], 405);


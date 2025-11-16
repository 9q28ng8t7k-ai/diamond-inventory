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
        ORDER BY i.updated_at DESC, i.id DESC
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
    $unitPrice = null;
    if (isset($data['unit_price']) && $data['unit_price'] !== '') {
        $unitPrice = (float)$data['unit_price'];
    }
    $note      = trim($data['note'] ?? '');
    $shapeType = $data['shape_type'] ?? 'box';

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

    if ($errors) {
        json_response(['error' => implode('; ', $errors)], 400);
    }

    $now = gmdate('Y-m-d H:i:s');

    if ($id > 0) {
        // 更新（目前前端不會用）
        $stmt = $db->prepare("
            UPDATE items
            SET vendor     = :vendor,
                size_str   = :size_str,
                length     = :length,
                width      = :width,
                height     = :height,
                qty        = :qty,
                unit_price = :unit_price,
                note       = :note,
                shape_type = :shape_type,
                updated_at = :updated_at
            WHERE id = :id
        ");
        $stmt->execute([
            ':vendor'     => $vendor,
            ':size_str'   => $sizeStr,
            ':length'     => $length,
            ':width'      => $width,
            ':height'     => $height,
            ':qty'        => $qty,
            ':unit_price' => $unitPrice,
            ':note'       => $note,
            ':shape_type' => $shapeType,
            ':updated_at' => $now,
            ':id'         => $id,
        ]);
    } else {
        // 新增
        $stmt = $db->prepare("
            INSERT INTO items
            (vendor, size_str, length, width, height, qty, unit_price, note, shape_type, created_at, updated_at)
            VALUES
            (:vendor, :size_str, :length, :width, :height, :qty, :unit_price, :note, :shape_type, :created_at, :updated_at)
        ");
        $stmt->execute([
            ':vendor'     => $vendor,
            ':size_str'   => $sizeStr,
            ':length'     => $length,
            ':width'      => $width,
            ':height'     => $height,
            ':qty'        => $qty,
            ':unit_price' => $unitPrice,
            ':note'       => $note,
            ':shape_type' => $shapeType,
            ':created_at' => $now,
            ':updated_at' => $now,
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

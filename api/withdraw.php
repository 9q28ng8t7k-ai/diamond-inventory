<?php
// api/withdraw.php
require __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET：列出某批次的領料紀錄
if ($method === 'GET') {
    $itemId = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
    if ($itemId <= 0) json_response(['error' => 'invalid item_id'], 400);

    $stmt = $db->prepare("
        SELECT * FROM withdrawals
        WHERE item_id = :id
        ORDER BY created_at DESC, id DESC
    ");
    $stmt->execute([':id' => $itemId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_response($rows);
}

// POST：新增一筆領料 + 扣庫存
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_response(['error' => 'Invalid JSON'], 400);
    }

    $itemId  = (int)($data['item_id'] ?? 0);
    $qty     = (int)($data['qty'] ?? 0);
    $purpose = trim($data['purpose'] ?? '');

    if ($itemId <= 0 || $qty <= 0) {
        json_response(['error' => 'invalid item_id or qty'], 400);
    }

    // 查庫存
    $stmt = $db->prepare("SELECT qty FROM items WHERE id = :id");
    $stmt->execute([':id' => $itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) json_response(['error' => 'item not found'], 404);

    $currentQty = (int)$item['qty'];
    if ($qty > $currentQty) {
        json_response(['error' => 'not enough stock'], 400);
    }

    $now = gmdate('Y-m-d H:i:s');

    $db->beginTransaction();

    // 新增領料紀錄
    $stmt = $db->prepare("
        INSERT INTO withdrawals (item_id, qty, purpose, created_at)
        VALUES (:item_id, :qty, :purpose, :created_at)
    ");
    $stmt->execute([
        ':item_id'   => $itemId,
        ':qty'       => $qty,
        ':purpose'   => $purpose,
        ':created_at'=> $now,
    ]);

    // 扣庫存
    $stmt = $db->prepare("
        UPDATE items
        SET qty = qty - :qty,
            updated_at = :updated_at
        WHERE id = :id
    ");
    $stmt->execute([
        ':qty'        => $qty,
        ':updated_at' => $now,
        ':id'         => $itemId,
    ]);

    $db->commit();
    json_response(['ok' => true]);
}

// PUT：修改一筆領料的用途（不動數量）
if ($method === 'PUT') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_response(['error' => 'Invalid JSON'], 400);
    }

    $id      = (int)($data['id'] ?? 0);
    $purpose = trim($data['purpose'] ?? '');

    if ($id <= 0) json_response(['error' => 'invalid id'], 400);

    $stmt = $db->prepare("SELECT * FROM withdrawals WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) json_response(['error' => 'withdraw not found'], 404);

    $stmt = $db->prepare("
        UPDATE withdrawals
        SET purpose = :purpose
        WHERE id = :id
    ");
    $stmt->execute([
        ':purpose' => $purpose,
        ':id'      => $id,
    ]);

    json_response(['ok' => true]);
}

json_response(['error' => 'Method not allowed'], 405);

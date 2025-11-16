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

    refresh_archive_status($db, $itemId);

    $db->commit();
    json_response(['ok' => true]);
}

// PUT：修改一筆領料的用途 / 數量（會回補或再扣庫存）
if ($method === 'PUT') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_response(['error' => 'Invalid JSON'], 400);
    }

    $id      = (int)($data['id'] ?? 0);
    $purpose = trim($data['purpose'] ?? '');
    $qty     = $data['qty'] ?? null;

    if ($id <= 0) json_response(['error' => 'invalid id'], 400);

    $stmt = $db->prepare("SELECT * FROM withdrawals WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) json_response(['error' => 'withdraw not found'], 404);

    $itemId = (int)$row['item_id'];
    $originalQty = (int)$row['qty'];

    $newQty = $qty !== null ? (int)$qty : $originalQty;
    if ($newQty <= 0) {
        json_response(['error' => 'qty must be > 0'], 400);
    }

    $db->beginTransaction();

    if ($newQty !== $originalQty) {
        $delta = $newQty - $originalQty; // >0 代表需要再扣庫存
        $now = gmdate('Y-m-d H:i:s');

        if ($delta > 0) {
            $stmt = $db->prepare("SELECT qty FROM items WHERE id = :id");
            $stmt->execute([':id' => $itemId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) {
                $db->rollBack();
                json_response(['error' => 'item not found'], 404);
            }
            if ((int)$item['qty'] < $delta) {
                $db->rollBack();
                json_response(['error' => 'not enough stock to increase withdraw qty'], 400);
            }

            $stmt = $db->prepare("UPDATE items SET qty = qty - :delta, updated_at = :updated_at WHERE id = :id");
            $stmt->execute([
                ':delta'      => $delta,
                ':updated_at' => $now,
                ':id'         => $itemId,
            ]);
        } else {
            $stmt = $db->prepare("UPDATE items SET qty = qty + :delta_abs, updated_at = :updated_at WHERE id = :id");
            $stmt->execute([
                ':delta_abs'  => abs($delta),
                ':updated_at' => $now,
                ':id'         => $itemId,
            ]);
        }
        refresh_archive_status($db, $itemId);
    }

    $stmt = $db->prepare("
        UPDATE withdrawals
        SET purpose = :purpose,
            qty      = :qty
        WHERE id = :id
    ");
    $stmt->execute([
        ':purpose' => $purpose,
        ':qty'      => $newQty,
        ':id'      => $id,
    ]);

    $db->commit();

    json_response(['ok' => true]);
}

// DELETE：撤回一筆領料並回補庫存
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) json_response(['error' => 'invalid id'], 400);

    $stmt = $db->prepare("SELECT * FROM withdrawals WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) json_response(['error' => 'withdraw not found'], 404);

    $itemId = (int)$row['item_id'];
    $qty = (int)$row['qty'];
    $now = gmdate('Y-m-d H:i:s');

    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE items SET qty = qty + :qty, updated_at = :updated_at WHERE id = :id");
    $stmt->execute([
        ':qty'        => $qty,
        ':updated_at' => $now,
        ':id'         => $itemId,
    ]);

    $stmt = $db->prepare("DELETE FROM withdrawals WHERE id = :id");
    $stmt->execute([':id' => $id]);

    refresh_archive_status($db, $itemId);

    $db->commit();

    json_response(['ok' => true]);
}

json_response(['error' => 'Method not allowed'], 405);

function refresh_archive_status(PDO $db, int $itemId): void
{
    $stmt = $db->prepare("SELECT qty, is_archived, depleted_at FROM items WHERE id = :id");
    $stmt->execute([':id' => $itemId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return;
    }

    $qty = (int)$row['qty'];
    if ($qty <= 0) {
        $depletedAt = $row['depleted_at'] ?? null;
        if (!$depletedAt) {
            $depletedAt = gmdate('Y-m-d H:i:s');
        }
        if ((int)$row['is_archived'] !== 1 || $row['depleted_at'] !== $depletedAt) {
            $stmt = $db->prepare("UPDATE items SET is_archived = 1, depleted_at = :depleted_at WHERE id = :id");
            $stmt->execute([
                ':depleted_at' => $depletedAt,
                ':id'          => $itemId,
            ]);
        }
    } else {
        if ((int)$row['is_archived'] !== 0 || $row['depleted_at'] !== null) {
            $stmt = $db->prepare("UPDATE items SET is_archived = 0, depleted_at = NULL WHERE id = :id");
            $stmt->execute([':id' => $itemId]);
        }
    }
}

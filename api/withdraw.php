<?php
// Withdrawal operations
require __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        list_withdrawals($db);
        break;
    case 'POST':
        create_withdrawal($db);
        break;
    case 'PUT':
        update_withdrawal($db);
        break;
    case 'DELETE':
        delete_withdrawal($db);
        break;
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function list_withdrawals(PDO $db): void
{
    $itemId = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
    if ($itemId <= 0) {
        json_response(['error' => 'item_id required'], 400);
    }
    $stmt = $db->prepare('SELECT * FROM withdrawals WHERE item_id = :item_id ORDER BY created_at DESC, id DESC');
    $stmt->execute([':item_id' => $itemId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_response($rows);
}

function create_withdrawal(PDO $db): void
{
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $itemId = isset($data['item_id']) ? (int)$data['item_id'] : 0;
    $qty = isset($data['qty']) ? (int)$data['qty'] : 0;
    $purpose = trim((string)($data['purpose'] ?? ''));

    if ($itemId <= 0 || $qty <= 0) {
        json_response(['error' => 'item_id and qty are required'], 400);
    }

    $db->beginTransaction();
    try {
        $item = find_item_for_update($db, $itemId);
        if (!$item) {
            throw new RuntimeException('item not found');
        }
        if ($item['qty'] < $qty) {
            throw new RuntimeException('insufficient stock');
        }

        $now = gmdate('Y-m-d H:i:s');
        $db->prepare('INSERT INTO withdrawals (item_id, qty, purpose, created_at, updated_at) VALUES (:item_id, :qty, :purpose, :created_at, :updated_at)')
            ->execute([
                ':item_id' => $itemId,
                ':qty' => $qty,
                ':purpose' => $purpose,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);

        $db->prepare('UPDATE items SET qty = qty - :qty, updated_at = :updated_at WHERE id = :id')
            ->execute([':qty' => $qty, ':updated_at' => $now, ':id' => $itemId]);

        refresh_archive_status($db, $itemId);
        $db->commit();
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        $db->rollBack();
        json_response(['error' => $e->getMessage()], 400);
    }
}

function update_withdrawal(PDO $db): void
{
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $qty = isset($data['qty']) ? (int)$data['qty'] : 0;
    $purpose = trim((string)($data['purpose'] ?? ''));

    if ($id <= 0 || $qty <= 0) {
        json_response(['error' => 'id and qty required'], 400);
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT * FROM withdrawals WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$withdrawal) {
            throw new RuntimeException('record not found');
        }

        $item = find_item_for_update($db, (int)$withdrawal['item_id']);
        if (!$item) {
            throw new RuntimeException('item not found');
        }

        $diff = $qty - (int)$withdrawal['qty'];
        if ($diff > 0 && $item['qty'] < $diff) {
            throw new RuntimeException('insufficient stock');
        }

        $now = gmdate('Y-m-d H:i:s');
        $db->prepare('UPDATE withdrawals SET qty = :qty, purpose = :purpose, updated_at = :updated_at WHERE id = :id')
            ->execute([
                ':qty' => $qty,
                ':purpose' => $purpose,
                ':updated_at' => $now,
                ':id' => $id,
            ]);

        $db->prepare('UPDATE items SET qty = qty - :diff, updated_at = :updated_at WHERE id = :id')
            ->execute([':diff' => $diff, ':updated_at' => $now, ':id' => $withdrawal['item_id']]);

        refresh_archive_status($db, (int)$withdrawal['item_id']);
        $db->commit();
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        $db->rollBack();
        json_response(['error' => $e->getMessage()], 400);
    }
}

function delete_withdrawal(PDO $db): void
{
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        json_response(['error' => 'id required'], 400);
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT * FROM withdrawals WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$withdrawal) {
            throw new RuntimeException('record not found');
        }

        $now = gmdate('Y-m-d H:i:s');
        $db->prepare('DELETE FROM withdrawals WHERE id = :id')->execute([':id' => $id]);
        $db->prepare('UPDATE items SET qty = qty + :qty, updated_at = :updated_at WHERE id = :id')
            ->execute([':qty' => $withdrawal['qty'], ':updated_at' => $now, ':id' => $withdrawal['item_id']]);

        refresh_archive_status($db, (int)$withdrawal['item_id']);
        $db->commit();
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        $db->rollBack();
        json_response(['error' => $e->getMessage()], 400);
    }
}

function find_item_for_update(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM items WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    return $item ?: null;
}
?>

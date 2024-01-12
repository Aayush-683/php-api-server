<?php
$itemsFile = 'items.json';

function loadItems()
{
    global $itemsFile;
    if (file_exists($itemsFile)) {
        return json_decode(file_get_contents($itemsFile), true);
    } else {
        return [];
    }
}

function saveItems($items)
{
    global $itemsFile;
    file_put_contents($itemsFile, json_encode($items, JSON_PRETTY_PRINT));
}

function sendResponse($status, $data = null)
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

$items = loadItems();

function getItemById($id)
{
    global $items;
    foreach ($items as $item) {
        if ($item['id'] == $id) {
            return $item;
        }
    }
    return null;
}

function getItemId($id) {
    global $items;
    $i = 0;
    foreach ($items as $item) {
        if ($item['id'] == $id) {
            return $i;
        } else {
            $i++;
        }
    }
    return null;
}

function getItems()
{
    global $items;
    $displayItems = [];
    foreach ($items as $item) {
        $displayItems[] = ['id' => $item['id'], 'name' => $item['name']];
    }
    sendResponse(200, $displayItems);
}

function createItem()
{
    global $items;

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['name'])) {
        sendResponse(400, ['error' => 'Name is required', 'data' => $data, 'body' => file_get_contents('php://input')]);
    }

    $newItem = ['id' => count($items) + 1, 'name' => $data['name']];
    $items[] = $newItem;

    saveItems($items);

    sendResponse(200, $newItem);
}

function updateItem($id)
{
    global $items;

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['name'])) {
        sendResponse(400, ['error' => 'Name is required', 'data' => $data, 'body' => file_get_contents('php://input')]);
    }

    $existingItem = getItemById($id);

    if (!$existingItem) {
        $newItem = ['id' => $id, 'name' => $data['name']];
        $items[] = $newItem;
        saveItems($items);
        sendResponse(201, $newItem);
    } else {
        $existingItem['name'] = $data['name'];
        $itemID = getItemId($id);
        $items[$itemID] = $existingItem;
        saveItems($items);
        sendResponse(200, $existingItem);
    }
}

function partialUpdateItem($id)
{
    global $items;

    $data = json_decode(file_get_contents('php://input'), true);

    $itemToUpdate = getItemById($id);

    if (!$itemToUpdate) {
        sendResponse(404, ['error' => 'Item not found', 'data' => $data, 'body' => file_get_contents('php://input')]);
    }

    if (!isset($data['name'])) {
        sendResponse(400, ['error' => 'Name is required', 'data' => $data, 'body' => file_get_contents('php://input')]);
    }

    $itemToUpdate['name'] = $data['name'];
    $itemID = getItemId($id);
    $items[$itemID] = $itemToUpdate;
    saveItems($items);

    sendResponse(200, $itemToUpdate);
}

function deleteItem($id)
{
    global $items;

    $indexToRemove = -1;

    foreach ($items as $index => $item) {
        if ($item['id'] == $id) {
            $indexToRemove = $index;
            break;
        }
    }

    if ($indexToRemove === -1) {
        sendResponse(404, ['error' => 'Item not found', 'data' => $data, 'body' => file_get_contents('php://input')]);
    }

    $deletedItem = array_splice($items, $indexToRemove, 1)[0];
    $deletedItem['status'] = 'deleted';

    saveItems($items);

    sendResponse(200, $deletedItem);
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$baseUrl = '/php';

$path = str_replace($baseUrl, '', $requestUri);
$path = strtok($path, '?');

if ($requestMethod === 'GET' && $path === '/') {
    echo '<h1>Welcome to the API!</h1>';
    echo '<h2>Use the following endpoints:</h2>';
    echo '<h3>';
    echo 'GET /items - to retrieve all items<br>';
    echo 'POST /items - to create a new item<br>';
    echo 'PUT /items/:id - to create a new item<br>';
    echo 'PATCH /items/:id - to update an existing item<br>';
    echo 'DELETE /items/:id - to delete an item<br>';
    echo '</h3>';
} elseif ($requestMethod === 'GET' && $path === '/items') {
    getItems();
} elseif ($requestMethod === 'POST' && $path === '/items') {
    createItem();
} elseif ($requestMethod === 'PUT' && str_contains($path, '/items/')) {
    preg_match('/\/items\/(\d+)/', $path, $matches);
    $itemId = intval($matches[1]);
    updateItem($itemId);
} elseif ($requestMethod === 'PATCH' && str_contains($path, '/items/')) {
    preg_match('/\/items\/(\d+)/', $path, $matches);
    $itemId = intval($matches[1]);
    partialUpdateItem($itemId);
} elseif ($requestMethod === 'DELETE' && str_contains($path, '/items/')) {
    preg_match('/\/items\/(\d+)/', $path, $matches);
    $itemId = intval($matches[1]);
    deleteItem($itemId);
} else {
    sendResponse(404, ['error' => 'Endpoint not found', 'path' => $path, 'method' => $requestMethod, 'uri' => $requestUri]);
}
?>

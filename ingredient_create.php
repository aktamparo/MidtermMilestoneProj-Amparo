<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

require_login();
header('Content-Type: application/json');

if (!is_post() || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
	http_response_code(419);
	echo json_encode(['error' => 'Invalid request']);
	exit;
}

$name = trim($_POST['name'] ?? '');

if (strlen($name) < 2 || strlen($name) > 160) {
	http_response_code(422);
	echo json_encode(['error' => 'Ingredient must be 2 to 160 characters.']);
	exit;
}

$find = db()->prepare('SELECT id, name FROM ingredients WHERE name = ?');
$find->execute([$name]);
$ingredient = $find->fetch();

if (!$ingredient) {
	$insert = db()->prepare('INSERT INTO ingredients (name) VALUES (?)');
	$insert->execute([$name]);
	$ingredient = [
		'id' => (int) db()->lastInsertId(),
		'name' => $name,
	];
}

echo json_encode(['ingredient' => $ingredient]);

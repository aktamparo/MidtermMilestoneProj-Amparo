<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$user = require_login();
header('Content-Type: application/json');

if (!is_post() || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
	http_response_code(419);
	echo json_encode(['error' => 'Invalid request']);
	exit;
}

$recipe_id = (int) ($_POST['recipe_id'] ?? 0);
$check = db()->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND recipe_id = ?');
$check->execute([$user['id'], $recipe_id]);

if ($check->fetch()) {
	$statement = db()->prepare('DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?');
	$statement->execute([$user['id'], $recipe_id]);
	echo json_encode(['saved' => false]);
	exit;
}

$statement = db()->prepare('INSERT IGNORE INTO favorites (user_id, recipe_id) VALUES (?, ?)');
$statement->execute([$user['id'], $recipe_id]);
echo json_encode(['saved' => true]);

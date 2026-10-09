<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$user = require_login();

if (!is_post()) {
	redirect('index.php');
}

check_csrf();

$comment_id = (int) ($_POST['comment_id'] ?? 0);
$recipe_id = (int) ($_POST['recipe_id'] ?? 0);
$body = trim($_POST['body'] ?? '');
$action = $_POST['action'] ?? 'save';

if ($action === 'delete') {
	$statement = db()->prepare('DELETE FROM comments WHERE id = ? AND user_id = ?');
	$statement->execute([$comment_id, $user['id']]);
} elseif (strlen($body) >= 2 && strlen($body) <= 2000) {
	if ($comment_id) {
		$statement = db()->prepare('UPDATE comments SET body = ? WHERE id = ? AND user_id = ?');
		$statement->execute([$body, $comment_id, $user['id']]);
	} else {
		$statement = db()->prepare('INSERT INTO comments (recipe_id, user_id, body) VALUES (?, ?, ?)');
		$statement->execute([$recipe_id, $user['id'], $body]);
	}
}

redirect('recipe.php?id=' . $recipe_id);

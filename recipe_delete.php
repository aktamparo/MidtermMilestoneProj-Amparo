<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$user = require_login();

if (!is_post()) {
	redirect('index.php');
}

check_csrf();

$statement = db()->prepare('DELETE FROM recipes WHERE id = ? AND user_id = ?');
$statement->execute([(int) ($_POST['id'] ?? 0), $user['id']]);

flash('success', 'Recipe removed.');
redirect('index.php');

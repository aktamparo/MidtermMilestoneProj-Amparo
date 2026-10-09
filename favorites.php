<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$user = require_login();
$statement = db()->prepare(
	'SELECT r.*, c.name AS category_name, u.name AS author_name
	 FROM favorites f
	 JOIN recipes r ON r.id = f.recipe_id
	 JOIN categories c ON c.id = r.category_id
	 JOIN users u ON u.id = r.user_id
	 WHERE f.user_id = ?
	 ORDER BY f.created_at DESC'
);
$statement->execute([$user['id']]);
$recipes = $statement->fetchAll();
$page_title = 'My favorites';
require __DIR__ . '/header.php';
?><h1>My favorites</h1><p class="lede">The recipes you want to come back to.</p><?php if (!$recipes): ?><p class="empty">You have not saved a recipe yet. Browse the kitchen and tap “Save to favorites”.</p><?php else: ?><div class="grid"><?php foreach ($recipes as $recipe): ?><article class="recipe-card"><span class="badge"><?= e($recipe['category_name']) ?></span><h3><a href="recipe.php?id=<?= $recipe['id'] ?>"><?= e($recipe['title']) ?></a></h3><p><?= e($recipe['description']) ?></p><div class="meta">By <?= e($recipe['author_name']) ?></div></article><?php endforeach; ?></div><?php endif; ?><?php require __DIR__ . '/footer.php'; ?>

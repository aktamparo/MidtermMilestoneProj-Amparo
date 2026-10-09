<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$user = require_login();
$recipe_id = (int) ($_GET['id'] ?? 0);
$statement = db()->prepare(
	'SELECT r.*, c.name AS category_name, u.name AS author_name,
			EXISTS (SELECT 1 FROM favorites f WHERE f.recipe_id = r.id AND f.user_id = ?) AS is_saved
	 FROM recipes r
	 JOIN categories c ON c.id = r.category_id
	 JOIN users u ON u.id = r.user_id
	 WHERE r.id = ?'
);
$statement->execute([$user['id'], $recipe_id]);
$recipe = $statement->fetch();

if (!$recipe) {
	http_response_code(404);
	exit('Recipe not found.');
}

$ingredient_ids = json_decode($recipe['ingredient_ids'], true) ?: [];
$ingredients = [];

if ($ingredient_ids) {
	$placeholders = implode(',', array_fill(0, count($ingredient_ids), '?'));
	$ingredient_statement = db()->prepare("SELECT name FROM ingredients WHERE id IN ($placeholders) ORDER BY name");
	$ingredient_statement->execute($ingredient_ids);
	$ingredients = $ingredient_statement->fetchAll();
}

$comment_statement = db()->prepare(
	'SELECT c.*, u.name AS author_name
	 FROM comments c
	 JOIN users u ON u.id = c.user_id
	 WHERE c.recipe_id = ?
	 ORDER BY c.created_at DESC'
);
$comment_statement->execute([$recipe_id]);
$comments = $comment_statement->fetchAll();

$page_title = $recipe['title'];
require __DIR__ . '/header.php';
?><div class="recipe-head"><div><span class="badge"><?= e($recipe['category_name']) ?></span><h1><?= e($recipe['title']) ?></h1><p class="lede"><?= e($recipe['description']) ?></p><p class="small">Shared by <?= e($recipe['author_name']) ?> on <?= e(date('M j, Y', strtotime($recipe['created_at']))) ?><?php if ($recipe['updated_at'] !== $recipe['created_at']): ?> &middot; edited <?= e(date('M j, Y', strtotime($recipe['updated_at']))) ?><?php endif; ?></p></div><div class="actions"><button class="button <?= $recipe['is_saved'] ? '' : 'secondary' ?>" type="button" data-favorite="<?= $recipe['id'] ?>" data-csrf="<?= csrf_token() ?>"><?= $recipe['is_saved'] ? 'Saved to favorites' : 'Save to favorites' ?></button><?php if ((int)$recipe['user_id'] === (int)$user['id']): ?><a class="button secondary" href="recipe_form.php?id=<?= $recipe['id'] ?>">Edit</a><form method="post" action="recipe_delete.php" onsubmit="return confirm('Remove this recipe?');"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $recipe['id'] ?>"><button class="button danger" type="submit">Delete</button></form><?php endif; ?></div></div>
<div class="recipe-content"><section><h2>Ingredients</h2><ul class="ingredients"><?php foreach ($ingredients as $ingredient): ?><li><?= e($ingredient['name']) ?></li><?php endforeach; ?></ul></section><section><h2>Cooking steps</h2><p><?= nl2br(e($recipe['instructions'])) ?></p></section></div>
<section style="margin-top:42px"><h2>Kitchen talk</h2><form class="panel" method="post" action="comment_action.php"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>"><div class="field"><label for="body">Leave feedback</label><textarea id="body" name="body" required maxlength="2000" placeholder="What did you think? Any helpful tip?"></textarea></div><button class="button" type="submit">Post comment</button></form><?php if (!$comments): ?><p class="empty">No comments yet. Start the conversation.</p><?php else: ?><?php foreach ($comments as $comment): ?><article class="comment"><div class="small"><strong><?= e($comment['author_name']) ?></strong> &middot; <?= e(date('M j, Y', strtotime($comment['created_at']))) ?><?php if ($comment['updated_at'] !== $comment['created_at']): ?> &middot; edited<?php endif; ?></div><?php if ((int)$comment['user_id'] === (int)$user['id']): ?><form method="post" action="comment_action.php"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>"><input type="hidden" name="comment_id" value="<?= $comment['id'] ?>"><textarea name="body" required maxlength="2000"><?= e($comment['body']) ?></textarea><div class="actions"><button class="button secondary" name="action" value="save" type="submit">Save edit</button><button class="button danger" name="action" value="delete" type="submit">Delete</button></div></form><?php else: ?><p><?= nl2br(e($comment['body'])) ?></p><?php endif; ?></article><?php endforeach; ?><?php endif; ?></section><?php require __DIR__ . '/footer.php'; ?>

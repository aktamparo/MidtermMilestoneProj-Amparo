<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$user = require_login();
$page_title = 'Browse recipes';
$search = trim($_GET['search'] ?? '');
$category_id = (int)($_GET['category'] ?? 0);

$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$sql = 'SELECT r.*, c.name AS category_name, u.name AS author_name,
        EXISTS (SELECT 1 FROM favorites f WHERE f.recipe_id = r.id AND f.user_id = ?) AS is_saved
        FROM recipes r JOIN categories c ON c.id = r.category_id JOIN users u ON u.id = r.user_id WHERE 1=1';
$params = [$user['id']];

if ($search !== '') {
        $sql .= ' AND (r.title LIKE ? OR r.description LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
}

if ($category_id > 0) {
        $sql .= ' AND r.category_id = ?';
        $params[] = $category_id;
}

$sql .= ' ORDER BY r.created_at DESC';
$statement = db()->prepare($sql);
$statement->execute($params);
$recipes = $statement->fetchAll();

$meal = db()->query('SELECT r.id, r.title FROM recipes r ORDER BY RAND() LIMIT 1')->fetch();
require __DIR__ . '/header.php';
?>
<section class="hero"><div><p class="badge">A neighborhood recipe notebook</p><h1>Good food travels best by word of mouth.</h1><p class="lede">Share the dishes your family knows by heart, discover something new, and keep your favorites close.</p><div class="actions"><a class="button" href="recipe_form.php">Share your recipe</a><?php if ($meal): ?><a class="button secondary" href="recipe.php?id=<?= $meal['id'] ?>">Try <?= e($meal['title']) ?></a><?php endif; ?></div></div><div class="hero-art"><span>Today's kitchen note</span><strong>Cook generously.<br>Share freely.</strong></div></section>
<h2>Latest from the barangay</h2>
<form class="filters" method="get"><div class="field"><label for="search">Search</label><input id="search" name="search" value="<?= e($search) ?>" placeholder="Try chicken, sweet, soup..."></div><div class="field"><label for="category">Category</label><select id="category" name="category"><option value="0">All categories</option><?php foreach ($categories as $category): ?><option value="<?= $category['id'] ?>" <?= $category_id === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div><button class="button" type="submit">Find recipes</button></form>
<?php if (!$recipes): ?><p class="empty">No recipes matched that search yet. Be the first to share one.</p><?php else: ?><div class="grid"><?php foreach ($recipes as $recipe): ?><article class="recipe-card"><span class="badge"><?= e($recipe['category_name']) ?></span><h3><a href="recipe.php?id=<?= $recipe['id'] ?>"><?= e($recipe['title']) ?></a></h3><p><?= e($recipe['description']) ?></p><div class="meta">By <?= e($recipe['author_name']) ?><?php if ($recipe['is_saved']): ?> &middot; Saved<?php endif; ?></div></article><?php endforeach; ?></div><?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>

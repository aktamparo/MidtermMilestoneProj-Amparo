<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$user = require_login();
$recipe_id = (int) ($_GET['id'] ?? 0);
$recipe = null;
$selected_ingredients = [];

if ($recipe_id) {
	$statement = db()->prepare('SELECT * FROM recipes WHERE id = ? AND user_id = ?');
	$statement->execute([$recipe_id, $user['id']]);
	$recipe = $statement->fetch();

	if (!$recipe) {
		http_response_code(404);
		exit('Recipe not found.');
	}

	$selected_ingredients = json_decode($recipe['ingredient_ids'], true) ?: [];
}

$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$master_ingredients = db()->query('SELECT id, name FROM ingredients ORDER BY name')->fetchAll();

if (!$selected_ingredients) {
	$selected_ingredients = [''];
}

$page_title = $recipe ? 'Edit recipe' : 'Share a recipe';
require __DIR__ . '/header.php';
?>
<div class="panel">
	<h1><?= $recipe ? 'Edit your recipe' : 'Share a recipe' ?></h1>
	<p class="lede">Choose from the shared ingredient masterlist. Add a new ingredient once, and everyone can reuse it.</p>

	<form class="form-stack" method="post" action="recipe_save.php">
		<input type="hidden" name="csrf" value="<?= csrf_token() ?>">
		<input type="hidden" name="id" value="<?= $recipe['id'] ?? 0 ?>">

		<div class="field">
			<label for="title">Recipe title</label>
			<input id="title" name="title" required maxlength="120" value="<?= e($recipe['title'] ?? '') ?>">
		</div>

		<div class="field">
			<label for="description">Short description</label>
			<input id="description" name="description" required maxlength="255" value="<?= e($recipe['description'] ?? '') ?>">
		</div>

		<div class="field">
			<label for="category">Category</label>
			<select id="category" name="category_id" required>
				<?php foreach ($categories as $category): ?>
					<option value="<?= $category['id'] ?>" <?= (int) ($recipe['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>>
						<?= e($category['name']) ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="field">
			<label>Ingredients</label>
			<div id="ingredients">
				<?php foreach ($selected_ingredients as $selected_id): ?>
					<div class="ingredient-row">
						<select name="ingredient_ids[]" required>
							<option value="">Choose an ingredient</option>
							<?php foreach ($master_ingredients as $ingredient): ?>
								<option value="<?= $ingredient['id'] ?>" <?= (string) $selected_id === (string) $ingredient['id'] ? 'selected' : '' ?>>
									<?= e($ingredient['name']) ?>
								</option>
							<?php endforeach; ?>
						</select>
						<button type="button" aria-label="Remove ingredient">&times;</button>
					</div>
				<?php endforeach; ?>
			</div>

			<button id="add-ingredient" class="button secondary" type="button">+ Add ingredient</button>

			<div class="ingredient-new">
				<input id="new-ingredient" maxlength="160" placeholder="New master ingredient">
				<button id="save-ingredient" class="button secondary" type="button" data-csrf="<?= csrf_token() ?>">
					Add to masterlist
				</button>
			</div>
		</div>

		<div class="field">
			<label for="instructions">Cooking steps</label>
			<textarea id="instructions" name="instructions" required maxlength="10000" placeholder="Write one step per line..."><?= e($recipe['instructions'] ?? '') ?></textarea>
		</div>

		<div class="actions">
			<button class="button" type="submit"><?= $recipe ? 'Save changes' : 'Publish recipe' ?></button>
			<a class="button secondary" href="index.php">Cancel</a>
		</div>
	</form>
</div>
<?php require __DIR__ . '/footer.php'; ?>

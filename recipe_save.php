<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$user = require_login();

if (!is_post()) {
	redirect('index.php');
}

check_csrf();

$id = (int) ($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$instructions = trim($_POST['instructions'] ?? '');
$category_id = (int) ($_POST['category_id'] ?? 0);
$ingredient_ids = array_values(array_unique(array_filter(array_map('intval', $_POST['ingredient_ids'] ?? []))));

$ingredient_count = count($ingredient_ids);
$valid_ingredients = 0;

if ($ingredient_count) {
	$placeholders = implode(',', array_fill(0, $ingredient_count, '?'));
	$ingredient_check = db()->prepare("SELECT COUNT(*) FROM ingredients WHERE id IN ($placeholders)");
	$ingredient_check->execute($ingredient_ids);
	$valid_ingredients = (int) $ingredient_check->fetchColumn();
}

if (
	strlen($title) < 3
	|| strlen($title) > 120
	|| strlen($description) < 5
	|| strlen($description) > 255
	|| !$instructions
	|| strlen($instructions) > 10000
	|| !$category_id
	|| $ingredient_count < 1
	|| $ingredient_count > 50
	|| $valid_ingredients !== $ingredient_count
) {
	flash('error', 'Please complete all recipe fields and choose at least one valid ingredient.');
	redirect($id ? 'recipe_form.php?id=' . $id : 'recipe_form.php');
}

$pdo = db();
$pdo->beginTransaction();

try {
	$ingredient_json = json_encode($ingredient_ids, JSON_THROW_ON_ERROR);

	if ($id) {
		$owner = $pdo->prepare('SELECT id FROM recipes WHERE id = ? AND user_id = ?');
		$owner->execute([$id, $user['id']]);

		if (!$owner->fetch()) {
			throw new RuntimeException('Recipe not found.');
		}

		$statement = $pdo->prepare(
			'UPDATE recipes
			 SET category_id = ?, title = ?, description = ?, instructions = ?, ingredient_ids = ?
			 WHERE id = ? AND user_id = ?'
		);
		$statement->execute([
			$category_id,
			$title,
			$description,
			$instructions,
			$ingredient_json,
			$id,
			$user['id'],
		]);
	} else {
		$statement = $pdo->prepare(
			'INSERT INTO recipes (user_id, category_id, title, description, instructions, ingredient_ids)
			 VALUES (?, ?, ?, ?, ?, ?)'
		);
		$statement->execute([
			$user['id'],
			$category_id,
			$title,
			$description,
			$instructions,
			$ingredient_json,
		]);
		$id = (int) $pdo->lastInsertId();
	}

	$pdo->commit();
	flash('success', $id ? 'Recipe saved.' : 'Recipe published.');
	redirect('recipe.php?id=' . $id);
} catch (Throwable $error) {
	if ($pdo->inTransaction()) {
		$pdo->rollBack();
	}

	flash('error', 'The recipe could not be saved.');
	redirect('recipe_form.php' . ($id ? '?id=' . $id : ''));
}

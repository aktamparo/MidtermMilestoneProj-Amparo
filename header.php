<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$user = current_user();
$flash = take_flash();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title ?? 'Barangay Kusina') ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header"><div class="container nav">
    <a class="brand" href="index.php">Barangay <span>Kusina</span></a>
    <nav class="nav-links">
        <?php if ($user): ?>
            <a href="index.php">Browse</a><a href="favorites.php">My favorites</a><a href="recipe_form.php">Share a recipe</a>
            <span class="small">Hi, <?= e($user['name']) ?></span><a href="logout.php">Log out</a>
        <?php else: ?>
            <a href="login.php">Log in</a><a class="button" href="register.php">Join the kitchen</a>
        <?php endif; ?>
    </nav>
</div></header>
<main><div class="container">
<?php if ($flash): ?><div class="notice <?= $flash['type'] === 'error' ? 'error' : '' ?>"><?= e($flash['message']) ?></div><?php endif; ?>

<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
if (current_user()) redirect('index.php');

$errors = [];

if (is_post()) {
    check_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (strlen($name) < 2 || strlen($name) > 80) {
        $errors[] = 'Name must be between 2 and 80 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);

        if ($check->fetch()) {
            $errors[] = 'That email is already registered.';
        } else {
            $statement = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            Auth::login((int) db()->lastInsertId());
            flash('success', 'Welcome to the kitchen!');
            redirect('index.php');
        }
    }
}

$page_title = 'Create an account'; require __DIR__ . '/header.php';
?><div class="panel narrow"><h1>Create your kitchen account</h1><p class="lede">Join neighbors sharing practical, delicious recipes.</p><?php foreach ($errors as $error): ?><div class="notice error"><?= e($error) ?></div><?php endforeach; ?><form class="form-stack" method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><div class="field"><label for="name">Your name</label><input id="name" name="name" required maxlength="80" value="<?= old('name') ?>"></div><div class="field"><label for="email">Email</label><input id="email" type="email" name="email" required value="<?= old('email') ?>"></div><div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required minlength="8"></div><button class="button" type="submit">Create account</button></form><p class="small">Already a member? <a href="login.php">Log in</a>.</p></div><?php require __DIR__ . '/footer.php'; ?>

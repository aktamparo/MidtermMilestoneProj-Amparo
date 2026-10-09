<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

if (current_user()) redirect('index.php');

$error = '';

if (is_post()) {
	check_csrf();
	$email = strtolower(trim($_POST['email'] ?? ''));
	$password = $_POST['password'] ?? '';
	$user_id = Auth::attempt($email, $password);

	if ($user_id !== null) {
		Auth::login($user_id);
		redirect('index.php');
	}

	$error = 'Email or password is incorrect.';
}

$page_title = 'Log in'; require __DIR__ . '/header.php';
?><div class="panel narrow"><h1>Welcome back</h1><p class="lede">The stove is warm. Come on in.</p><?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?><form class="form-stack" method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><div class="field"><label for="email">Email</label><input id="email" type="email" name="email" required value="<?= old('email') ?>"></div><div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required></div><button class="button" type="submit">Log in</button></form><p class="small">New here? <a href="register.php">Create an account</a>.</p></div><?php require __DIR__ . '/footer.php'; ?>

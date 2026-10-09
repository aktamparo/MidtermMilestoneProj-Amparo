<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Auth.php';

function e(mixed $value): string
{
    $text = is_scalar($value) ? (string) $value : '';
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;

    if ($user === null) {
        $statement = db()->prepare('SELECT id, name, email FROM users WHERE id = ?');
        $statement->execute([$_SESSION['user_id']]);
        $user = $statement->fetch() ?: null;
    }

    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('login.php');
    }
    return $user;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function check_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Invalid form token. Please go back and try again.');
    }
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

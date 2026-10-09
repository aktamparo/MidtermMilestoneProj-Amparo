<?php
declare(strict_types=1);
class Auth
{
    public static function attempt(string $email, string $password): ?int
    {
        $statement = db()->prepare('SELECT id, password FROM users WHERE email = ?');
        $statement->execute([$email]);
        $account = $statement->fetch();

        if (!$account || !password_verify($password, $account['password'])) {
            return null;
        }

        return (int)$account['id'];
    }

    public static function login(int $user_id): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user_id;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}

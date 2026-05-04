<?php

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['admin']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Response::json(['error' => 'Authentication required'], 401);
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        if (!in_array($_SESSION['admin']['role'], $roles, true)) {
            Response::json(['error' => 'Permission denied'], 403);
        }
    }

    public static function login(array $admin): void
    {
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id' => (int) $admin['id'],
            'name' => $admin['name'],
            'email' => $admin['email'],
            'role' => $admin['role'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}

<?php

class AuthController
{
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Response::json(['error' => 'Email and password are required.'], 422);
        }

        $admin = (new Admin())->findByEmail($email);
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            $payload = ['error' => 'Invalid login credentials.'];

            if (ErrorHandler::debug()) {
                $payload['debug'] = [
                    'email_found' => (bool) $admin,
                    'is_active' => $admin ? (int) $admin['is_active'] : null,
                    'hash_prefix' => $admin ? substr($admin['password_hash'], 0, 7) : null,
                    'hint' => 'If email_found is true, reset this account password_hash using database/schema.sql or the SQL in README.md.',
                ];
            }

            Response::json($payload, 401);
        }

        Auth::login($admin);
        Response::json(['success' => true, 'admin' => Auth::user()]);
    }

    public function logout(): void
    {
        Auth::logout();
        Response::json(['success' => true]);
    }

    public function me(): void
    {
        Response::json(['admin' => Auth::user()]);
    }
}

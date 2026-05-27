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
            Response::json(['error' => 'Incorrect email and password'], 401);
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

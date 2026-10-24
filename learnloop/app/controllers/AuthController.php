<?php

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect(Auth::dashboardRoute(Auth::role()));
        }
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(): void
    {
        Csrf::requireValid();

        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $v = new Validator();
        $v->required($_POST, 'email', 'Email')->email($_POST, 'email', 'Email')
          ->required($_POST, 'password', 'Password');

        $errors = $v->errors();
        if (empty($errors) && !Auth::attempt($email, $password)) {
            $errors['email'] = 'Invalid credentials, or this account is inactive.';
        }

        if (!empty($errors)) {
            $old = $_POST;
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        redirect(Auth::dashboardRoute(Auth::role()));
    }

    public function logout(): void
    {
        Csrf::requireValid();
        Auth::logout();
        redirect('login');
    }
}

<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AuthController extends Controller
{
    private $user;

    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
    }

    public function login(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $this->validateCSRF();

            $email = $request->request->get('email');
            $password = $request->request->get('password');

            try {
                $user = $this->user->authenticate($email, $password);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    return $this->redirect('/');
                }
                
                return $this->render('auth/login', [
                    'error' => 'Invalid credentials',
                    'csrf_token' => $this->generateCSRFToken()
                ]);
            } catch (\Exception $e) {
                return $this->render('auth/login', [
                    'error' => $e->getMessage(),
                    'csrf_token' => $this->generateCSRFToken()
                ]);
            }
        }

        return $this->render('auth/login', [
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }

    public function register(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $this->validateCSRF();

            $username = $request->request->get('username');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            try {
                if ($password !== $passwordConfirm) {
                    throw new \Exception('Passwords do not match');
                }

                $user = $this->user->create([
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'role' => 'user'
                ]);

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    return $this->redirect('/');
                }

                return $this->render('auth/register', [
                    'error' => 'Could not create user',
                    'csrf_token' => $this->generateCSRFToken()
                ]);
            } catch (\Exception $e) {
                return $this->render('auth/register', [
                    'error' => $e->getMessage(),
                    'csrf_token' => $this->generateCSRFToken()
                ]);
            }
        }

        return $this->render('auth/register', [
            'csrf_token' => $this->generateCSRFToken()
        ]);
    }

    public function logout(): Response
    {
        session_destroy();
        return $this->redirect('/login');
    }
} 
<?php

namespace App\Models;

class User extends Model
{
    protected $table = 'users';

    public function authenticate(string $email, string $password)
    {
        $user = $this->findBy(['email' => $email]);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }

    public function create(array $data)
    {
        // Validate email uniqueness
        if ($this->findBy(['email' => $data['email']])) {
            throw new \Exception('Email already exists');
        }

        // Validate username uniqueness
        if ($this->findBy(['username' => $data['username']])) {
            throw new \Exception('Username already exists');
        }

        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->find($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        // Check email uniqueness if it's being updated
        if (isset($data['email']) && $data['email'] !== $user['email']) {
            if ($this->findBy(['email' => $data['email']])) {
                throw new \Exception('Email already exists');
            }
        }

        // Check username uniqueness if it's being updated
        if (isset($data['username']) && $data['username'] !== $user['username']) {
            if ($this->findBy(['username' => $data['username']])) {
                throw new \Exception('Username already exists');
            }
        }

        // Hash password if it's being updated
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        return parent::update($id, $data);
    }

    public function getByRole(string $role)
    {
        return $this->where('role', $role);
    }

    public function updateLastLogin($id)
    {
        return $this->update($id, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }

    public function changePassword($id, string $currentPassword, string $newPassword)
    {
        $user = $this->find($id);

        if (!$user) {
            throw new \Exception('User not found');
        }

        if (!password_verify($currentPassword, $user['password'])) {
            throw new \Exception('Current password is incorrect');
        }

        return $this->update($id, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT)
        ]);
    }
} 
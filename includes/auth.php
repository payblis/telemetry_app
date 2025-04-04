<?php
// auth.php

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isExpert() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'expert';
}
?>
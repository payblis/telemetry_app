<?php
include '../includes/config.php';
include '../includes/auth.php';
$id = $_GET['id'];
$pdo->prepare("DELETE FROM motos WHERE id = ?")->execute([$id]);
header('Location: list.php');
exit;
?>
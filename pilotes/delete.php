<?php
include '../includes/config.php';
include '../includes/auth.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM pilotes WHERE id = ?");
$stmt->execute([$id]);
header('Location: list.php');
exit;
?>
<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_POST['id']]);
}
header("Location: index.php");
exit;
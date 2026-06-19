<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO products (name, price, qty) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['price'], $_POST['qty']]);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">Add Product</h1>
    <form method="POST" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="qty" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
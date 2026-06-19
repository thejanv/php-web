<?php
require 'config.php';
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Products</h1>
        <a href="create.php" class="btn btn-primary">+ Add Product</a>
    </div>
    <table class="table table-bordered bg-white">
        <thead class="table-dark">
            <tr><th>ID</th><th>Name</th><th>Price</th><th>Qty</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['id']) ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['price']) ?></td>
                <td><?= htmlspecialchars($p['qty']) ?></td>
                <td>
                    <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                    <form method="POST" action="delete.php" style="display:inline"
                          onsubmit="return confirm('Delete this product?')">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Deletes</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
            <tr><td colspan="5" class="text-center text-muted">No products yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
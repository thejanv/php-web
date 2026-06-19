<?php
require 'config.php';
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Topbar -->
    <div class="bg-white border-b border-gray-200 px-6 h-14 flex items-center justify-between">
        <span class="text-xs font-semibold tracking-widest uppercase text-gray-800">Inventory</span>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-6 py-10">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-baseline gap-2">
                <h1 class="text-xl font-semibold text-gray-900">Products</h1>
                <span class="text-xs text-gray-400"><?= count($products) ?> items</span>
            </div>
            <a href="create.php"
               class="inline-flex items-center gap-1 px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition">
                + Add product
            </a>
        </div>

        <!-- Table -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Price</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Qty</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <p class="text-sm font-medium text-gray-500">No products yet</p>
                            <p class="text-xs text-gray-400 mt-1">Add your first product to get started.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 text-xs text-gray-300"><?= htmlspecialchars($p['id']) ?></td>
                            <td class="px-5 py-3 font-medium text-gray-800"><?= htmlspecialchars($p['name']) ?></td>
                            <td class="px-5 py-3 text-gray-500 tabular-nums">LKR <?= number_format($p['price'], 2) ?></td>
                            <td class="px-5 py-3">
                                <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-medium rounded-full">
                                    <?= htmlspecialchars($p['qty']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="edit.php?id=<?= $p['id'] ?>"
                                       class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-md hover:bg-gray-50 transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="delete.php"
                                          onsubmit="return confirm('Delete <?= htmlspecialchars($p['name'], ENT_QUOTES) ?>?')">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium text-red-500 border border-gray-200 rounded-md hover:bg-red-50 hover:border-red-200 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
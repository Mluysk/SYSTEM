<?php
$databasePath = __DIR__ . '/data/database.sqlite';
if (!is_dir(dirname($databasePath))) {
    mkdir(dirname($databasePath), 0777, true);
}

try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    role TEXT NOT NULL,
    email TEXT NOT NULL
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS recipes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    servings INTEGER DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS recipe_ingredients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    recipe_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity REAL NOT NULL,
    unit TEXT DEFAULT "",
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)');

$existingUser = $pdo->query('SELECT COUNT(*) as count FROM users')->fetch(PDO::FETCH_ASSOC);
if ((int) $existingUser['count'] === 0) {
    $stmt = $pdo->prepare('INSERT INTO users (name, role, email) VALUES (?, ?, ?)');
    $stmt->execute(['Maria Oliveira', 'Chefe de Cozinha', 'maria.oliveira@example.com']);
}

$existingProducts = $pdo->query('SELECT COUNT(*) as count FROM products')->fetch(PDO::FETCH_ASSOC);
if ((int) $existingProducts['count'] === 0) {
    $products = [
        ['Farinha de Trigo', 'Farinha branca tradicional', 5.5],
        ['Açúcar', 'Açúcar refinado', 4.2],
        ['Chocolate em Pó', 'Chocolate em pó 50% cacau', 12.9],
        ['Leite', 'Leite integral 1L', 4.8]
    ];
    $stmt = $pdo->prepare('INSERT INTO products (name, description, price) VALUES (?, ?, ?)');
    foreach ($products as $product) {
        $stmt->execute($product);
    }
}

$existingRecipes = $pdo->query('SELECT COUNT(*) as count FROM recipes')->fetch(PDO::FETCH_ASSOC);
if ((int) $existingRecipes['count'] === 0) {
    $pdo->beginTransaction();
    try {
        $stmtRecipe = $pdo->prepare('INSERT INTO recipes (name, description, servings) VALUES (?, ?, ?)');
        $stmtRecipe->execute([
            'Bolo de Chocolate Clássico',
            'Receita tradicional de bolo de chocolate fofinho.',
            8
        ]);
        $recipeId = (int) $pdo->lastInsertId();

        $stmtIngredient = $pdo->prepare('INSERT INTO recipe_ingredients (recipe_id, product_id, quantity, unit) VALUES (?, ?, ?, ?)');
        $stmtIngredient->execute([$recipeId, 1, 2.5, 'xícaras']);
        $stmtIngredient->execute([$recipeId, 2, 2, 'xícaras']);
        $stmtIngredient->execute([$recipeId, 3, 1, 'xícara']);
        $stmtIngredient->execute([$recipeId, 4, 1.5, 'xícaras']);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

<?php
require_once __DIR__ . '/config.php';

function getUser(): array
{
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM users LIMIT 1');
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getProducts(): array
{
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM products ORDER BY name');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProduct(int $productId): ?array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    return $product ?: null;
}

function createProduct(string $name, ?string $description, float $price): void
{
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO products (name, description, price) VALUES (?, ?, ?)');
    $stmt->execute([$name, $description, $price]);
}

function getRecipes(): array
{
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM recipes ORDER BY created_at DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecipe(int $recipeId): ?array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM recipes WHERE id = ?');
    $stmt->execute([$recipeId]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$recipe) {
        return null;
    }

    $stmtIngredients = $pdo->prepare('SELECT ri.*, p.name as product_name, p.price as product_price FROM recipe_ingredients ri JOIN products p ON p.id = ri.product_id WHERE ri.recipe_id = ?');
    $stmtIngredients->execute([$recipeId]);
    $ingredients = $stmtIngredients->fetchAll(PDO::FETCH_ASSOC);
    $recipe['ingredients'] = $ingredients;

    $totalCost = 0;
    foreach ($ingredients as $ingredient) {
        $totalCost += $ingredient['quantity'] * $ingredient['product_price'];
    }
    $recipe['total_cost'] = $totalCost;

    return $recipe;
}

function createRecipe(string $name, ?string $description, int $servings, array $ingredients): void
{
    global $pdo;
    $pdo->beginTransaction();
    try {
        $stmtRecipe = $pdo->prepare('INSERT INTO recipes (name, description, servings) VALUES (?, ?, ?)');
        $stmtRecipe->execute([$name, $description, $servings]);
        $recipeId = (int) $pdo->lastInsertId();

        $stmtIngredient = $pdo->prepare('INSERT INTO recipe_ingredients (recipe_id, product_id, quantity, unit) VALUES (?, ?, ?, ?)');
        foreach ($ingredients as $ingredient) {
            $stmtIngredient->execute([
                $recipeId,
                $ingredient['product_id'],
                $ingredient['quantity'],
                $ingredient['unit'] ?? ''
            ]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function formatCurrency(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

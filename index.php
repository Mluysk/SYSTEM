<?php
require_once __DIR__ . '/functions.php';
session_start();

$alerts = $_SESSION['alerts'] ?? [];
$_SESSION['alerts'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create_product') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float) str_replace(',', '.', $_POST['price'] ?? '0');

            if ($name === '' || $price <= 0) {
                throw new RuntimeException('Informe um nome e um valor válido para o produto.');
            }

            createProduct($name, $description !== '' ? $description : null, $price);
            $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Produto cadastrado com sucesso.'];
            header('Location: index.php?page=products');
            exit;
        }

        if ($action === 'create_recipe') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $servings = max(1, (int) ($_POST['servings'] ?? 1));
            $ingredientProductIds = $_POST['ingredient_product_id'] ?? [];
            $ingredientQuantities = $_POST['ingredient_quantity'] ?? [];
            $ingredientUnits = $_POST['ingredient_unit'] ?? [];

            if ($name === '') {
                throw new RuntimeException('Informe um nome para a receita.');
            }

            $ingredients = [];
            foreach ($ingredientProductIds as $index => $productId) {
                $productId = (int) $productId;
                $quantity = (float) str_replace(',', '.', $ingredientQuantities[$index] ?? '0');
                $unit = trim($ingredientUnits[$index] ?? '');

                if ($productId > 0 && $quantity > 0) {
                    $ingredients[] = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'unit' => $unit,
                    ];
                }
            }

            if (empty($ingredients)) {
                throw new RuntimeException('Adicione pelo menos um ingrediente válido.');
            }

            createRecipe($name, $description !== '' ? $description : null, $servings, $ingredients);
            $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Receita cadastrada com sucesso.'];
            header('Location: index.php?page=recipes');
            exit;
        }
    } catch (Throwable $exception) {
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => $exception->getMessage()];
        $redirectPage = $action === 'create_product' ? 'add_product' : 'add_recipe';
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }
}

$user = getUser();
$page = $_GET['page'] ?? 'dashboard';
$validPages = ['dashboard', 'products', 'add_product', 'recipes', 'add_recipe', 'view_recipe'];
if (!in_array($page, $validPages, true)) {
    $page = 'dashboard';
}

function isActive(string $currentPage, string $target): string
{
    return $currentPage === $target ? 'active' : '';
}

function renderAlerts(array $alerts): void
{
    foreach ($alerts as $alert) {
        $type = htmlspecialchars($alert['type']);
        $message = htmlspecialchars($alert['message']);
        echo "<div class=\"alert {$type}\">{$message}</div>";
    }
}

$products = getProducts();
$recipes = [];
if (in_array($page, ['recipes', 'view_recipe', 'dashboard'], true)) {
    $recipes = getRecipes();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sistema de Receitas e Produtos</title>
    <link rel="stylesheet" href="assets/style.css" />
  </head>
  <body>
    <div class="layout">
      <aside class="sidebar">
        <div class="brand">Melu Chef</div>
        <div class="user-card">
          <div class="name"><?php echo htmlspecialchars($user['name'] ?? 'Usuário'); ?></div>
          <div class="small"><?php echo htmlspecialchars($user['role'] ?? 'Cargo'); ?></div>
          <div class="small"><?php echo htmlspecialchars($user['email'] ?? 'email@example.com'); ?></div>
        </div>
        <nav>
          <a class="<?php echo isActive($page, 'dashboard'); ?>" href="index.php?page=dashboard">Visão Geral</a>
          <a class="<?php echo isActive($page, 'products'); ?>" href="index.php?page=products">Produtos</a>
          <a class="<?php echo isActive($page, 'add_product'); ?>" href="index.php?page=add_product">Cadastrar Produto</a>
          <a class="<?php echo in_array($page, ['recipes', 'add_recipe', 'view_recipe'], true) ? 'active' : ''; ?>" href="index.php?page=recipes">Receitas</a>
          <a class="<?php echo isActive($page, 'add_recipe'); ?>" href="index.php?page=add_recipe">Nova Receita</a>
        </nav>
      </aside>
      <main class="content">
        <header>
          <h1>
            <?php
            switch ($page) {
                case 'products':
                    echo 'Produtos Cadastrados';
                    break;
                case 'add_product':
                    echo 'Cadastrar Produto';
                    break;
                case 'recipes':
                    echo 'Receitas Disponíveis';
                    break;
                case 'add_recipe':
                    echo 'Criar Receita Personalizada';
                    break;
                case 'view_recipe':
                    echo 'Detalhes da Receita';
                    break;
                default:
                    echo 'Painel Principal';
            }
            ?>
          </h1>
        </header>
        <?php renderAlerts($alerts); ?>
        <?php if ($page === 'dashboard'): ?>
          <section class="grid two-columns">
            <div class="card">
              <h2>Resumo de Produtos</h2>
              <p>Você possui <strong><?php echo count($products); ?></strong> produtos cadastrados.</p>
              <a class="button" href="index.php?page=products">Ver lista de produtos</a>
            </div>
            <div class="card">
              <h2>Receitas em destaque</h2>
              <p>Explore receitas prontas para uso ou crie novas combinações personalizadas.</p>
              <a class="button" href="index.php?page=recipes">Ver receitas</a>
            </div>
          </section>
        <?php elseif ($page === 'products'): ?>
          <div class="card">
            <?php if (count($products) === 0): ?>
              <p>Nenhum produto cadastrado até o momento.</p>
              <a class="button" href="index.php?page=add_product">Cadastrar Produto</a>
            <?php else: ?>
              <table class="table">
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $product): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($product['name']); ?></td>
                      <td><?php echo htmlspecialchars($product['description'] ?? '-'); ?></td>
                      <td><?php echo formatCurrency((float) $product['price']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        <?php elseif ($page === 'add_product'): ?>
          <div class="card">
            <form method="post">
              <input type="hidden" name="action" value="create_product" />
              <div class="field">
                <label for="name">Nome do Produto</label>
                <input type="text" id="name" name="name" placeholder="Ex: Farinha de Trigo" required />
              </div>
              <div class="field">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" rows="3" placeholder="Detalhes adicionais"></textarea>
              </div>
              <div class="field">
                <label for="price">Valor (R$)</label>
                <input type="number" step="0.01" min="0" id="price" name="price" placeholder="0,00" required />
              </div>
              <button type="submit" class="button">Salvar Produto</button>
            </form>
          </div>
        <?php elseif ($page === 'recipes'): ?>
          <div class="card">
            <?php if (count($recipes) === 0): ?>
              <p>Nenhuma receita cadastrada ainda.</p>
              <a class="button" href="index.php?page=add_recipe">Criar Receita</a>
            <?php else: ?>
              <table class="table">
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Porções</th>
                    <th>Criada em</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recipes as $recipe): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($recipe['name']); ?></td>
                      <td><?php echo htmlspecialchars($recipe['servings']); ?> porções</td>
                      <td><?php echo date('d/m/Y H:i', strtotime($recipe['created_at'])); ?></td>
                      <td><a class="button secondary" href="index.php?page=view_recipe&id=<?php echo $recipe['id']; ?>">Ver detalhes</a></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        <?php elseif ($page === 'add_recipe'): ?>
          <div class="card">
            <form method="post" id="recipe-form">
              <input type="hidden" name="action" value="create_recipe" />
              <div class="field">
                <label for="recipe-name">Nome da Receita</label>
                <input type="text" id="recipe-name" name="name" placeholder="Ex: Bolo Formigueiro" required />
              </div>
              <div class="field">
                <label for="recipe-description">Descrição</label>
                <textarea id="recipe-description" name="description" rows="3" placeholder="Conte como a receita é feita"></textarea>
              </div>
              <div class="field">
                <label for="recipe-servings">Porções</label>
                <input type="number" min="1" id="recipe-servings" name="servings" value="8" />
              </div>
              <h3>Ingredientes</h3>
              <p class="small">Selecione os produtos cadastrados e informe a quantidade utilizada.</p>
              <table class="ingredients-table" id="ingredients-table">
                <thead>
                  <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Unidade</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <select name="ingredient_product_id[]" required>
                        <option value="">Selecione</option>
                        <?php foreach ($products as $product): ?>
                          <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td>
                      <input type="number" step="0.01" min="0" name="ingredient_quantity[]" placeholder="0" required />
                    </td>
                    <td>
                      <input type="text" name="ingredient_unit[]" placeholder="Ex: gramas" />
                    </td>
                    <td>
                      <button type="button" class="button secondary" onclick="removeRow(this)">Remover</button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <button type="button" class="button secondary" onclick="addIngredientRow()">Adicionar ingrediente</button>
              <br /><br />
              <button type="submit" class="button">Salvar Receita</button>
            </form>
          </div>
        <?php elseif ($page === 'view_recipe'): ?>
          <?php
          $recipeId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
          $recipe = $recipeId ? getRecipe($recipeId) : null;
          if (!$recipe): ?>
            <div class="card">
              <p>Receita não encontrada.</p>
              <a class="button" href="index.php?page=recipes">Voltar</a>
            </div>
          <?php else: ?>
            <div class="card">
              <h2><?php echo htmlspecialchars($recipe['name']); ?></h2>
              <?php if (!empty($recipe['description'])): ?>
                <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
              <?php endif; ?>
              <p><strong>Porções:</strong> <?php echo htmlspecialchars($recipe['servings']); ?></p>
              <h3>Ingredientes e Cálculos</h3>
              <table class="ingredients-table">
                <thead>
                  <tr>
                    <th>Ingrediente</th>
                    <th>Qtd</th>
                    <th>Unidade</th>
                    <th>Valor Unitário</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recipe['ingredients'] as $ingredient):
                    $total = (float) $ingredient['quantity'] * (float) $ingredient['product_price'];
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($ingredient['product_name']); ?></td>
                      <td><?php echo htmlspecialchars($ingredient['quantity']); ?></td>
                      <td><?php echo htmlspecialchars($ingredient['unit']); ?></td>
                      <td><?php echo formatCurrency((float) $ingredient['product_price']); ?></td>
                      <td><?php echo formatCurrency($total); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <p><strong>Custo total da receita:</strong> <?php echo formatCurrency((float) $recipe['total_cost']); ?></p>
              <p><strong>Custo por porção:</strong> <?php echo formatCurrency($recipe['total_cost'] / max(1, (int) $recipe['servings'])); ?></p>
              <a class="button secondary" href="index.php?page=recipes">Voltar para receitas</a>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </main>
    </div>
    <script>
      function addIngredientRow() {
        const tableBody = document.querySelector('#ingredients-table tbody');
        const template = document.createElement('tr');
        template.innerHTML = `
          <td>
            <select name="ingredient_product_id[]" required>
              <option value="">Selecione</option>
              <?php foreach ($products as $product): ?>
                <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <input type="number" step="0.01" min="0" name="ingredient_quantity[]" placeholder="0" required />
          </td>
          <td>
            <input type="text" name="ingredient_unit[]" placeholder="Ex: gramas" />
          </td>
          <td>
            <button type="button" class="button secondary" onclick="removeRow(this)">Remover</button>
          </td>
        `;
        tableBody.appendChild(template);
      }

      function removeRow(button) {
        const row = button.closest('tr');
        const tableBody = row.parentElement;
        if (tableBody.children.length === 1) {
          alert('A receita deve conter pelo menos um ingrediente.');
          return;
        }
        row.remove();
      }
    </script>
  </body>
</html>

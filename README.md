# Melu Chef - Sistema de Produtos e Receitas

Este projeto é um sistema simples em PHP com SQLite que permite gerenciar produtos, cadastrar receitas personalizadas e visualizar cálculos de custo de cada preparação.

## Recursos principais

- Menu lateral com informações do usuário fictício e navegação rápida.
- Cadastro de produtos com nome, descrição e valor.
- Listagem completa dos produtos cadastrados.
- Criação de receitas personalizadas usando os produtos cadastrados como ingredientes.
- Listagem de receitas com data de criação e acesso aos detalhes.
- Tela detalhada de cada receita com cálculo automático do custo total e custo por porção.

## Como executar

1. Certifique-se de ter o PHP 8.1 ou superior instalado em sua máquina.
2. Em um terminal, execute um servidor embutido do PHP a partir da raiz do projeto:

   ```bash
   php -S localhost:8000
   ```

3. Acesse `http://localhost:8000` no navegador para utilizar o sistema.

O banco de dados utiliza SQLite e é criado automaticamente na pasta `data/` na primeira execução.

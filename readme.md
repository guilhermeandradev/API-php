# PHP REST API

Uma API REST simples construída em PHP puro para gerenciamento de usuários.

## 📋 Descrição

Este projeto implementa uma API REST básica em PHP para realizar operações CRUD (Create, Read, Update, Delete) em uma tabela de usuários. A API utiliza PDO para conexão segura com banco de dados MySQL e retorna respostas em formato JSON.

## 🔧 Configuração

### **Pré-requisitos**
- PHP 7.0 ou superior
- MySQL
- Servidor web (Apache, Nginx, etc.)
- PDO PHP Extension
- XAMPP (opcional para ambiente local)

### **Configuração do Banco de Dados**

1. Crie um banco de dados MySQL chamado `api_mvc`.
2. Crie a tabela `users` com a seguinte estrutura:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL
);
```

3. Configure as credenciais do banco de dados no arquivo `Database.php`:

```php
private $host = "localhost";
private $db_name = "api_mvc";
private $username = "root";
private $password = "";
```

## 🚀 Endpoints

### **GET /**
- Retorna todos os usuários.
- Não requer parâmetros.

### **GET /?id={id}**
- Retorna um usuário específico.
- Parâmetro: `id` (ID do usuário).

### **POST /**
- Cria um novo usuário.
- Corpo da requisição (JSON):

```json
{
    "name": "Nome do Usuário",
    "description": "Descrição do Usuário"
}
```

### **PUT /?id={id}**
- Atualiza um usuário existente.
- Parâmetro: `id` (ID do usuário).
- Corpo da requisição (JSON):

```json
{
    "name": "Novo Nome",
    "description": "Nova Descrição"
}
```

### **DELETE /?id={id}**
- Remove um usuário.
- Parâmetro: `id` (ID do usuário).

## 🔒 Segurança

O projeto implementa algumas práticas básicas de segurança:
- Uso de PDO para prevenção de SQL Injection.
- Prepared Statements para todas as queries.
- Validação de dados de entrada.
- Headers de resposta apropriados.

## 📝 Estrutura do Projeto

```
├── index.php        # Ponto de entrada da API e manipulação de rotas
└── Data
    └── Database.php  # Classe de conexão com o banco de dados
```

## ⚠️ Notas Importantes

Este é um projeto básico e pode precisar de adaptações para uso em produção.


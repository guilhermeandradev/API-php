<?php

header("Content-Type: application/json");

require_once "Data/Database.php";

$database = new Database();
$conn = $database->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($item ?: ["message" => "Item não encontrado"]);
        } else {
            $stmt = $conn->query("SELECT * FROM users");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['name']) && isset($data['description'])) {
            $stmt = $conn->prepare("INSERT INTO users (name, description) VALUES (:name, :description)");
            $stmt->bindParam(":name", $data['name']);
            $stmt->bindParam(":description", $data['description']);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Item criado com sucesso"]);
            } else {
                echo json_encode(["message" => "Erro ao criar item"]);
            }
        } else {
            echo json_encode(["message" => "Dados incompletos"]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($_GET['id']) && isset($data['name']) && isset($data['description'])) {
            $stmt = $conn->prepare("UPDATE users SET name = :name, description = :description WHERE id = :id");
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->bindParam(":name", $data['name']);
            $stmt->bindParam(":description", $data['description']);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Item atualizado com sucesso"]);
            } else {
                echo json_encode(["message" => "Erro ao atualizar item"]);
            }
        } else {
            echo json_encode(["message" => "Dados incompletos"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(":id", $_GET['id']);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Item deletado com sucesso"]);
            } else {
                echo json_encode(["message" => "Erro ao deletar item"]);
            }
        } else {
            echo json_encode(["message" => "ID não fornecido"]);
        }
        break;

    default:
        echo json_encode(["message" => "Método não suportado"]);
    break;
}
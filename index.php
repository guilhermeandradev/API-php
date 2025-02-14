<?php

//vou deixar comentado para melhor entedimento da estrutura e o que fiz.

// cabeçalhos para permitir requisições externas 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "Data/Database.php";
// URL e token( não tem senha definida )
class Index {
    private $conn;
    private $webhookUrl = 'https://webhook.fiqon.app/webhook/async/9e324339-4955-4a66-9b7d-ccbfac86a9a8';
    private $webhookToken = '1111111'; 

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    private function getRequestData() {
        return json_decode(file_get_contents("php://input"), true);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'OPTIONS':
                    $this->sendResponse(['status' => 'ok']);
                    break;
                case 'GET':
                    $this->handleGet();
                    break;
                case 'POST':
                    $this->handlePost();
                    break;
                case 'PUT':
                    $this->handlePut();
                    break;
                case 'DELETE':
                    $this->handleDelete();
                    break;
                default:
                    $this->sendResponse(["error" => "Método não suportado"], 405);
            }
        } catch (Exception $e) {
            $this->sendResponse(["error" => $e->getMessage()], 500);
        }
    }

    private function handleGet() {
        if (isset($_GET['id'])) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                $this->sendResponse(["error" => "Usuário não encontrado"], 404);
            }

            $this->sendResponse($item);
        } else {
            $stmt = $this->conn->query("SELECT * FROM users");
            $this->sendResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    }
// REQUISIÇÃO PRINCIPAL 
    private function handlePost() {
        $data = $this->getRequestData();

        if (!isset($data['name']) || !isset($data['description']) || !isset($data['email'])) {
            $this->sendResponse(["error" => "Campos obrigatórios: name, description, email"], 400);
        }

        $stmt = $this->conn->prepare("INSERT INTO users (name, description, email) VALUES (:name, :description, :email)");
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":email", $data['email']);

        if (!$stmt->execute()) {
            $this->sendResponse(["error" => "Erro ao criar usuário"], 500);
        }

        $id = $this->conn->lastInsertId();

        //  Envia os dados ao Webhook via cURL
        $webhookResponse = $this->sendToWebhook([
            "message" => "Novo usuário cadastrado: " . $data['name'],
            "email" => $data['email']
        ]);

        $this->sendResponse([
            "message" => "Usuário criado com sucesso",
            "id" => $id,
            "webhook" => $webhookResponse
        ], 201);
    }

    private function handlePut() {
        if (!isset($_GET['id'])) {
            $this->sendResponse(["error" => "ID não fornecido"], 400);
        }

        $data = $this->getRequestData();
        if (!isset($data['name']) || !isset($data['description']) || !isset($data['email'])) {
            $this->sendResponse(["error" => "Campos obrigatórios: name, description, email"], 400);
        }

        $stmt = $this->conn->prepare("UPDATE users SET name = :name, description = :description, email = :email WHERE id = :id");
        $stmt->bindParam(":id", $_GET['id']);
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":email", $data['email']);

        if (!$stmt->execute()) {
            $this->sendResponse(["error" => "Erro ao atualizar usuário"], 500);
        }

        if ($stmt->rowCount() === 0) {
            $this->sendResponse(["error" => "Usuário não encontrado"], 404);
        }

        $this->sendResponse(["message" => "Usuário atualizado com sucesso"]);
    }

    private function handleDelete() {
        if (!isset($_GET['id'])) {
            $this->sendResponse(["error" => "ID não fornecido"], 400);
        }

        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(":id", $_GET['id']);

        if (!$stmt->execute()) {
            $this->sendResponse(["error" => "Erro ao deletar usuário"], 500);
        }

        if ($stmt->rowCount() === 0) {
            $this->sendResponse(["error" => "Usuário não encontrado"], 404);
        }

        $this->sendResponse(["message" => "Usuário deletado com sucesso"]);
    }

    // Esse é o Mmétodo que envia os dados para o Webhook via cURL e os tratamentos de erro
    private function sendToWebhook($data){
        $jsonData = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $this->webhookToken
        ]);

        // Requisição sendo executada
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Verifica se houve erro na requisição
        if (curl_errno($ch)) {
            return [
                "status_code" => $httpCode,
                "error" => curl_error($ch)
            ];
        }

        curl_close($ch);

        // Retorna a resposta do Webhook e o código HTTP
        return [
            "status_code" => $httpCode,
            "response" => json_decode($response, true)
        ];
    }
}

$api = new Index();
$api->handleRequest();

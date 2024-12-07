<?php

namespace App\Services;

use App\Database;

class LoteService
{
    /**
     * Procesa la solicitud HTTP y ejecuta la operación CRUD correspondiente.
     *
     * @param string $method Método HTTP de la solicitud.
     */
    public function handleRequest(string $method): void
    {
        // Inicializa la base de datos antes de procesar cualquier solicitud
        Database::setDB();

        switch ($method) {
            case 'GET':
                $this->readLotes();
                break;
            case 'POST':
                $this->createLote();
                break;
            case 'PUT':
                $this->updateLote();
                break;
            case 'DELETE':
                $this->deleteLote();
                break;
            default:
                $this->response(405, [
                    'status' => false,
                    'message' => 'Método no permitido.'
                ]);
        }
    }

    /**
     * Crea un nuevo lote en la base de datos.
     */
    private function createLote(): void
    {
        $data = $this->getJsonInput();

        // Validar datos obligatorios
        if (!isset($data['lote'], $data['precio'], $data['clientID'])) {
            $this->response(400, [
                'status' => false,
                'message' => 'Faltan datos obligatorios: lote, precio, clientID.'
            ]);
            return;
        }

        $stmt = Database::getConnection()->prepare(
            "INSERT INTO debts (lote, precio, clientID, vencimiento) VALUES (:lote, :precio, :clientID, :vencimiento)"
        );

        $stmt->bindValue(':lote', $data['lote']);
        $stmt->bindValue(':precio', $data['precio'], SQLITE3_INTEGER);
        $stmt->bindValue(':clientID', $data['clientID'], SQLITE3_INTEGER);
        $stmt->bindValue(':vencimiento', $data['vencimiento'] ?? null);

        $this->executeStatement($stmt, 'Lote creado exitosamente.', 'Error al crear el lote.');
    }

    private function readLotes(): void
    {
        $loteID = $_GET['loteID'] ?? null;

        $query = $loteID
            ? "SELECT * FROM debts WHERE lote = :loteID"
            : "SELECT * FROM debts";

        $stmt = Database::getConnection()->prepare($query);

        if ($loteID) {
            $stmt->bindValue(':loteID', $loteID);
        }

        $result = $stmt->execute();
        $lotes = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['clientID'] = (int)$row['clientID'];
            $lotes[] = $row;
        }

        if ($lotes) {
            $this->response(200, [
                'status' => true,
                'message' => 'Lotes encontrados.',
                'data' => $lotes
            ]);
        } else {
            $this->response(404, [
                'status' => false,
                'message' => 'No se encontraron lotes.',
                'data' => []
            ]);
        }
    }

    private function updateLote(): void
    {
        $data = $this->getJsonInput();

        if (!isset($data['id'], $data['lote'], $data['precio'], $data['clientID'])) {
            $this->response(400, [
                'status' => false,
                'message' => 'Faltan datos obligatorios: id, lote, precio, clientID.'
            ]);
            return;
        }

        $stmt = Database::getConnection()->prepare(
            "UPDATE debts SET lote = :lote, precio = :precio, clientID = :clientID, vencimiento = :vencimiento WHERE id = :id"
        );

        $stmt->bindValue(':id', $data['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':lote', $data['lote']);
        $stmt->bindValue(':precio', $data['precio'], SQLITE3_INTEGER);
        $stmt->bindValue(':clientID', $data['clientID'], SQLITE3_INTEGER);
        $stmt->bindValue(':vencimiento', $data['vencimiento'] ?? null);

        $this->executeStatement($stmt, 'Lote actualizado exitosamente.', 'Error al actualizar el lote.');
    }

    private function deleteLote(): void
    {
        $data = $this->getJsonInput();

        if (!isset($data['id'])) {
            $this->response(400, [
                'status' => false,
                'message' => 'El ID del lote es obligatorio.'
            ]);
            return;
        }

        $stmt = Database::getConnection()->prepare("DELETE FROM debts WHERE id = :id");
        $stmt->bindValue(':id', $data['id'], SQLITE3_INTEGER);

        $this->executeStatement($stmt, 'Lote eliminado exitosamente.', 'Error al eliminar el lote.');
    }

    private function getJsonInput(): array
    {
        return json_decode(file_get_contents("php://input"), true) ?? [];
    }

    private function executeStatement(\SQLite3Stmt $stmt, string $successMessage, string $errorMessage): void
    {
        if ($stmt->execute()) {
            $this->response(200, [
                'status' => true,
                'message' => $successMessage
            ]);
        } else {
            $this->response(500, [
                'status' => false,
                'message' => $errorMessage
            ]);
        }
    }

    private function response(int $statusCode, array $data): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }
}

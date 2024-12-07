<?php

require_once 'Database.php';

// Configuración de encabezados para el servicio REST
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Origin: *");

class LoteService
{
    /**
     * Maneja la solicitud REST y ejecuta la operación CRUD correspondiente.
     */
    public static function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD']; // Determina el método HTTP usado

        switch ($method) {
            case 'GET':
                self::readLotes();
                break;
            case 'POST':
                self::createLote();
                break;
            case 'PUT':
                self::updateLote();
                break;
            case 'DELETE':
                self::deleteLote();
                break;
            default:
                http_response_code(405); // Código 405: Método no permitido
                echo json_encode([
                    'status' => false,
                    'message' => 'Método no permitido.'
                ]);
        }
    }

    /**
     * Operación CREATE: Crea un nuevo lote en la base de datos.
     */
    private static function createLote(): void
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['lote'], $data['precio'], $data['clientID'])) {
            http_response_code(400); // Código 400: Solicitud incorrecta
            echo json_encode([
                'status' => false,
                'message' => 'Faltan datos obligatorios: lote, precio, clientID.'
            ]);
            return;
        }

        $cnx = Database::getConnection();
        $stmt = $cnx->prepare("INSERT INTO debts (lote, precio, clientID, vencimiento) VALUES (:lote, :precio, :clientID, :vencimiento)");
        $stmt->bindValue(':lote', $data['lote'], SQLITE3_TEXT);
        $stmt->bindValue(':precio', $data['precio'], SQLITE3_INTEGER);
        $stmt->bindValue(':clientID', $data['clientID'], SQLITE3_INTEGER);
        $stmt->bindValue(':vencimiento', $data['vencimiento'] ?? null, SQLITE3_TEXT);

        if ($stmt->execute()) {
            http_response_code(201); // Código 201: Creado
            echo json_encode([
                'status' => true,
                'message' => 'Lote creado exitosamente.'
            ]);
        } else {
            http_response_code(500); // Código 500: Error interno del servidor
            echo json_encode([
                'status' => false,
                'message' => 'Error al crear el lote.'
            ]);
        }
    }

    /**
     * Operación READ: Obtiene los lotes de la base de datos.
     */
    private static function readLotes(): void
    {
        $loteID = $_GET['loteID'] ?? null;

        $cnx = Database::getConnection();

        if ($loteID) {
            // Leer un lote específico
            $stmt = $cnx->prepare("SELECT * FROM debts WHERE lote = :loteID");
            $stmt->bindValue(':loteID', $loteID, SQLITE3_TEXT);
        } else {
            // Leer todos los lotes
            $stmt = $cnx->prepare("SELECT * FROM debts");
        }

        $result = $stmt->execute();
        $lotes = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['clientID'] = (int) $row['clientID']; // Convertir clientID a entero
            $lotes[] = $row;
        }

        if (!empty($lotes)) {
            http_response_code(200); // Código 200: Éxito
            echo json_encode([
                'status' => true,
                'message' => 'Lotes encontrados.',
                'data' => $lotes
            ]);
        } else {
            http_response_code(404); // Código 404: No encontrado
            echo json_encode([
                'status' => false,
                'message' => 'No se encontraron lotes.',
                'data' => []
            ]);
        }
    }

    /**
     * Operación UPDATE: Actualiza un lote en la base de datos.
     */
    private static function updateLote(): void
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id'], $data['lote'], $data['precio'], $data['clientID'])) {
            http_response_code(400); // Código 400: Solicitud incorrecta
            echo json_encode([
                'status' => false,
                'message' => 'Faltan datos obligatorios: id, lote, precio, clientID.'
            ]);
            return;
        }

        $cnx = Database::getConnection();
        $stmt = $cnx->prepare("UPDATE debts SET lote = :lote, precio = :precio, clientID = :clientID, vencimiento = :vencimiento WHERE id = :id");
        $stmt->bindValue(':id', $data['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':lote', $data['lote'], SQLITE3_TEXT);
        $stmt->bindValue(':precio', $data['precio'], SQLITE3_INTEGER);
        $stmt->bindValue(':clientID', $data['clientID'], SQLITE3_INTEGER);
        $stmt->bindValue(':vencimiento', $data['vencimiento'] ?? null, SQLITE3_TEXT);

        if ($stmt->execute()) {
            http_response_code(200); // Código 200: Éxito
            echo json_encode([
                'status' => true,
                'message' => 'Lote actualizado exitosamente.'
            ]);
        } else {
            http_response_code(500); // Código 500: Error interno del servidor
            echo json_encode([
                'status' => false,
                'message' => 'Error al actualizar el lote.'
            ]);
        }
    }

    /**
     * Operación DELETE: Elimina un lote de la base de datos.
     */
    private static function deleteLote(): void
    {
        parse_str(file_get_contents("php://input"), $data);

        if (!isset($data['id'])) {
            http_response_code(400); // Código 400: Solicitud incorrecta
            echo json_encode([
                'status' => false,
                'message' => 'El ID del lote es obligatorio.'
            ]);
            return;
        }

        $cnx = Database::getConnection();
        $stmt = $cnx->prepare("DELETE FROM debts WHERE id = :id");
        $stmt->bindValue(':id', $data['id'], SQLITE3_INTEGER);

        if ($stmt->execute()) {
            http_response_code(200); // Código 200: Éxito
            echo json_encode([
                'status' => true,
                'message' => 'Lote eliminado exitosamente.'
            ]);
        } else {
            http_response_code(500); // Código 500: Error interno del servidor
            echo json_encode([
                'status' => false,
                'message' => 'Error al eliminar el lote.'
            ]);
        }
    }
}

// Manejar la solicitud
LoteService::handleRequest();

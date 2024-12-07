<?php 

require_once 'Database.php';

class DesafioUno {

    /**
     * Recupera las deudas de un cliente específico y las muestra en formato JSON.
     * 
     * @param int $clientID El ID del cliente para el cual se obtendrán las deudas.
     */
    public static function getClientDebt(int $clientID): void
    {
        // Inicializa la base de datos y recupera todos los lotes
        Database::setDB();
        $lotes = self::getLotes();

        // Estructura inicial del JSON de respuesta
        $response = [
            'status' => true,
            'message' => 'No hay Lotes para cobrar',
            'data' => [
                'total' => 0,
                'detail' => []
            ]
        ];

        // Iterar sobre los lotes y filtrar los que corresponden al cliente especificado
        foreach ($lotes as $lote) {
            // Verifica que el lote tenga vencimiento y coincida con el cliente
            if (!empty($lote->vencimiento) && $lote->clientID === (string) $clientID) {
                $response['status'] = true;
                $response['message'] = 'Tienes Lotes para cobrar';
                $response['data']['total'] += $lote->precio;

                // Agregar el detalle del lote al arreglo de detalles
                $response['data']['detail'][] = [
                    'id' => $lote->id,
                    'lote' => $lote->lote,
                    'precio' => $lote->precio,
                    'clientID' => $lote->clientID,
                    'vencimiento' => $lote->vencimiento
                ];
            }
        }

        // Mostrar la respuesta en formato JSON
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    /**
     * Recupera todos los lotes de la base de datos.
     * 
     * @return array Un arreglo de objetos que representan los lotes.
     */
    private static function getLotes(): array
    {
        $lotes = [];
        $cnx = Database::getConnection();

        // Ejecutar la consulta para obtener todos los lotes
        $stmt = $cnx->query("SELECT * FROM debts");
        while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
            // Convertir clientID a string para garantizar consistencia
            $row['clientID'] = (string) $row['clientID'];
            $lotes[] = (object) $row;
        }

        return $lotes;
    }
}

// Ejecutar la función para recuperar las deudas del cliente 123456
DesafioUno::getClientDebt(123456);

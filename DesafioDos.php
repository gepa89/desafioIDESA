<?php 
require_once 'Database.php';

class DesafioDos {

    /**
     * Recupera los lotes de la base de datos según el ID del lote especificado y los devuelve en formato JSON.
     * 
     * @param string $loteID El ID del lote a buscar en la base de datos.
     */
    public static function retriveLotes(string $loteID): void {
        // Inicializa la base de datos y obtiene los lotes correspondientes
        Database::setDB();

        // Convierte los lotes obtenidos a formato JSON y los muestra
        echo json_encode(self::getLotes($loteID), JSON_PRETTY_PRINT);
    }

    /**
     * Obtiene todos los lotes de la base de datos que coincidan con el ID del lote especificado.
     * 
     * @param string $loteID El ID del lote a buscar.
     * @return array Un arreglo de lotes que cumplen con el criterio de búsqueda.
     */
    private static function getLotes(string $loteID): array {
        $lotes = [];
        $cnx = Database::getConnection();

        // Preparar la consulta SQL para evitar inyecciones
        $stmt = $cnx->prepare("SELECT * FROM debts WHERE lote = :loteID");
        $stmt->bindValue(':loteID', $loteID, SQLITE3_TEXT);

        // Ejecutar la consulta
        $result = $stmt->execute();

        // Iterar sobre los resultados y construir el arreglo de lotes
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            // Convertir clientID a entero para garantizar consistencia con el JSON esperado
            $row['clientID'] = (int) $row['clientID'];

            // Agregar el lote al arreglo
            $lotes[] = $row;
        }

        return $lotes;
    }
}

// Ejecutar la función para recuperar los lotes con el ID '00148'
DesafioDos::retriveLotes('00148');

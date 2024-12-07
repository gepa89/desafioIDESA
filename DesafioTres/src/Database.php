<?php

namespace App;

use SQLite3;

class Database
{
    private static string $dbPath = __DIR__ . '/Db/idesa.db'; // Ruta absoluta al archivo de la base de datos
    private static ?SQLite3 $connection = null;

    /**
     * Configura la base de datos, crea la tabla y la llena con datos iniciales.
     */
    public static function setDB(): void
    {
        // Verificar si el directorio 'Db' existe, si no, crearlo
        $dbDir = dirname(self::$dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        $db = self::getConnection();

        try {
            $db->exec("BEGIN TRANSACTION");

            // Crear la tabla debts
            $db->exec("DROP TABLE IF EXISTS debts");
            $db->exec("CREATE TABLE debts(
                id INTEGER PRIMARY KEY, 
                lote TEXT, 
                precio INT, 
                clientID INT,  
                vencimiento DATE
            )");

            // Insertar datos iniciales
            $db->exec("INSERT INTO debts(id, lote, precio, clientID, vencimiento) 
                        VALUES (1, '00145', 150000, 123456, '2022-09-01')");
            $db->exec("INSERT INTO debts(id, lote, precio, clientID, vencimiento) 
                        VALUES (2, '00146', 110000, 135486, NULL)");
            $db->exec("INSERT INTO debts(id, lote, precio, clientID, vencimiento) 
                        VALUES (3, '00147', 160000, 135486, NULL)");
            $db->exec("INSERT INTO debts(id, lote, precio, clientID, vencimiento) 
                        VALUES (4, '00148', 130000, 123456, '2022-10-01')");
            $db->exec("INSERT INTO debts(id, lote, precio, clientID, vencimiento) 
                        VALUES (5, '00148', 145000, 123456, NULL)");
            $db->exec("INSERT INTO debts(id, lote, precio, clientID, vencimiento) 
                        VALUES (6, '00148', 190000, 123456, '2022-12-01')");
            $db->exec("INSERT INTO debts(id, lote, precio, clientID, vencimiento) 
                        VALUES (7, '00148', 190000, 123456, '2023-01-01')");
            $db->exec("INSERT INTO debts(id, lote, precio, clientID, vencimiento) 
                        VALUES (8, '00148', 190000, 123456, '2023-02-01')");

            $db->exec("COMMIT");

        } catch (\Exception $e) {
            $db->exec("ROLLBACK");
            echo "An error occurred: " . $e->getMessage();
        }
    }

    /**
     * Obtiene la conexión a la base de datos SQLite.
     *
     * @return SQLite3
     */
    public static function getConnection(): SQLite3
    {
        if (self::$connection === null) {
            self::$connection = new SQLite3(self::$dbPath);
        }

        return self::$connection;
    }
}

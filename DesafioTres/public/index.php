<?php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Services/LoteService.php';

use App\Services\LoteService;

$method = $_SERVER['REQUEST_METHOD'];

// Soporte para sobrescribir métodos con un encabezado o parámetro
if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
} elseif (isset($_POST['_method'])) {
    $method = $_POST['_method'];
}

// Depuración: Imprime el método sobrescrito
file_put_contents('php://stdout', "Método sobrescrito recibido: " . $method . PHP_EOL);

// Crear instancia del servicio
$service = new LoteService();

// Manejar la solicitud basada en el método HTTP
$service->handleRequest($method);

<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/database.php';

// Obtener todos los usuarios (vendedores)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, nombre, username FROM usuarios ORDER BY nombre");
        $usuarios = $stmt->fetchAll();
        echo json_encode($usuarios);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener vendedores: ' . $e->getMessage()]);
    }
}
?>
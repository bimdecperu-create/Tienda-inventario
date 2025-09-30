<?php
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');

include 'config/database.php';

echo "=== DEBUG INFORMATION ===\n\n";

// Verificar conexión a base de datos
echo "Database connection: OK\n";

// Verificar si los datos se reciben
echo "Request method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Raw POST data: " . file_get_contents('php://input') . "\n";

// Verificar si los datos se pueden decodificar
$data = json_decode(file_get_contents('php://input'), true);
echo "Decoded data: " . print_r($data, true) . "\n";

// Verificar si la tabla productos existe
$stmt = $pdo->query("SHOW TABLES LIKE 'productos'");
if ($stmt->rowCount() > 0) {
    echo "Table 'productos' exists: OK\n";
} else {
    echo "Table 'productos' does not exist!\n";
}

// Verificar si la tabla ventas existe
$stmt = $pdo->query("SHOW TABLES LIKE 'ventas'");
if ($stmt->rowCount() > 0) {
    echo "Table 'ventas' exists: OK\n";
} else {
    echo "Table 'ventas' does not exist!\n";
}

// Verificar si hay productos
$stmt = $pdo->query("SELECT COUNT(*) as count FROM productos");
$row = $stmt->fetch();
echo "Number of products: " . $row['count'] . "\n";

// Verificar si hay usuarios
$stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
$row = $stmt->fetch();
echo "Number of users: " . $row['count'] . "\n";

echo "\n=== END DEBUG ===";
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'u759470939_inventario';
$username = 'u759470939_weldshop';
$password = '&[UA0r6Nx';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión exitosa a la base de datos!\n";
    
    // Verificar si las tablas existen
    $stmt = $pdo->query("SHOW TABLES LIKE 'productos'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabla 'productos' existe\n";
    } else {
        echo "❌ Tabla 'productos' no existe\n";
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'ventas'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabla 'ventas' existe\n";
    } else {
        echo "❌ Tabla 'ventas' no existe\n";
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabla 'usuarios' existe\n";
    } else {
        echo "❌ Tabla 'usuarios' no existe\n";
    }
    
} catch(PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}
?>
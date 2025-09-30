<?php
// Configuración de la base de datos - ACTUALIZA ESTOS DATOS
$host = 'localhost';
$dbname = 'u759470939_inventariowel'; // Tu nombre de base de datos
$username = 'u759470939_tienda';  // Tu usuario de MySQL
$password = 'L@yA64]6b';     // Tu contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Depuración: Mostrar que la conexión se estableció correctamente
    error_log('Conexión a la base de datos establecida correctamente');
} catch(PDOException $e) {
    error_log('Error de conexión: ' . $e->getMessage());
    die("Conexión fallida: " . $e->getMessage());
}
?>
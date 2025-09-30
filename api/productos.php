<?php
// Desactivar errores para producción
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/database.php';

// Obtener todos los productos
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM productos ORDER BY nombre");
        $productos = $stmt->fetchAll();
        echo json_encode($productos);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener productos: ' . $e->getMessage()]);
    }
}

// Agregar nuevo producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO productos (nombre, categoria, stock, precio, proveedor, stock_minimo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre'],
            $data['categoria'],
            $data['stock'],
            $data['precio'],
            $data['proveedor'],
            $data['stock_minimo']
        ]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al agregar producto: ' . $e->getMessage()]);
    }
}

// Eliminar producto
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de producto requerido']);
            exit;
        }
        
        // Verificar si el producto existe
        $stmt = $pdo->prepare("SELECT id FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
        
        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            exit;
        }
        
        // Eliminar el producto
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No se pudo eliminar el producto']);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar producto: ' . $e->getMessage()]);
    }
}

// Actualizar producto
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de producto requerido']);
            exit;
        }
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
        }
        
        // Verificar si el producto existe
        $stmt = $pdo->prepare("SELECT id FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
        
        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            exit;
        }
        
        // Actualizar el producto
        $stmt = $pdo->prepare("
            UPDATE productos SET 
                nombre = ?, categoria = ?, stock = ?, precio = ?, 
                proveedor = ?, stock_minimo = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['nombre'],
            $data['categoria'],
            $data['stock'],
            $data['precio'],
            $data['proveedor'] ?? null,
            $data['stock_minimo'] ?? 10,
            $id
        ]);
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar producto: ' . $e->getMessage()]);
    }
}

?>
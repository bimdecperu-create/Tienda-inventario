<?php
// Desactivar errores para producción
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/database.php';

// Obtener todas las ventas
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT 
                v.id, v.producto_id, v.cantidad, v.precio_unitario, v.precio_con_descuento, 
                v.descuento_aplicado, v.total, v.cliente, v.telefono, v.vendedor_id, 
                v.estado, v.metodo_pago, v.precio_recibido, v.saldo_restante, v.fecha_venta,
                p.nombre as producto_nombre, u.nombre as vendedor_nombre 
            FROM ventas v 
            JOIN productos p ON v.producto_id = p.id 
            JOIN usuarios u ON v.vendedor_id = u.id 
            ORDER BY v.fecha_venta DESC
        ");
        $ventas = $stmt->fetchAll();
        echo json_encode($ventas);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener ventas: ' . $e->getMessage()]);
    }
}

// Registrar nueva venta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        error_log('Datos recibidos: ' . print_r($data, true)); // ¡LINEA DE DEPURACIÓN!
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
        }
        
        // Validar campos requeridos
        if (!isset($data['producto_id']) || !isset($data['cantidad']) || !isset($data['precio_unitario']) || !isset($data['cliente']) || !isset($data['vendedor_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Campos requeridos faltantes']);
            exit;
        }
        
        // Calcular descuento si existe
        $precio_con_descuento = isset($data['precio_con_descuento']) ? $data['precio_con_descuento'] : $data['precio_unitario'];
        $descuento_aplicado = $data['precio_unitario'] - $precio_con_descuento;
        // Validación de campos opcionales - Asegurar valores predeterminados
        if (!isset($data['telefono']) || $data['telefono'] === '') {
            $data['telefono'] = null;
        }
        if (!isset($data['metodo_pago']) || $data['metodo_pago'] === '') {
            $data['metodo_pago'] = null;
        }
        if (!isset($data['precio_recibido']) || $data['precio_recibido'] === '') {
            $data['precio_recibido'] = 0;
        }
        if (!isset($data['saldo_restante']) || $data['saldo_restante'] === '') {
            $data['saldo_restante'] = 0;
        }
        $total = $precio_con_descuento * $data['cantidad'];
        
        $pdo->beginTransaction();
        
        // Registrar la venta
        $stmt = $pdo->prepare("
            INSERT INTO ventas (
                producto_id, cantidad, precio_unitario, precio_con_descuento, 
                descuento_aplicado, total, estado, cliente, telefono, vendedor_id,
                metodo_pago, precio_recibido, saldo_restante
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['producto_id'],
            $data['cantidad'],
            $data['precio_unitario'],
            $precio_con_descuento,
            $descuento_aplicado,
            $total,
            $data['estado'],
            $data['cliente'],
            $data['telefono'], // ¡NUEVO CAMPO AGREGADO!
            $data['vendedor_id'],
            $data['metodo_pago'], // ¡NUEVO CAMPO AGREGADO!
            $data['precio_recibido'],
            $data['saldo_restante']
        ]);
        
        // Actualizar stock
        $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$data['cantidad'], $data['producto_id']]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch(PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Error al registrar venta: ' . $e->getMessage()]);
    }
}

// Editar venta
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $venta_id = $data['id'] ?? null;
        
        if (!$venta_id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de venta requerido']);
            exit;
        }
        
        // Obtener la venta original para restaurar stock
        $stmt = $pdo->prepare("SELECT * FROM ventas WHERE id = ?");
        $stmt->execute([$venta_id]);
        $venta_original = $stmt->fetch();
        
        if (!$venta_original) {
            http_response_code(404);
            echo json_encode(['error' => 'Venta no encontrada']);
            exit;
        }
        
        // Restaurar stock original
        $stmt = $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $stmt->execute([$venta_original['cantidad'], $venta_original['producto_id']]);
        
        // Aplicar nueva cantidad
        $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$data['cantidad'], $data['producto_id']]);
        
        // Actualizar la venta
        $precio_con_descuento = isset($data['precio_con_descuento']) ? $data['precio_con_descuento'] : $data['precio_unitario'];
        $descuento_aplicado = $data['precio_unitario'] - $precio_con_descuento;
        // Validación de campos opcionales - Asegurar valores predeterminados
        if (!isset($data['telefono']) || $data['telefono'] === '') {
            $data['telefono'] = null;
        }
        if (!isset($data['metodo_pago']) || $data['metodo_pago'] === '') {
            $data['metodo_pago'] = null;
        }
        if (!isset($data['precio_recibido']) || $data['precio_recibido'] === '') {
            $data['precio_recibido'] = 0;
        }
        if (!isset($data['saldo_restante']) || $data['saldo_restante'] === '') {
            $data['saldo_restante'] = 0;
        }
        $total = $precio_con_descuento * $data['cantidad'];
        
        $stmt = $pdo->prepare("
            UPDATE ventas SET 
                producto_id = ?, cantidad = ?, precio_unitario = ?, 
                precio_con_descuento = ?, descuento_aplicado = ?, total = ?, 
                estado = ?, cliente = ?, telefono = ?, vendedor_id = ?,
                metodo_pago = ?, precio_recibido = ?, saldo_restante = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['producto_id'],
            $data['cantidad'],
            $data['precio_unitario'],
            $precio_con_descuento,
            $descuento_aplicado,
            $total,
            $data['estado'],
            $data['cliente'],
            $data['telefono'], // ¡NUEVO CAMPO AGREGADO!
            $data['vendedor_id'],
            $data['metodo_pago'], // ¡NUEVO CAMPO AGREGADO!
            $data['precio_recibido'],
            $data['saldo_restante'],
            $venta_id
        ]);
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al editar venta: ' . $e->getMessage()]);
    }
}

// Eliminar venta
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de venta requerido']);
            exit;
        }
        
        // Obtener la venta para restaurar stock
        $stmt = $pdo->prepare("SELECT * FROM ventas WHERE id = ?");
        $stmt->execute([$id]);
        $venta = $stmt->fetch();
        
        if ($venta) {
            // Restaurar stock
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$venta['cantidad'], $venta['producto_id']]);
        }
        
        // Eliminar la venta
        $stmt = $pdo->prepare("DELETE FROM ventas WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Venta no encontrada']);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar venta: ' . $e->getMessage()]);
    }
}
?>
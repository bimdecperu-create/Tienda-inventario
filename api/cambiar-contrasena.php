<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Verificar que los datos requeridos existan
        if (!isset($data['user_id']) || !isset($data['current_password']) || !isset($data['new_password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit;
        }
        
        // Verificar que el usuario exista
        $stmt = $pdo->prepare("SELECT id, password FROM usuarios WHERE id = ?");
        $stmt->execute([$data['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }
        
        // Verificar contraseña actual
        if (!password_verify($data['current_password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Contraseña actual incorrecta']);
            exit;
        }
        
        // Generar hash de la nueva contraseña
        $newPasswordHash = password_hash($data['new_password'], PASSWORD_DEFAULT);
        
        // Actualizar contraseña
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $result = $stmt->execute([$newPasswordHash, $data['user_id']]);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al actualizar contraseña']);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
    }
}
?>
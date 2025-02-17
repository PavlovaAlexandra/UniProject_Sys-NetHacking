<?php
require_once 'db.php';

function logEvent($user_id, $event_type, $event_description) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO logs (user_id, event_type, event_description)
                               VALUES (:user_id, :event_type, :event_description)");
        
        // Выполнение запроса с переданными параметрами
        $stmt->execute([
            'user_id' => $user_id, // ID пользователя (или NULL)
            'event_type' => $event_type, // Тип события
            'event_description' => $event_description // Описание события
        ]);
    } catch (PDOException $e) {
        error_log('Error logging event: ' . $e->getMessage());
    }
}
?>

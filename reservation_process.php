<?php

require_once 'config.php';
require_once 'TimeRestrictionValidator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = new TimeRestrictionValidator();
    $currentTime = date('Y-m-d H:i:s');
    
    if (!$validator->isValidReservationTime($currentTime)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Reservations are only allowed between 8:30 AM and 5:30 PM'
        ]);
        exit;
    }
    
    // Your existing reservation logic here
    // If validation passes, proceed with the reservation
}

?>
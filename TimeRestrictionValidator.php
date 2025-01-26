<?php
// TimeRestrictionValidator.php
class TimeRestrictionValidator {
    private $startTime = '08:30:00';
    private $endTime = '17:30:00';
    
    public function isValidReservationTime($requestedTime) {
        $currentTime = date('H:i:s', strtotime($requestedTime));
        return ($currentTime >= $this->startTime && $currentTime <= $this->endTime);
    }
    
    public function cleanInvalidTimeReservations($conn) {
        $query = "DELETE FROM pc_reservations 
                 WHERE TIME(start_time) < ? 
                 OR TIME(start_time) > ?";
                 
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $this->startTime, $this->endTime);
        return $stmt->execute();
    }
}
?>
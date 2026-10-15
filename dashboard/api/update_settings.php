<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($data) {
    $query = "UPDATE settings SET 
              min_temp = :minTemp, 
              max_temp = :maxTemp, 
              target_temp = :targetTemp, 
              soil_threshold = :soilThreshold, 
              pump_duration = :pumpDuration 
              WHERE id = 1";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":minTemp", $data->minTemp);
    $stmt->bindParam(":maxTemp", $data->maxTemp);
    $stmt->bindParam(":targetTemp", $data->targetTemp);
    $stmt->bindParam(":soilThreshold", $data->soilThreshold);
    $stmt->bindParam(":pumpDuration", $data->pumpDuration);
    
    if ($stmt->execute()) {
        echo json_encode(array("success" => true, "message" => "Settings updated successfully"));
    } else {
        echo json_encode(array("success" => false, "message" => "Error updating settings"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Invalid data"));
}
?>

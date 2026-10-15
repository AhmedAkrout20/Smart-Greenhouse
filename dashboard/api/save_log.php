<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($data && isset($data->message)) {
    $query = "INSERT INTO system_logs (message, log_level) VALUES (:message, :level)";
    $stmt = $db->prepare($query);
    
    $level = isset($data->level) ? $data->level : 'info';
    
    $stmt->bindParam(":message", $data->message);
    $stmt->bindParam(":level", $level);
    
    if ($stmt->execute()) {
        echo json_encode(array("message" => "Log saved"));
    } else {
        echo json_encode(array("message" => "Error saving log"));
    }
}
?>

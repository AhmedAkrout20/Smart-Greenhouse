<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($data && isset($data->title) && isset($data->message)) {
    $query = "INSERT INTO alerts (alert_type, title, message) VALUES (:type, :title, :message)";
    $stmt = $db->prepare($query);
    
    $type = isset($data->type) ? $data->type : 'info';
    
    $stmt->bindParam(":type", $type);
    $stmt->bindParam(":title", $data->title);
    $stmt->bindParam(":message", $data->message);
    
    if ($stmt->execute()) {
        echo json_encode(array("success" => true, "message" => "Alert saved"));
    } else {
        echo json_encode(array("success" => false, "message" => "Error saving alert"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Invalid data"));
}
?>

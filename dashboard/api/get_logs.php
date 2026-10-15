<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM system_logs ORDER BY timestamp DESC LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute();

$logs = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $logs[] = array(
        "time" => date("H:i:s", strtotime($row['timestamp'])),
        "message" => $row['message'],
        "level" => $row['log_level']
    );
}

echo json_encode($logs);
?>

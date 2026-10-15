<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM alerts ORDER BY timestamp DESC LIMIT 20";
$stmt = $db->prepare($query);
$stmt->execute();

$alerts = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $alerts[] = array(
        "id" => $row['id'],
        "type" => $row['alert_type'],
        "title" => $row['title'],
        "message" => $row['message'],
        "time" => date("H:i:s", strtotime($row['timestamp']))
    );
}

echo json_encode($alerts);
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Paramètre de période (24h, 7d, 30d)
$period = isset($_GET['period']) ? $_GET['period'] : '24h';

switch($period) {
    case '24h':
        $hours = 24;
        break;
    case '7d':
        $hours = 168; // 7 * 24
        break;
    case '30d':
        $hours = 720; // 30 * 24
        break;
    default:
        $hours = 24;
}

$query = "SELECT temperature, humidity, 
          DATE_FORMAT(timestamp, '%H:%i') as time,
          timestamp
          FROM sensor_data 
          WHERE timestamp >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
          ORDER BY timestamp ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(":hours", $hours);
$stmt->execute();

$data = array();
$tempSum = 0;
$humSum = 0;
$count = 0;
$tempMin = 100;
$tempMax = -100;
$humMin = 100;
$humMax = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = array(
        "time" => $row['time'],
        "temp" => (float)$row['temperature'],
        "hum" => (float)$row['humidity']
    );
    
    // Calcul des stats
    $tempSum += $row['temperature'];
    $humSum += $row['humidity'];
    $count++;
    
    if ($row['temperature'] < $tempMin) $tempMin = $row['temperature'];
    if ($row['temperature'] > $tempMax) $tempMax = $row['temperature'];
    if ($row['humidity'] < $humMin) $humMin = $row['humidity'];
    if ($row['humidity'] > $humMax) $humMax = $row['humidity'];
}

$response = array(
    "data" => $data,
    "stats" => array(
        "tempAvg" => $count > 0 ? round($tempSum / $count, 1) : 0,
        "humAvg" => $count > 0 ? round($humSum / $count, 1) : 0,
        "tempMin" => $tempMin,
        "tempMax" => $tempMax,
        "humMin" => $humMin,
        "humMax" => $humMax
    )
);

echo json_encode($response);
?>

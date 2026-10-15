<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les dernières données des capteurs
$query_sensor = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
$stmt_sensor = $db->prepare($query_sensor);
$stmt_sensor->execute();
$sensor_data = $stmt_sensor->fetch(PDO::FETCH_ASSOC);

// Récupérer les derniers états des actionneurs
$query_actuator = "SELECT * FROM actuator_states ORDER BY timestamp DESC LIMIT 1";
$stmt_actuator = $db->prepare($query_actuator);
$stmt_actuator->execute();
$actuator_data = $stmt_actuator->fetch(PDO::FETCH_ASSOC);

// Combiner les données
$response = array(
    "temp" => $sensor_data ? (float)$sensor_data['temperature'] : 25.0,
    "hum" => $sensor_data ? (float)$sensor_data['humidity'] : 60.0,
    "soil" => $sensor_data ? (int)$sensor_data['soil_moisture'] : 500,
    "water" => $sensor_data ? (int)$sensor_data['water_level'] : 1,
    "p5" => $actuator_data ? (int)$actuator_data['pump_5v'] : 0,
    "p12" => $actuator_data ? (int)$actuator_data['pump_12v'] : 0,
    "heater" => $actuator_data ? (int)$actuator_data['heater'] : 0,
    "fan" => $actuator_data ? (int)$actuator_data['fan'] : 0,
    "state" => $actuator_data ? (int)$actuator_data['system_state'] : 0
);

echo json_encode($response);
?>

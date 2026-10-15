<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM settings WHERE id = 1";
$stmt = $db->prepare($query);
$stmt->execute();

$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($settings) {
    echo json_encode(array(
        "minTemp" => (float)$settings['min_temp'],
        "maxTemp" => (float)$settings['max_temp'],
        "targetTemp" => (float)$settings['target_temp'],
        "soilThreshold" => (int)$settings['soil_threshold'],
        "pumpDuration" => (int)$settings['pump_duration']
    ));
} else {
    // Valeurs par défaut si aucun paramètre en base
    echo json_encode(array(
        "minTemp" => 20,
        "maxTemp" => 30,
        "targetTemp" => 25,
        "soilThreshold" => 400,
        "pumpDuration" => 10
    ));
}
?>

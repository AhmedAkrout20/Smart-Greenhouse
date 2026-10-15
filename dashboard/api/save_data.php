<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($data) {
    // Sauvegarder les données des capteurs
    if (isset($data->temp) && isset($data->hum)) {
        $query = "INSERT INTO sensor_data (temperature, humidity, soil_moisture, water_level) 
                  VALUES (:temp, :hum, :soil, :water)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":temp", $data->temp);
        $stmt->bindParam(":hum", $data->hum);
        $stmt->bindParam(":soil", $data->soil);
        $stmt->bindParam(":water", $data->water);
        $stmt->execute();
    }

    // Sauvegarder les états des actionneurs
    if (isset($data->state)) {
        $query = "INSERT INTO actuator_states (pump_5v, pump_12v, heater, fan, system_state) 
                  VALUES (:p5, :p12, :heater, :fan, :state)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":p5", $data->p5);
        $stmt->bindParam(":p12", $data->p12);
        $stmt->bindParam(":heater", $data->heater);
        $stmt->bindParam(":fan", $data->fan);
        $stmt->bindParam(":state", $data->state);
        $stmt->execute();
    }

    echo json_encode(array("message" => "Data saved successfully"));
} else {
    echo json_encode(array("message" => "Unable to save data"));
}
?>

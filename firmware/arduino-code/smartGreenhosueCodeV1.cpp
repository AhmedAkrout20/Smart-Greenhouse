// PROJECT: Smart Greenhosue (AUTOMATIC WATERING SYSTEM)


// ==================== LIBRARIES ====================

#include <DHT.h> // for the DHT11 temperature and humidity sensor
#include <Wire.h> // I2C communication (used by the LCD screen)
#include <LiquidCrystal_I2C.h> // LCD screen management via I2C

// ==================== SENSOR CLASSES ====================

// --------- CLASS TemperatureHumiditySensor ---------

class TemperatureHumiditySensor {
private:
    DHT dht;              // DHT object
    int pin;              // Sensor pin
    float temperature;    // Temperature reading
    float humidity;       // Humidity reading

public:
    // ----- CONSTRUCTOR -----
    TemperatureHumiditySensor(int p, int type) : dht(p, type), pin(p) {
        temperature = 0;
        humidity = 0;
    }

    // Sensor initialization
    void begin() {
        dht.begin();
    }

    // Read the sensor values
    void read() {
        humidity = dht.readHumidity();
        temperature = dht.readTemperature();

        if (isnan(humidity) || isnan(temperature)) {
            Serial.println("Failed to read from DHT sensor!");
        }
    }

    // Getters (encapsulation)
    float getTemperature() { return temperature; }
    float getHumidity() { return humidity; }
};

// --------- CLASS WaterLevelSensor ---------
// Water level sensor logic (ON / OFF) 
class WaterLevelSensor {
private:
    int pin;

public:
    // Constructor
    WaterLevelSensor(int p) : pin(p) {}

    void begin() {
        pinMode(pin, INPUT);
    }

    bool hasWater() {
        return digitalRead(pin) == HIGH;
    }
};

// --------- CLASS SoilMoistureSensor ---------
// Soil moisture sensor (analog)
class SoilMoistureSensor {
private:
    int pin;

public:
    SoilMoistureSensor(int p) : pin(p) {}

    void begin() {
        pinMode(pin, INPUT);
    }

    int getMoistureLevel() {
        return analogRead(pin);
    }

    bool isWet() {
        return getMoistureLevel() < 400; // low value = wet soil
    }
};

// ==================== ACTUATOR CLASSES ====================


// --------- CLASS RelayDevice (PARENT CLASS) ---------

// This class will be inherited by the pump, heater, and fan
class RelayDevice {
protected:              // accessible to child classes
    int pin;
    bool state;

public:
    RelayDevice(int p) : pin(p), state(false) {}

    virtual void begin() {
        pinMode(pin, OUTPUT);
        turnOff();
    }

    void turnOn() {
        digitalWrite(pin, HIGH);
        state = true;
    }

    void turnOff() {
        digitalWrite(pin, LOW);
        state = false;
    }

    bool isOn() { return state; }
};

// --------- CLASS WaterPump (INHERITANCE) ---------
// WaterPump inherits from RelayDevice
class WaterPump : public RelayDevice {
private:
    String name;

public:
    // Call to the parent class constructor (RelayDevice)
    WaterPump(int p, String n) : RelayDevice(p), name(n) {}

    void pump(int duration) {
        Serial.println(name + " pumping...");
        turnOn();
        delay(duration);
        turnOff();
    }
};

// --------- CLASS Heater ---------
class Heater : public RelayDevice {
public:
    Heater(int p) : RelayDevice(p) {}
};

// --------- CLASS CoolingFan ---------
class CoolingFan : public RelayDevice {
public:
    CoolingFan(int p) : RelayDevice(p) {}
};

// ==================== LED ====================

class LED {
private:
    int pin;
    bool state;
    unsigned long lastBlinkTime;
    int blinkInterval;

public:
    LED(int p) : pin(p), state(false), lastBlinkTime(0), blinkInterval(0) {}

    void begin() {
        pinMode(pin, OUTPUT);
        turnOff();
    }

    void turnOn() {
        digitalWrite(pin, HIGH);
        state = true;
    }

    void turnOff() {
        digitalWrite(pin, LOW);
        state = false;
    }

    // Non-blocking blinking
    void blink(int interval) {
        unsigned long now = millis();
        if (now - lastBlinkTime >= interval) {
            state = !state;
            digitalWrite(pin, state);
            lastBlinkTime = now;
        }
    }
};

// ==================== BUZZER ====================

class Buzzer {
private:
    int pin;
    bool state;
    unsigned long lastToneTime;
    int toneInterval;

public:
    Buzzer(int p) : pin(p), state(false), lastToneTime(0), toneInterval(0) {}

    void begin() {
        pinMode(pin, OUTPUT);
    }

    void tone(int frequency, int interval) {
        unsigned long now = millis();
        if (now - lastToneTime >= interval) {
            state = !state;
            if (state) ::tone(pin, frequency);
            else noTone(pin);
            lastToneTime = now;
        }
    }

    void stopTone() {
        noTone(pin);
        state = false;
    }
};

// ==================== BUTTON ====================

class Button {
private:
    int pin;
    bool lastState;
    unsigned long lastDebounceTime;
    const unsigned long debounceDelay = 50;

public:
    Button(int p) : pin(p), lastState(HIGH), lastDebounceTime(0) {}

    void begin() {
        pinMode(pin, INPUT_PULLUP);
    }

    // Press detection with debouncing
    bool isPressed() {
        bool reading = digitalRead(pin);
        if (reading == LOW && lastState == HIGH && (millis() - lastDebounceTime) > debounceDelay) {
            lastDebounceTime = millis();
            lastState = LOW;
            return true;
        }
        lastState = reading;
        return false;
    }
};

// ==================== LCD ====================

class LCDDisplay {
private:
    LiquidCrystal_I2C lcd;

public:
    // Constructor + I2C initialization
    LCDDisplay(uint8_t address) : lcd(address, 16, 2) {}

    void begin() {
        lcd.init();
        lcd.backlight();
    }

    void show(float temp, float hum) {
        lcd.setCursor(0, 0);
        lcd.print("Temp: "); lcd.print(temp);
        lcd.setCursor(0, 1);
        lcd.print("Hum: "); lcd.print(hum);
    }
};

// ==================== MAIN SYSTEM ====================

// This class groups ALL the components

class PlantWateringSystem {
private:
    TemperatureHumiditySensor* dht;
    WaterPump* pump;
    Heater* heater;
    Button* startBtn;
    LCDDisplay* lcd;

public:
    PlantWateringSystem() {}

    void begin() {
        dht->begin();
        pump->begin();
        heater->begin();
        startBtn->begin();
        lcd->begin();
    }

    void update() {
        dht->read();
        lcd->show(dht->getTemperature(), dht->getHumidity());

        if (startBtn->isPressed()) {
            pump->pump(5000);
        }
    }

    // Dependency injection (good OOP practice)
    void setAll(TemperatureHumiditySensor* t, WaterPump* p, Heater* h, Button* b, LCDDisplay* l) {
        dht = t;
        pump = p;
        heater = h;
        startBtn = b;
        lcd = l;
    }
};

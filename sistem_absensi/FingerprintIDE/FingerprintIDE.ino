// =========================================================================
// PROGRAM ABSENSI SIDIK JARI FINAL - TERINTEGRASI DENGAN WEB SERVER
// Dibuat untuk ESP8266 (NodeMCU), Sensor AS608, LCD 16x2 I2C, dan Buzzer
// =========================================================================

#include <Adafruit_Fingerprint.h>
#include <SoftwareSerial.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// === BAGIAN BARU: Aktifkan kembali library WiFi dan HTTP ===
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>


// --- Konfigurasi Pin ESP8266 (NodeMCU D-pins) ---
#define FINGERPRINT_RX_PIN D5 // GPIO14 - Hubungkan ke pin TXD pada sensor AS608
#define FINGERPRINT_TX_PIN D6 // GPIO12 - Hubungkan ke pin RXD pada sensor AS608
#define BUZZER_PIN         D7 // GPIO13


// === BAGIAN BARU: Pengaturan Penting untuk Koneksi Online ===
// --- Konfigurasi WiFi ---
const char* ssid = "TP-Link_ADA8";         // Ganti dengan nama WiFi Anda
const char* password = "19096421"; // Ganti dengan password WiFi Anda

// --- Konfigurasi Server Web ---
// Ganti dengan alamat IP komputer Anda dan nama folder proyek
const char* serverUrl = "http://192.168.0.110/sistem_absensi/public/record_attendance.php"; 
// ============================================================


// --- Inisialisasi Komponen ---
SoftwareSerial fingerprintSerial(FINGERPRINT_RX_PIN, FINGERPRINT_TX_PIN);
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&fingerprintSerial);
LiquidCrystal_I2C lcd(0x27, 16, 2); // Ganti 0x27 dengan alamat I2C LCD Anda jika berbeda

// --- Variabel Global ---
char currentMode = 'V'; 
uint8_t enrollId = 0;

// --- FUNGSI SETUP ---
void setup() {
    Serial.begin(115200);
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    lcd.init();
    lcd.backlight();
    lcd.setCursor(0, 0);
    lcd.print("Sistem Absensi");
    delay(1500);

    // Inisialisasi Sensor
    lcd.clear();
    lcd.print("Cek Sensor...");
    finger.begin(57600);
    if (finger.verifyPassword()) {
        lcd.clear();
        lcd.print("Sensor OK!");
        Serial.println("Sensor Sidik Jari Ditemukan!");
        beep(true);
    } else {
        lcd.clear();
        lcd.print("Sensor Gagal!");
        Serial.println("Tidak dapat menemukan sensor sidik jari :(");
        beep(false);
        while (1) { delay(1); } // Berhenti jika sensor gagal
    }
    delay(1000);
    
    // === BAGIAN BARU: Panggil fungsi untuk menghubungkan WiFi ===
    connectToWifi();

    Serial.println("=====================================");
    printInstructions();
    displayMode();
}

// --- FUNGSI LOOP UTAMA ---
void loop() {
    if (Serial.available() > 0) {
        char command = Serial.read();
        switch (command) {
            case 'e': case 'E': currentMode = 'E'; displayMode(); startEnrollment(); break;
            case 'd': case 'D': currentMode = 'D'; displayMode(); deleteFingerprint(); break;
            case 'v': case 'V': currentMode = 'V'; displayMode(); break;
        }
    }

    if (currentMode == 'V') {
        runAttendanceMode();
    }
    
    delay(50);
}

// --- FUNGSI-FUNGSI UTAMA ---

// === BAGIAN BARU: Fungsi untuk menghubungkan ke WiFi ===
void connectToWifi() {
    lcd.clear();
    lcd.print("Hubungkan WiFi..");
    Serial.print("\nMenghubungkan ke: ");
    Serial.println(ssid);

    WiFi.begin(ssid, password);
    int wifi_attempts = 0;
    while (WiFi.status() != WL_CONNECTED && wifi_attempts < 20) { // Coba 10 detik
        delay(500);
        Serial.print(".");
        lcd.setCursor(wifi_attempts % 16, 1);
        lcd.print(".");
        wifi_attempts++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\nWiFi Terhubung!");
        Serial.print("Alamat IP: "); Serial.println(WiFi.localIP());
        lcd.clear();
        lcd.print("WiFi Terhubung!");
        lcd.setCursor(0,1);
        lcd.print(WiFi.localIP());
        beep(true);
    } else {
        Serial.println("\nGagal terhubung WiFi.");
        lcd.clear();
        lcd.print("WiFi Gagal!");
        beep(false);
    }
    delay(2000);
}

// === BAGIAN BARU: Fungsi untuk mengirim data ke server ===
void sendToServer(int fingerprintID) {
    // Cek koneksi, coba sambung ulang jika terputus
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi terputus. Mencoba menghubungkan ulang...");
        connectToWifi(); 
        if (WiFi.status() != WL_CONNECTED) {
            lcd.clear();
            lcd.print("WiFi Offline");
            lcd.setCursor(0,1);
            lcd.print("Data Tdk Kirim");
            beep(false);
            delay(2000);
            return; // Keluar dari fungsi jika masih gagal terhubung
        }
    }

    HTTPClient http;
    WiFiClient client;

    Serial.print("[HTTP] Mengirim ke server: "); Serial.println(serverUrl);
    lcd.clear();
    lcd.print("Mengirim data...");
    
    // Siapkan data yang akan dikirim
    String postData = "fingerprint_id=" + String(fingerprintID);
    
    if (http.begin(client, serverUrl)) { // Mulai koneksi HTTP
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        
        int httpCode = http.POST(postData); // Kirim data dengan metode POST

        if (httpCode > 0) {
            String payload = http.getString();
            Serial.printf("[HTTP] Kode respons: %d\n", httpCode);
            Serial.println("[HTTP] Respons server: " + payload);
            lcd.clear();
            lcd.print("Server Respon:");
            lcd.setCursor(0,1);
            lcd.print(payload.substring(0,16)); // Tampilkan 16 karakter pertama dari respons server
        } else {
            Serial.printf("[HTTP] Gagal, error: %s\n", http.errorToString(httpCode).c_str());
            lcd.clear();
            lcd.print("HTTP Gagal");
        }
        http.end(); // Akhiri koneksi
    } else {
        Serial.printf("[HTTP] Gagal terhubung\n");
        lcd.clear();
        lcd.print("Server Conn Fail");
    }
}


void runAttendanceMode() {
    int fingerID = getFingerprintID();
    
    if (fingerID > 0) {
        Serial.print("Terdeteksi ID #"); Serial.println(fingerID);
        lcd.clear();
        lcd.print("Absen Berhasil!");
        lcd.setCursor(0, 1);
        lcd.print("ID Siswa: " + String(fingerID));
        beep(true);

        // === BAGIAN BARU: Panggil fungsi untuk mengirim data ke server ===
        sendToServer(fingerID); 
        
        delay(3000);
        displayMode();
    } 
    else if (fingerID == -3) {
        Serial.println("Sidik jari tidak dikenali.");
        lcd.clear();
        lcd.print("Sidik Jari");
        lcd.setCursor(0, 1);
        lcd.print("Tdk Dikenali");
        beep(false);
        delay(2000);
        displayMode();
    }
}


// --- SISA FUNGSI (TIDAK BERUBAH DARI VERSI SEBELUMNYA) ---
void printInstructions() {
    Serial.println("Ketik perintah di Serial Monitor dan tekan Enter:");
    Serial.println("  'e' - Masuk ke Mode Pendaftaran Sidik Jari Baru");
    Serial.println("  'd' - Masuk ke Mode Hapus Sidik Jari");
    Serial.println("  'v' - Kembali ke Mode Absensi (Verifikasi)");
    Serial.println("=====================================");
}

void displayMode() {
    lcd.clear();
    if (currentMode == 'V') {
        lcd.print("Mode Absensi");
        lcd.setCursor(0, 1);
        lcd.print("Tempelkan Jari");
    } else if (currentMode == 'E') {
        lcd.print("Mode Daftar");
    } else if (currentMode == 'D') {
        lcd.print("Mode Hapus");
    }
}

void startEnrollment() {
    // Bersihkan buffer serial dari input sebelumnya
    while (Serial.available() > 0) { Serial.read(); }

    Serial.println("Ketik ID untuk pendaftaran (1-" + String(finger.capacity) + ") lalu tekan Enter:");
    lcd.clear();
    lcd.print("Input ID Daftar");
    lcd.setCursor(0, 1);
    lcd.print("di Serial Mon...");

    // Tunggu sampai ada input di Serial Monitor (tanpa batas waktu)
    while (Serial.available() == 0) {
        lcd.setCursor(15, 1); lcd.print("."); delay(300);
        lcd.setCursor(15, 1); lcd.print(" "); delay(300);
    }

    enrollId = Serial.parseInt();
    while (Serial.available() > 0) { Serial.read(); }

    if (enrollId > 0 && enrollId <= finger.capacity) {
        Serial.println("Mendaftarkan untuk ID #" + String(enrollId));
        lcd.clear(); lcd.print("Daftar ID: " + String(enrollId));
        delay(1000);
        getFingerprintEnroll(enrollId);
    } else {
        Serial.println("ID tidak valid. Pendaftaran dibatalkan.");
        lcd.clear(); lcd.print("ID tidak valid!"); beep(false);
        delay(2000);
    }

    currentMode = 'V'; 
    displayMode();
}

void deleteFingerprint() {
    while (Serial.available() > 0) { Serial.read(); }

    Serial.println("Ketik ID sidik jari yang akan dihapus (1-" + String(finger.capacity) + ") lalu tekan Enter:");
    lcd.clear();
    lcd.print("Input ID Hapus");
    lcd.setCursor(0,1);
    lcd.print("di Serial Mon...");

    while(Serial.available() == 0) {
        lcd.setCursor(15, 1); lcd.print("."); delay(300);
        lcd.setCursor(15, 1); lcd.print(" "); delay(300);
    }

    uint8_t id_to_delete = Serial.parseInt();
    while (Serial.available() > 0) { Serial.read(); }

    if (id_to_delete == 0) {
        Serial.println("ID tidak valid. Penghapusan dibatalkan.");
        lcd.clear(); lcd.print("ID tidak valid!"); beep(false); delay(2000);
    } else {
        Serial.print("Menghapus ID #"); Serial.println(id_to_delete);
        lcd.clear(); lcd.print("Hapus ID: #"); lcd.print(id_to_delete);

        uint8_t result = finger.deleteModel(id_to_delete);
        lcd.setCursor(0,1);
        if (result == FINGERPRINT_OK) {
            Serial.println("Berhasil dihapus!");
            lcd.print("Berhasil!"); beep(true);
        } else {
            Serial.print("Gagal menghapus! Kode: 0x"); Serial.println(result, HEX);
            lcd.print("Gagal!"); beep(false);
        }
        delay(2000);
    }

    currentMode = 'V'; 
    displayMode();
}

uint8_t getFingerprintEnroll(uint8_t id) {
    lcd.clear(); lcd.print("Letakkan jari..");
    int p = -1;
    while (p != FINGERPRINT_OK) { p = finger.getImage(); if (p == FINGERPRINT_NOFINGER) delay(100); }
    lcd.clear(); lcd.print("Memproses...");
    p = finger.image2Tz(1);
    if (p != FINGERPRINT_OK) { lcd.clear(); lcd.print("Error Proses 1"); beep(false); return p; }
    
    lcd.clear(); lcd.print("Angkat jari...");
    delay(1000);
    p = 0;
    while (p != FINGERPRINT_NOFINGER) { p = finger.getImage(); delay(50); }

    lcd.clear(); lcd.print("Jari yang sama"); lcd.setCursor(0,1); lcd.print("sekali lagi...");
    p = -1;
    while (p != FINGERPRINT_OK) { p = finger.getImage(); if (p == FINGERPRINT_NOFINGER) delay(100); }
    lcd.clear(); lcd.print("Memproses...");
    p = finger.image2Tz(2);
    if (p != FINGERPRINT_OK) { lcd.clear(); lcd.print("Error Proses 2"); beep(false); return p; }

    lcd.clear(); lcd.print("Membuat model...");
    p = finger.createModel();
    if (p != FINGERPRINT_OK) {
        lcd.clear();
        if (p == FINGERPRINT_ENROLLMISMATCH) lcd.print("Jari tdk cocok");
        else lcd.print("Error Model");
        beep(false);
        return p;
    }
    
    lcd.clear(); lcd.print("Simpan model...");
    p = finger.storeModel(id);
    if (p == FINGERPRINT_OK) {
        lcd.clear(); lcd.print("Pendaftaran"); lcd.setCursor(0,1); lcd.print("Berhasil!");
        beep(true);
    } else {
        lcd.clear(); lcd.print("Gagal Simpan!"); beep(false);
    }
    delay(2000);
    return p;
}

int getFingerprintID() {
    uint8_t p = finger.getImage();
    if (p != FINGERPRINT_OK) return (p == FINGERPRINT_NOFINGER) ? 0 : -1;
    p = finger.image2Tz();
    if (p != FINGERPRINT_OK) return -2;
    p = finger.fingerSearch();
    if (p != FINGERPRINT_OK) return -3;
    return finger.fingerID;
}

void beep(bool success) {
    if (success) {
        tone(BUZZER_PIN, 2000, 100);
        delay(120);
        tone(BUZZER_PIN, 2500, 150);
    } else {
        tone(BUZZER_PIN, 400, 250);
        delay(280);
        tone(BUZZER_PIN, 400, 250);
    }
}

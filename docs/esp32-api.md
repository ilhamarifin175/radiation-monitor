# Dokumentasi Penerimaan Data ESP32 → Web Monitor Radiasi Area

## Gambaran Umum Alur Data

```
[Detektor GM] ──── [ESP32 Dalam] ──LoRa──► [ESP32 Gateway / Luar]
                                                      │
                                              WiFi / Internet
                                                      │
                                                      ▼
                                          [Laravel Web App]
                                          POST /api/store-data-*
                                                      │
                                                      ▼
                                             [Database MySQL]
                                        (monitor_luar / monitor_dalam)
```

---

## 1. Konfigurasi Dasar

| Parameter       | Nilai                                      |
|-----------------|--------------------------------------------|
| Base URL        | `http://<IP_SERVER>/api`                   |
| Content-Type    | `application/json`                         |
| Autentikasi     | Header `Api-Key: <nilai_kunci>`            |
| Metode kirim    | HTTP POST                                  |
| Format data     | JSON body                                  |

### API Key

API Key dikonfigurasi di file `.env` server:

```env
API_KEY=<nilai_kunci_rahasia>
```

Setiap request dari ESP32 **wajib** menyertakan header:

```
Api-Key: <nilai_kunci_rahasia>
```

Jika header tidak ada atau salah, server mengembalikan:

```json
HTTP/1.1 401 Unauthorized
{
  "error": "invalid_key",
  "message": "Invalid key"
}
```

---

## 2. Endpoint Kirim Data Monitor Luar

**Digunakan oleh**: ESP32 yang membaca sensor detektor GM di luar ruangan.

```
POST /api/store-data-outdoor-monitor
```

### Header Request

```
Content-Type: application/json
Api-Key: <nilai_kunci>
```

### Body Request (JSON)

```json
{
  "timestamp":   "2025-06-23 14:30:00",
  "cps":         42,
  "usvh":        0.23,
  "suhu":        31.5,
  "kelembapan":  68.2
}
```

### Keterangan Field

| Field        | Tipe      | Satuan      | Keterangan                                |
|--------------|-----------|-------------|-------------------------------------------|
| `timestamp`  | `string`  | –           | Waktu ukur format `YYYY-MM-DD HH:MM:SS`  |
| `cps`        | `integer` | count/detik | Counts Per Second detektor GM             |
| `usvh`       | `float`   | µSv/jam     | Laju dosis radiasi, 2 desimal             |
| `suhu`       | `float`   | °C          | Suhu udara, 1 desimal                     |
| `kelembapan` | `float`   | %           | Kelembapan udara, 1 desimal               |

### Response Sukses

```json
HTTP/1.1 200 OK
{
  "message": "Data stored successfully."
}
```

---

## 3. Endpoint Kirim Data Monitor Dalam

**Digunakan oleh**: ESP32 yang membaca sensor detektor GM di dalam ruang instalasi radiologi.

```
POST /api/store-data-indoor-monitor
```

### Header Request

```
Content-Type: application/json
Api-Key: <nilai_kunci>
```

### Body Request (JSON)

```json
{
  "timestamp":   "2025-06-23 14:30:00",
  "seq":         101,
  "cps":         710,
  "usvh":        5.07,
  "suhu":        23.4,
  "kelembapan":  51.0,
  "relay":       "ON",
  "jaringan":      "LoRa",
  "rssi":        -78
}
```

### Keterangan Field

| Field        | Tipe      | Satuan      | Keterangan                                          |
|--------------|-----------|-------------|-----------------------------------------------------|
| `timestamp`  | `string`  | –           | Waktu ukur format `YYYY-MM-DD HH:MM:SS`            |
| `seq`        | `integer` | –           | Nomor urut paket (sequence number)                  |
| `cps`        | `integer` | count/detik | Counts Per Second detektor GM                       |
| `usvh`       | `float`   | µSv/jam     | Laju dosis radiasi, 2 desimal                       |
| `suhu`       | `float`   | °C          | Suhu udara, 1 desimal                               |
| `kelembapan` | `float`   | %           | Kelembapan udara, 1 desimal                         |
| `relay`      | `string`  | `ON`/`OFF`  | Status relay alarm (`OFF` = ambang batas terlampaui) |
| `jaringan`     | `string`  | –           | Jenis jaringan komunikasi (`"LoRa"` atau `"WiFi"`)      |
| `rssi`       | `integer` | dBm         | Kekuatan sinyal LoRa (nilai negatif, misal `-78`)   |

> **Catatan Relay**: `relay = "OFF"` berarti alarm aktif (laju dosis > 10 µSv/jam).  
> `relay = "ON"` berarti kondisi normal (laju dosis ≤ 10 µSv/jam).

### Response Sukses

```json
HTTP/1.1 200 OK
{
  "message": "Data stored successfully."
}
```

---

## 4. Endpoint Kirim Data Kualitas Jaringan (Integrity Stats)

**Digunakan oleh**: ESP32 gateway untuk melaporkan statistik paket WiFi dan LoRa per periode pengiriman.

```
POST /api/store-integrity-stats
```

### Header Request

```
Content-Type: application/json
Api-Key: <nilai_kunci>
```

### Body Request (JSON)

```json
{
  "timestamp":    "2025-06-23 14:30:00",
  "wifi_terima":  95,
  "wifi_hilang":  3,
  "wifi_pdr":     96.94,
  "lora_terima":  98,
  "lora_hilang":  1,
  "lora_pdr":     98.99
}
```

### Keterangan Field

| Field          | Tipe      | Satuan | Keterangan                                      |
|----------------|-----------|--------|-------------------------------------------------|
| `timestamp`    | `string`  | –      | Waktu pengukuran format `YYYY-MM-DD HH:MM:SS`  |
| `wifi_terima`  | `integer` | paket  | Jumlah paket WiFi berhasil diterima             |
| `wifi_hilang`  | `integer` | paket  | Jumlah paket WiFi yang hilang/gagal             |
| `wifi_pdr`     | `float`   | %      | Packet Delivery Ratio WiFi (0–100), 2 desimal   |
| `lora_terima`  | `integer` | paket  | Jumlah paket LoRa berhasil diterima             |
| `lora_hilang`  | `integer` | paket  | Jumlah paket LoRa yang hilang/gagal             |
| `lora_pdr`     | `float`   | %      | Packet Delivery Ratio LoRa (0–100), 2 desimal   |

> **Rumus PDR**: `pdr = (terima / (terima + hilang)) × 100`

### Response Sukses

```json
HTTP/1.1 200 OK
{
  "message": "Data stored successfully."
}
```

---

## 5. Contoh Kode ESP32 (Arduino / C++)

### Kirim Data Monitor Luar

```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid       = "NAMA_WIFI";
const char* password   = "PASSWORD_WIFI";
const char* serverUrl  = "http://<IP_SERVER>/api/store-data-outdoor-monitor";
const char* apiKey     = "<nilai_kunci_rahasia>";

void kirimDataLuar(int cps, float usvh, float suhu, float kelembapan,
                   String timestamp) {
    if (WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Api-Key", apiKey);

    StaticJsonDocument<256> doc;
    doc["timestamp"]   = timestamp;        // "2025-06-23 14:30:00"
    doc["cps"]         = cps;
    doc["usvh"]        = serialized(String(usvh, 2));
    doc["suhu"]        = serialized(String(suhu, 1));
    doc["kelembapan"]  = serialized(String(kelembapan, 1));

    String body;
    serializeJson(doc, body);

    int httpCode = http.POST(body);
    if (httpCode == 200) {
        Serial.println("Data luar terkirim.");
    } else {
        Serial.printf("Gagal: HTTP %d\n", httpCode);
    }
    http.end();
}
```

### Kirim Data Monitor Dalam

```cpp
const char* serverUrlDalam = "http://<IP_SERVER>/api/store-data-indoor-monitor";

void kirimDataDalam(int seq, int cps, float usvh, float suhu, float kelembapan,
                    String relay, int rssi, String timestamp) {
    if (WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;
    http.begin(serverUrlDalam);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Api-Key", apiKey);

    StaticJsonDocument<384> doc;
    doc["timestamp"]   = timestamp;
    doc["seq"]         = seq;
    doc["cps"]         = cps;
    doc["usvh"]        = serialized(String(usvh, 2));
    doc["suhu"]        = serialized(String(suhu, 1));
    doc["kelembapan"]  = serialized(String(kelembapan, 1));
    doc["relay"]       = relay;            // "ON" atau "OFF"
    doc["jaringan"]      = "LoRa";
    doc["rssi"]        = rssi;

    String body;
    serializeJson(doc, body);

    int httpCode = http.POST(body);
    if (httpCode == 200) {
        Serial.println("Data dalam terkirim.");
    } else {
        Serial.printf("Gagal: HTTP %d\n", httpCode);
    }
    http.end();
}
```

### Kirim Data Kualitas Jaringan

```cpp
const char* serverUrlIntegrity = "http://<IP_SERVER>/api/store-integrity-stats";

void kirimIntegrityStats(int wifiTerima, int wifiHilang,
                         int loraTerima, int loraHilang,
                         String timestamp) {
    if (WiFi.status() != WL_CONNECTED) return;

    float wifiPdr = (wifiTerima + wifiHilang > 0)
        ? ((float)wifiTerima / (wifiTerima + wifiHilang)) * 100.0 : 0;
    float loraPdr = (loraTerima + loraHilang > 0)
        ? ((float)loraTerima / (loraTerima + loraHilang)) * 100.0 : 0;

    HTTPClient http;
    http.begin(serverUrlIntegrity);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Api-Key", apiKey);

    StaticJsonDocument<256> doc;
    doc["timestamp"]    = timestamp;
    doc["wifi_terima"]  = wifiTerima;
    doc["wifi_hilang"]  = wifiHilang;
    doc["wifi_pdr"]     = serialized(String(wifiPdr, 2));
    doc["lora_terima"]  = loraTerima;
    doc["lora_hilang"]  = loraHilang;
    doc["lora_pdr"]     = serialized(String(loraPdr, 2));

    String body;
    serializeJson(doc, body);

    int httpCode = http.POST(body);
    if (httpCode == 200) {
        Serial.println("Integrity stats terkirim.");
    } else {
        Serial.printf("Gagal: HTTP %d\n", httpCode);
    }
    http.end();
}
```

---

## 6. Struktur Tabel Database

### `monitor_luar`

| Kolom        | Tipe           | Keterangan              |
|--------------|----------------|-------------------------|
| `id`         | INT (PK, AI)   | Primary key             |
| `timestamp`  | DATETIME       | Waktu pengukuran        |
| `cps`        | INT            | Counts Per Second       |
| `usvh`       | DECIMAL(10,2)  | Laju dosis (µSv/jam)    |
| `suhu`       | DECIMAL(10,1)  | Suhu (°C)               |
| `kelembapan` | DECIMAL(10,1)  | Kelembapan (%)          |
| `created_at` | DATETIME       | Waktu data diterima     |

### `monitor_dalam`

| Kolom        | Tipe           | Keterangan                              |
|--------------|----------------|-----------------------------------------|
| `id`         | INT (PK, AI)   | Primary key                             |
| `timestamp`  | DATETIME       | Waktu pengukuran                        |
| `seq`        | INT            | Nomor urut paket                        |
| `cps`        | INT            | Counts Per Second                       |
| `usvh`       | DECIMAL(10,2)  | Laju dosis (µSv/jam)                   |
| `suhu`       | DECIMAL(10,1)  | Suhu (°C)                               |
| `kelembapan` | DECIMAL(10,1)  | Kelembapan (%)                          |
| `relay`      | VARCHAR(3)     | Status alarm (`ON` = normal, `OFF` = alarm) |
| `jaringan`     | VARCHAR(10)    | Jenis jaringan komunikasi (`LoRa` / `WiFi`)      |
| `rssi`       | INT            | Kekuatan sinyal (dBm)                   |
| `created_at` | DATETIME       | Waktu data diterima                     |

### `integrity_stats`

| Kolom          | Tipe           | Keterangan                              |
|----------------|----------------|-----------------------------------------|
| `id`           | INT (PK, AI)   | Primary key                             |
| `timestamp`    | DATETIME       | Waktu pengukuran                        |
| `wifi_terima`  | INT            | Paket WiFi diterima                     |
| `wifi_hilang`  | INT            | Paket WiFi hilang                       |
| `wifi_pdr`     | DECIMAL(5,2)   | PDR WiFi dalam persen (%)              |
| `lora_terima`  | INT            | Paket LoRa diterima                     |
| `lora_hilang`  | INT            | Paket LoRa hilang                       |
| `lora_pdr`     | DECIMAL(5,2)   | PDR LoRa dalam persen (%)              |
| `created_at`   | DATETIME       | Waktu data diterima                     |

---

## 7. Kode Respons HTTP

| Kode | Arti                                              |
|------|---------------------------------------------------|
| 200  | Data berhasil disimpan                            |
| 401  | API Key tidak valid atau tidak disertakan         |
| 422  | Format data tidak sesuai (validasi gagal)         |
| 500  | Kesalahan internal server                         |

---

## 7. Pengujian API dengan cURL

### Uji Monitor Luar

```bash
curl -X POST http://<IP_SERVER>/api/store-data-outdoor-monitor \
  -H "Content-Type: application/json" \
  -H "Api-Key: <nilai_kunci>" \
  -d '{
    "timestamp":   "2025-06-23 14:30:00",
    "cps":         42,
    "usvh":        0.23,
    "suhu":        31.5,
    "kelembapan":  68.2
  }'
```

### Uji Monitor Dalam

```bash
curl -X POST http://<IP_SERVER>/api/store-data-indoor-monitor \
  -H "Content-Type: application/json" \
  -H "Api-Key: <nilai_kunci>" \
  -d '{
    "timestamp":   "2025-06-23 14:30:00",
    "seq":         101,
    "cps":         710,
    "usvh":        5.07,
    "suhu":        23.4,
    "kelembapan":  51.0,
    "relay":       "ON",
    "jaringan":      "LoRa",
    "rssi":        -78
  }'
```

---

*Dokumen ini dibuat untuk keperluan Tugas Akhir — Monitor Radiasi Area, Poltek Nuklir 2026.*

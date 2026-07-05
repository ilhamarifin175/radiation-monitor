-- ============================================================
--  Data Dummy — Monitor Radiasi Area
--  Periode  : 24 Jam Terakhir (288 record @ 5 menit)
--  Dibuat   : untuk keperluan pengujian / demo TA
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE monitor_luar;
TRUNCATE TABLE monitor_dalam;
TRUNCATE TABLE integrity_stats;
SET FOREIGN_KEY_CHECKS = 1;

DROP PROCEDURE IF EXISTS generate_dummy_data;

DELIMITER $$

CREATE PROCEDURE generate_dummy_data()
BEGIN
    DECLARE i        INT DEFAULT 0;
    DECLARE total    INT DEFAULT 288;   -- 24 jam × 12 (per 5 menit)
    DECLARE base_time DATETIME;
    DECLARE curr_time DATETIME;

    -- Monitor Luar
    DECLARE usvh_luar   DECIMAL(10,2);
    DECLARE cps_luar    INT;
    DECLARE suhu_luar   DECIMAL(10,1);
    DECLARE humid_luar  DECIMAL(10,1);

    -- Monitor Dalam
    DECLARE usvh_dalam  DECIMAL(10,2);
    DECLARE cps_dalam   INT;
    DECLARE suhu_dalam  DECIMAL(10,1);
    DECLARE humid_dalam DECIMAL(10,1);
    DECLARE relay_val   VARCHAR(3);
    DECLARE rssi_val    INT;
    DECLARE seq_val     INT DEFAULT 1;

    -- Integrity Stats
    DECLARE wifi_recv    INT;
    DECLARE wifi_lost    INT;
    DECLARE wifi_pdr_val DECIMAL(5,2);
    DECLARE lora_recv    INT;
    DECLARE lora_lost    INT;
    DECLARE lora_pdr_val DECIMAL(5,2);

    SET base_time = DATE_SUB(NOW(), INTERVAL 24 HOUR);

    WHILE i < total DO
        SET curr_time = DATE_ADD(base_time, INTERVAL (i * 5) MINUTE);

        -- ── Monitor Luar ─────────────────────────────────────
        -- Radiasi latar belakang outdoor: 0.05–0.45 µSv/jam
        -- Variasi diurnal: puncak siang hari
        SET usvh_luar = ROUND(
            0.22
            + 0.12 * SIN(i * 2 * PI() / total)
            + (RAND() - 0.5) * 0.08,
        2);
        IF usvh_luar < 0.05 THEN SET usvh_luar = 0.05; END IF;
        IF usvh_luar > 0.48 THEN SET usvh_luar = 0.48; END IF;

        SET cps_luar   = FLOOR(usvh_luar * 200 + RAND() * 8);
        SET suhu_luar  = ROUND(28.5 + 5.5 * SIN((i - 72) * 2 * PI() / total) + (RAND() - 0.5) * 0.8, 1);
        SET humid_luar = ROUND(70.0 - 12.0 * SIN((i - 72) * 2 * PI() / total) + (RAND() - 0.5) * 2.0, 1);
        IF suhu_luar  > 35.0 THEN SET suhu_luar  = 35.0; END IF;
        IF humid_luar > 90.0 THEN SET humid_luar = 90.0; END IF;
        IF humid_luar < 45.0 THEN SET humid_luar = 45.0; END IF;

        -- ── Monitor Dalam ─────────────────────────────────────
        -- Radiasi indoor instalasi radiologi: 1–9 µSv/jam normal
        -- Tiga periode spike melampaui ambang 10 µSv/jam
        SET usvh_dalam = ROUND(
            5.0
            + 2.5 * SIN(i * 2 * PI() / total)
            + 1.2 * SIN(i * 4 * PI() / total)
            + (RAND() - 0.5) * 1.5,
        2);
        IF usvh_dalam < 0.80 THEN SET usvh_dalam = 0.80; END IF;
        IF usvh_dalam > 9.80 THEN SET usvh_dalam = 9.80; END IF;

        -- Spike 1: sekitar jam ke-4 sampai ke-5 (i 48–60)
        IF i BETWEEN 48  AND 60  THEN SET usvh_dalam = ROUND(10.50 + RAND() * 2.50, 2); END IF;
        -- Spike 2: sekitar jam ke-11 sampai ke-12 (i 132–144)
        IF i BETWEEN 132 AND 144 THEN SET usvh_dalam = ROUND(11.00 + RAND() * 2.00, 2); END IF;
        -- Spike 3: sekitar jam ke-19 sampai ke-20 (i 228–240)
        IF i BETWEEN 228 AND 240 THEN SET usvh_dalam = ROUND(10.20 + RAND() * 3.00, 2); END IF;

        -- Relay OFF ketika melebihi ambang 10 µSv/jam
        IF usvh_dalam > 10.00 THEN
            SET relay_val = 'OFF';
        ELSE
            SET relay_val = 'ON';
        END IF;

        SET cps_dalam   = FLOOR(usvh_dalam * 140 + RAND() * 12);
        SET suhu_dalam  = ROUND(22.5 + 1.5 * SIN(i * 2 * PI() / total) + (RAND() - 0.5) * 0.4, 1);
        SET humid_dalam = ROUND(50.0 + 4.0 * SIN(i * 2 * PI() / total) + (RAND() - 0.5) * 1.0, 1);
        SET rssi_val    = FLOOR(-65 - RAND() * 35);

        -- ── Integrity Stats ───────────────────────────────────
        -- PDR dalam persen (0–100), 2 angka desimal
        SET wifi_recv    = FLOOR(88 + RAND() * 14);
        SET wifi_lost    = FLOOR(RAND() * 8);
        SET wifi_pdr_val = ROUND((wifi_recv / (wifi_recv + wifi_lost)) * 100, 2);

        SET lora_recv    = FLOOR(93 + RAND() * 8);
        SET lora_lost    = FLOOR(RAND() * 4);
        SET lora_pdr_val = ROUND((lora_recv / (lora_recv + lora_lost)) * 100, 2);

        -- ── Insert ────────────────────────────────────────────
        INSERT INTO monitor_luar
            (timestamp, cps, usvh, suhu, kelembapan, created_at)
        VALUES
            (curr_time, cps_luar, usvh_luar, suhu_luar, humid_luar, curr_time);

        INSERT INTO monitor_dalam
            (timestamp, seq, cps, usvh, suhu, kelembapan, relay, jaringan, rssi, created_at)
        VALUES
            (curr_time, seq_val, cps_dalam, usvh_dalam, suhu_dalam, humid_dalam,
             relay_val, 'LoRa', rssi_val, curr_time);

        INSERT INTO integrity_stats
            (timestamp, wifi_terima, wifi_hilang, wifi_pdr, lora_terima, lora_hilang, lora_pdr, created_at)
        VALUES
            (curr_time, wifi_recv, wifi_lost, wifi_pdr_val, lora_recv, lora_lost, lora_pdr_val, curr_time);

        SET i       = i + 1;
        SET seq_val = seq_val + 1;
    END WHILE;
END$$

DELIMITER ;

CALL generate_dummy_data();
DROP PROCEDURE IF EXISTS generate_dummy_data;

-- Verifikasi jumlah data
SELECT 'monitor_luar'    AS tabel, COUNT(*) AS jumlah FROM monitor_luar
UNION ALL
SELECT 'monitor_dalam',  COUNT(*) FROM monitor_dalam
UNION ALL
SELECT 'integrity_stats', COUNT(*) FROM integrity_stats;

CREATE TABLE IF NOT EXISTS phone_otps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    country_code VARCHAR(10) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    otp_expire_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_otp_expire_time (otp_expire_time)
);

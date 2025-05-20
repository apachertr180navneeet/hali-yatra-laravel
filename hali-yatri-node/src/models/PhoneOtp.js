const db = require('../config/database');

class PhoneOtp {
    static async create(otpData) {
        const [result] = await db.execute(
            `INSERT INTO phone_otps (
                phone, country_code, otp, otp_expire_time
            ) VALUES (?, ?, ?, ?)`,
            [
                otpData.phone,
                otpData.country_code,
                otpData.otp,
                otpData.otp_expire_time
            ]
        );
        return result.insertId;
    }

    static async findByPhone(phone) {
        const [rows] = await db.execute('SELECT * FROM phone_otps WHERE phone = ? ORDER BY created_at DESC LIMIT 1', [phone]);
        return rows[0];
    }

    static async findValidOtp(phone, otp) {
        const [rows] = await db.execute(
            'SELECT * FROM phone_otps WHERE phone = ? AND otp = ? AND otp_expire_time > NOW() ORDER BY created_at DESC LIMIT 1',
            [phone, otp]
        );
        return rows[0];
    }

    static async deleteExpiredOtps() {
        const [result] = await db.execute('DELETE FROM phone_otps WHERE otp_expire_time < NOW()');
        return result.affectedRows;
    }

    static async deleteByPhone(phone) {
        const [result] = await db.execute('DELETE FROM phone_otps WHERE phone = ?', [phone]);
        return result.affectedRows > 0;
    }
}

module.exports = PhoneOtp;

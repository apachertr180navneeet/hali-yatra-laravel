const db = require('../config/database');

class AppUser {
    static async create(userData) {
        const [result] = await db.execute(
            `INSERT INTO app_users (
                first_name, last_name, full_name, email, phone, password,
                address, area, city, state, country, country_code,
                zipcode, latitude, longitude, bio, device_type,
                device_token, avatar, slug
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
            [
                userData.first_name, userData.last_name, userData.full_name,
                userData.email, userData.phone, userData.password,
                userData.address, userData.area || '', userData.city || '',
                userData.state || '', userData.country || '', userData.country_code,
                userData.zipcode || '', userData.latitude, userData.longitude,
                userData.bio || '', userData.device_type || '',
                userData.device_token || '', userData.avatar || '',
                userData.slug
            ]
        );
        return result.insertId;
    }

    static async findByEmail(email) {
        const [rows] = await db.execute('SELECT * FROM app_users WHERE email = ?', [email]);
        return rows[0];
    }

    static async findByPhone(phone) {
        const [rows] = await db.execute('SELECT * FROM app_users WHERE phone = ?', [phone]);
        return rows[0];
    }

    static async findById(id) {
        const [rows] = await db.execute('SELECT * FROM app_users WHERE id = ?', [id]);
        return rows[0];
    }

    static async update(id, updateData) {
        const fields = Object.keys(updateData).map(key => `${key} = ?`).join(', ');
        const values = [...Object.values(updateData), id];

        const [result] = await db.execute(
            `UPDATE app_users SET ${fields} WHERE id = ?`,
            values
        );
        return result.affectedRows > 0;
    }
}

module.exports = AppUser;

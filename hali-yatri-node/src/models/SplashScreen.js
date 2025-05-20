const db = require('../config/database');

class SplashScreen {
    static async create(splashData) {
        const [result] = await db.execute(
            `INSERT INTO splash_screens (
                type, heading, content, image
            ) VALUES (?, ?, ?, ?)`,
            [
                splashData.type,
                splashData.heading,
                splashData.content,
                splashData.image || ''
            ]
        );
        return result.insertId;
    }

    static async findAll() {
        const [rows] = await db.execute('SELECT * FROM splash_screens');
        return rows;
    }

    static async findById(id) {
        const [rows] = await db.execute('SELECT * FROM splash_screens WHERE id = ?', [id]);
        return rows[0];
    }

    static async findByType(type) {
        const [rows] = await db.execute('SELECT * FROM splash_screens WHERE type = ?', [type]);
        return rows[0];
    }

    static async update(id, updateData) {
        const fields = Object.keys(updateData).map(key => `${key} = ?`).join(', ');
        const values = [...Object.values(updateData), id];

        const [result] = await db.execute(
            `UPDATE splash_screens SET ${fields} WHERE id = ?`,
            values
        );
        return result.affectedRows > 0;
    }

    static async delete(id) {
        const [result] = await db.execute('DELETE FROM splash_screens WHERE id = ?', [id]);
        return result.affectedRows > 0;
    }
}

module.exports = SplashScreen;

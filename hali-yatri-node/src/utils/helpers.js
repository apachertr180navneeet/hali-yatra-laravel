const db = require('../config/database');

const generateSlug = async (table, name) => {
    const slug = name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');

    let uniqueSlug = slug;
    let counter = 1;

    while (true) {
        const [rows] = await db.execute(
            `SELECT id FROM ${table} WHERE slug = ? LIMIT 1`,
            [uniqueSlug]
        );

        if (rows.length === 0) {
            break;
        }

        uniqueSlug = `${slug}-${counter}`;
        counter++;
    }

    return uniqueSlug;
};

const cleanImage = (filename) => {
    return filename
        .toLowerCase()
        .replace(/[^a-z0-9.]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

module.exports = {
    generateSlug,
    cleanImage
};

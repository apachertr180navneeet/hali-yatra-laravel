const nodemailer = require('nodemailer');

const transporter = nodemailer.createTransport({
    host: process.env.MAIL_HOST,
    port: process.env.MAIL_PORT,
    secure: process.env.MAIL_ENCRYPTION === 'ssl',
    auth: {
        user: process.env.MAIL_USERNAME,
        pass: process.env.MAIL_PASSWORD
    }
});

const sendMail = async (to, subject, html) => {
    try {
        const mailOptions = {
            from: process.env.MAIL_FROM_ADDRESS,
            to,
            subject,
            html
        };

        const info = await transporter.sendMail(mailOptions);
        return info;
    } catch (error) {
        throw new Error('Error sending email: ' + error.message);
    }
};

module.exports = {
    sendMail
};

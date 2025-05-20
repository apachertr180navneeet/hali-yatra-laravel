const { body } = require('express-validator');

const validators = {
    // Phone OTP validation
    sendPhoneOtp: [
        body('phone')
            .isLength({ min: 4, max: 13 })
            .withMessage('Phone number must be between 4 and 13 digits'),
        body('country_code')
            .isLength({ max: 5 })
            .withMessage('Country code must not exceed 5 characters')
    ],

    // Verify phone OTP validation
    verifyPhoneOtp: [
        body('phone')
            .isLength({ min: 4, max: 13 })
            .withMessage('Phone number must be between 4 and 13 digits'),
        body('country_code')
            .isLength({ max: 5 })
            .withMessage('Country code must not exceed 5 characters'),
        body('otp')
            .isLength({ max: 4 })
            .withMessage('OTP must not exceed 4 characters')
    ],

    // Registration validation
    register: [
        body('first_name')
            .notEmpty()
            .withMessage('First name is required'),
        body('last_name')
            .notEmpty()
            .withMessage('Last name is required'),
        body('email')
            .isEmail()
            .withMessage('Invalid email format')
            .custom(async (value) => {
                const user = await require('../models/User').findOne({ email: value });
                if (user) {
                    throw new Error('This email is already registered');
                }
                return true;
            }),
        body('phone')
            .isNumeric()
            .isLength({ min: 4, max: 12 })
            .withMessage('Phone number must be between 4 and 12 digits')
            .custom(async (value) => {
                const user = await require('../models/User').findOne({ phone: value });
                if (user) {
                    throw new Error('This phone number is already registered');
                }
                return true;
            }),
        body('password')
            .isLength({ min: 6 })
            .withMessage('Password must be at least 6 characters long'),
        body('address')
            .notEmpty()
            .withMessage('Address is required'),
        body('country_code')
            .isNumeric()
            .withMessage('Country code must be numeric'),
        body('latitude')
            .notEmpty()
            .withMessage('Latitude is required'),
        body('longitude')
            .notEmpty()
            .withMessage('Longitude is required'),
        body('avatar')
            .custom((value, { req }) => {
                if (!req.file) {
                    throw new Error('Avatar image is required');
                }
                return true;
            })
    ],

    // Login validation
    login: [
        body('email')
            .notEmpty()
            .withMessage('Email is required'),
        body('password')
            .notEmpty()
            .withMessage('Password is required'),
        body('device_type')
            .isIn(['ios', 'android', 'web', 'mobile', 'tablet'])
            .withMessage('Invalid device type'),
        body('device_token')
            .notEmpty()
            .withMessage('Device token is required')
    ],

    // Set forgot password validation
    setForgotPassword: [
        body('phone')
            .isLength({ min: 4, max: 13 })
            .withMessage('Phone number must be between 4 and 13 digits')
            .custom(async (value) => {
                const user = await require('../models/User').findOne({ phone: value });
                if (!user) {
                    throw new Error('Phone number user not exists');
                }
                return true;
            }),
        body('country_code')
            .isLength({ max: 5 })
            .withMessage('Country code must not exceed 5 characters')
            .custom(async (value, { req }) => {
                const user = await require('../models/User').findOne({
                    phone: req.body.phone,
                    country_code: value
                });
                if (!user) {
                    throw new Error('Phone number user not exists');
                }
                return true;
            }),
        body('password')
            .isLength({ min: 6 })
            .withMessage('Password must be at least 6 characters long')
            .custom(async (value, { req }) => {
                const user = await require('../models/User').findOne({
                    phone: req.body.phone,
                    country_code: req.body.country_code
                });
                if (user && await require('bcryptjs').compare(value, user.password)) {
                    throw new Error('Cannot use your old password as new password');
                }
                return true;
            })
    ],

    // Change password validation
    changePassword: [
        body('old_password')
            .notEmpty()
            .withMessage('Old password is required'),
        body('new_password')
            .isLength({ min: 6 })
            .withMessage('New password must be at least 6 characters long')
            .custom(async (value, { req }) => {
                const user = await require('../models/User').findById(req.user.id);
                if (await require('bcryptjs').compare(value, user.password)) {
                    throw new Error('Cannot use your old password as new password');
                }
                return true;
            })
    ],

    // Update profile validation
    updateProfile: [
        body('email')
            .optional()
            .isEmail()
            .withMessage('Invalid email format')
            .custom(async (value, { req }) => {
                const user = await require('../models/User').findOne({
                    email: value,
                    _id: { $ne: req.user.id }
                });
                if (user) {
                    throw new Error('This email is already registered');
                }
                return true;
            }),
        body('phone')
            .optional()
            .isNumeric()
            .isLength({ min: 4, max: 12 })
            .withMessage('Phone number must be between 4 and 12 digits')
            .custom(async (value, { req }) => {
                const user = await require('../models/User').findOne({
                    phone: value,
                    _id: { $ne: req.user.id }
                });
                if (user) {
                    throw new Error('This phone number is already registered');
                }
                return true;
            }),
        body('avatar')
            .optional()
            .custom((value, { req }) => {
                if (req.file && !req.file.mimetype.startsWith('image/')) {
                    throw new Error('Only image files are allowed');
                }
                return true;
            })
    ]
};

module.exports = validators;

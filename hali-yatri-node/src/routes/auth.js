const express = require('express');
const router = express.Router();
const authController = require('../controllers/authController');
const auth = require('../middleware/auth');
const validators = require('../middleware/validators');
const { upload } = require('../utils/fileUpload');

// Public routes
router.get('/splash-screens', authController.splashScreens);
router.post('/send-phone-otp', validators.sendPhoneOtp, authController.sendPhoneOtp);
router.post('/verify-phone-otp', validators.verifyPhoneOtp, authController.verifyPhoneOtp);
router.post('/register', upload.single('avatar'), validators.register, authController.register);
router.post('/verify-register', validators.verifyPhoneOtp, authController.verifyRegister);
router.post('/login', validators.login, authController.login);
router.post('/set-forgot-password', validators.setForgotPassword, authController.setForgotPassword);

// Protected routes
router.get('/user', auth, authController.getUser);
router.post('/change-password', auth, validators.changePassword, authController.changePassword);
router.put('/update-profile', auth, upload.single('avatar'), validators.updateProfile, authController.updateProfile);
router.post('/logout', auth, authController.logout);
router.delete('/delete-account', auth, authController.deleteAccount);

module.exports = router;

const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const { validationResult } = require('express-validator');
const User = require('../models/User');
const AppUser = require('../models/AppUser');
const PhoneOtp = require('../models/PhoneOtp');
const SplashScreen = require('../models/SplashScreen');
const { sendMail } = require('../utils/mailer');
const { uploadFile } = require('../utils/fileUpload');
const { generateSlug } = require('../utils/helpers');

const authController = {
    // Get splash screens
    async splashScreens(req, res) {
        try {
            const baseUrl = process.env.BASE_URL || 'http://localhost:3000';
            const splashScreens = await SplashScreen.find({}, 'type heading content image');

            const formattedScreens = splashScreens.map(screen => ({
                ...screen.toObject(),
                image: screen.image ? `${baseUrl}/${screen.image}` : null
            }));

            return res.status(200).json({
                status: true,
                data: formattedScreens
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Send phone OTP
    async sendPhoneOtp(req, res) {
        try {
            const errors = validationResult(req);
            if (!errors.isEmpty()) {
                return res.status(200).json({
                    status: false,
                    message: errors.array()[0].msg
                });
            }

            const { phone, country_code } = req.body;
            const code = '1234'; // For development, in production use random code
            const expireTime = new Date(Date.now() + 120 * 60 * 1000); // 120 minutes

            let phoneUser = await PhoneOtp.findOne({ phone, country_code });
            if (!phoneUser) {
                phoneUser = new PhoneOtp();
            }

            phoneUser.phone = phone;
            phoneUser.country_code = country_code;
            phoneUser.otp = code;
            phoneUser.otp_expire_time = expireTime;
            await phoneUser.save();

            return res.status(200).json({
                status: true,
                message: 'A one-time password has been sent to your phone, please check.'
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Verify phone OTP
    async verifyPhoneOtp(req, res) {
        try {
            const errors = validationResult(req);
            if (!errors.isEmpty()) {
                return res.status(200).json({
                    status: false,
                    message: errors.array()[0].msg
                });
            }

            const { phone, country_code, otp } = req.body;
            const phoneUser = await PhoneOtp.findOne({ phone, country_code });

            if (!phoneUser) {
                return res.status(200).json({
                    status: false,
                    message: 'Invalid phone number. Please check and try again'
                });
            }

            if (phoneUser.otp !== otp) {
                return res.status(200).json({
                    status: false,
                    message: 'Invalid verification code. Please try again'
                });
            }

            if (Date.now() > phoneUser.otp_expire_time) {
                return res.status(200).json({
                    status: true,
                    message: 'Verification code is expired.'
                });
            }

            await PhoneOtp.deleteOne({ phone, country_code });

            return res.status(200).json({
                status: true,
                message: 'Verified successfully.'
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Register user
    async register(req, res) {
        try {
            const errors = validationResult(req);
            if (!errors.isEmpty()) {
                return res.status(200).json({
                    status: false,
                    message: errors.array()[0].msg
                });
            }

            const {
                first_name,
                last_name,
                email,
                phone,
                password,
                address,
                area,
                city,
                state,
                country,
                country_code,
                zipcode,
                latitude,
                longitude,
                bio,
                device_type,
                device_token
            } = req.body;

            // Delete existing app user if exists
            await AppUser.deleteOne({ phone, country_code });

            const fullName = `${first_name} ${last_name}`;
            const slug = await generateSlug('users', fullName);

            // Handle file upload
            let avatar = '';
            if (req.file) {
                avatar = await uploadFile(req.file, 'uploads/user');
            }

            const appUser = new AppUser({
                first_name,
                last_name,
                full_name: fullName,
                slug,
                email,
                phone,
                password,
                address,
                area: area || '',
                city: city || '',
                state: state || '',
                country: country || '',
                country_code,
                zipcode: zipcode || '',
                latitude,
                longitude,
                bio: bio || '',
                device_type: device_type || '',
                device_token: device_token || '',
                avatar
            });

            await appUser.save();

            return res.status(200).json({
                status: true,
                message: 'Otp is sent on your phone! Please verify otp to complete your registration'
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Verify registration
    async verifyRegister(req, res) {
        try {
            const errors = validationResult(req);
            if (!errors.isEmpty()) {
                return res.status(200).json({
                    status: false,
                    message: errors.array()[0].msg
                });
            }

            const { phone, country_code, otp } = req.body;

            const phoneUser = await PhoneOtp.findOne({ phone, country_code, otp });
            const appUser = await AppUser.findOne({ phone, country_code });

            if (!phoneUser) {
                return res.status(200).json({
                    status: false,
                    message: 'Please enter valid otp.'
                });
            }

            if (Date.now() > phoneUser.otp_expire_time) {
                return res.status(200).json({
                    status: true,
                    message: 'Otp time is expired.'
                });
            }

            const user = new User({
                first_name: appUser.first_name,
                last_name: appUser.last_name,
                full_name: appUser.full_name,
                email: appUser.email,
                slug: appUser.slug,
                phone: appUser.phone,
                password: await bcrypt.hash(appUser.password, 10),
                address: appUser.address,
                area: appUser.area,
                city: appUser.city,
                state: appUser.state,
                country: appUser.country,
                country_code: appUser.country_code,
                zipcode: appUser.zipcode,
                latitude: appUser.latitude,
                longitude: appUser.longitude,
                device_type: appUser.device_type,
                device_token: appUser.device_token,
                bio: appUser.bio,
                phone_verified_at: new Date(),
                avatar: appUser.avatar,
                role: 'user',
                status: 'active'
            });

            await user.save();
            await AppUser.deleteOne({ phone, country_code });

            // Generate JWT token
            const token = jwt.sign(
                { id: user._id },
                process.env.JWT_SECRET,
                { expiresIn: '24h' }
            );

            return res.status(200).json({
                status: true,
                message: 'Account created successfully!',
                access_token: token,
                token_type: 'bearer',
                user: await authController.getUserDetail(user._id)
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Login
    async login(req, res) {
        try {
            const errors = validationResult(req);
            if (!errors.isEmpty()) {
                return res.status(200).json({
                    status: false,
                    message: errors.array()[0].msg
                });
            }

            const { email, password, device_type, device_token } = req.body;

            const user = await User.findOne({ email });
            if (!user) {
                return res.status(200).json({
                    status: false,
                    message: 'Email not exists'
                });
            }

            if (user.status === 'inactive') {
                return res.status(200).json({
                    status: false,
                    message: 'Your account is not activated yet.'
                });
            }

            const isMatch = await bcrypt.compare(password, user.password);
            if (!isMatch) {
                return res.status(200).json({
                    status: false,
                    message: 'Invalid email or password. Please try again'
                });
            }

            user.device_type = device_type;
            user.device_token = device_token;
            await user.save();

            const token = jwt.sign(
                { id: user._id },
                process.env.JWT_SECRET,
                { expiresIn: '24h' }
            );

            return res.status(200).json({
                status: true,
                message: 'Logged in successfully.',
                access_token: token,
                token_type: 'bearer',
                user: await authController.getUserDetail(user._id)
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Get user details
    async getUserDetail(userId) {
        return await User.findById(userId);
    },

    // Get current user
    async getUser(req, res) {
        try {
            const user = await User.findById(req.user.id);
            if (!user) {
                return res.status(200).json({
                    status: false,
                    message: 'User not found.'
                });
            }

            return res.status(200).json({
                status: true,
                message: 'User found successfully.',
                user: await authController.getUserDetail(user._id)
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Set forgot password
    async setForgotPassword(req, res) {
        try {
            const errors = validationResult(req);
            if (!errors.isEmpty()) {
                return res.status(200).json({
                    status: false,
                    message: errors.array()[0].msg
                });
            }

            const { phone, country_code, password } = req.body;

            const user = await User.findOne({ phone, country_code });
            if (!user) {
                return res.status(200).json({
                    status: false,
                    message: 'Phone number user not exists'
                });
            }

            const isMatch = await bcrypt.compare(password, user.password);
            if (isMatch) {
                return res.status(200).json({
                    status: false,
                    message: 'Cannot use your old password as new password.'
                });
            }

            user.password = await bcrypt.hash(password, 10);
            await user.save();

            return res.status(200).json({
                status: true,
                message: 'New Password set successfully. Please Login'
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Change password
    async changePassword(req, res) {
        try {
            const errors = validationResult(req);
            if (!errors.isEmpty()) {
                return res.status(200).json({
                    status: false,
                    message: errors.array()[0].msg
                });
            }

            const { old_password, new_password } = req.body;
            const user = await User.findById(req.user.id);

            const isOldPasswordMatch = await bcrypt.compare(old_password, user.password);
            if (!isOldPasswordMatch) {
                return res.status(200).json({
                    status: false,
                    message: 'Old Password did not matched!'
                });
            }

            const isNewPasswordMatch = await bcrypt.compare(new_password, user.password);
            if (isNewPasswordMatch) {
                return res.status(200).json({
                    status: false,
                    message: 'Cannot use your old password as new password.'
                });
            }

            user.password = await bcrypt.hash(new_password, 10);
            await user.save();

            return res.status(200).json({
                status: true,
                message: 'Password changed successfully',
                user: await authController.getUserDetail(user._id)
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Update profile
    async updateProfile(req, res) {
        try {
            const errors = validationResult(req);
            if (!errors.isEmpty()) {
                return res.status(200).json({
                    status: false,
                    message: errors.array()[0].msg
                });
            }

            const user = await User.findById(req.user.id);
            const updateData = req.body;

            if (updateData.first_name) {
                user.first_name = updateData.first_name;
                user.full_name = `${updateData.first_name} ${user.last_name}`;
            }

            if (updateData.last_name) {
                user.last_name = updateData.last_name;
                user.full_name = `${user.first_name} ${updateData.last_name}`;
            }

            if (req.file) {
                const avatar = await uploadFile(req.file, 'uploads/user');
                user.avatar = avatar;
            }

            // Update other fields
            const allowedFields = [
                'email', 'phone', 'address', 'area', 'city', 'state',
                'country', 'country_code', 'zipcode', 'latitude', 'longitude',
                'bio', 'device_type', 'device_token'
            ];

            allowedFields.forEach(field => {
                if (updateData[field] !== undefined) {
                    user[field] = updateData[field];
                }
            });

            await user.save();

            return res.status(200).json({
                status: true,
                message: 'Profile updated successfully!',
                user: await authController.getUserDetail(user._id)
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Logout
    async logout(req, res) {
        try {
            // In JWT, we don't need to invalidate the token on the server side
            // The client should remove the token
            return res.status(200).json({
                status: true,
                message: 'User successfully signed out.'
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    },

    // Delete account
    async deleteAccount(req, res) {
        try {
            const user = await User.findById(req.user.id);
            user.email = `${Date.now()}_delete_${user.email}`;
            user.phone = `${Date.now()}_delete_${user.phone}`;
            user.status = 'inactive';
            await user.save();

            return res.status(200).json({
                status: true,
                message: 'Account deleted successfully.'
            });
        } catch (error) {
            return res.status(500).json({
                status: false,
                message: error.message
            });
        }
    }
};

module.exports = authController;

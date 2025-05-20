const jwt = require('jsonwebtoken');
const User = require('../models/User');

const auth = async (req, res, next) => {
    try {
        const token = req.header('Authorization')?.replace('Bearer ', '');

        if (!token) {
            return res.status(401).json({
                status: false,
                message: 'Authentication required'
            });
        }

        const decoded = jwt.verify(token, process.env.JWT_SECRET);
        const user = await User.findById(decoded.id);

        if (!user) {
            return res.status(401).json({
                status: false,
                message: 'User not found'
            });
        }

        if (user.status === 'inactive') {
            return res.status(401).json({
                status: false,
                message: 'Your account is not activated yet'
            });
        }

        req.user = user;
        next();
    } catch (error) {
        return res.status(401).json({
            status: false,
            message: 'Invalid token'
        });
    }
};

module.exports = auth;

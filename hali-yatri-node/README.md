# Hali Yatri Node.js API

This is the Node.js Express.js version of the Hali Yatri application using MySQL database.

## Project Structure

```
hali-yatri-node/
├── src/
│   ├── config/         # Configuration files
│   ├── controllers/    # Route controllers
│   ├── middleware/     # Custom middleware
│   ├── models/         # Database models
│   ├── routes/         # API routes
│   ├── services/       # Business logic
│   ├── utils/          # Utility functions
│   └── app.js          # Main application file
├── tests/              # Test files
├── .env.example        # Example environment variables
├── package.json        # Project dependencies
└── README.md          # Project documentation
```

## Prerequisites

- Node.js (v14 or higher)
- MySQL (v8.0 or higher)

## Setup Instructions

1. Install dependencies:
   ```bash
   npm install
   ```

2. Create a MySQL database:
   ```sql
   CREATE DATABASE hali_yatri;
   ```

3. Create a `.env` file in the root directory with the following variables:
   ```
   PORT=3000
   NODE_ENV=development
   
   # Database Configuration
   DB_HOST=localhost
   DB_USER=your_mysql_username
   DB_PASSWORD=your_mysql_password
   DB_NAME=hali_yatri
   
   # JWT Configuration
   JWT_SECRET=your_jwt_secret_key_here
   JWT_EXPIRES_IN=7d
   ```

4. Start the development server:
   ```bash
   npm run dev
   ```

## API Endpoints

### Authentication
- POST /api/auth/send-phone-otp
- POST /api/auth/verify-phone-otp
- POST /api/auth/register
- POST /api/auth/verify-register
- POST /api/auth/login
- POST /api/auth/set-forgot-password

### User Management
- GET /api/users
- POST /api/users
- GET /api/users/:id
- PUT /api/users/:id
- DELETE /api/users/:id

### Bookings
- GET /api/bookings
- POST /api/bookings
- GET /api/bookings/:id
- PUT /api/bookings/:id
- DELETE /api/bookings/:id

### Payment Types
- GET /api/payment-types
- POST /api/payment-types
- GET /api/payment-types/:id
- PUT /api/payment-types/:id
- DELETE /api/payment-types/:id

### Locations
- GET /api/locations
- POST /api/locations
- GET /api/locations/:id
- PUT /api/locations/:id
- DELETE /api/locations/:id

### Ticket Types
- GET /api/ticket-types
- POST /api/ticket-types
- GET /api/ticket-types/:id
- PUT /api/ticket-types/:id
- DELETE /api/ticket-types/:id

## Development

- Run tests: `npm test`
- Start development server: `npm run dev`
- Start production server: `npm start`

## Database Migrations

The application uses Sequelize for database management. In development mode, the database schema will be automatically synchronized. For production, you should use migrations:

1. Create a migration:
   ```bash
   npx sequelize-cli migration:generate --name migration-name
   ```

2. Run migrations:
   ```bash
   npx sequelize-cli db:migrate
   ```

3. Undo migrations:
   ```bash
   npx sequelize-cli db:migrate:undo
   ``` 

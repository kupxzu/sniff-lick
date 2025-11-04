# API Documentation

## Authentication & User Management API

This API provides comprehensive user authentication and profile management functionality with proper error handling.

### Authentication Endpoints

#### 1. Register User
**POST** `/api/auth/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com"
  },
  "token": "your-auth-token"
}
```

#### 2. Login (with username or email)
**POST** `/api/auth/login`

**Request Body:**
```json
{
  "login": "johndoe",  // Can be username or email
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com"
  },
  "token": "your-auth-token"
}
```

#### 3. Logout
**POST** `/api/auth/logout`

**Headers:** `Authorization: Bearer your-auth-token`

**Response:**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

#### 4. Get Authenticated User
**GET** `/api/auth/user`

**Headers:** `Authorization: Bearer your-auth-token`

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com"
  }
}
```

### User Profile Management Endpoints

#### 1. Get Profile
**GET** `/api/user/profile`

**Headers:** `Authorization: Bearer your-auth-token`

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com"
  }
}
```

#### 2. Update Complete Profile
**PUT** `/api/user/profile`

**Headers:** `Authorization: Bearer your-auth-token`

**Request Body (all fields optional):**
```json
{
  "name": "John Smith",
  "username": "johnsmith",
  "email": "johnsmith@example.com",
  "current_password": "oldpassword123",  // Required if updating password
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### 3. Update Username Only
**PUT** `/api/user/username`

**Headers:** `Authorization: Bearer your-auth-token`

**Request Body:**
```json
{
  "username": "newusername"
}
```

#### 4. Update Email Only
**PUT** `/api/user/email`

**Headers:** `Authorization: Bearer your-auth-token`

**Request Body:**
```json
{
  "email": "newemail@example.com"
}
```

#### 5. Update Password Only
**PUT** `/api/user/password`

**Headers:** `Authorization: Bearer your-auth-token`

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

### Error Responses

All endpoints include proper error handling with try-catch blocks. Common error responses:

#### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "username": ["The username has already been taken."]
  }
}
```

#### Authentication Error (401)
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

#### Server Error (500)
```json
{
  "success": false,
  "message": "Operation failed",
  "error": "Detailed error message"
}
```

### Features Implemented

✅ **Username Field**: Added unique username field to users table
✅ **Login with Username**: Users can login using either username or email
✅ **Profile Updates**: Complete API for updating username, email, and password
✅ **Try-Catch Error Handling**: All controllers have comprehensive error handling
✅ **Authentication**: Full authentication system with Sanctum tokens
✅ **Validation**: Proper validation for all inputs
✅ **Security**: Password verification required for password changes

### Database Migration

Don't forget to run the migration to add the username field:

```bash
php artisan migrate
```

### Usage Notes

1. All protected routes require the `Authorization: Bearer {token}` header
2. The `login` field accepts both username and email for flexible authentication
3. Password updates require the current password for security
4. All responses follow a consistent format with `success`, `message`, and data fields
5. Comprehensive error handling ensures proper API responses in all scenarios
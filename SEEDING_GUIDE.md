# Database Seeding Guide

## Available Seeders

### UserSeeder
Creates predefined users for testing and development:

**Predefined Users:**
- **Admin**: username: `admin`, email: `admin@slvc.com`, password: `password123`
- **Test User**: username: `testuser`, email: `test@example.com`, password: `password123`  
- **Demo**: username: `demo`, email: `demo@slvc.com`, password: `demo123`
- **Developer**: username: `dev`, email: `dev@slvc.com`, password: `dev123`
- **Manager**: username: `manager`, email: `manager@slvc.com`, password: `manager123`
- **Random Users**: 15 additional users created with faker data

## Commands

### Run all seeders:
```bash
php artisan db:seed
```

### Run specific seeder:
```bash
php artisan db:seed --class=UserSeeder
```

### Refresh database and seed:
```bash
php artisan migrate:refresh --seed
```

### Fresh migration with seeding:
```bash
php artisan migrate:fresh --seed
```

## Login Credentials for Testing

You can use any of these credentials to test the login:

1. **Admin User**
   - Username: `admin` or Email: `admin@slvc.com`
   - Password: `password123`

2. **Test User** 
   - Username: `testuser` or Email: `test@example.com`
   - Password: `password123`

3. **Demo User**
   - Username: `demo` or Email: `demo@slvc.com` 
   - Password: `demo123`

4. **Developer**
   - Username: `dev` or Email: `dev@slvc.com`
   - Password: `dev123`

5. **Manager**
   - Username: `manager` or Email: `manager@slvc.com`
   - Password: `manager123`

## Notes

- All users are created with verified email addresses
- Passwords are properly hashed using Laravel's Hash facade
- The UserFactory now includes username generation for random users
- You can login using either username or email as designed in the API
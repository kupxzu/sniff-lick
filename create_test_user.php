<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create or update a test user
$user = User::updateOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]
);

echo "Test user created/updated:\n";
echo "Email: " . $user->email . "\n";
echo "Username: " . $user->username . "\n";
echo "Password: password123\n";

// Verify the password works
$passwordCheck = Hash::check('password123', $user->password);
echo "Password verification: " . ($passwordCheck ? 'SUCCESS' : 'FAILED') . "\n";
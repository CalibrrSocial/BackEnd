#!/bin/bash
# Emergency fix script to restore API functionality

echo "=== Emergency API Fix Script ==="
echo "This will restore your API to working condition"
echo ""

# SSH into server and run fixes
ssh -i ~/.ssh/your-key.pem ubuntu@api.calibrr.com << 'ENDSSH'
set -e

echo "1. Navigating to project directory..."
cd /var/www/api.calibrr.com/Social_Backend

echo "2. Fetching latest main branch..."
git fetch origin
git checkout main
git pull origin main

echo "3. Installing composer dependencies (with correct flags)..."
composer install --no-dev --optimize-autoloader --ignore-platform-req=php

echo "4. Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
rm -f bootstrap/cache/config.php

echo "5. Checking/creating .env file..."
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

echo "6. Setting up database..."
php artisan migrate --force

echo "7. Setting up Laravel Passport..."
if [ ! -f storage/oauth-private.key ]; then
    php artisan passport:keys --force
fi
php artisan passport:client --personal --name="Personal Access Client" --no-interaction || true

echo "8. Configuring cache to use file driver (Redis issues)..."
sed -i 's/CACHE_DRIVER=.*/CACHE_DRIVER=file/' .env
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
sed -i 's/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' .env

echo "9. Rebuilding configuration cache..."
php artisan config:cache
php artisan route:cache

echo "10. Setting correct permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "11. Creating test user..."
php artisan tinker <<'PHP'
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Delete existing test user if exists
User::where('email', 'test@calibrr.com')->delete();

// Create new test user
$user = User::create([
    'email' => 'test@calibrr.com',
    'phone' => '5555551234',
    'password' => Hash::make('TestPassword123'),
    'first_name' => 'Test',
    'last_name' => 'User',
    'dob' => '2000-01-01',
    'email_verified_at' => now(),
]);

echo "Test user created: test@calibrr.com / TestPassword123\n";
PHP

echo "12. Restarting web server..."
sudo systemctl restart apache2
sudo systemctl restart php8.3-fpm || true

echo "=== Fix completed! ==="
echo "Test credentials: test@calibrr.com / TestPassword123"

ENDSSH

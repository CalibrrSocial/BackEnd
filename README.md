# Calibrr Backend API

Laravel-based REST API backend for the Calibrr social networking app.

## Automatic Deployment

**Pushing to `main` automatically deploys to EC2 via GitHub Actions.**

See [docs/DEPLOYMENT_GUIDE.md](docs/DEPLOYMENT_GUIDE.md) for:
- How the deployment works
- Setting up deployment for new repositories
- Troubleshooting deployment issues

## Local Development Setup

1. Clone the repository
2. Copy `.env.example` to `.env` and configure database
3. Install dependencies:
   ```bash
   composer install
   ```
4. Generate app key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Start development server:
   ```bash
   php artisan serve
   ```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login
- `POST /api/auth/forgot-password` - Request password reset

### Profile
- `GET /api/profile/{id}` - Get user profile
- `PUT /api/profile/{id}` - Update profile

### Profile Likes
- `GET /api/profile/{id}/likes` - Get like count
- `POST /api/profile/{id}/likes?profileLikedId={targetId}` - Like profile
- `DELETE /api/profile/{id}/likes?profileLikedId={targetId}` - Unlike profile
- `GET /api/profile/{id}/likes/received` - Get received likes
- `GET /api/profile/{id}/likes/sent` - Get sent likes

### Attribute Likes
- `POST /api/profile/{id}/attributes/like` - Like an attribute
- `DELETE /api/profile/{id}/attributes/like` - Unlike an attribute
- `GET /api/profile/{id}/attributes/{category}/{attribute}/likes` - Get attribute like count

### Relationships
- `GET /api/user/{id}/relationships` - Get user relationships
- `POST /api/friend/request` - Send friend request
- `PUT /api/friend/update` - Accept/decline friend request

### Search
- `GET /api/search/distance` - Search users by distance
- `GET /api/search/name` - Search users by name

## Environment Variables

Required environment variables:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=calibrr
DB_USERNAME=your_username
DB_PASSWORD=your_password

# AWS Lambda Notifications
LAMBDA_REGION=us-east-1
LAMBDA_PROFILE_LIKED_FUNCTION=emailNotificationFinal
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret

# S3 Storage (optional)
AWS_BUCKET=your_bucket
AWS_URL=your_s3_url
```

## Manual Deployment

```bash
# On EC2 instance
cd /var/www/html/calibrr-backend
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
sudo systemctl restart apache2
```

## Built With

- [Laravel 8](https://laravel.com) - PHP Framework
- [Laravel Passport](https://laravel.com/docs/passport) - API Authentication
- [AWS SDK](https://aws.amazon.com/sdk-for-php/) - Lambda & S3 Integration

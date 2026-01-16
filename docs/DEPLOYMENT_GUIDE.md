# EC2 Deployment Guide

This guide explains how the automatic deployment to EC2 works via GitHub Actions.

## Overview

When you push to the `main` branch of this repository, GitHub Actions automatically deploys the code to the EC2 instance. This is configured via the `.github/workflows/deploy.yml` file.

**Current Status:** ✅ Configured and working for the original repository.

## How It Works

```
Push to main branch
       ↓
GitHub Actions triggered
       ↓
SSH into EC2 instance
       ↓
Pull latest code
       ↓
Install dependencies
       ↓
Run Laravel migrations
       ↓
Clear caches & restart Apache
```

## Setting Up Deployment (For New Repositories/Clones)

If you clone this repository to a new GitHub account or want to set up deployment for a different EC2 instance, follow these steps:

### Step 1: Enable GitHub Actions

1. Go to your repository on GitHub
2. Click **Settings** → **Actions** → **General**
3. Under "Actions permissions", select **Allow all actions and reusable workflows**
4. Click **Save**

### Step 2: Configure Repository Secrets

Go to **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

Add the following secrets:

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `SSH_HOST` | EC2 instance public IP or hostname | `18.220.142.127` |
| `SSH_USER` | SSH username for EC2 | `ubuntu` |
| `SSH_KEY` | Private SSH key (entire contents of .pem file) | `-----BEGIN RSA PRIVATE KEY-----...` |
| `PROJECT_DIR` | Path to Laravel project on EC2 | `/var/www/html/calibrr-backend` |
| `BRANCH` | Branch to deploy | `main` |

### Step 3: SSH Key Setup

1. Generate an SSH key pair (or use an existing one):
   ```bash
   ssh-keygen -t rsa -b 4096 -f calibrr-deploy-key
   ```

2. Add the **public key** to the EC2 instance:
   ```bash
   # On EC2 instance
   echo "YOUR_PUBLIC_KEY" >> ~/.ssh/authorized_keys
   ```

3. Add the **private key** contents to GitHub as the `SSH_KEY` secret

### Step 4: Verify EC2 Prerequisites

Ensure your EC2 instance has:

- [ ] Apache/Nginx web server
- [ ] PHP 7.3+ with required extensions (xml, curl, mbstring, etc.)
- [ ] Composer installed globally
- [ ] MySQL/MariaDB database
- [ ] Git installed
- [ ] Proper directory permissions for www-data

### Step 5: Test the Deployment

1. Make a small change to a file
2. Commit and push to `main`
3. Go to **Actions** tab in GitHub to watch the deployment
4. Check for green checkmark ✅

## Workflow File Explained

The deployment workflow is located at `.github/workflows/deploy.yml`:

```yaml
name: Deploy to EC2
on:
  push:
    branches: [ main ]      # Triggers on push to main

jobs:
  deploy:
    runs-on: ubuntu-latest
    timeout-minutes: 10     # Fail if takes longer than 10 min
    steps:
      - name: SSH deploy
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            # ... deployment commands
```

### What the Script Does:

1. **Updates packages** - `sudo apt-get update`
2. **Installs PHP extensions** - xml, curl if missing
3. **Fixes permissions** - Ensures proper ownership
4. **Pulls latest code** - `git fetch && git reset --hard`
5. **Installs Composer dependencies** - `composer install --no-dev`
6. **Runs migrations** - `php artisan migrate --force`
7. **Clears Laravel caches** - config, route, cache
8. **Restarts Apache** - Clears opcache

## Troubleshooting

### Deployment Failed - SSH Connection

- Verify `SSH_HOST` is correct (check EC2 public IP)
- Verify `SSH_KEY` is the complete private key including headers
- Check EC2 security group allows SSH (port 22) from GitHub IPs

### Deployment Failed - Permission Denied

- Ensure `SSH_USER` has sudo access
- Check that www-data owns the storage directory

### Deployment Failed - Composer Error

- Check PHP version compatibility
- Ensure all required PHP extensions are installed

### View Deployment Logs

1. Go to **Actions** tab
2. Click on the failed workflow run
3. Click on **SSH deploy** step
4. Review the output logs

## Manual Deployment

If you need to deploy manually (GitHub Actions not available):

```bash
# SSH into EC2
ssh -i your-key.pem ubuntu@your-ec2-ip

# Navigate to project
cd /var/www/html/calibrr-backend

# Pull latest
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Restart Apache
sudo systemctl restart apache2
```

## Security Notes

- Never commit secrets to the repository
- Use GitHub Secrets for all sensitive values
- Rotate SSH keys periodically
- Consider using AWS Systems Manager Session Manager instead of SSH keys for enhanced security


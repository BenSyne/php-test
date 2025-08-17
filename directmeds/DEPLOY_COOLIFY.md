# Coolify Deployment Guide with Traefik

This guide is specifically for deploying Direct Meds on Coolify, which uses Traefik as its reverse proxy.

## Quick Deploy Steps

### 1. Prepare Your Repository

```bash
# Use the Coolify-specific docker-compose
cp docker-compose.coolify.yml docker-compose.yml

# Commit everything
git add .
git commit -m "Configure for Coolify deployment"
git push origin main
```

### 2. Configure in Coolify Dashboard

1. **Create New Resource**:
   - Click "New Resource"
   - Select "Docker Compose"
   - Choose your server

2. **Source Configuration**:
   - Repository: Your Git URL
   - Branch: `main`
   - Build Pack: Docker Compose
   - Compose File: `docker-compose.coolify.yml` (or `docker-compose.yml` if renamed)

3. **Environment Variables** (Add these in Coolify):
   ```env
   APP_NAME=Direct Meds
   APP_ENV=production
   APP_KEY=base64:generate-this-key
   APP_DEBUG=false
   APP_DOMAIN=your-domain.com
   
   DB_DATABASE=directmeds
   DB_USERNAME=directmeds_user
   DB_PASSWORD=your-secure-password
   DB_ROOT_PASSWORD=your-root-password
   
   # Add other variables from .env.production as needed
   ```

4. **Domain Configuration**:
   - Domain: `your-domain.com`
   - SSL: Enable (Coolify handles Let's Encrypt automatically)

5. **Resource Limits** (Optional):
   ```yaml
   app: 2GB RAM, 2 CPU
   mysql: 1GB RAM, 1 CPU
   redis: 512MB RAM, 0.5 CPU
   ```

### 3. Deploy

1. Click "Deploy"
2. Monitor the deployment logs
3. Wait for "Deployment successful"

### 4. Post-Deployment Setup

SSH into your Coolify server or use Coolify's terminal:

```bash
# Find your container
docker ps | grep directmeds-app

# Run migrations
docker exec -it directmeds-app php artisan migrate --force

# Create admin user
docker exec -it directmeds-app php artisan tinker
>>> User::create(['name' => 'Admin', 'email' => 'admin@directmeds.com', 'password' => bcrypt('YourSecurePassword')]);
>>> exit

# Set permissions (if needed)
docker exec -it directmeds-app chown -R www-data:www-data storage bootstrap/cache
```

## Traefik Configuration

Coolify automatically configures Traefik, but these labels in `docker-compose.coolify.yml` control the routing:

```yaml
labels:
  - "traefik.enable=true"
  - "traefik.http.routers.directmeds.rule=Host(`${APP_DOMAIN}`)"
  - "traefik.http.routers.directmeds.entrypoints=websecure"
  - "traefik.http.routers.directmeds.tls=true"
  - "traefik.http.routers.directmeds.tls.certresolver=letsencrypt"
```

## Database Access

Since we're using MySQL (not SQLite), you have several options:

### Option 1: phpMyAdmin (Development Only)
Add to `docker-compose.coolify.yml`:

```yaml
phpmyadmin:
  image: phpmyadmin:latest
  environment:
    PMA_HOST: mysql
    PMA_USER: root
    PMA_PASSWORD: ${DB_ROOT_PASSWORD}
  labels:
    - "traefik.enable=true"
    - "traefik.http.routers.pma.rule=Host(`pma.${APP_DOMAIN}`)"
    - "traefik.http.routers.pma.entrypoints=websecure"
    - "traefik.http.routers.pma.tls=true"
  networks:
    - directmeds-network
```

### Option 2: Direct MySQL Access
```bash
# Connect to MySQL container
docker exec -it directmeds-mysql mysql -u root -p

# Or from host with port forwarding
docker exec -it directmeds-mysql mysql -h localhost -P 3306 -u directmeds_user -p
```

## Monitoring

### Health Checks
- Main: `https://your-domain.com/health`
- Liveness: `https://your-domain.com/health/liveness`
- Readiness: `https://your-domain.com/health/readiness`

### Logs
In Coolify dashboard:
- Click on your application
- Go to "Logs" tab
- Select service (app/mysql/redis)

Or via SSH:
```bash
# Application logs
docker logs directmeds-app -f

# MySQL logs
docker logs directmeds-mysql -f

# Redis logs
docker logs directmeds-redis -f
```

## Troubleshooting

### 502 Bad Gateway
```bash
# Check if app is running
docker ps | grep directmeds

# Check app logs
docker logs directmeds-app --tail 50

# Restart the app
docker restart directmeds-app
```

### Database Connection Failed
```bash
# Verify MySQL is running
docker exec -it directmeds-mysql mysqladmin ping -u root -p

# Check environment variables
docker exec -it directmeds-app env | grep DB_

# Test connection from app
docker exec -it directmeds-app php artisan tinker
>>> DB::connection()->getPdo();
```

### Permission Issues
```bash
docker exec -it directmeds-app chown -R www-data:www-data storage bootstrap/cache
docker exec -it directmeds-app chmod -R 775 storage bootstrap/cache
```

## Backup Strategy

### Automated Backups
Create a backup script in Coolify:

```bash
#!/bin/bash
# Backup database
docker exec directmeds-mysql mysqldump -u root -p${DB_ROOT_PASSWORD} directmeds > /backups/db-$(date +%Y%m%d).sql

# Backup uploads
docker cp directmeds-app:/var/www/html/public/uploads /backups/uploads-$(date +%Y%m%d)

# Keep only last 7 days
find /backups -name "*.sql" -mtime +7 -delete
find /backups -type d -name "uploads-*" -mtime +7 -exec rm -rf {} +
```

## Security Notes

1. **Environment Variables**: 
   - Use Coolify's secret management
   - Never commit `.env` files

2. **Database**:
   - MySQL is internal only (not exposed)
   - Use strong passwords
   - Regular backups

3. **HTTPS**:
   - Coolify handles SSL automatically
   - Forces HTTPS redirect

4. **Updates**:
   - Use Coolify's "Redeploy" for updates
   - Always backup before updates

## Performance Optimization

1. **Enable OPcache** (already configured)
2. **Redis Caching** (already configured)
3. **CDN for Assets** (optional):
   - Configure Cloudflare
   - Update `APP_URL` and asset URLs

## Support

- Check Coolify logs first
- Review this guide
- Check Laravel logs: `/storage/logs/laravel.log`
- MySQL issues: Check connection and credentials
# Docker Deployment Guide for Direct Meds

This guide covers deploying the Direct Meds pharmacy platform using Docker and Coolify.

## Prerequisites

- Docker 20.10+ installed
- Docker Compose 2.0+ installed
- Coolify instance set up and running
- Domain name configured (optional but recommended)

## Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd directmeds
```

### 2. Set Up Environment Variables

```bash
cp .env.production .env
# Edit .env with your actual values
nano .env
```

Key variables to update:
- `APP_KEY` - Generate with `php artisan key:generate`
- `DB_PASSWORD` - Set a secure database password
- `APP_URL` - Your production domain
- Mail configuration
- Payment gateway credentials
- HIPAA compliance keys

### 3. Build and Run with Docker Compose

```bash
# Build the containers
docker-compose build

# Start the services
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f app
```

### 4. Initialize the Application

```bash
# Run database migrations
docker-compose exec app php artisan migrate --force

# Seed initial data (optional)
docker-compose exec app php artisan db:seed

# Create admin user
docker-compose exec app php artisan tinker
# >>> User::create(['name' => 'Admin', 'email' => 'admin@directmeds.com', 'password' => bcrypt('password')]);
```

## Deployment to Coolify

### 1. Prepare for Coolify

1. Push your code to a Git repository (GitHub, GitLab, etc.)
2. Ensure the `Dockerfile` and `docker-compose.yml` are in the repository root

### 2. Configure in Coolify

1. **Create New Application**:
   - Go to your Coolify dashboard
   - Click "New Application"
   - Select "Docker Compose" as the build pack

2. **Connect Repository**:
   - Add your Git repository URL
   - Configure branch (main/master)
   - Set up deploy key if private repository

3. **Environment Variables**:
   - Add all variables from `.env.production`
   - Use Coolify's secret management for sensitive values
   - Enable "Show in Build" for build-time variables

4. **Configure Resources**:
   ```yaml
   Services:
     - app: 2 CPUs, 4GB RAM
     - mysql: 1 CPU, 2GB RAM  
     - redis: 0.5 CPU, 512MB RAM
   ```

5. **Health Checks**:
   - Liveness: `/health/liveness`
   - Readiness: `/health/readiness`
   - Main check: `/health`

6. **Persistent Storage**:
   - MySQL data: `/var/lib/mysql`
   - App storage: `/var/www/html/storage`
   - Uploads: `/var/www/html/public/uploads`

### 3. Deploy

1. Click "Deploy" in Coolify
2. Monitor deployment logs
3. Wait for health checks to pass
4. Access your application at the configured domain

## Container Architecture

### Services

1. **app** (Laravel + Nginx + PHP-FPM)
   - Port: 80
   - Handles web requests and background jobs
   - Runs queue workers and scheduler

2. **mysql** (Database)
   - Port: 3306 (internal)
   - Stores application data
   - Persistent volume for data

3. **redis** (Cache & Sessions)
   - Port: 6379 (internal)
   - Handles cache, sessions, and queues
   - Persistent volume for data

4. **phpmyadmin** (Database Management)
   - Port: 8080
   - Optional, remove in production
   - Web interface for MySQL

## Monitoring

### Health Endpoints

- `/health` - Complete system health check
- `/health/liveness` - Basic liveness probe
- `/health/readiness` - Readiness for traffic
- `/status` - Application status and features

### Logs

```bash
# Application logs
docker-compose logs app

# Database logs
docker-compose logs mysql

# Redis logs
docker-compose logs redis

# Follow all logs
docker-compose logs -f
```

### Metrics

Access container metrics:
```bash
docker stats
```

## Maintenance

### Backup Database

```bash
# Create backup
docker-compose exec mysql mysqldump -u root -p directmeds > backup.sql

# Restore backup
docker-compose exec -T mysql mysql -u root -p directmeds < backup.sql
```

### Update Application

```bash
# Pull latest changes
git pull origin main

# Rebuild containers
docker-compose build app

# Apply migrations
docker-compose exec app php artisan migrate --force

# Restart services
docker-compose restart
```

### Clear Cache

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

## Security Considerations

1. **Environment Variables**:
   - Never commit `.env` file
   - Use Coolify's secret management
   - Rotate keys regularly

2. **Network Security**:
   - Use HTTPS in production
   - Configure firewall rules
   - Limit database access

3. **Container Security**:
   - Run as non-root user
   - Keep base images updated
   - Scan for vulnerabilities

4. **HIPAA Compliance**:
   - Enable audit logging
   - Encrypt data at rest
   - Configure backup retention

## Troubleshooting

### Container Won't Start

```bash
# Check logs
docker-compose logs app

# Verify environment variables
docker-compose config

# Check file permissions
docker-compose exec app ls -la storage/
```

### Database Connection Issues

```bash
# Test connection
docker-compose exec app php artisan tinker
# >>> DB::connection()->getPdo();

# Check MySQL status
docker-compose exec mysql mysql -u root -p -e "SHOW STATUS;"
```

### Performance Issues

```bash
# Check resource usage
docker stats

# Review slow queries
docker-compose exec mysql mysql -u root -p -e "SHOW PROCESSLIST;"

# Monitor Redis
docker-compose exec redis redis-cli INFO
```

## Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure proper `APP_KEY`
- [ ] Set up SSL/TLS certificates
- [ ] Configure backup strategy
- [ ] Set up monitoring and alerts
- [ ] Configure log rotation
- [ ] Review security settings
- [ ] Test disaster recovery
- [ ] Document deployment process
- [ ] Set up CI/CD pipeline

## Support

For issues or questions:
- Check application logs
- Review Coolify documentation
- Contact system administrator

## License

Copyright © 2024 Direct Meds. All rights reserved.
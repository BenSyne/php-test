# Direct Meds - Pharmacy E-Commerce Platform

A comprehensive pharmacy e-commerce platform built with Laravel, featuring HIPAA compliance, prescription management, and multi-role authentication.

## Project Structure

```
php-test/
├── directmeds/          # Laravel application
│   ├── app/            # Application logic
│   ├── config/         # Configuration files
│   ├── database/       # Migrations and seeders
│   ├── docker/         # Docker configuration files
│   ├── public/         # Public assets
│   ├── resources/      # Views and frontend assets
│   ├── routes/         # Application routes
│   └── storage/        # Application storage
├── docs/               # Documentation
├── Dockerfile          # Production Docker image
├── docker-compose.yml  # Docker Compose configuration
└── .env.example       # Environment template
```

## Quick Start with Docker

### Local Development

1. Clone the repository:
```bash
git clone https://github.com/BenSyne/php-test.git
cd php-test
```

2. Copy environment file:
```bash
cp .env.example .env
# Edit .env with your configuration
```

3. Start with Docker Compose:
```bash
docker-compose up -d
```

4. Access the application:
- Application: http://localhost
- phpMyAdmin: http://localhost:8080

### Deployment on Coolify

1. Connect this GitHub repository to Coolify
2. Set build context to repository root (/)
3. Use `docker-compose.yml` for deployment
4. Configure environment variables in Coolify
5. Deploy!

## Technology Stack

- **Backend**: PHP 8.3, Laravel 10.x
- **Database**: MySQL 8.0
- **Cache/Sessions**: Redis
- **Web Server**: Nginx
- **Process Manager**: Supervisor
- **Container**: Docker with Alpine Linux

## Features

- 🏥 **Pharmacy Management**: Complete e-commerce for pharmaceuticals
- 💊 **Prescription System**: Upload, verify, and manage prescriptions
- 👥 **Multi-Role System**: Patient, Pharmacist, Prescriber, Admin roles
- 🔒 **HIPAA Compliant**: Audit logging and data encryption
- 🛡️ **Security**: Two-factor authentication, role-based access control
- 📊 **Admin Dashboard**: Modern UI with analytics and reporting
- 🚀 **Docker Ready**: Optimized for container deployment

## Environment Variables

Key variables to configure:

- `APP_KEY`: Laravel application key
- `APP_DOMAIN`: Your domain name
- `DB_PASSWORD`: Database password
- `STRIPE_KEY/STRIPE_SECRET`: Payment gateway
- `MAIL_*`: Email configuration
- `HIPAA_*`: Compliance settings

## Documentation

- [Deployment Guide](./directmeds/DEPLOY_COOLIFY.md)
- [Docker Setup](./directmeds/README.Docker.md)
- [API Documentation](./directmeds/storage/api-docs/api-docs.json)

## Health Checks

The application provides health endpoints for monitoring:

- `/health` - Complete system health
- `/health/liveness` - Basic liveness check
- `/health/readiness` - Ready for traffic

## Support

For issues or questions, please open an issue on GitHub.

## License

Copyright © 2024 Direct Meds. All rights reserved.
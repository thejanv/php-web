# PHP Web App — Docker Compose Stack

A learning project exploring containerised PHP/Nginx/MySQL deployment with SSL, backup strategy, and a local → staging → prod promotion workflow.

## Stack

- PHP 8.3 (FPM)
- Nginx 1.27
- MySQL 8.4
- Docker Compose

## Project Structure

```
.
├── nginx/          # Nginx Dockerfile + config
├── php/            # PHP Dockerfile
├── mysql/          # init.sql (runs on first boot)
├── src/            # Application source
│   ├── config.php
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   └── delete.php
├── backup.sh       # Daily mysqldump script
├── docker-compose.yml
└── .env.example
```

## Local Development

```bash
# Copy env and fill in values
cp .env.example .env

# Start stack (override file exposes port 8080 and 3307 automatically)
docker compose up -d

# Visit
http://localhost:8080
```

## Production Deployment

```bash
# SSH into VPS
ssh root@your-vps-ip

# Clone
git clone https://github.com/thejanv/php-web.git /opt/ashadi
cd /opt/ashadi

# Create env
cp .env.example .env
nano .env

# Issue SSL cert (first time only)
docker compose up -d nginx
docker compose run --rm certbot certonly \
  --webroot \
  --webroot-path=/var/www/certbot \
  -d yourdomain.com \
  -d www.yourdomain.com \
  --email you@email.com \
  --agree-tos \
  --no-eff-email

# Bring everything up
docker compose up -d
```

## Deploying Updates

```bash
git pull origin main
docker compose up -d --build
```

## Backup

Daily mysqldump runs via cron at 2am, stored in `/opt/ashadi/backups/`, 7-day retention.

```bash
# Manual backup
./backup.sh
```

## Environment Variables

See `.env.example` for required variables.

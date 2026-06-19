# Disaster Recovery Runbook

## PHP Web App — Docker Compose Stack

**Last updated:** June 2026  
**Maintained by:** Thejan Vithanage  
**Production URL:** https://test.thejanv.com  
**VPS:** srv1764927 — 187.77.154.94  
**Repo:** https://github.com/thejanv/php-web

---

## 1. Contacts

| Role  | Name             | Contact                      |
| ----- | ---------------- | ---------------------------- |
| Owner | Thejan Vithanage | thejan.vithanage@outlook.com |

---

## 2. Architecture Overview

```
GitHub (thejanv/php-web)
        ↓ GitHub Actions (SSH)
VPS srv1764927 (187.77.154.94)
        ├── /opt/ashadi          ← production
        └── /opt/ashadi-staging  ← staging

Production stack (Docker Compose):
  nginx     → port 80, 443 (SSL via Let's Encrypt)
  php       → port 9000 (internal)
  mysql     → port 3306 (internal)
  certbot   → auto-renewal loop
```

---

## 3. Backups

| What       | Where                  | Schedule          | Retention |
| ---------- | ---------------------- | ----------------- | --------- |
| MySQL dump | `/opt/ashadi/backups/` | Daily 2:00 AM UTC | 7 days    |

**Verify backups are running:**

```bash
ls -lh /opt/ashadi/backups/
tail -20 /var/log/ashadi-backup.log
```

**Manual backup:**

```bash
set -a && source /opt/ashadi/.env && set +a
/opt/ashadi/backup.sh
```

---

## 4. Common Incidents

### 4.1 Site is down

```bash
# SSH into VPS
ssh root@187.77.154.94

# Check container status
cd /opt/ashadi
docker compose ps

# Check logs
docker compose logs nginx --tail 20
docker compose logs php --tail 20
docker compose logs mysql --tail 20

# Restart stack
docker compose restart

# If that fails, full restart
docker compose down
docker compose up -d
```

---

### 4.2 Database is down

```bash
cd /opt/ashadi
docker compose ps mysql

# Check logs
docker compose logs mysql --tail 20

# Restart mysql only
docker compose restart mysql

# Verify healthy
docker compose ps mysql
```

---

### 4.3 SSL certificate expired or nginx won't start

```bash
cd /opt/ashadi

# Check cert expiry
docker compose run --rm --entrypoint certbot certbot certificates

# Force renewal
docker compose run --rm --entrypoint certbot certbot renew --force-renewal

# Reload nginx
docker compose exec nginx nginx -s reload

# If options-ssl-nginx.conf is missing (fresh VPS)
curl -s https://raw.githubusercontent.com/certbot/certbot/master/certbot-nginx/certbot_nginx/_internal/tls_configs/options-ssl-nginx.conf \
  > /opt/ashadi/certbot/conf/options-ssl-nginx.conf

# If ssl-dhparams.pem is missing (fresh VPS)
openssl dhparam -out /opt/ashadi/certbot/conf/ssl-dhparams.pem 2048

docker compose up -d nginx
```

---

### 4.4 Restore database from backup

```bash
# List available backups
ls -lh /opt/ashadi/backups/

# Restore a specific backup
set -a && source /opt/ashadi/.env && set +a

gunzip < /opt/ashadi/backups/backup_YYYYMMDD_HHMMSS.sql.gz | \
  docker compose exec -T mysql mysql \
  -u root -p"${DB_ROOT_PASSWORD}" "${DB_NAME}"

# Verify
docker compose exec mysql mysql \
  -u root -p"${DB_ROOT_PASSWORD}" "${DB_NAME}" \
  -e "SHOW TABLES; SELECT COUNT(*) FROM products;"
```

---

### 4.5 Deploy a specific commit to production

```bash
cd /opt/ashadi
git fetch origin
git checkout main
git pull origin main

# Or roll back to a specific commit
git log --oneline -10
git checkout <commit-hash>

docker compose up -d --build
```

---

## 5. Fresh VPS Recovery (Full Rebuild)

If the VPS is lost entirely, follow these steps in order:

**Step 1 — Provision new VPS on Hostinger**

- Ubuntu 24.04
- Note the new IP

**Step 2 — Open firewall ports**

In Hostinger panel → Firewall, add Accept rules for:

- TCP 22 (SSH)
- TCP 80 (HTTP)
- TCP 443 (HTTPS)
- TCP 8080 (staging)

**Step 3 — Install Docker**

```bash
curl -fsSL https://get.docker.com | sh
```

**Step 4 — Add deploy SSH key**

```bash
mkdir -p ~/.ssh
echo "YOUR_PUBLIC_KEY" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

**Step 5 — Clone and configure**

```bash
git clone https://github.com/thejanv/php-web.git /opt/ashadi
cd /opt/ashadi
cp .env.example .env
nano .env  # fill in prod credentials
```

**Step 6 — Create SSL cert**

```bash
mkdir -p /opt/ashadi/certbot/www
mkdir -p /opt/ashadi/certbot/conf

# Create temp override for HTTP-only nginx
cat > /opt/ashadi/docker-compose.override.yml << 'EOF'
services:
  nginx:
    volumes:
      - ./src:/var/www/html:ro
      - ./certbot/www:/var/www/certbot:ro
      - ./certbot/conf:/etc/letsencrypt:ro
      - ./nginx/default.dev.conf:/etc/nginx/conf.d/default.conf:ro
  certbot:
    entrypoint: "/bin/sh -c 'exit 0'"
EOF

docker compose up -d nginx

# Issue cert
docker compose run --rm --entrypoint certbot certbot certonly \
  --webroot \
  --webroot-path=/var/www/certbot \
  -d test.thejanv.com \
  --email thejan.vithanage@outlook.com \
  --agree-tos \
  --no-eff-email

# Generate DH params
openssl dhparam -out /opt/ashadi/certbot/conf/ssl-dhparams.pem 2048

# Download SSL options
curl -s https://raw.githubusercontent.com/certbot/certbot/master/certbot-nginx/certbot_nginx/_internal/tls_configs/options-ssl-nginx.conf \
  > /opt/ashadi/certbot/conf/options-ssl-nginx.conf

# Remove override and start full stack
rm /opt/ashadi/docker-compose.override.yml
docker compose up -d
```

**Step 7 — Restore database**

```bash
# Copy latest backup from old VPS or local machine
scp backup_YYYYMMDD.sql.gz root@NEW_VPS_IP:/opt/ashadi/backups/

set -a && source /opt/ashadi/.env && set +a

gunzip < /opt/ashadi/backups/backup_YYYYMMDD.sql.gz | \
  docker compose exec -T mysql mysql \
  -u root -p"${DB_ROOT_PASSWORD}" "${DB_NAME}"
```

**Step 8 — Restore cron**

```bash
crontab -e
# Add:
0 2 * * * . /opt/ashadi/.env && /opt/ashadi/backup.sh >> /var/log/ashadi-backup.log 2>&1
0 3 * * * docker compose -f /opt/ashadi/docker-compose.yml exec -T nginx nginx -s reload
```

**Step 9 — Update DNS**

- Point `test.thejanv.com` A record to new VPS IP in Hostinger DNS Manager

**Step 10 — Update GitHub Secrets**

- Update `VPS_HOST` in GitHub → Settings → Secrets to new IP

---

## 6. Staging Setup (if lost)

```bash
git clone https://github.com/thejanv/php-web.git /opt/ashadi-staging
cd /opt/ashadi-staging
git checkout staging
cp .env.example .env
nano .env  # staging credentials

cat > docker-compose.override.yml << 'EOF'
services:
  nginx:
    ports:
      - "8080:80"
    volumes:
      - ./src:/var/www/html:ro
      - ./nginx/default.dev.conf:/etc/nginx/conf.d/default.conf:ro
  certbot:
    entrypoint: "/bin/sh -c 'exit 0'"
EOF

docker compose up -d

set -a && source .env && set +a
docker compose exec mysql mysql -u root -p${DB_ROOT_PASSWORD} ashadi_staging -e "
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  qty INT NOT NULL
);"
```

---

## 7. Useful Commands

```bash
# View all running containers
docker compose ps

# Follow logs
docker compose logs -f

# Restart single service
docker compose restart nginx

# Rebuild and restart
docker compose up -d --build

# Enter MySQL shell
set -a && source .env && set +a
docker compose exec mysql mysql -u root -p${DB_ROOT_PASSWORD} ${DB_NAME}

# Check cert expiry
docker compose run --rm --entrypoint certbot certbot certificates

# Check backup log
tail -f /var/log/ashadi-backup.log
```

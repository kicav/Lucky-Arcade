# Chạy Lucky Arcade v0.9 trên cloud

## Codespaces

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

Runner khởi động web server, queue worker và scheduler. Live Experience sử dụng HTTPS polling nên không cần mở thêm cổng WebSocket.

## Production Docker

```bash
cp .env.production.example .env.production
docker compose --env-file .env.production -f docker-compose.production.yml up -d --build
docker compose --env-file .env.production -f docker-compose.production.yml exec app php artisan arcade:doctor --strict
```

Production uses PostgreSQL and Redis. Set unique secrets, HTTPS domain, administrator 2FA and external encrypted backup storage before public access.

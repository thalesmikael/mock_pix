setup: composer install
up: docker compose up -d
down: docker compose down -v
migrate: docker compose exec app php scripts/migrate.php
worker: docker compose exec app php scripts/worker.php
report: docker compose exec app php scripts/report_daily.php
logs: docker compose logs -f
shell: docker compose exec app bash
test: composer test

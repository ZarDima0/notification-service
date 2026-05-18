.PHONY: clear migrate
clear:
	docker compose exec app php artisan optimize:clear
	docker compose restart worker

migrate:
	docker compose exec app php artisan migrate

setup:
	cp backend/.env.example backend/.env
	docker compose up -d
	sleep 5
	docker compose exec app php artisan key:generate
	docker compose exec app composer install
	docker compose exec app php artisan migrate
	docker compose exec app php artisan optimize:clear
	@echo "✅ Готово! Сервис доступен на http://localhost:8000"
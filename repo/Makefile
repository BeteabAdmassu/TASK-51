SHELL := /bin/sh

.PHONY: setup dev down test backend-test frontend-test

setup:
	docker compose up --build -d

dev:
	docker compose up

down:
	docker compose down

test: backend-test frontend-test

backend-test:
	docker compose run --rm backend sh -c "composer install --no-interaction --prefer-dist && php artisan test"

frontend-test:
	docker compose run --rm frontend sh -c "npm install && npm run test"

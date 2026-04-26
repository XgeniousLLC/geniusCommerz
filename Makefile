.PHONY: install dev test lint lint-fix analyse format typecheck fresh

install:
	composer install
	npm install
	cp -n .env.example .env || true
	php artisan key:generate
	php artisan migrate

dev:
	composer dev

test:
	composer test

lint:
	composer lint
	npm run format:check

lint-fix:
	composer lint:fix
	npm run format

analyse:
	composer analyse

typecheck:
	npm run typecheck

format:
	npm run format

fresh:
	php artisan migrate:fresh --seed

clear:
	php artisan config:clear
	php artisan cache:clear
	php artisan view:clear
	php artisan route:clear

# Commands

```bash
# Database
php artisan migrate
php artisan db:seed                               # all seeders
php artisan db:seed --class=AdminSeeder           # admin@example.com / password
php artisan db:seed --class=IntegrationSeeder     # re-sync group/label onto existing rows (rows are created lazily on first save)

# Cache
php artisan view:clear && php artisan cache:clear

# Frontend
npm run dev       # Vite watch
npm run build     # production

# Tests
./vendor/bin/pest
```

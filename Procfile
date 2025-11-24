web: vendor/bin/heroku-php-apache2 public/
# Descomenta la siguiente línea si utilizas colas en producción
worker: php artisan queue:work --sleep=3 --tries=3

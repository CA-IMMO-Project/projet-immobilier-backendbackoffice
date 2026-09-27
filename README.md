composer install

cp .env.example .env

Configurer ensuite les informations PostgreSQL dans .env :

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nom_de_la_base
DB_USERNAME=nom_utilisateur
DB_PASSWORD=mot_de_passe

php artisan key:generate

php artisan migrate --seed

php artisan serve

Les api sera accessible à :

http://127.0.0.1:8000/api
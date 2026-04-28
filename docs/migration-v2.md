# Migrating from v2 to v3

Ensure that both MariaDB and PostgreSQL are installed and running on your server.

## Configure MariaDB Connection

Add these to your `.env`:

```dotenv
MARIADB_HOST=
MARIADB_DATABASE=
MARIADB_USERNAME=
MARIADB_PASSWORD=
```

## Migration Steps

Run these commands in order. This sets up all migrations in PostgreSQL and migrates data from MariaDB.

```bash
php artisan db:wipe
php artisan migrate
php artisan game:add-version --default <SC_UNPACKED_DATA_VERSION>
php artisan data:migrate --all
php artisan db:seed
php artisan game:sync
php artisan starmap:sync

# Let the job runner finish processing all jobs
php artisan queue:work

# After ALL jobs have finished
php artisan data:migrate-translations
```

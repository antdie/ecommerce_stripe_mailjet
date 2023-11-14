# Instructions


## Start dev
```bash
# install php dependencies
composer install

# install js dependencies
yarn install

# start php local server
php -S 127.0.0.1:8000 -t public/

# start docker database & mailcatcher in background
docker-compose up -d

# start webpack encore bundler
yarn watch

# create database from migrations
php bin/console doctrine:migrations:migrate

# load fake data from fixtures
php bin/console doctrine:fixtures:load
```


## Usage

Fixtures users (name / pass / role)
```
sa@example.com / sa / ROLE_SUPER_ADMIN
a@example.com / a / ROLE_ADMIN
u@example.com / u / ROLE_USER
```

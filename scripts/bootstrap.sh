#!/bin/bash

echo "=> Checking for docker..."

docker help > /dev/null || \
    (echo "Error: Docker not found. Please manually install it first" && \
     exit 1)

# ------------------------------------------------------------------------------------------------

echo "=> Checking for docker-compose..."

docker-compose help > /dev/null || \
    (echo "Error: Docker-compose not found. Please manually install it first" && \
     exit 1)

# ------------------------------------------------------------------------------------------------

#echo "=> Checking for running containers..."
#
#SERVICE_NAME="laravel.test"
#CONTAINER_NAME=$(docker-compose ps -q "$SERVICE_NAME")
#
#if [ "$(docker container inspect -f '{{.State.Status}}' "$CONTAINER_NAME")" == "running" ]; then
#    echo "Running containers found. Stoppping and removing..."
#    docker-compose down
#fi

# ------------------------------------------------------------------------------------------------

echo "=> Generating SSL certificate for local dev..."

./scripts/generate-cert.sh || exit 1

# ------------------------------------------------------------------------------------------------

echo "=> Orchestrating dev environment..."

docker-compose up -d

# ------------------------------------------------------------------------------------------------

echo "=> Downloading composer dependencies..."

docker-compose exec php-fpm composer install

# ------------------------------------------------------------------------------------------------

echo "=> Generating app key..."

docker-compose exec php-fpm php artisan key:generate

# ------------------------------------------------------------------------------------------------

echo "=> Preparing database..."

docker-compose exec php-fpm php artisan migrate --seed

# ------------------------------------------------------------------------------------------------

echo "=> Downloading NPM dependencies..."

docker-compose exec php-fpm npm install

# ------------------------------------------------------------------------------------------------

echo "=> Building front-end assets..."

docker-compose exec php-fpm npm run dev

# ------------------------------------------------------------------------------------------------

echo "=================================="
echo " "
echo "Done! Ready for development"
echo "Visit http://localhost"
echo " "
echo "=================================="

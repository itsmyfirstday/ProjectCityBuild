#!/bin/bash

service_name=php-fpm

# Boot temporary new container if not currently running
if [ -z "$(docker-compose ps -q $service_name)" ] || [ -z "$(docker ps -q --no-trunc | grep "$(docker-compose ps -q $service_name)")" ]; then
    echo "Container not running. Booting temporary container..."
    docker-compose run \
        --rm \
        --no-deps \
        php-fpm php ./vendor/bin/phpunit
else
    echo "Running test in existing container..."
    docker-compose exec php-fpm php ./vendor/bin/phpunit
fi

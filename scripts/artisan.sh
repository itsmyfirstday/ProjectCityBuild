#!/bin/bash

if [ -z `docker ps -q --no-trunc | grep $(docker-compose ps -q php-fpm)` ]; then
  docker-compose up -d
fi

if [ -z $1 ]; then
    docker-compose exec php-fpm php artisan
    exit 0
fi

docker-compose exec php-fpm php artisan $1

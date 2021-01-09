#!/bin/bash

if [ -z `docker ps -q --no-trunc | grep $(docker-compose ps -q php-fpm)` ]; then
  docker-compose up -d
fi

docker-compose exec php-fpm npm run watch

FROM php:7.4-fpm-alpine

ARG ENV

WORKDIR /var/www/html

RUN echo "UTC" > /etc/timezone

RUN apk add --no-cache --update \
    zip \
    unzip \
    libzip-dev \
    curl \
    sqlite \
    supervisor \
    nodejs \
    npm \
    mysql-client

RUN docker-php-ext-configure zip

RUN docker-php-ext-install \
    mysqli \
    pdo_mysql \
    zip

RUN apk add shadow && \
    usermod -u 1000 www-data && \
    groupmod -g 1000 www-data

COPY ./docker/php-fpm/php.ini /etc/php7.4/conf.d/php.ini

RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN rm -rf composer-setup.php

COPY ./docker/php-fpm/supervisord.conf /etc/supervisord.conf

RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root

COPY --chown=www-data:www-data . .

# We don't need to bake-in dependencies since they'll be overwritten
# by a mounted volume in a dev environment
RUN if [ "$ENV" = "prod" ] ; then \
        rm -rf vendor && composer install --no-dev ; \
        npm install ; \
        npm run prod ; \
    fi

RUN chmod 664 -R . && \
    chmod 775 -R ./storage && \
    chmod 775 -R ./scripts

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

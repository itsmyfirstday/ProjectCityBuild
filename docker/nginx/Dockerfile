FROM nginx:alpine

WORKDIR /etc/nginx

COPY ./docker/nginx/conf.d/app.conf ./conf.d/default.conf

COPY ./public /var/www/html/public

EXPOSE 80
EXPOSE 443

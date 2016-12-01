FROM php:7-apache

MAINTAINER Jozef Liška <jozoliska@gmail.com>

RUN docker-php-ext-install pdo_mysql

COPY . /var/www/html/cc-filters

EXPOSE 8000

CMD ["php", "/var/www/html/cc-filters/bin/console", "server:run"]
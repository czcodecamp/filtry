FROM php:7-apache

MAINTAINER Jozef Liška <jozoliska@gmail.com>

RUN docker-php-ext-install pdo_mysql

COPY . /var/www/html/cc-filters

COPY ./docker_init/vhost.conf /etc/apache2/sites-enabled/000-default.conf

RUN /var/www/html/cc-filters/docker_init/init.sh

EXPOSE 80
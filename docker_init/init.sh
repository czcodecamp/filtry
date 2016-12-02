#!/bin/sh

echo Setting up var directory

rm -rf /var/www/html/cc-filters/var/cache
rm -rf /var/www/html/cc-filters/var/logs
rm -rf /var/www/html/cc-filters/var/sessions
chown -R www-data:www-data /var/www/html/cc-filters/var
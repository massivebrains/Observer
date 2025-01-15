FROM 058372639833.dkr.ecr.us-east-1.amazonaws.com/soci-images/php:8.3-fpm-alpine3.19
ENV APP_ENV prod

ARG NEW_RELIC_LICENSE_KEY

RUN set -ex && \
    docker-php-ext-enable pcov amqp redis && \
	docker-php-ext-enable sockets && \
    echo "newrelic.license = \"$NEW_RELIC_LICENSE_KEY\"" >> $PHP_INI_DIR/conf.d/90-override.ini && \
    echo "newrelic.appname = \"status-monitoring\"" >> $PHP_INI_DIR/conf.d/90-override.ini

# Copy the current application files
COPY ./src /application

# Modify PHP-FPM configuration
# defaults:
# pm.max_children = 5
# pm.start_servers = 2
# pm.min_spare_servers = 1
# pm.max_spare_servers = 3
#
# relevant info:
# idle php-fpm process
# php.ini
# container resource

RUN sed -i '/pm\.max_children\ =/c\pm.max_children = 12' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i '/pm\.start_servers\ =/c\pm.start_servers = 6' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i '/pm\.min_spare_servers\ =/c\pm.min_spare_servers = 3' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i '/pm\.max_spare_servers\ =/c\pm.max_spare_servers = 6' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;pm\.status_path\ =\ \/status/pm.status_path = \/status/' /usr/local/etc/php-fpm.d/www.conf

# Install the necessary Composer packages
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R :www-data /application && chmod -R 775 /application/storage /application/bootstrap/cache

ENTRYPOINT /usr/local/sbin/php-fpm

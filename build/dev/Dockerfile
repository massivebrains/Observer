FROM 058372639833.dkr.ecr.us-east-1.amazonaws.com/soci-images/php:8.3-fpm-alpine3.19
ARG NEW_RELIC_LICENSE_KEY

RUN set -ex && \
    docker-php-ext-enable xdebug pcov amqp redis && \
    docker-php-ext-enable sockets && \
    echo "newrelic.license = \"$NEW_RELIC_LICENSE_KEY\"" >> $PHP_INI_DIR/conf.d/90-override.ini && \
    echo "newrelic.appname = \"status-monitoring\"" >> $PHP_INI_DIR/conf.d/90-override.ini

# Copy the current application files
COPY ./src /application

# Install the necessary Composer packages
RUN composer install --optimize-autoloader

ENTRYPOINT /usr/local/sbin/php-fpm

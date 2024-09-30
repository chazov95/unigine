FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
        mc \
        curl \
        wget \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libonig-dev \
        libzip-dev \
        libmcrypt-dev \
        && pecl install mcrypt \
        && docker-php-ext-enable mcrypt \
        && docker-php-ext-install -j$(nproc) iconv mbstring mysqli pdo_mysql zip \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install -j$(nproc) gd

RUN pecl channel-update pecl.php.net && pecl install xdebug \
    > /dev/null \
    && echo 'zend_extension=xdebug.so' > /usr/local/etc/php/conf.d/xdebug.ini

RUN echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=localhost" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=true" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

WORKDIR /var/www

CMD ["php-fpm"]

FROM php:7.4-fpm

ENV TZ Asia/Tokyo
ENV COMPOSER_ALLOW_SUPERUSER 1

# install Lib for composer
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash - && \
    apt-get update -qq && \
    apt-get install -y libicu-dev &&\
    apt-get install --no-install-recommends -y libpq-dev libonig-dev libxml2-dev nodejs git zip unzip && \
    apt-get install --no-install-recommends -y zlib1g-dev libfreetype6-dev libpng-dev libjpeg62-turbo-dev libwebp-dev libxpm-dev && \
    apt-get clean && \
    rm -rf /var/cache/apt && \
    npm install npm@latest -g

# add extention
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm && \
    docker-php-ext-install -j$(nproc) gd && \
    docker-php-ext-install mbstring pdo pdo_pgsql json &&\
    docker-php-ext-install intl 

# php.conf php-fpm.conf
COPY conf/php.ini /usr/local/etc/php/php.ini
# COPY conf/docker.conf /usr/local/etc/php-fpm.d/docker.conf

# install Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

WORKDIR /app

# CakePHP に必要な拡張機能
# apt-get install -y libicu-dev &&\
# docker-php-ext-install intl \
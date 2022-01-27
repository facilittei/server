FROM php:8.0-fpm

RUN apt-get update && apt-get install -y \
    build-essential \
    libwebp-dev \
    libpng-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    nano \
    unzip \
    git \
    curl \
    libzip-dev
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql zip exif pcntl bcmath
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd

RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install apcu && docker-php-ext-enable apcu

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
COPY ./infra/php/local.ini /usr/local/etc/php/conf.d/local.ini

RUN rm -rf vendor || true \
    && mkdir vendor

COPY src/composer.json .

RUN composer install

COPY src .

RUN cp .env.example .env \
    && php artisan key:generate \
    && php artisan test

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www
COPY --chown=www:www src /var/www/html
USER www

EXPOSE 9000
CMD ["php-fpm"]

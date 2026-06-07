FROM php:8.3-cli

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-progress --prefer-dist

COPY . .

ENV X_LISTEN=0.0.0.0:8080
EXPOSE 8080

ENTRYPOINT ["php", "public/index.php"]

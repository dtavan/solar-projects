FROM php:7.2

RUN apt-get update -y && apt-get install \
    openssl \
    zip unzip \
    zlib1g-dev \
    git \
    -y

RUN docker-php-ext-install \
    pcntl \
    pdo_mysql \
    zip

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && chmod +x composer.phar \
    && mv composer.phar /bin/composer

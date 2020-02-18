FROM php:7.3-apache

RUN a2enmod rewrite
RUN a2enmod headers

# --- Default.
RUN apt-get update && \
    apt-get install -y --no-install-recommends git

RUN apt-get install -y libpq-dev gnupg
RUN docker-php-ext-install sockets

RUN docker-php-ext-install mbstring pdo pdo_mysql \
    && pecl install xdebug

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev
RUN docker-php-ext-install zip

# --- Composer.
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# --- Node 11
RUN apt-get -y install curl gnupg && \
  curl -sL https://deb.nodesource.com/setup_11.x  | bash - && \
  apt-get -y install nodejs
RUN npm install -g yarn

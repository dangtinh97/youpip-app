FROM php:8.1-fpm
WORKDIR /var/www/html
COPY . /var/www/html
RUN echo ls
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip
# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

EXPOSE 9000

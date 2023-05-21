FROM php:8.1-fpm
WORKDIR /var/www/html
COPY . /var/www/html
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libssl-dev

#RUN apt-get install libssl-dev pkg-config
# Install the MongoDB driver
RUN pecl install mongodb && \
    docker-php-ext-enable mongodb

# Copy the PHP-FPM configuration file
#COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf

# Copy the PHP configuration file
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


EXPOSE 9000

CMD ["php-fpm"]

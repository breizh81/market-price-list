# Use an official PHP image as a base
FROM php:8.1-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    wget \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Set Xdebug configuration
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/php.ini

# Install Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Install PostgreSQL PHP driver extension
RUN docker-php-ext-install pdo_pgsql

# Set up the working directory
WORKDIR /var/www/html

# Copy the composer.json and composer.lock files
COPY composer.json composer.lock ./

# Install project dependencies
RUN composer install

# Copy the rest of the application code
COPY . .

# Ensure bin/console is executable
RUN chmod +x bin/console

# List files to verify existence and permissions
RUN ls -la /var/www/html/bin

# Expose port 8000 to the outside world
EXPOSE 8000

# Set the PHP_IDE_CONFIG environment variable
ENV PHP_IDE_CONFIG="serverName=DockerSymfonyProject"

# Command to run the Symfony server
CMD ["symfony", "server:start", "--port=8000", "--dir=public/"]

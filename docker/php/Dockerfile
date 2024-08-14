# Use an official PHP image as a base
FROM php:8.1-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    wget \
    libpq-dev \
    curl \
    supervisor \
    libssl-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs

# Verify installation
RUN node -v
RUN npm -v

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

# Install AMQP extension
RUN apt-get update && apt-get install -y \
    librabbitmq-dev \
    && pecl install amqp \
    && docker-php-ext-enable amqp

# Set up the working directory
WORKDIR /var/www/html

# Create directories and set permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

# Set environment variables (if needed)
ENV SYMFONY_ENV=dev

# Copy the rest of the application code
COPY . .

# Install project dependencies without scripts
RUN composer install --no-scripts --no-dev --prefer-dist

# Ensure bin/console is executable
RUN chmod +x bin/console

# Copy supervisord configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# List files to verify existence and permissions
RUN ls -la /var/www/html/bin

# Expose port 8000 to the outside world
EXPOSE 8000

# Set the PHP_IDE_CONFIG environment variable
ENV PHP_IDE_CONFIG="serverName=DockerSymfonyProject"

# Start supervisord
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]

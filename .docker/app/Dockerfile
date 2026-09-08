FROM dunglas/frankenphp

# Arguments defined in docker-compose.yml
ARG user=fmi-laravel
ARG uid=1000

# Install system dependencies
RUN apt-get update \
    && apt-get install -y \
        git \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    mbstring \
    pcntl \
    bcmath \
    intl \
    zip \
    gd \
    opcache

# Get latest Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /app

USER $user

ENTRYPOINT ["php", "artisan", "octane:frankenphp"]

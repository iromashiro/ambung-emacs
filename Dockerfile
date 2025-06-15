FROM dunglas/frankenphp

# change url
ENV SERVER_NAME=ambung.muaraenimkab.go.id

# install dependencies
RUN apt-get update && apt-get install -y \
    && install-php-extensions \
    bcmath \
    pdo_pgsql \
    xml \
    mbstring \
    zip \
    curl \
    pcntl \
  && rm -rf /var/lib/apt/lists/*

COPY . /app

ENTRYPOINT ["php", "artisan", "octane:frankenphp"]

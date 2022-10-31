FROM php:8.1-cli

RUN apt-get update && \
	    apt-get install -y zip

RUN docker-php-ext-install pcntl bcmath

RUN curl -sS https://getcomposer.org/installer | \
            php -- --install-dir=/usr/bin/ --filename=composer

COPY . /app

WORKDIR /app

RUN php /usr/bin/composer install --no-dev --no-interaction

CMD [ "php", "./mikro-watch",  "daemon" ]

FROM php:7.2-cli

RUN apt-get update && \
	    apt-get install -y zip

RUN docker-php-ext-install pcntl bcmath

RUN curl -sS https://getcomposer.org/installer | \
            php -- --install-dir=/usr/bin/ --filename=composer

COPY . /usr/src/myapp

WORKDIR /usr/src/myapp

RUN php /usr/bin/composer install --no-dev --no-interaction

CMD [ "php", "./mikro-watch",  "daemon" ]

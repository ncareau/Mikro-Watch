FROM php:7.2-cli

RUN docker-php-ext-install pcntl

COPY . /usr/src/myapp

WORKDIR /usr/src/myapp

CMD [ "php", "./mikro-watch",  "daemon" ]

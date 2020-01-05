FROM lavoweb/php-7.3

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
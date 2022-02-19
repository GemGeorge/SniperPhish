FROM --platform=$TARGETPLATFORM php:7.4-apache

WORKDIR /var/www/html
COPY  . .

RUN a2enmod rewrite
ENV DEBIAN_FRONTEND=noninteractive

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apt update
RUN apt install -y gawk libc-client-dev libkrb5-dev zlib1g libpng-dev zlib1g-dev && rm -r /var/lib/apt/lists/*
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && docker-php-ext-install imap
RUN docker-php-ext-install gd && docker-php-ext-enable gd

RUN chown -R www-data:www-data /var/www
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ADD start.sh /
RUN chmod +x /start.sh
CMD ["/start.sh"]

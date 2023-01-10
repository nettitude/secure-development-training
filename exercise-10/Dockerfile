FROM php:7.2-apache

COPY src/ /var/www/html/
COPY conf/resolv.conf /etc/resolv.conf

RUN apt-get update \
    && apt-get install -y \
        curl \
        libxrender1 \
	wkhtmltopdf \
        libfontconfig \
        libxtst6 \
        xz-utils \
	git \
	zip \
	unzip \
	libzip-dev \
	    && docker-php-ext-install zip

# Install Composer
COPY conf/installer /tmp/installer
RUN cat /tmp/installer | php

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R +r /var/www/html

COPY conf/startup.sh /root/startup.sh
RUN php composer.phar require mikehaertl/phpwkhtmltopdf
CMD ["sh","/root/startup.sh"]

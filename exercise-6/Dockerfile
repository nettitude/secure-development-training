FROM orsolin/docker-php-5.3-apache
ARG DEBIAN_FRONTEND=noninteractive

COPY src/ /var/www/html/
COPY conf/resolv.conf /etc/resolv.conf

# Install Mariadb
RUN echo 'deb http://archive.debian.org/debian jessie main' > /etc/apt/sources.list && apt-get update
RUN apt-get install -y dialog apt-utils --force-yes
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
RUN apt-get install -y mariadb-server --force-yes

# Configure MariaDB
COPY conf/wordpress.sql /tmp/wordpress.sql
RUN service mysql restart && cat /tmp/wordpress.sql | mysql && echo "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('wordpress');" | mysql

RUN printf "\nallow_url_include=1" >> /etc/php5/apache2/php.ini


RUN chown -R www-data:www-data /var/www/html
RUN chmod -R +r /var/www/html

COPY conf/startup.sh /root/startup.sh
CMD ["bash","/root/startup.sh"]

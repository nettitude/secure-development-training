#!/bin/bash
php composer.phar require mikehaertl/phpwkhtmltopdf
service apache2 restart
chmod 777 pdfs
bash -i
tail -F /var/log/apache2/access.log

#!/bin/bash
service apache2 restart > /dev/null
chmod 777 pdfs

echo "  ______                   _            __  ___  "
echo " |  ____|                 (_)          /_ |/ _ \ "
echo " | |__  __  _____ _ __ ___ _ ___  ___   | | | | |"
echo " |  __| \ \/ / _ \ '__/ __| / __|/ _ \  | | | | |"
echo " | |____ >  <  __/ | | (__| \__ \  __/  | | |_| |"
echo " |______/_/\_\___|_|  \___|_|___/\___|  |_|\___/ "
echo "                                                 "
echo "Press Ctrl+C to exit."
echo ""

tail -F /var/log/apache2/access.log

#!/usr/bin/env bash
service apache2 restart
service mysql restart
echo "  ______                   _              __  "
echo " |  ____|                 (_)            / /  "
echo " | |__  __  _____ _ __ ___ _ ___  ___   / /_  "
echo " |  __| \ \/ / _ \ '__/ __| / __|/ _ \ | '_ \ "
echo " | |____ >  <  __/ | | (__| \__ \  __/ | (_) |"
echo " |______/_/\_\___|_|  \___|_|___/\___|  \___/ "
echo ""
echo "Press Ctrl+C to exit."
echo ""
( trap exit SIGINT ; read -r -d '' _ </dev/tty )

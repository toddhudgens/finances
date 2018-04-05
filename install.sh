#!/bin/bash

printf "\n"
printf "My Financials Web App Installer v0.1\n\n"
printf "1. Creating database schema\n"
read -p "MySQL Root Username: " MYSQL_UN

rm ~/.mylogin.cnf > /dev/null 2>&1
mysql_config_editor set --login-path=local --host=localhost --user=$MYSQL_UN --password
mysql --login-path=local -e "CREATE database my_financials;" > /dev/null 2>&1
mysql --login-path=local -e "SOURCE /var/www/sql/schema.sql;"

MYSQLUSERPW="$(cat /dev/urandom | tr -cd 'a-f0-9' | head -c 32)"
printf "[DONE]\n\n"

printf "2. Creating a MySQL application user\n"
mysql --login-path=local -e "CREATE USER myfinancials@'localhost' IDENTIFIED BY '$MYSQLUSERPW';" > /dev/null 2>&1
mysql --login-path=local -e "GRANT ALL PRIVILEGES ON my_financials.* TO myfinancials@'localhost' IDENTIFIED BY '$MYSQLUSERPW';"
mysql --login-path=local -e "FLUSH PRIVILEGES;"
printf "[DONE]\n\n"

printf "3. Writing .htaccess file\n"
sed -i "/SetEnv DBPW/c\SetEnv DBPW \"$MYSQLUSERPW\"" www/.htaccess
printf "[DONE]\n\n"

printf "4. Running composer\n"
cd www
composer install
cd ../

printf "5. Create your login user\n"
read -p "Username: " username
while true; do
    read -s -p "Password: " password
    echo
    read -s -p "Password (again): " password2
    echo
    [ "$password" = "$password2" ] && break
    echo "Please try again"
done
mysql --login-path=local -e "DELETE FROM my_financials.users"
mysql --login-path=local -e "INSERT INTO my_financials.users VALUES(0,\"$username\",md5(\"$password&CASH_RULES_EVERYTHING_AROUND_ME_CREAM\"));"

printf "\n6. Create an AlphaVanta API key (optional)\n"
printf "  - If you want to use the stock / mutual fund features, you'll need an API key to get pricing data\n"
printf "  - Sign up for an API key at https://www.alphavantage.co/support/\n\n"
read -p "API KEY: [hit enter to skip]" alphavantageapikey
sed -i "/SetEnv ALPHA_VANTAGE_API_KEY/c\SetEnv ALPHA_VANTAGE_API_KEY \"$alphavantageapikey\"" www/.htaccess
printf "[DONE]\n\n"


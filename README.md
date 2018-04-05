# my-financials
A PHP/Apache/Mysql webapp used to manage personal finances

## Debian Linux Install process

1. Install the dependencies
  * sudo apt-get install apache2 libapache2-mod-php7.1 php7.1-curl php7.1-mysql composer mysql-client-core-5.7 mysql-server-5.7

2. Enable mod_rewrite
  * sudo a2enmod rewrite
  * sudo systemctl restart apache2

3. Download the project from Github
  * git clone https://github.com/toddhugens/my-financials
  *  cd my-financials

4. Run the install.sh script
  * chmod +x install.sh
  * ./install.sh

5. In your apache config, point the DocumentRoot directive to the www/ folder in the project, 

6. Now you should be able to browse back to the starting page, and you should see a login screen. 

7. Login with the username and password you just created, and create your first account.

8. You're now ready to start tracking your finances! 

# my-financials
A PHP/Apache/Mysql webapp used to manage personal finances

## Debian Linux Install process

1. Install the dependencies
  > sudo apt-get install apache2 libapache2-mod-php7.1 php7.1-curl php7.1-mysql composer mysql-client-core-5.7 mysql-server-5.7

2. Enable mod_rewrite
  > sudo a2enmod rewrite
  
  > sudo systemctl restart apache2

3. If you have a Github account, clone the project
  > git clone https://github.com/toddhugens/my-financials
  > cd my-financials

4. If you don't have a Github account, download the project
  > wget https://github.com/toddhudgens/my-financials/archive/master.zip
  > unzip master.zip
  > mv my-finacials-master my-financials
  > cd my-financials
  
5. Run the install.sh script
  > chmod +x install.sh
  > ./install.sh

6. In your apache config, point the DocumentRoot directive to the www/ folder in the project. Restart apache and browse to the site you just configured. It should be working!

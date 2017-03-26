# finances
PHP/Mysql webapp used to manage personal finances

## Install process

1. Place the checked-out code into the desired webroot folder. This could be /var/www or /var/www/htdoc, or something else if you are using a vhost setup. I like to setup individual vhosts for each site on my webserver and place them all under /sites/.

2. Browse to the default page (http://yourdomain.com/). You should see a welcome screen.

3. Follow the install process to setup the database tables and database users. 

4. Continue through the install process, and setup your user account.

5. Once complete, delete install.php, and set the file mode on the config file to 400:  $ chmod 400 conf/config.php

6. Now you should be able to browse back to the starting page, and you should see a login screen. 

7. Login with the username and password you just created, and create your first account.

8. You're now ready to start tracking your finances! 
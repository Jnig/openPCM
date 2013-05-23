README
======

What is openPCM?
-----------------

openPCM is a web based management tool for virtualization clusters. It uses KVM as hypervisor and integrates well with other open source tools like pacemaker and DRBD.


Requirements
------------

Webserver with:
* PHP 5.3.3+
* Apache with mod_rewrite
* MySQL
* libssh2-php
* php5-ldap

Installation
------------


1. Extract openPCM to /var/www and let your Apache DocumentRoot point to /var/www/web/

2. Copy /var/www/app/config/parameters.yml.dist to parameters.yml and edit the database settings

3. Install libaries:

        cd /var/www && composer.phar update

4. Setup database

        php /var/www/app/console doctrine:schema:update --force

5. Create an admin user

        php /var/www/app/console fos:user:create admin123 --super-admin

5. Create SSH key

        cd /var/www/app/config/ssh && ssh-keygen -N "" -f id_rsa

6. Login with your admin user http://localhost/



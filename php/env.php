<?php
// Define your environment variables

//DATA BASE
define('DB_HOST', '127.0.0.1');
define('DB_USER', '[dbuser]');
define('DB_PASS', '[dbpassword]');
define('DB_NAME', 'main_db'); // leave this value as default if you use the documentation to create the database

//HOST
define('PATH_APACHE_CONF', '[/..path../example.conf]');//only compatible for apache server
define('DOMAIN', '[your domain]'); //not necessary if you set the path to your apache configuration file

//EMAIL
define('SMTP_SERVER', '[smtp.example.com]');
define('SMTP_USERNAME', '[example@domain.com]');
define('SMTP_PASSWORD', '[smt_password]');
define('SMTP_PORT', '[number]');

?>

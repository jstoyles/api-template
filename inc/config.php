<?php
date_default_timezone_set('America/New_York');

define("HTTP_OR_HTTPS", isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off'?'https':'http');
define("BASE_URL", HTTP_OR_HTTPS . '://' . $_SERVER['SERVER_NAME']);

define("VERSION", "v1");
define("DEBUG", "on");

/*
By default this API assumes MySQL or MariaDB will be used as a backend database.
PDO is used for database access, so the database functions in functions/database.php
can be adjusted to support other databases like SQLLite or MSSQL
*/

define("DB_TYPE","mysql"); //for mariadb still use mysql
define("DB_HOST", '<DB Address>'); //If the code and database are hosted on the same server, just use localhost
define("DB_USER", "<DB Username>");
define("DB_PASSWORD", "<DB Passworsd>");
define("DB_NAME", "<DB Name>");

define("PUBLIC_KEY", "<Public API Key>");
define("PRIVATE_KEY", "<Private API Key>");

define("VERSION", "v1");
?>

# How to set up the website

## Frameworks & Dependency Manager
The MLVA website is based on the PHP framework [CodeIgniter 3](https://codeigniter.com/), using [Twig](https://twig.symfony.com/) as a template engine and [Vue](https://vuejs.org/) for some dynamic pages.

It uses [Composer](https://getcomposer.org/) as a package manager.

## Server Requirement

In a nutshell:
- Apache2 (or Nginx)
- MySQL => 5.6
- PHP >= 5.4
- GIT
- composer (optionnal)
- sendmail or similar (the mail() function for PHP)

PHP Extensions:
- MCrypt
- Curl
- php5-json
- mb-string
- php-cli

It also need multiple checking on server side:
- UrlRewriting enabled
- Increased the maximum execution time for php scripts in `php.ini`: `max_execution_time = 300` (5mn to allow heavy uploads)
- Check in `php.ini` that you're able to receive big forms. memory_limit: 128M -> 256M and post_max_size:  8M -> 20M

## Database
You need to have a working SQL database and link it to the server using CodeIgniter's configuration files. You also need to initialize the database with the file `database/mlva_starter.sql`.

It will create all the tables and an admin you can connect with on the website (login: admin, password: test), remember to change the password.

## Configuration Files
The configuration files must be placed in the folder `codeigniter/application/config/production/`.
You can simply copy `config.php` and `database.php` from `config/testing/` and edit the following lines:

1. In `config.php`, line 20
```php
$config['base_url'] = 'http://mlva.dev'; // Put the actual web url
```

2. In `database.php`, line 76
```php
// Connect your database properly
$db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'root',
	'password' => '',
	'database' => 'mlva',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
```

## Virtual Host
For the website to work properly, you need to create a virtual host to the folder `codeigniter/public`.
For example in Apache:
```
<VirtualHost *:80>
    ServerAdmin mlva@ensta.fr
    DocumentRoot "var/www/mlva/codeigniter/public"
    ServerName mlva.dev
    ServerAlias www.mlva.dev
</VirtualHost>

<Directory var/www/mlva/codeigniter/public>
	Order Deny,Allow
	Allow from all
</Directory>
ou pour une version d'apache plus récente:
<Directory var/www/mlva/codeigniter/public>
	AllowOverride All
	Options +Indexes +FollowSymLinks +MultiViews
			Require all granted
</Directory>
```

# Instructions pour l'installation du site

Le site MLVAbank est basé sur le framework PHP CodeIgniter 3, avec le moteur de template Twig.
Il utilise le gestionnaire de paquets Composer.

## Fichiers de configuration
Les fichiers de configuration doivent être placés dans le dossier `codeigniter/application/config/production/`.
Il suffit de copier les fichiers `config.php` et `database.php` situé dans le dossier `config/testing/` et de modifier les lignes suivantes:

1. Dans le fichier `config.php`, ligne 20
```php
$config['base_url'] = 'http://mlva.dev';// Remplacer par l'url du site
```

2. Dans le fichier `database.php`, lignes 76 et suivantes

```php
//Remplacer par le nom de la base de données et les identifiants associés
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

## Virtual host
Pour que le site fonctionne, il faut créer un virtual host qui pointe vers le dossier codeigniter/public.

Exemple de virtual host:
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
```

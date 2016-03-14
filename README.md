# Instructions pour l'installation du site

Le site MLVAbank est basé sur le framework PHP CodeIgniter 3, avec le moteur de template Twig.
Il utilise le gestionnaire de paquets Composer.

## Exigences pour le serveur

En résumé:
- Apache2 (ou Nginx)
- Mysql
- PHP >= 5.4
- GIT
- composer (optionnel)

Extensions PHP:
- MCrypt
- Curl
- php5-json
- mb-string
- php-cli

Il faut également faire plusieurs vérifications côté serveur:
- UrlRewriting activé
- Avoir modifié la durée maximale d'exécution d'un script php, dans le `php.ini` : `max_execution_time = 300` (5 minutes à cause des gros uploads)

## Base de données
Le fichier à importer dans la base de données se nomme `mlva_starter.sql`, il contient la structure et un utilisateur admin pour pouvoir se connecter. (Login: admin, Mot de passe: test)

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

Exemple de virtual host (pour apache):
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

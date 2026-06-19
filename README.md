# Backcountry Pathfinders — community

Site communautaire de partage de **lieux de poser backcountry pour MSFS 2024** :
carte interactive, fiches de lieux (profil de relief, nature du sol, friction),
contributions et commentaires. Les relevés sont produits par une application
desktop compagnon qui les envoie via une API.

## Pile technique

- **PHP 8.2** (via XAMPP) + **MySQL/MariaDB**
- **Composer** pour l'autoloading (PSR-4) et les dépendances
- **bramus/router** comme micro-routeur
- **PDO** pour l'accès base (pas d'ORM)
- **Leaflet** pour la carte (front)

## Structure

```
public/         Racine web exposée par Apache (SEUL dossier public)
  index.php     Front controller (point d'entrée unique)
  .htaccess     Réécriture d'URL → index.php
  assets/       CSS / JS / images
src/            Code applicatif (hors racine web)
  Core/         Briques internes : Router config, Database (PDO), View
  Controllers/  Pages du site web
  Api/          Endpoints JSON reçus par l'appli desktop
  Models/       Accès aux données
  Views/        Gabarits HTML
config/         config.php (local, non versionné) + config.example.php
database/        schema.sql + migrations/
storage/        uploads (captures), cache, logs
```

## Installation locale (XAMPP)

1. **Composer** : installer depuis https://getcomposer.org puis, à la racine du projet :
   ```
   composer install
   ```
2. **Jonction Apache** : `C:\xampp\htdocs\backcountry` doit pointer vers le
   dossier **`public/`** du projet :
   ```
   rmdir "C:\xampp\htdocs\backcountry"
   mklink /J "C:\xampp\htdocs\backcountry" "...\backcountry-pathfinders\public"
   ```
3. **Base de données** : créer la base `backcountry` (phpMyAdmin), puis importer
   `database/schema.sql` (à venir).
4. **Config** : copier `config/config.example.php` en `config/config.php` et
   renseigner les identifiants MySQL.
5. Ouvrir **http://localhost/backcountry/**

## Licence

GPL-3.0-or-later — © Cyril Milani

# Atlas du Graphisme — Déploiement

Guide complet pour passer de la démo locale à la production OVH avec GitHub.

---

## Étape 1 — Repo GitHub

### Créer le repo

1. Sur GitHub : **New repository** → `atlas-graphisme` (privé recommandé)
2. Ne pas initialiser avec README (on push le projet existant)

### Init Git local

```powershell
cd "c:\Users\prspx\Desktop\Test AI"
git init
git remote add origin https://github.com/TON_USERNAME/atlas-graphisme.git
```

### Créer `.gitignore`

```
database/
database/atlas.db
scraper/config.local.php
```

> `database/atlas.db` ne va pas en repo — chaque environnement a sa propre DB.
> Les credentials OVH ne vont jamais en repo.

### Premier push

```powershell
git add .
git commit -m "feat: Atlas du Graphisme v1 — 22 styles, Three.js, PHP API"
git branch -M main
git push -u origin main
```

---

## Étape 2 — MySQL OVH

### Créer la base

Dans l'espace client OVH → **Web Cloud → Bases de données** :
1. Créer une DB MySQL 8.x (ou MariaDB 10.x)
2. Créer un utilisateur avec tous les droits sur cette DB
3. Noter : `host`, `dbname`, `user`, `password`

### Importer le schéma

Via phpMyAdmin OVH (ou MySQL CLI si SSH disponible) :

```sql
-- Importer schema.sql (via phpMyAdmin → Importer)
```

### Configurer `scraper/config.php`

Sur le serveur OVH, définir la variable d'environnement dans `.htaccess` :

```apache
# .htaccess à la racine du projet
SetEnv APP_ENV prod
```

Puis mettre à jour les constantes dans `scraper/config.php` :

```php
define('DB_HOST', 'votre-host.mysql.db');   // fourni par OVH
define('DB_NAME', 'votre_db_name');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_password');
```

> ⚠️ Ne jamais commiter ces valeurs. Créer un `config.local.php` ignoré par git si nécessaire.

### Lancer les scrapers sur OVH

Via SSH (si activé sur l'hébergement) ou via URL web :

```
https://votre-domaine.com/scraper/init_sqlite.php   # → init MySQL
https://votre-domaine.com/scraper/fetch_wikidata.php
https://votre-domaine.com/scraper/fetch_wikipedia.php
```

> ⚠️ Protéger le dossier `scraper/` en prod avec un `.htaccess` :
> ```apache
> # scraper/.htaccess
> Require ip 1.2.3.4   # votre IP uniquement
> # ou
> Require all denied
> ```
> Une fois les scrapers lancés, bloquer l'accès public.

---

## Étape 3 — Adapter `init_sqlite.php` pour MySQL

Le script `init_sqlite.php` utilise des syntaxes SQLite. Pour MySQL, utiliser `schema.sql` directement (déjà écrit en MySQL natif).

| Différence | SQLite | MySQL |
|---|---|---|
| Auto-increment | `INTEGER PRIMARY KEY AUTOINCREMENT` | `INT UNSIGNED AUTO_INCREMENT` |
| Date courante | `datetime('now')` | `NOW()` |
| WAL mode | `PRAGMA journal_mode=WAL` | N/A |
| Import | `init_sqlite.php` | `schema.sql` via phpMyAdmin |

---

## Étape 4 — Structure OVH

L'hébergement OVH mutalisé expose un seul dossier racine (ex: `www/` ou `public_html/`). Placer le projet ainsi :

```
www/                        ← racine publique OVH
├── index.html
├── api/
│   └── courants.php
├── scraper/
│   ├── .htaccess           ← BLOQUER l'accès public
│   ├── config.php
│   ├── fetch_wikidata.php
│   └── fetch_wikipedia.php
└── .htaccess               ← SetEnv APP_ENV prod
```

> `database/` n'existe pas en prod — on utilise MySQL directement.

---

## Checklist déploiement

```
[ ] Repo GitHub créé et premier push effectué
[ ] DB MySQL créée sur OVH (host, name, user, pass notés)
[ ] schema.sql importé dans MySQL via phpMyAdmin
[ ] config.php mis à jour avec credentials OVH
[ ] SetEnv APP_ENV prod dans .htaccess racine
[ ] scraper/ protégé par .htaccess (accès IP uniquement)
[ ] fetch_wikidata.php exécuté → données Wikidata en MySQL
[ ] fetch_wikipedia.php exécuté → données Wikipedia en MySQL
[ ] api/courants.php testé → JSON valide (22 courants)
[ ] index.html testé en prod → scène 3D chargée
[ ] scraper/ bloqué définitivement (Require all denied)
```

---

## Variables d'environnement à ne jamais commiter

| Variable | Où | Valeur exemple |
|---|---|---|
| `DB_HOST` | `config.php` | `mysql-xxx.db.ovh.net` |
| `DB_NAME` | `config.php` | `atlasgraphisme` |
| `DB_USER` | `config.php` | `atlasgraphisme` |
| `DB_PASS` | `config.php` | `xxxxxxxx` |

**Pattern recommandé :** créer un `scraper/config.prod.php` (dans `.gitignore`) et l'inclure conditionnellement, ou utiliser `SetEnv` + `getenv()` pour ne jamais mettre de credentials dans le code versionné.

---

## Mises à jour de données futures

Pour re-scraper sans perdre les données manuelles :

```powershell
# Wikidata uniquement (ne touche pas nom, positions, couleurs)
php scraper/fetch_wikidata.php

# Wikipedia uniquement
php scraper/fetch_wikipedia.php

# Reset complet (ATTENTION : perd les données manuelles)
php scraper/init_sqlite.php   # local SQLite seulement
```

En MySQL prod, le reset complet = réimporter `schema.sql` + relancer les scrapers.

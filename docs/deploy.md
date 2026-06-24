# Atlas du Graphisme — Déploiement

**Prod :** https://www.nothuman.be/atlas/
**Hébergeur :** OVH (FTP manuel)
**Workflow :** éditer les sources → `./sync.ps1` → FTP upload `deploy/`

---

## Workflow quotidien

### 1. Modifier les sources (jamais dans `deploy/` directement)

| Fichier source | Correspond à |
|---|---|
| `index.html` | Front-end principal |
| `style.css` | Styles |
| `api/courants.php` | API REST |
| `scraper/*.php` | Scrapers (sauf `init_sqlite.php`) |
| `deploy/admin.php` | Admin — exception : éditer directement dans `deploy/` |

### 2. Synchroniser vers `deploy/`

```powershell
./sync.ps1
```

Copie automatiquement : `index.html`, `style.css`, `.htaccess`, `api/courants.php`, `scraper/fetch_*.php`.

> Ne touche **jamais** `deploy/scraper/config.php` (credentials MySQL prod) ni `deploy/admin.php`.

### 3. Uploader via FTP

Uploader le contenu de `deploy/` dans `/atlas/` sur OVH.

### 4. Vérifier

```
https://www.nothuman.be/atlas/
https://www.nothuman.be/atlas/api/courants.php?nocache=1
```

---

## Gestion des images (admin)

- URL : https://www.nothuman.be/atlas/admin.php
- Mot de passe : `AtlasAdmin2024`
- **5 slots** par mouvement (`image_1` à `image_5`) — saisie manuelle uniquement
- Filtre par mouvement : dropdown en haut de page
- URLs `commons.wikimedia.org/wiki/File:...` résolues automatiquement
- Premier save : crée les colonnes `image_3`/`image_4`/`image_5` automatiquement (ALTER TABLE silencieux)

> ⚠️ L'admin n'existe qu'en prod (écrit en MySQL).

---

## Scrapers texte

Ordre : **Wikidata d'abord**, puis Wikipedia.

```
https://www.nothuman.be/atlas/scraper/fetch_wikidata.php?token=AtlasRun2024
https://www.nothuman.be/atlas/scraper/fetch_wikipedia.php?token=AtlasRun2024
```

Scraper Cooper Hewitt (images `image_3`/`image_4`) :
```
https://www.nothuman.be/atlas/scraper/fetch_cooperhewitt.php?token=AtlasRun2024
```

Après scraping, vider le cache :
```
https://www.nothuman.be/atlas/api/courants.php?nocache=1
```

---

## Modifier le schéma DB

1. Modifier `schema.sql` (tous les INSERT sont `INSERT IGNORE` — safe à re-importer)
2. Appliquer sur OVH via phpMyAdmin → base `nothumanatlas` → Importer
3. Mettre à jour `scraper/init_sqlite.php` pour cohérence locale
4. Relancer `php scraper/init_sqlite.php` en local

---

## Config prod (OVH)

| Paramètre | Valeur |
|---|---|
| Host MySQL | `nothumanatlas.mysql.db` |
| Base / User | `nothumanatlas` |
| Pass | `GraphAtlas1234` |
| Token scrapers | `AtlasRun2024` |
| Mot de passe admin | `AtlasAdmin2024` |

---

## Config locale

```powershell
cd "c:\Users\prspx\Desktop\Test AI"
php -S localhost:8080                    # serveur dev
php scraper/init_sqlite.php             # (re)créer la DB SQLite
php scraper/fetch_wikidata.php          # scraper textes
php scraper/fetch_wikipedia.php
```

---

## Règles absolues

| Règle | Détail |
|---|---|
| **Jamais `datetime('now')` en SQL** | Utiliser `db_now()` → `:now => db_now()` |
| **Jamais éditer `deploy/` directement** | Sauf `deploy/admin.php` |
| **Jamais uploader `init_sqlite.php`** | SQLite local uniquement |
| **Cache API = 5 min** | Bypasser avec `?nocache=1` après scraping |

---

## Fichiers à ne pas uploader sur OVH

```
scraper/init_sqlite.php       ← SQLite local only
database/atlas.db             ← SQLite local only
scraper/config.example.php
scraper/config.php            ← utiliser deploy/scraper/config.php
test_*.php                    ← fichiers de debug temporaires
```

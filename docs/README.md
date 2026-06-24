# Atlas du Graphisme — Documentation

> Plateforme éducative immersive en WebGL — 37 courants graphiques (1880–2020) en constellation 3D interactive.
> **Prod :** https://www.nothuman.be/atlas/

---

## Décisions techniques arrêtées

| Sujet | Décision |
|---|---|
| **Fond** | Blanc (`#ffffff`) par défaut — toggle dark `◐` haut droite, `data-theme="dark"` sur `<html>`, mémorisé en `localStorage` |
| **Images** | Gestion **manuelle uniquement** via admin.php — pas de scraper image |
| **Textes** | Scraper Wikipedia EN pour `description_longue` uniquement |
| **Artistes / Key points** | Colonnes `artistes` (JSON) + `key_points` (TEXT) en DB — saisie manuelle ou scraper dédié |
| **Deploy** | Script PowerShell `sync.ps1` à la racine — plus de copie manuelle |
| **SQL datetime** | Toujours `db_now()` (PHP) — jamais `datetime('now')` ni `NOW()` en SQL |
| **Hit-testing cartes** | `PlaneGeometry` THREE.js invisibles coplanaires aux cartes CSS3D — raycaster WebGL fiable à tout angle |
| **Positions constellation** | Formule `z = 8 - (debut - 1880) × 0.2` appliquée à tous les nœuds y compris niveau 2 — multiplicateur X/Y ×2.4, Z ×2.1 |
| **Thème light** | Nœuds `0x444444`, tubes `0x555555` — contraste renforcé pour écrans mal calibrés |
| **Thème dark** | Tubes `0x1a1a1a`, opacité ×0.45 — suppression du halo/bloom causé par la BokehPass |

---

## To-Do — Consolidation (priorité par ordre)

### Étape 1 — Données : migrer artistes + key_points en DB ✅ DONE

- [x] Ajouter colonnes `artistes` (TEXT/JSON) et `key_points` (TEXT) dans `schema.sql`
- [x] Ajouter dans `scraper/init_sqlite.php` (SQLite local) — colonnes `image_3/4/5` aussi
- [x] Migrer le `FALLBACK_CONTENT` de `index.html` vers `scraper/init_sqlite.php` (seed)
- [x] Mettre à jour `api/courants.php` : inclure `artistes` et `key_points` dans la réponse
- [x] Mettre à jour `index.html` : lire `artistes` et `key_points` depuis l'API
- [x] Compléter les 15 sous-mouvements (artistes + key_points pour tous)
- [x] Sync via `./sync.ps1` + migration prod via `migrate.php`

### Étape 2 — Deploy : créer `sync.ps1` ✅ DONE

- [x] `sync.ps1` créé à la racine — copie `index.html`, `style.css`, `api/`, `scraper/fetch_*.php`
- [x] Ne touche pas `deploy/scraper/config.php` (credentials prod)
- [x] Usage : `./sync.ps1` depuis la racine

### Étape 3 — Upload prod + données ✅ DONE

- [x] Colonnes `artistes`, `key_points`, `image_3/4/5` ajoutées sur MySQL OVH
- [x] 15 sous-mouvements présents en prod (déjà insérés)
- [x] `artistes` + `key_points` seedés pour les 37 courants
- [x] Dates, `description_courte`, `wikipedia_titre` : 37/37 remplis
- [x] Saisir les images des 37 courants via https://www.nothuman.be/atlas/admin.php

### Étape 4 — Contenu Wikipedia (textes) ✅ DONE

- [x] `fetch_wikidata.php` lancé en prod — descriptions + dates pour courants avec `wikidata_id`
- [x] `fetch_wikipedia.php` lancé en prod — `description_longue` pour les courants avec `wikipedia_titre`
- [x] `wikipedia_titre` ajouté pour tous les 37 courants → futur re-scraping possible

### Étape 5 — Thème dark ✅ DONE

- [x] Toggle `◐` haut droite — `data-theme="dark"` sur `<html>`
- [x] CSS : variables `--bg`, `--fg`, `--fg-mid`, `--fg-lo`, `--fg-faint`, `--card-bg`, `--lb-bg`
- [x] Three.js : `scene.background` + `fog` + nœuds + tubes + particules mis à jour
- [x] Mémorisé en `localStorage`

---

## Statut actuel

| Composant | Local | Prod (OVH) |
|-----------|-------|------------|
| App WebGL (`index.html`) | ✅ opérationnel | ✅ opérationnel |
| CSS (`style.css`) | ✅ | ✅ |
| API PHP (`api/courants.php`) | ✅ SQLite | ✅ MySQL |
| DB — 22 courants niveau 1 | ✅ | ✅ |
| DB — 15 sous-mouvements niveau 2 | ✅ | ✅ |
| DB — 55 relations | ✅ | ✅ |
| DB — `artistes` + `key_points` | ✅ 37/37 | ✅ 37/37 |
| DB — descriptions courtes (Wikidata) | ✅ 37/37 | ✅ 37/37 |
| DB — descriptions longues (Wikipedia) | ✅ scrapé | ✅ scrapé |
| DB — dates (debut/fin) | ✅ 37/37 | ✅ 37/37 |
| DB — `wikipedia_titre` | ✅ 37/37 | ✅ 37/37 |
| DB — images (image_1 à image_5) | — | ✅ 37/37 saisis via admin |
| Admin images (5 slots + filtre) | — | ✅ en ligne |
| Scrapers texte (wikidata + wikipedia) | ✅ local | ✅ prod (token) |

---

## Loading screen — Architecture validée (2026-06-24)

### Scénario cible
1. `#ls` transparent → la scène 3D est visible derrière le counter dès le départ
2. Three.js init + `animate()` démarrent **avant** le premier `await` (scène vide, tourne déjà)
3. API fetch → nœuds/arêtes/particules créés à `opacity: 0`
4. Image preload 20→65% → chaque nœud s'allume en 3D (`opacity 1`, `couleur` accent) au fur et à mesure
5. Audio preload 65→100%
6. `hideLoadingScreen` → transition caméra (vue zénithale → position normale) + fog activé + bokeh fade-in

- **`autoRotateSpeed = 0.25`** — doit être déclaré dans le bloc init controls (à ne pas perdre lors d'un refactor)

### Règles d'implémentation
- **Pas d'`earlyLoop`** — un seul `animate()`, démarré tôt, itère des tableaux vides sans crash
- **`let` pour les variables qu'`animate()` utilise** (`nodeMap`, `nodeMeshes`, `edgeMeshes`, `pMat`, `lerpTarget`, etc.) — déclarées **avant** l'appel à `animate()`
- **`const` pour renderer, scene, camera, controls, composer, bokehPass** — déclarés dans le bloc Three.js init (avant API fetch)
- **`scene.fog = null`** pendant le loading — activé à la fin de la transition caméra
- **`bokehPass.uniforms['aperture'].value = 0`** pendant le loading — fade-in après transition
- **Pas de doublon** de déclarations — supprimer les anciens blocs RENDERER/NODES/EDGES/PARTICLES du bas du fichier

### Transitions
- Caméra départ : `(0, 50, -4.5)` top-down, `camera.up = (0,0,-1)`
- Caméra arrivée : `(6.75, 5.25, 24.75)`, `camera.up = (0,1,0)` — ease-out cubic, 3.2s
- Couleurs nœuds : accent pendant loading → gris `COLOR_DEFAULT` à la fin de la transition
- UI (title, hint, timeline) : `opacity 0` → `opacity 1` avec delay 500ms après transition

### Backup de référence
- `_backup_240626/` — version stable avant le chantier loading screen
- État : scène 3D fonctionnelle, loading screen opaque classique, pas de transition caméra

## Idées / Roadmap future

### Son ambiant par ère
- Remplacer le drone synthé global par 8 boucles audio thématiques, une par tranche temporelle
- Crossfade (2s) au changement d'ère lors de la sélection d'un nœud — deselect → retour drone neutre
- **Buckets :** 1850–1900 / 1900–1920 / 1920–1945 / 1945–1965 / 1965–1980 / 1980–1995 / 1995–2010 / 2010–2025
- **Fichiers :** `audio/era-1850.mp3` … `audio/era-2010.mp3` (~8 fichiers, boucles seamless 30–60s, ~9Mo total)
- **Sources à trouver :** field recordings, musique d'archive, ambiances thématiques par ère
- **Code :** détection `c.periode.debut` → bucket → `AudioBufferSourceNode` lazy-load + crossfade Web Audio API
| Scrapers images | 🚫 supprimés | 🚫 non pertinents |
| `sync.ps1` | ✅ opérationnel | — |
| Dark theme toggle | ✅ | ✅ |
| Labels 3D billboard | ✅ | ✅ |
| Lightbox images | ✅ | ✅ |
| Hit-testing cartes (PlaneGeometry) | ✅ | ✅ |
| Nœud actif coloré (couleur accent) | ✅ | ✅ |

---


---

## Environnements — LOCAL vs PROD

> **Règle absolue** : ne JAMAIS confondre les deux. Les SQL et configs sont différents.

### Local

| Paramètre | Valeur |
|-----------|--------|
| **DB** | SQLite — `database/atlas.db` |
| **Config** | `scraper/config.php` → `APP_ENV=local` auto-détecté |
| **Init DB** | `php scraper/init_sqlite.php` (SQLite uniquement) |
| **API** | `http://localhost:8080/api/courants.php` |
| **Scrapers** | `php scraper/fetch_wikidata.php` / `php scraper/fetch_wikipedia.php` |
| **SQL dialect** | SQLite : `datetime('now')`, types `TEXT`/`INTEGER` |
| **Serveur** | `php -S localhost:8080` depuis la racine |

### Prod (OVH)

| Paramètre | Valeur |
|-----------|--------|
| **DB** | MySQL — host `nothumanatlas.mysql.db`, DB/user `nothumanatlas` |
| **Config** | `deploy/scraper/config.php` → MySQL hardcodé, `APP_ENV=prod` |
| **Init DB** | `schema.sql` importé via phpMyAdmin (MySQL uniquement) |
| **API** | `https://www.nothuman.be/atlas/api/courants.php` |
| **Scrapers** | URL web avec token : `?token=AtlasRun2024` |
| **SQL dialect** | MySQL : `NOW()` ou paramètre PHP `date('Y-m-d H:i:s')` |
| **Cache API** | `Cache-Control: public, max-age=300` → ajouter `?nocache=1` pour bypasser |

### ⚠️ Pièges connus

- `datetime('now')` → **SQLite uniquement**. Sur MySQL, utiliser `date('Y-m-d H:i:s')` en PHP ou `NOW()`.
- `deploy/scraper/config.php` ≠ `scraper/config.php` — deux fichiers distincts, ne pas confondre.
- `scraper/init_sqlite.php` → **LOCAL UNIQUEMENT**. Ne jamais l'uploader/exécuter sur OVH.
- L'API a un cache 5 min. Après un scraping, ajouter `?nocache=1` pour voir les nouvelles données.
- `deploy/index.html` + `deploy/style.css` doivent toujours être des copies exactes des sources.
- Tous les INSERTs dans `schema.sql` sont en `INSERT IGNORE` — safe à re-importer.

---

## Structure des fichiers

```
project/
├── index.html                  # Source principale — copier vers deploy/ après chaque modif
├── style.css                   # CSS séparé — copier vers deploy/ après chaque modif
├── schema.sql                  # Schéma MySQL complet + seed (INSERT IGNORE — safe à re-importer)
├── Infos.txt                   # Notes diverses
│
├── api/
│   └── courants.php            # API locale (lit SQLite)
│
├── scraper/                    # ⚠️ LOCAL UNIQUEMENT
│   ├── config.php              # Config auto-détect SQLite/MySQL + COURANTS_CONFIG (37 courants)
│   ├── config.example.php      # Template config (sans credentials)
│   ├── init_sqlite.php         # Crée et seed la DB SQLite locale (37 courants + 55 relations)
│   ├── fetch_wikidata.php      # Scrape Wikidata SPARQL (description, dates, image P18)
│   └── fetch_wikipedia.php     # Scrape Wikipedia EN (extrait long, thumbnail)
│
├── database/
│   └── atlas.db                # SQLite local (ne pas commiter)
│
├── deploy/                     # ✈️ TOUT CE DOSSIER va sur OVH /atlas/
│   ├── index.html              # = copie de ../index.html
│   ├── style.css               # = copie de ../style.css
│   ├── admin.php               # Interface admin images (mot de passe : AtlasAdmin2024)
│   ├── api/
│   │   └── courants.php        # = copie de ../api/courants.php (lit MySQL)
│   └── scraper/                # Scrapers prod (protégés par token)
│       ├── config.php          # Config MySQL OVH + SCRAPER_TOKEN + COURANTS_CONFIG (37)
│       ├── fetch_wikidata.php  # Scraper Wikidata (token requis)
│       └── fetch_wikipedia.php # Scraper Wikipedia (token requis)
│
└── docs/
    ├── README.md               # Cette documentation
    └── deploy.md               # Checklist déploiement
```

---

## Les 22 courants (niveau 1)

| Slug | Nom | Période | Wikidata |
|------|-----|---------|----------|
| `arts-crafts` | Arts & Crafts | 1880–1910 | Q330369 |
| `art-nouveau` | Art Nouveau | 1890–1910 | Q34636 |
| `futurisme` | Futurism | 1909–1944 | Q38054 |
| `constructivisme` | Constructivism | 1915–1935 | Q80930 |
| `de-stijl` | De Stijl | 1917–1931 | Q160830 |
| `bauhaus` | Bauhaus | 1919–1933 | Q124354 |
| `art-deco` | Art Deco | 1920–1940 | Q48584 |
| `surrealisme` | Surrealism | 1924–1966 | Q35922 |
| `style-suisse` | Swiss Style | 1950–1972 | Q2140911 |
| `pop-art` | Pop Art | 1955–1972 | Q134147 |
| `vernacular` | Vernacular / Push Pin | 1950–1980 | — |
| `psychedelique` | Psychedelic | 1965–1975 | Q696160 |
| `new-wave-typo` | New Wave / Swiss Punk | 1970–1988 | — |
| `pixel-art` | Pixel Art | 1975–∞ | — |
| `postmodernisme` | Postmodernism | 1975–1995 | Q3318563 |
| `memphis` | Memphis Group | 1981–1988 | Q1755576 |
| `grunge` | Grunge Typography | 1988–2000 | — |
| `y2k` | Y2K Aesthetic | 1995–2005 | — |
| `skeuomorphisme` | Skeuomorphism | 2000–2013 | Q899542 |
| `flat-design` | Flat Design | 2010–2020 | Q15266360 |
| `vaporwave` | Vaporwave | 2010–∞ | Q15869946 |
| `brutalisme-web` | Web Brutalism | 2015–∞ | — |

---

## Les 15 sous-mouvements (niveau 2)

| Slug | Nom | Parent | Période | Wikidata |
|------|-----|--------|---------|----------|
| `aesthetic-movement` | Aesthetic Movement | arts-crafts | 1868–1901 | Q1050297 |
| `jugendstil` | Jugendstil | art-nouveau | 1896–1910 | Q34627 |
| `vorticism` | Vorticism | futurisme | 1914–1920 | Q193699 |
| `neoplasticisme` | Néoplasticisme | de-stijl | 1917–1931 | Q744239 |
| `new-typography` | New Typography | bauhaus | 1928–1950 | — |
| `ulm-school` | École d'Ulm | bauhaus | 1953–1968 | Q680909 |
| `streamline` | Streamline Moderne | art-deco | 1930–1950 | Q185023 |
| `lettrisme` | Lettrisme | surrealisme | 1945–1975 | Q200654 |
| `op-art` | Op Art | style-suisse | 1964–1975 | Q80113 |
| `neo-pop` | Neo-Pop | pop-art | 1980–1995 | — |
| `psychedelic-poster` | Affiches Psychédéliques | psychedelique | 1966–1972 | — |
| `deconstruction-typo` | Déconstruction Typo | postmodernisme | 1988–1998 | — |
| `material-design` | Material Design | flat-design | 2014–∞ | Q17030296 |
| `synthwave` | Synthwave | vaporwave | 2012–∞ | Q27611341 |
| `lo-fi-aesthetic` | Lo-fi Aesthetic | vaporwave | 2015–∞ | — |

> Visuellement : nœuds plus petits (`0.10` vs `0.175`), tubes de liaison plus fins et semi-transparents, labels en opacité réduite.

---

## Rendu 3D — différenciation niveau 1 / niveau 2

| Attribut | Niveau 1 | Niveau 2 |
|----------|----------|----------|
| Cube | `BoxGeometry(0.175)` | `BoxGeometry(0.10)` |
| Couleur par défaut | `#888888` | `#aaaaaa` |
| Ring halo | `0.17–0.21` | `0.10–0.13` |
| Tube radius | `0.012` | `0.006` |
| Tube opacité | `0.50` | `0.25` |
| Label opacité max | `1.0` | `0.6` |

---

## Architecture technique

### Front-end

- **Three.js 0.165.0** via CDN — ES modules
- **WebGLRenderer** + **CSS3DRenderer** superposés
- **OrbitControls** — drag/zoom/orbit avec `autoRotate` au chargement (désactivé au premier clic)
- **BokehPass** (EffectComposer) — profondeur de champ vers le nœud actif
- **Nodes** : `BoxGeometry` colorée — taille selon `niveau`
- **Edges** : `TubeGeometry` — épaisseur selon niveau des endpoints
- **Particles** : `Points` (8000) aléatoires — ambiance
- **Floating cards** : `CSS3DObject` — 6 cartes au clic d'un nœud :
  - 5 cartes image — face caméra (`camRy + off.ry` comme rotation de base)
  - 1 carte info (nom, dates, description, artistes, principes) — face caméra (`camRy`)
- **Nav dots** : `<div>` cliquables → `navigateTo()` → lerp caméra

### Back-end

- PHP 8.1+, PDO
- Auto-détecte SQLite (local) ou MySQL (prod) via `APP_ENV`
- `GET /api/courants.php` → liste tous les courants avec `niveau` + relations
- `GET /api/courants.php?slug=xxx` → détail
- `Cache-Control: public, max-age=300`

### Scrapers

- `fetch_wikidata.php` : requête SPARQL → `image_wikidata`, `description_courte`, dates
- `fetch_wikipedia.php` : Wikipedia REST API EN → `image_wikipedia`, `description_longue`
- En prod : protégés par `?token=AtlasRun2024`
- Ordre d'exécution : **Wikidata d'abord**, puis Wikipedia

---

## Schéma DB

```sql
courants
  id, slug, nom, wikidata_id, wikipedia_titre
  description_courte, description_longue
  periode_debut, periode_fin
  image_wikidata, image_wikipedia, image_3, image_4, image_5
  artistes (JSON)             -- ex: ["Herbert Bayer","László Moholy-Nagy"]
  key_points (TEXT)           -- principes visuels clés
  couleur_accent, typographie
  mots_cles (JSON), principes_visuels
  pos_x, pos_y, pos_z         -- position 3D (Z = axe temporel)
  niveau                      -- 1=principal  2=sous-mouvement
  fetched_at

courant_relations
  source_id, cible_id
  type_relation  -- influence | derivation | opposition | contemporain

artistes, courant_artistes, objets_visuels
```

---

## API — Format de réponse

```json
{
  "slug": "bauhaus",
  "nom": "Bauhaus",
  "wikidata_id": "Q124354",
  "description_courte": "20th century European graphic design style",
  "periode": { "debut": 1919, "fin": 1933 },
  "images": ["https://...", "https://..."],
  "da": { "couleur": "#e63946", "typo": "\"Helvetica Neue\", sans-serif" },
  "artistes": ["Herbert Bayer", "László Moholy-Nagy", "Paul Klee"],
  "key_points": "Grid, pure geometry, primary colours, sans-serif, form follows function.",
  "mots_cles": [],
  "position": { "x": 0, "y": 0, "z": -4 },
  "niveau": 1,
  "relations": [
    { "cible": "style-suisse", "type": "derivation" }
  ]
}
```

---

## Concept

L'Atlas du Graphisme est un outil pédagogique destiné aux étudiants en design graphique. Il représente 37 courants graphiques (1880–2020) sous forme de nœuds dans un espace 3D navigable, reliés par leurs influences historiques réelles.

**Parti pris visuel :**
- Fond blanc, particules grises, typographie discrète
- Axe Z = axe temporel (Arts & Crafts z=+8 → Web Brutalism z=-19.5)
- Dispersion X/Y éditoriale — effet constellation
- Profondeur de champ dynamique (BokehPass) vers le nœud actif
- Sous-mouvements visuellement discrets — satellites des courants principaux


---

## Statut actuel

| Composant | Local | Prod (OVH) |
|-----------|-------|------------|
| App WebGL (`index.html`) | ✅ opérationnel | ✅ opérationnel |
| API PHP (`api/courants.php`) | ✅ SQLite | ✅ MySQL |
| DB — 22 courants | ✅ SQLite seed | ✅ MySQL seed |
| DB — 40 relations | ✅ | ✅ |
| DB — descriptions (Wikidata) | ❌ non scrapé | ✅ scrapé |
| DB — images auto (Wikipedia) | ❌ non scrapé | ✅ scrapé (20/22) |
| DB — colonnes `image_3` / `image_4` | — | ✅ créées au 1er save admin |
| Admin images (`admin.php`) | — | ✅ protégé `AtlasAdmin2024` |
| Scrapers protégés (token) | — | ✅ `AtlasRun2024` |
| Images curées (haute qualité) | ❌ | ❌ à saisir via admin |

---

## Environnements — LOCAL vs PROD

> **Règle absolue** : ne JAMAIS confondre les deux. Les SQL et configs sont différents.

### Local

| Paramètre | Valeur |
|-----------|--------|
| **DB** | SQLite — `database/atlas.db` |
| **Config** | `scraper/config.php` → `APP_ENV=local` auto-détecté |
| **Init DB** | `php scraper/init_sqlite.php` (SQLite uniquement) |
| **API** | `http://localhost:8080/api/courants.php` |
| **Scrapers** | `php scraper/fetch_wikidata.php` / `php scraper/fetch_wikipedia.php` |
| **SQL dialect** | SQLite : `datetime('now')`, types `TEXT`/`INTEGER` |
| **Serveur** | `php -S localhost:8080` depuis la racine |

### Prod (OVH)

| Paramètre | Valeur |
|-----------|--------|
| **DB** | MySQL — host `nothumalatlas.mysql.db`, DB/user `nothumanatlas` |
| **Config** | `deploy/scraper/config.php` → MySQL hardcodé, `APP_ENV=prod` |
| **Init DB** | `schema.sql` importé via phpMyAdmin (MySQL uniquement) |
| **API** | `https://www.nothuman.be/atlas/api/courants.php` |
| **Scrapers** | URL web avec token : `?token=AtlasRun2024` |
| **SQL dialect** | MySQL : `NOW()` ou paramètre PHP `date('Y-m-d H:i:s')` |
| **Cache API** | `Cache-Control: public, max-age=300` → ajouter `?nocache=1` pour bypasser |

### ⚠️ Pièges connus

- `datetime('now')` → **SQLite uniquement**. Sur MySQL, utiliser `date('Y-m-d H:i:s')` en PHP ou `NOW()`.
- `deploy/scraper/config.php` ≠ `scraper/config.php` — deux fichiers distincts, ne pas confondre.
- `scraper/init_sqlite.php` → **LOCAL UNIQUEMENT**. Ne jamais l'uploader/exécuter sur OVH.
- L'API a un cache 5 min. Après un scraping, ajouter `?nocache=1` pour voir les nouvelles données.
- `deploy/index.html` doit être une copie exacte de `index.html` (même chemin API relatif `api/courants.php`).

---

## Structure des fichiers

```
project/
├── index.html                  # Source principale — copier vers deploy/ après chaque modif
├── schema.sql                  # Schéma MySQL complet (référence prod uniquement)
├── Infos.txt                   # Notes diverses
│
├── api/
│   └── courants.php            # API locale (lit SQLite)
│
├── scraper/                    # ⚠️ LOCAL UNIQUEMENT
│   ├── config.php              # Config auto-détect SQLite/MySQL + COURANTS_CONFIG
│   ├── config.example.php      # Template config (sans credentials)
│   ├── init_sqlite.php         # Crée et seed la DB SQLite locale
│   ├── fetch_wikidata.php      # Scrape Wikidata SPARQL (description, dates, image P18)
│   └── fetch_wikipedia.php     # Scrape Wikipedia EN (extrait long, thumbnail)
│
├── database/
│   └── atlas.db                # SQLite local (ne pas commiter)
│
├── deploy/                     # ✈️ TOUT CE DOSSIER va sur OVH /atlas/
│   ├── index.html              # = copie de ../index.html
│   ├── admin.php               # Interface admin images (mot de passe : AtlasAdmin2024)
│   ├── api/
│   │   └── courants.php        # = copie de ../api/courants.php (lit MySQL)
│   └── scraper/                # Scrapers prod (protégés par token)
│       ├── config.php          # Config MySQL OVH + SCRAPER_TOKEN
│       ├── fetch_wikidata.php  # Scraper Wikidata (token requis)
│       └── fetch_wikipedia.php # Scraper Wikipedia (token requis)
│
└── docs/
    ├── README.md               # Cette documentation
    └── deploy.md               # Checklist déploiement
```

---

## Les 22 courants

| Slug | Nom affiché | Période | Wikidata QID | Images |
|------|-------------|---------|--------------|--------|
| `arts-crafts` | Arts & Crafts | 1880–1910 | Q330369 | Wikipedia ✅ |
| `art-nouveau` | Art Nouveau | 1890–1910 | Q34636 | Wikidata ✅ Wikipedia ✅ |
| `futurisme` | Futurism | 1909–1944 | — | Wikipedia ✅ |
| `constructivisme` | Constructivism | 1915–1935 | — | Wikipedia ✅ |
| `de-stijl` | De Stijl | 1917–1931 | — | Wikipedia ✅ |
| `bauhaus` | Bauhaus | 1919–1933 | Q124354 | Wikipedia ✅ |
| `art-deco` | Art Deco | 1920–1940 | — | Wikipedia ✅ |
| `surrealisme` | Surrealism | 1924–1966 | — | Wikipedia ✅ |
| `style-suisse` | Swiss Style | 1950–1972 | Q2140911 | Wikipedia ✅ |
| `pop-art` | Pop Art | 1955–1972 | Q134147 | Wikidata ✅ Wikipedia ✅ |
| `vernacular` | Vernacular / Push Pin | 1950–1980 | — | ❌ |
| `psychedelique` | Psychedelic | 1965–1975 | Q696160 | Wikipedia ✅ |
| `new-wave-typo` | New Wave / Swiss Punk | 1970–1988 | — | Wikipedia ✅ |
| `pixel-art` | Pixel Art | 1975–∞ | Q811179 | Wikipedia ✅ |
| `postmodernisme` | Postmodernism | 1975–1995 | Q3318563 | Wikipedia ✅ |
| `memphis` | Memphis Group | 1981–1988 | Q1755576 | Wikipedia ✅ |
| `grunge` | Grunge Typography | 1988–2000 | — | ❌ |
| `y2k` | Y2K Aesthetic | 1995–2005 | — | Wikipedia ✅ |
| `skeuomorphisme` | Skeuomorphism | 2000–2013 | Q899542 | Wikipedia ✅ |
| `flat-design` | Flat Design | 2010–2020 | Q15266360 | ❌ |
| `vaporwave` | Vaporwave | 2010–∞ | Q15869946 | Wikipedia ✅ |
| `brutalisme-web` | Web Brutalism | 2015–∞ | — | ❌ (API timeout) |

> **Images manquantes (4)** : vernacular, grunge, flat-design, brutalisme-web — à alimenter via l'admin.

> **Format images DB** : 4 slots par courant — `image_wikidata` (img1), `image_wikipedia` (img2), `image_3`, `image_4`. L'API retourne un array filtré `images: [url1, url2, ...]`. Les colonnes `image_3` et `image_4` sont créées automatiquement au premier save dans l'admin (pas besoin de migration manuelle).

> **Sources recommandées** pour images curées : [Artvee](https://artvee.com), [rawpixel.com/free](https://www.rawpixel.com/free), [NYPL Digital Collections](https://digitalcollections.nypl.org), [Cooper Hewitt](https://collection.cooperhewitt.org), [Met Open Access](https://www.metmuseum.org/art/collection). L'admin résout aussi automatiquement les URLs `commons.wikimedia.org/wiki/File:...` via l'API Wikimedia.

---

## Architecture technique

### Front-end (`index.html`)

- **Three.js 0.165.0** via CDN (jsdelivr) — ES modules
- **WebGLRenderer** + **CSS3DRenderer** (superposés)
- **OrbitControls** — drag/zoom/orbit
- **BokehPass** (EffectComposer) — profondeur de champ vers le nœud actif
- **Nodes** : `SphereGeometry` colorée, label 3D en `<div>` projeté
- **Edges** : `LineSegments` entre nœuds liés
- **Particles** : `Points` aléatoires (ambiance)
- **Floating cards** : `CSS3DObject` — 5 cartes spawned au clic d'un nœud :
  - 4 cartes image (images Wikipedia/Wikidata) — rendu DOM haute résolution (`res: 3`)
  - 1 carte info (nom, dates, description, artistes, principes) — hauteur auto
- **Nav dots** : `<div>` cliquables → `navigateTo()` → lerp caméra

### Back-end (`api/courants.php`)

- PHP 8.1+, PDO
- Auto-détecte SQLite (local) ou MySQL (prod) via `APP_ENV`
- `GET /api/courants.php` → liste tous les courants (scène 3D)
- `GET /api/courants.php?slug=xxx` → détail avec artistes et objets visuels
- `Cache-Control: public, max-age=300`

### Scrapers

- `fetch_wikidata.php` : requête SPARQL → `image_wikidata`, `description_courte`, dates
- `fetch_wikipedia.php` : Wikipedia REST API EN → `image_wikipedia`, `description_longue`
- En prod : protégés par `?token=AtlasRun2024`
- SQL `fetched_at` : utilise `date('Y-m-d H:i:s')` PHP (compatible MySQL ET SQLite)

---

## Schéma DB (simplifié)

```sql
courants
  id, slug, nom, wikidata_id
  description_courte, description_longue
  periode_debut, periode_fin
  image_wikidata, image_wikipedia   -- URLs brutes
  couleur_accent, typographie       -- identité visuelle
  mots_cles (JSON), principes_visuels
  pos_x, pos_y, pos_z               -- position 3D scène
  fetched_at

courant_relations
  source_id, cible_id
  type_relation  -- influence | derivation | opposition | contemporain

courant_artistes (liaison many-to-many)
artistes
objets_visuels
```

---

## API — Format de réponse

```json
{
  "id": 3,
  "slug": "bauhaus",
  "nom": "Bauhaus",
  "wikidata_id": "Q124354",
  "description_courte": "20th century European graphic design style",
  "periode": { "debut": 1919, "fin": 1933 },
  "images": {
    "wikidata": "https://commons.wikimedia.org/wiki/Special:FilePath/...",
    "wikipedia": "https://upload.wikimedia.org/wikipedia/..."
  },
  "da": { "couleur": "#e63946", "typo": "\"Helvetica Neue\", sans-serif" },
  "mots_cles": [],
  "position": { "x": 0, "y": 0, "z": -4 },
  "niveau": 1,
  "relations": [
    { "cible": "style-suisse", "type": "derivation" }
  ]
}
```


---

## Concept

L'Atlas du Graphisme est un outil pédagogique destiné aux étudiants en design graphique. Il représente 22 courants graphiques majeurs (1880–2020) sous forme de nœuds dans un espace 3D navigable, reliés par leurs influences historiques réelles.

**Parti pris visuel :**
- Fond blanc, particules grises, typographie discrète — le graphisme comme sujet, pas le gadget
- Axe Z = axe temporel (Arts & Crafts z=+8 → Web Brutalism z=-19.5)
- Dispersion X/Y éditoriale → effet constellation, pas de grille
- Profondeur de champ (BokehPass / tilt-shift) — focus dynamique vers le nœud actif
- Chaque nœud hérite de la couleur et typographie de son courant à l'activation

---

## Les 22 courants couverts

| Slug | Nom | Période |
|------|-----|---------|
| `arts-crafts` | Arts & Crafts | 1880–1910 |
| `art-nouveau` | Art Nouveau | 1890–1910 |
| `futurisme` | Futurism | 1909–1944 |
| `constructivisme` | Constructivism | 1915–1935 |
| `de-stijl` | De Stijl | 1917–1931 |
| `bauhaus` | Bauhaus | 1919–1933 |
| `art-deco` | Art Deco | 1920–1940 |
| `surrealisme` | Surrealism | 1924–1966 |
| `style-suisse` | Swiss Style | 1950–1972 |
| `pop-art` | Pop Art | 1955–1972 |
| `vernacular` | Vernacular / Push Pin | 1950–1980 |
| `psychedelique` | Psychedelic | 1965–1975 |
| `new-wave-typo` | New Wave / Swiss Punk | 1970–1988 |
| `pixel-art` | Pixel Art | 1975–∞ |
| `postmodernisme` | Postmodernism | 1975–1995 |
| `memphis` | Memphis Group | 1981–1988 |
| `grunge` | Grunge Typography | 1988–2000 |
| `y2k` | Y2K Aesthetic | 1995–2005 |
| `skeuomorphisme` | Skeuomorphism | 2000–2013 |
| `flat-design` | Flat Design | 2010–2020 |
| `vaporwave` | Vaporwave | 2010–∞ |
| `brutalisme-web` | Web Brutalism | 2015–∞ |

---

## Architecture générale

```
┌─────────────────────────────────────────────────────┐
│  Sources externes                                    │
│  Wikidata SPARQL API  ·  Wikipedia REST API (EN)    │
└──────────────┬──────────────────────────────────────┘
               │ PHP scrapers (CLI)
               ▼
┌─────────────────────────────────────────────────────┐
│  Base de données                                     │
│  SQLite (local dev)  ·  MySQL (OVH prod)            │
└──────────────┬──────────────────────────────────────┘
               │ PHP REST API
               ▼
┌─────────────────────────────────────────────────────┐
│  api/courants.php  →  JSON                          │
└──────────────┬──────────────────────────────────────┘
               │ fetch() ES module
               ▼
┌─────────────────────────────────────────────────────┐
│  index.html — Three.js WebGL                        │
│  Nodes · Edges · Particles · BokehPass · Panel      │
└─────────────────────────────────────────────────────┘
```

---

## Structure des fichiers

```
project/
│
├── index.html                  # App principale (Three.js CDN, ES modules)
├── schema.sql                  # Schéma MySQL complet (référence prod)
│
├── scraper/
│   ├── config.php              # Connexion DB (SQLite/MySQL auto-détect) + liste des 22 courants
│   ├── init_sqlite.php         # Init DB locale : tables + seed data + relations
│   ├── fetch_wikidata.php      # Scrape Wikidata SPARQL (description, dates, image P18)
│   └── fetch_wikipedia.php     # Scrape Wikipedia EN REST API (extrait long, thumbnail)
│
├── api/
│   └── courants.php            # REST API JSON (GET liste / GET ?slug=xxx détail)
│
├── database/
│   └── atlas.db                # SQLite local (gitignored)
│
└── docs/
    ├── README.md               # Cette documentation
    └── deploy.md               # Guide déploiement OVH + GitHub
```

---

## Schéma de base de données

```sql
courants               -- Table principale
  id, slug, nom
  wikidata_id          -- QID Wikidata (ex: Q124354)
  wikipedia_titre      -- Titre article Wikipedia EN
  description_courte   -- Extrait court (Wikidata schema:description)
  description_longue   -- Extrait Wikipedia EN
  periode_debut        -- Année de début (INTEGER)
  periode_fin          -- Année de fin (INTEGER, null = présent)
  image_wikidata       -- URL Wikimedia Commons (P18)
  image_wikipedia      -- URL thumbnail Wikipedia
  couleur_accent       -- Hex color (#e63946)
  typographie          -- Font stack CSS
  mots_cles            -- JSON array
  principes_visuels    -- Texte libre
  pos_x / pos_y / pos_z -- Position 3D dans la scène
  fetched_at           -- Timestamp dernier scraping

artistes               -- Figures clés (à enrichir)
  id, wikidata_id, nom, slug, naissance, deces, nationalite, bio_courte, image

objets_visuels         -- Références visuelles (affiches, logos…)
  id, courant_id, titre, type, artiste_id, annee, image, legende

courant_relations      -- Graphe d'influences
  source_id, cible_id
  type_relation        -- influence | derivation | opposition | contemporain

courant_artistes       -- Table de liaison many-to-many
```

---

## API REST

**Base URL locale :** `http://localhost:8080/api/courants.php`

### `GET /api/courants.php`
Retourne tous les courants (vue scène 3D).

```json
[
  {
    "id": 6,
    "slug": "bauhaus",
    "nom": "Bauhaus",
    "wikidata_id": "Q124354",
    "description_courte": "German art school (1919–1933)",
    "periode": { "debut": 1919, "fin": 1933 },
    "images": {
      "wikidata": "https://commons.wikimedia.org/...",
      "wikipedia": "https://upload.wikimedia.org/..."
    },
    "da": { "couleur": "#e63946", "typo": "\"Helvetica Neue\", sans-serif" },
    "mots_cles": [],
    "position": { "x": 0, "y": -0.5, "z": 0.2 },
    "niveau": 1,
    "relations": [
      { "cible": "style-suisse", "type": "derivation" },
      { "cible": "pop-art", "type": "influence" }
    ]
  }
]
```

### `GET /api/courants.php?slug=bauhaus`
Retourne un courant en détail avec artistes et objets visuels.

---

## Pipeline de données

```
1. php scraper/init_sqlite.php     # Crée la DB, insère les 22 courants + 40 relations
2. php scraper/fetch_wikidata.php  # Enrichit : description courte, dates, image Wikimedia
3. php scraper/fetch_wikipedia.php # Enrichit : description longue (extrait EN), thumbnail
```

**Règles de merge :**
- `nom` : jamais écrasé par Wikidata (seed = source de vérité pour les noms)
- `description_courte` : `COALESCE(wikidata, existant)` — première valeur non nulle
- `description_longue` : Wikipedia uniquement
- `image_wikidata` / `image_wikipedia` : sources séparées, toutes deux conservées

**QIDs Wikidata validés :**

| QID | Courant |
|-----|---------|
| Q330369 | Arts & Crafts |
| Q34636 | Art Nouveau |
| Q124354 | Bauhaus |
| Q134147 | Pop Art |
| Q696160 | Psychedelic Art |
| Q2140911 | Swiss Style |
| Q3318563 | Postmodern Art |
| Q15266360 | Flat Design |

Les autres QIDs n'ont pas encore été vérifiés — Wikipedia EN suffit pour les textes.

---

## Front-end Three.js

**Librairie :** Three.js r0.165.0 via CDN `jsdelivr` (ES modules, importmap)

**Composants scène :**

| Élément | Détail |
|---------|--------|
| Nœuds | `BoxGeometry(0.175)` — cubes gris → noir au clic |
| Halos | `RingGeometry` transparent, opacité 0.2 au survol actif |
| Arêtes | `TubeGeometry` sur `QuadraticBezierCurve3`, courbure aléatoire |
| Particules | 8000 `Points` sans texture → carrés WebGL natifs |
| Post-process | `BokehPass` (focus dynamique vers nœud actif) + `OutputPass` |
| Contrôles | `OrbitControls` (damping 0.06, pan désactivé, distance 3–40) |

**Positionnement des nœuds :**
- Z = axe temporel : `z = 8 - (année - 1880) × 0.2`
- X et Y = dispersion éditoriale (constellation)
- Défini dans `scraper/init_sqlite.php`, persisté en DB, servi par l'API

**Navigation :**
- Clic nœud → lerp caméra (5 unités de distance), `isZooming` flag
- `controls.target` lerp vers `lerpTarget`
- Panel latéral droit (400px) avec typographie du courant actif
- Nav dots (droite) = nœud le plus proche de la caméra

---

## Environnement local

**Prérequis :**
- PHP 8.4 (installé via WinGet)
- Extensions activées : `pdo_sqlite`, `openssl`, `curl`

**Démarrage :**
```powershell
cd "c:\Users\prspx\Desktop\Test AI"
php -S localhost:8080
```

**Init complète depuis zéro :**
```powershell
php scraper/init_sqlite.php
php scraper/fetch_wikidata.php
php scraper/fetch_wikipedia.php
```

Puis ouvrir **http://localhost:8080**

---

## État actuel — Demo v1

- ✅ 22 styles · 40 connexions historiques
- ✅ Données Wikipedia EN (21/22 — brutalisme-web sans article dédié)
- ✅ Images Wikimedia pour 8 styles (QIDs validés)
- ✅ Images Wikipedia pour 19 styles
- ✅ Navigation 3D complète (orbit, zoom, panel)
- ✅ Labels `Nom · Année` dans la scène
- ✅ Fallback local si API inaccessible
- ⬜ Images dans le panel (disponibles en DB, pas encore affichées)
- ⬜ Artistes depuis Wikidata (scraper P135 prévu, pas activé)
- ⬜ Déploiement OVH / MySQL
- ⬜ Repo GitHub

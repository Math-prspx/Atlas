# Atlas du Graphisme — Documentation

> Plateforme éducative immersive en WebGL pour explorer l'histoire des mouvements graphiques à travers une constellation 3D interactive.

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

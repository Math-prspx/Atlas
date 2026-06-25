# Atlas Musical — Cahier de charges complet
> Clone de l'Atlas du Graphisme adapté aux styles musicaux.
> À démarrer plus tard. Ce doc suffit à un rebuild complet from scratch.

---

## 1. Concept

Plateforme immersive de culture musicale. Exploration des styles, genres et mouvements musicaux à travers une constellation 3D interactive. Même logique que l'Atlas du Graphisme — non linéaire, visuel, mémorable.

**Questions auxquelles le système répond :**
1. Où se situe ce genre dans le temps ?
2. Quels sont ses codes sonores et visuels ?
3. Comment se différencie-t-il des autres genres ?

---

## 2. Stack technique (identique à Atlas du Graphisme)

| Couche | Technologie |
|---|---|
| Front | HTML/CSS/JS vanilla + Three.js 0.165 CDN, aucun bundler |
| Back | PHP 8.1+, API REST JSON, PDO |
| DB local | SQLite |
| DB prod | MySQL OVH |
| Deploy | `sync.ps1` → `deploy/` → FTP OVH |
| Prod | `nothuman.be/atlas-musical/` (à créer) |

---

## 3. Architecture front (index.html)

### Règle critique : Three.js init avant le premier `await`

```
1. Three.js init (renderer, scene, camera, controls, composer, bokehPass)
2. animate() démarre — itère des tableaux vides sans crash
3. API fetch → genres créés à opacity 0 dans la scène
4. Image preload → chaque nœud s'allume (opacity 1, couleur accent)
5. Audio preload (30s extraits par genre)
6. hideLoadingScreen → transition caméra + fog + bokeh
```

### Variables à déclarer comme `let` AVANT animate() :
`nodeMap, nodeMeshes, ringMeshes, edgeMeshes, pMat, labelEls, tlDotMap, courants, edges, navDotsEl, lerpCameraPos, isZooming, pendingCardCourant, camTransition, camTransitionStart, floatingCards, cardHitPlanes`

### Règles OrbitControls :
- `camera.up` ne doit JAMAIS être modifié (orbit cassé sinon)
- `controls.autoRotateSpeed = 0.25` — à ne jamais oublier
- `scene.fog = null` pendant loading → activé fin de transition caméra

### Caméra :
- Départ : `(0, 35, 18)`, target `(0, 0, -4.5)` — vue haute inclinée
- Arrivée : `(6.75, 5.25, 24.75)` — transition ease-out cubic 3.2s
- `controls.target.set(0, 0, -9.75)`

### BokehPass :
- `focus: 50` au départ (distance caméra→constellation)
- `aperture: 0.0003` pendant loading → monte à `0.0015` pendant la transition

---

## 4. Schéma DB

### Table `genres` (renommée depuis `courants`)

```sql
CREATE TABLE `genres` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `wikidata_id`       VARCHAR(20)  UNIQUE,
  `wikipedia_titre`   VARCHAR(200) DEFAULT NULL,
  `slug`              VARCHAR(120) NOT NULL UNIQUE,
  `nom`               VARCHAR(150) NOT NULL,
  `description_courte` TEXT        DEFAULT NULL,
  `description_longue` LONGTEXT    DEFAULT NULL,
  `periode_debut`     SMALLINT     DEFAULT NULL,
  `periode_fin`       SMALLINT     DEFAULT NULL,
  `artistes`          TEXT         DEFAULT NULL,  -- JSON array
  `key_points`        TEXT         DEFAULT NULL,
  `couleur_accent`    VARCHAR(7)   DEFAULT '#888888',
  `typographie`       VARCHAR(100) DEFAULT '"Helvetica Neue", sans-serif',
  `mots_cles`         TEXT         DEFAULT NULL,  -- JSON array
  `pos_x`             FLOAT        DEFAULT 0,
  `pos_y`             FLOAT        DEFAULT 0,
  `pos_z`             FLOAT        DEFAULT 0,
  `niveau`            TINYINT      DEFAULT 1,     -- 1=principal 2=sous-genre
  `audio_preview`     VARCHAR(500) DEFAULT NULL,  -- URL 30s MP3 (Spotify ou archive.org)
  `image_1` to `image_5` VARCHAR(500) DEFAULT NULL,
  `created_at`        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Table `relations`
```sql
CREATE TABLE `relations` (
  `from_slug`  VARCHAR(120) NOT NULL,
  `to_slug`    VARCHAR(120) NOT NULL,
  `type`       ENUM('influence','derivation','opposition','contemporain') DEFAULT 'influence',
  PRIMARY KEY (`from_slug`, `to_slug`)
);
```

### Différence vs Atlas Graphisme :
- Colonne `audio_preview` ajoutée — URL vers 30s d'extrait audio représentatif du genre
- Pas de table `artistes` séparée (JSON inline suffit)

---

## 5. Sources de données

### Données relationnelles et textes

| Source | Usage | Accès |
|---|---|---|
| **Wikidata** | Dates, descriptions courtes, relations, QIDs | API SPARQL publique |
| **Wikipedia EN** | Descriptions longues | API MediaWiki publique |
| **MusicBrainz** | Genres avec hiérarchies, relations subgenre | API REST ouverte, sans auth |
| **Every Noise at Once** | Référence ultime ~6000 genres, positions XY | Scraping statique |

### Images

| Source | Usage |
|---|---|
| Wikimedia Commons | Affiches, pochettes, photos artistes — même workflow qu'Atlas |
| admin.php | Saisie manuelle (5 slots par genre) |

### Audio (extrait 30s par genre)

| Source | Recommandation | Notes |
|---|---|---|
| **Spotify API** | ⭐ Meilleur choix | `preview_url` = 30s MP3 direct. Gratuit, nécessite compte dev. |
| **Internet Archive** | Pour genres pre-1950 | Enregistrements historiques domaine public |
| **Free Music Archive** | CC0/CC-BY | API disponible, classé par genre |
| **YouTube Audio Library** | Boucles libres | Téléchargeable, classé genre/mood |

**Workflow audio recommandé :**
1. Pour chaque genre : chercher 1 artiste représentatif via Spotify Search API
2. Récupérer `preview_url` du track le plus populaire de cet artiste
3. Stocker l'URL dans `audio_preview` — pas de fichier local, stream direct
4. Pre-loader en ArrayBuffer au loading (même pattern qu'Atlas Graphisme)

---

## 6. Scrapers à créer

Même structure que `scraper/` dans Atlas du Graphisme :

| Fichier | Rôle |
|---|---|
| `scraper/config.php` | Credentials DB + tokens API |
| `scraper/init_sqlite.php` | Création DB locale + seed des ~50 genres |
| `scraper/fetch_wikidata.php` | Dates + descriptions courtes via SPARQL |
| `scraper/fetch_wikipedia.php` | Descriptions longues via MediaWiki API |
| `scraper/fetch_musicbrainz.php` | Relations entre genres (à créer) |
| `scraper/fetch_spotify_audio.php` | Récupère `preview_url` par genre (à créer) |

---

## 7. Genres de départ (seed ~50 entrées)

Suggestion de hiérarchie pour le seed initial :

**Niveau 1 (principaux)** — ~20 genres :
Blues · Jazz · Country · Rock · R&B · Soul · Funk · Electronic · Hip-Hop · Pop · Metal · Punk · Reggae · Classical · Folk · Gospel · Latin · World · Ambient · Experimental

**Niveau 2 (sous-genres)** — ~30 entrées :
Bebop · Hard Bop · Free Jazz · Bluegrass · Rockabilly · Rock'n'Roll · Psychedelic Rock · Progressive Rock · New Wave · Post-Punk · Heavy Metal · Thrash Metal · Techno · House · Drum & Bass · Trip-Hop · Trap · Lo-fi · Shoegaze · Noise...

---

## 8. Positions dans la constellation 3D

### Formule (même principe qu'Atlas Graphisme) :
```
pos_z = 8 - (debut - 1900) × 0.15   (axe temporel)
pos_x = spread éditorial ±8
pos_y = spread éditorial ±4
```

Multiplicateurs : X/Y ×1.8, Z ×1.58 (appliqués dans JS lors du mapApiData)

### Organisation spatiale suggérée :
- Axe Z = axe temporel (ancien à gauche/haut, récent à droite/bas)
- Axe X = spectre acoustique ↔ électronique
- Axe Y = intensité / énergie (calme en bas, intense en haut)

---

## 9. API PHP (`api/genres.php`)

Même structure que `api/courants.php` dans Atlas Graphisme.

Retourne pour chaque genre :
```json
{
  "slug": "jazz",
  "nom": "Jazz",
  "periode": { "debut": 1900, "fin": null },
  "da": { "couleur": "#c9a84c", "typo": "\"Georgia\", serif" },
  "description_courte": "...",
  "description_longue": "...",
  "artistes": ["Miles Davis", "John Coltrane"],
  "key_points": "Improvisation, swing...",
  "mots_cles": ["improvisation", "swing", "blues"],
  "images": ["url1", "url2", "url3", "url4", "url5"],
  "audio_preview": "https://p.scdn.co/mp3-preview/...",
  "position": { "x": -3.2, "y": 0.5, "z": 12.6 },
  "niveau": 1,
  "relations": [{ "cible": "blues", "type": "derivation" }]
}
```

---

## 10. Son ambiant

Deux approches possibles :

**Option A — Son par ère (comme Atlas Graphisme) :**
- 12 fichiers audio (1900s, 1920s, 1940s, ..., 2010s)
- Crossfade 2s quand l'utilisateur navigue entre genres d'ères différentes
- Sources : Internet Archive (pre-1970) + Free Music Archive (post-1970)

**Option B — Son par genre (plus précis) :**
- `audio_preview` Spotify 30s joué quand on sélectionne un genre
- Fondu enchaîné entre genres
- Plus interactif, plus riche

**Recommandation :** Option A pour l'ambiance générale + Option B en preview au clic.

---

## 11. DA par genre — exemples

| Genre | Couleur | Typographie | Ère |
|---|---|---|---|
| Blues | `#1a3a5c` | Georgia, serif | 1900-1950 |
| Jazz | `#c9a84c` | Palatino, serif | 1910-présent |
| Rock | `#e63946` | Impact, sans-serif | 1950-présent |
| Electronic | `#00b4d8` | Helvetica Neue | 1970-présent |
| Hip-Hop | `#f72585` | Arial Black | 1980-présent |
| Ambient | `#9b5de5` | Futura, sans-serif | 1970-présent |
| Metal | `#2a2a2a` | Black Metal font | 1970-présent |
| Pop | `#ffb703` | Gill Sans | 1960-présent |

---

## 12. Fonctionnalités UI (identiques à Atlas Graphisme)

- ✅ Constellation 3D WebGL (Three.js) — nœuds, arêtes (tubes), particules
- ✅ Loading screen 3D — nœuds s'allument pendant preload
- ✅ Transition caméra post-loading (vue haute → position normale)
- ✅ BokehPass depth of field
- ✅ Dark/light theme toggle
- ✅ OrbitControls — drag orbite, scroll zoom, autoRotate
- ✅ Clic sur nœud → zoom + cartes flottantes CSS3D (images + fiche info)
- ✅ Lightbox galerie (5 images)
- ✅ Labels 3D billboard
- ✅ Mini-timeline horizontale cliquable
- ✅ Nav dots droite
- ✅ Son ambiant par ère (crossfade)
- ✅ Curseur personnalisé coloré
- ✅ Typo monumentale fond

**Spécifique Atlas Musical :**
- 🆕 Bouton lecture extrait audio (30s preview) au clic sur un genre
- 🆕 Visualiseur audio simple (cercle pulsant au rythme) — optionnel
- 🆕 Filtre par décennie sur la timeline

---

## 13. Admin

Même `admin.php` qu'Atlas Graphisme — adapté pour gérer `audio_preview` en plus des images.

Ajouter un champ URL audio + bouton de test lecture.

---

## 14. Deploy

```
Source/                 → sync.ps1 → deploy/   → FTP OVH /atlas-musical/
├── index.html
├── style.css
├── api/genres.php
├── scraper/fetch_*.php
└── sounds/ (fichiers ERA)
```

Ne jamais toucher `deploy/scraper/config.php` (credentials MySQL prod).

---

## 15. Ordre de build recommandé

```
1. DB schema + seed ~20 genres manuels (slug, nom, dates, couleur, position)
2. API PHP genres.php (READ only)
3. index.html — Three.js init + constellation vide
4. Connexion API → nœuds dans la scène
5. Navigation (clic, zoom, cartes)
6. Scrapers (Wikidata + Wikipedia + MusicBrainz)
7. Audio (Spotify previews + ERA files)
8. Loading screen 3D (pattern Atlas Graphisme)
9. Admin images + audio
10. Polish (DA, transitions, dark theme)
11. Métadonnées + crédits + deploy
```

---

## 16. Références Atlas du Graphisme (pour copier le code)

- Repo GitHub : `https://github.com/Math-prspx/Atlas.git`
- Prod : `https://www.nothuman.be/atlas/`
- Le code source de `index.html` est le meilleur point de départ pour le clone
- Architecture loading screen documentée dans `docs/README.md`

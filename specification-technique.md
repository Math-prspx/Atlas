# Spécification technique

## Projet
Plateforme immersive de culture graphique destinée à des étudiants en graphisme.

L'objectif est de permettre l'exploration des styles, courants et contextes historiques du graphisme à travers une interface très visuelle, non linéaire et mémorable.

## 1. Vision produit

Le projet ne doit pas fonctionner comme une encyclopédie classique. Il doit prendre la forme d'un système d'exploration visuelle, proche d'un atlas interactif ou d'un arbre de progression.

L'utilisateur doit pouvoir :
- explorer des courants graphiques
- comprendre leur contexte historique et culturel
- identifier leurs codes visuels
- comparer des styles proches ou opposés
- revenir facilement en arrière
- sentir une progression claire dans l'interface

## 2. Objectifs pédagogiques

Le produit doit aider des étudiants à mieux comprendre la culture graphique en reliant :
- les périodes historiques
- les courants et styles
- les artistes et figures majeures
- les objets visuels de référence
- les influences et oppositions entre mouvements

Le système doit répondre à trois questions essentielles :
1. Où se situe ce style dans le temps ?
2. Quels sont ses codes visuels ?
3. Comment se différencie-t-il des autres styles ?

## 3. Direction artistique

La direction retenue repose sur une expérience 3D minimaliste et immersive.

### 3.0 Références visuelles

Les images de référence sont dans le dossier `/img_ref/` du projet.

| Fichier | Description |
|---|---|
| `lord_prspx_..._5136895f-..._0.png` | Point d'origine lumineux, explosion de fils fins sur fond noir. Nœud de départ du système. Très haute densité, effet de vitesse. |
| `lord_prspx_..._cd9cfe08-..._1.png` | Vue rapprochée réseau. Nœuds brillants, lignes fines interconnectées, accent cyan discret sur certains points. Logique de connexion entre courants. |
| `lord_prspx_..._cd9cfe08-..._3.png` | Axe principal en profondeur sur Z, branches secondaires fines et éparses. Vue la plus lisible. Référence principale pour la navigation. |
| `lord_prspx_..._def6f6a8-..._0.png` | Vue d'ensemble du système depuis le bas, perspective forte. Nœuds petits et précis, branches géométriques. Hiérarchie macro. |
| `lord_prspx_..._def6f6a8-..._1.png` | Plan rapproché sur un nœud central lumineux avec départ de branches. Interaction au niveau d'un embranchement. |
| `lord_prspx_..._def6f6a8-..._2.png` | Version plus brute. Montre la limite du bruit visuel à ne pas dépasser. |

**Palette validée**
- Fond : noir pur `#000000`
- Nœuds inactifs : gris sombre `#2a2a2a` — presque invisibles
- Nœuds actifs : couleur accent du courant, avec halo
- Fils/tubes : blanc très transparent `rgba(255,255,255,0.12)`
- Particules de fond : blanc très discret `rgba(255,255,255,0.35)`
- Lumière : émissive via bloom post-processing, douce, pas de flare excessif

**Ce que ces images confirment**
- le fond noir est non négociable
- les fils doivent être fins, précis et lisibles
- les nœuds sont petits et discrets — ils ne s'allument qu'à la sélection
- les embranchements doivent rester rares pour ne pas créer de chaos
- le bruit visuel doit être contrôlé : les refs les plus efficaces sont les plus épurées
- la couleur accent apparaît uniquement sur l'élément actif

### 3.1 Principes visuels
- un axe principal en profondeur sur l'axe Z
- un fil, tube ou conduit central servant de trajectoire
- des nœuds, sphères ou points de décision
- des embranchements rares mais signifiants
- une sensation de traversée, de progression et d'exploration
- une esthétique sobre, lisible et élégante

### 3.2 Métaphores possibles
- carte céleste
- arbre de compétences
- réseau organique
- courant spatial
- trajectoire lumineuse

### 3.3 Ton visuel
Le système doit rester :
- minimaliste
- immersif
- sophistiqué
- pédagogique
- non gadget

### 3.4 Direction retenue
La direction choisie est **sombre et spatiale + DA changeante par courant**.
Les variantes minimaliste/typographique et tonalité unique ont été écartées.

## 4. Principes d'interface

L'interface doit se comporter comme un système vivant et navigable.

### 4.1 Structure générale
- un point d'origine
- un axe de progression principal
- des bifurcations
- des branches thématiques, historiques ou stylistiques
- des points d'arrêt clairs
- une hiérarchie entre éléments principaux et secondaires

### 4.2 Navigation — comportements validés

| Action | Résultat |
|---|---|
| Drag souris | Orbite libre autour du centre de la scène (OrbitControls) |
| Scroll | Zoom in / out |
| Clic sur un nœud | Zoom animé vers ce nœud + ouverture du panel latéral |
| Clic ailleurs | Fermeture du panel |
| Nav dots (droite) | Déplace la cible d'orbite vers le courant correspondant |
| Arrivée sur le nœud | OrbitControls reprend le contrôle total — orbite libre |

**Règles de navigation validées**
- Un seul nœud actif à la fois
- Le nœud précédent s'éteint quand un nouveau est sélectionné
- Le zoom s'arrête automatiquement une fois arrivé, sans bloquer l'orbite
- Le panel se ferme si on clique en dehors d'un nœud

### 4.3 Lisibilité
La profondeur et l'immersion doivent servir la compréhension. L'interface ne doit jamais devenir opaque.

Hiérarchie visuelle validée :
- nœuds gris = disponibles mais non explorés
- nœud coloré + halo = courant actif
- tubes blancs discrets = relations entre courants

## 5. Contenu de chaque courant

Chaque style, courant ou mouvement doit être représenté par une fiche riche et structurée.

### 5.1 Champs principaux
- nom du courant
- période
- contexte historique
- contexte culturel
- mots-clés
- principes visuels
- figures majeures
- artistes associés
- exemples visuels
- influences
- courants liés
- courants opposés

### 5.2 Champs optionnels
- objets de référence
- affiches
- typographies
- mises en page
- identités visuelles
- magazines ou publications
- exercices pédagogiques
- comparaison avec d'autres courants

## 6. Organisation éditoriale

Le produit doit être structuré comme un atlas vivant du graphisme.

### 6.1 Entrées possibles
- entrée par période
- entrée par style
- entrée par mot-clé visuel
- entrée par artiste
- entrée par comparaison

### 6.2 Types de vues
- vue d'exploration principale
- vue de fiche détaillée
- vue de comparaison
- vue de contexte historique
- vue de collection ou de branche

### 6.3 Logique de progression
La progression doit donner envie de cliquer et de débloquer de nouveaux contenus, comme dans un système de skill tree.

## 7. Règles de design

### 7.1 À faire
- privilégier la clarté
- conserver un axe principal très lisible
- limiter le bruit visuel
- utiliser l'espace et la profondeur comme langage
- rendre les embranchements significatifs
- créer une hiérarchie visuelle forte

### 7.2 À éviter
- surcharge d'effets
- chaos visuel
- interfaces type dashboard trop conventionnelles
- hiérarchie floue
- décor qui nuit à la compréhension

## 8. Direction technique

### 8.1 Cible
Le projet doit être pensé pour le web avec rendu 3D, hébergé sur OVH (PHP 8.1+).

### 8.2 Stack retenue

| Couche | Technologie | Remarque |
|---|---|---|
| Rendu 3D | **Three.js** (ES modules, CDN) | Choix définitif — validé en prototype |
| Front | HTML / CSS / JS vanilla | Pas de framework |
| Back | PHP 8.1+ | API REST JSON |
| Base de données | MySQL (prod) | Hébergement OVH |
| Déploiement | OVH | Pas de CI/CD requis pour l'instant |

### 8.3 Support écran
- **Desktop : priorité 1** — l'expérience immersive 3D est conçue pour grand écran
- **Mobile : priorité 2** — une version dégradée est acceptable (navigation simplifiée, pas de 3D complexe)
- Résolution desktop cible : 1280px minimum, optimisé pour 1920px

### 8.4 Architecture modulaire
L'architecture doit permettre d'ajouter de nouveaux courants sans modifier le code.
- données séparées du code
- rendu 3D découplé du contenu
- fiches de style chargées dynamiquement via API

### 8.5 Composants techniques — état d'avancement

| Composant | État |
|---|---|
| Scène 3D (nœuds, tubes, particules) | ✅ Prototype validé |
| Bloom post-processing (UnrealBloomPass) | ✅ Prototype validé |
| OrbitControls (orbite + zoom) | ✅ Prototype validé |
| Zoom animé vers nœud sélectionné | ✅ Prototype validé |
| Panel latéral avec fiche par courant | ✅ Prototype validé |
| DA changeante (couleur + typo par courant) | ✅ Prototype validé |
| Nav dots avec indicateur actif | ✅ Prototype validé |
| Labels 3D projetés sur les nœuds | ✅ Prototype validé |
| API PHP servant les données en JSON | ⬜ À faire |
| Base MySQL avec les 10 courants | ⬜ À faire |
| Comparaison entre deux styles | ⬜ À faire |
| Vue historique / timeline | ⬜ À faire |

### 8.6 DA changeante par courant
Lorsqu'un courant est sélectionné ou actif, trois éléments visuels se transforment :

| Élément | Comportement |
|---|---|
| **Couleur dominante** | Teinte accent qui colore les nœuds actifs, les fils sélectionnés et les textes clés |
| **Typographie** | La police affichée dans la fiche change selon le courant (serif, sans-serif, slab, mono, display...) |
| **Fond / environnement** | La densité, la teinte de fond et l'atmosphère de la scène 3D évoluent subtilement |

La transition entre deux courants doit être animée, fluide et lisible.

## 9. Modèle de données

Les données sont stockées en MySQL et servies via une API PHP en JSON. Elles doivent pouvoir être enrichies progressivement sans refonte.

### 9.1 Table principale : `courants`

```sql
CREATE TABLE courants (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nom             VARCHAR(120) NOT NULL,
  slug            VARCHAR(120) NOT NULL UNIQUE,
  periode_debut   SMALLINT,
  periode_fin     SMALLINT,
  description_courte   TEXT,
  description_longue   LONGTEXT,
  contexte_historique  LONGTEXT,
  contexte_culturel    LONGTEXT,
  mots_cles       TEXT,
  principes_visuels    TEXT,
  couleur_accent  VARCHAR(7),
  typographie     VARCHAR(80),
  ambiance_fond   VARCHAR(80),
  position_x      FLOAT,
  position_y      FLOAT,
  position_z      FLOAT,
  niveau          TINYINT DEFAULT 1
);
```

### 9.2 Table : `artistes`

```sql
CREATE TABLE artistes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(120) NOT NULL,
  slug        VARCHAR(120) NOT NULL UNIQUE,
  naissance   SMALLINT,
  deces       SMALLINT,
  nationalite VARCHAR(60),
  bio_courte  TEXT
);
```

### 9.3 Table : `objets_visuels`

```sql
CREATE TABLE objets_visuels (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  titre       VARCHAR(200),
  type        ENUM('affiche','couverture','logo','typographie','photo','autre'),
  auteur_id   INT,
  annee       SMALLINT,
  source      VARCHAR(255),
  image       VARCHAR(255),
  legende     TEXT,
  courant_id  INT
);
```

### 9.4 Table de relations : `courant_relations`

```sql
CREATE TABLE courant_relations (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  courant_source  INT NOT NULL,
  courant_cible   INT NOT NULL,
  type_relation   ENUM('influence','opposition','derivation','contemporain')
);
```

### 9.5 Données de départ — validées en prototype

Les 10 courants suivants sont déjà intégrés dans `index.html` avec positions 3D, couleurs et contenus :

| Courant | Période | Couleur accent suggérée |
|---|---|---|
| Arts & Crafts | 1880–1910 | `#8B6914` |
| Art Nouveau | 1890–1910 | `#5C7A3E` |
| Bauhaus | 1919–1933 | `#E63946` |
| Style suisse / International | 1950–1970 | `#1D3557` |
| Pop Art | 1955–1970 | `#FFB703` |
| Psychédélique | 1965–1975 | `#9B5DE5` |
| Postmodernisme | 1975–1995 | `#F72585` |
| Grunge graphique | 1985–2000 | `#6B4226` |
| Flat Design | 2010–2020 | `#00B4D8` |
| Pixel Art | 1975–présent | `#06D6A0` |

## 10. Expérience utilisateur

### 10.1 Parcours attendu
1. L'utilisateur arrive sur un point d'origine visuel fort.
2. Il observe le système principal et les premières branches.
3. Il choisit un courant ou une époque.
4. Il explore une fiche détaillée.
5. Il peut comparer ce courant à d'autres.
6. Il peut revenir au point précédent sans friction.

### 10.2 Comportements validés en prototype
- orbite libre à tout moment via drag
- zoom vers un nœud au clic, puis reprise de l'orbite libre
- nœuds gris éteints par défaut, colorés uniquement quand sélectionnés
- panel latéral qui s'ouvre avec typographie et couleur du courant
- un seul nœud actif à la fois
- nav dots actifs selon le nœud le plus proche de la caméra

## 11. État d'avancement

### Fait
- Prototype 3D fonctionnel (`index.html`, standalone, Three.js CDN)
- 10 courants avec données, positions, couleurs, typographies
- Navigation OrbitControls + zoom auto validée
- Panel latéral avec fiche par courant
- DA changeante par courant (couleur + typo)

### Prochaines étapes
1. Back PHP + base MySQL : migrer les données du prototype vers l'API
2. Enrichir les fiches : images, exercices, comparaisons
3. Vue comparaison entre deux courants
4. Optimisation mobile (dégradée)
5. Déploiement OVH

## 12. Philosophie du projet

Le projet doit être pensé comme :
- un outil d'apprentissage
- un objet visuel fort
- une expérience d'exploration
- un système évolutif
- un socle réutilisable pour plusieurs styles et périodes

## 13. Critères de réussite

Le projet sera considéré comme réussi si :
- l'étudiant comprend rapidement la logique du système
- l'interface donne envie de cliquer et d'explorer
- la 3D sert la lecture et non l'effet gratuit
- les courants sont différenciés visuellement et conceptuellement
- la navigation reste simple, claire et mémorisable
- la base est extensible à de nouveaux styles

## 14. Priorités restantes

### Priorité 1 ✅ — Terminée
Prototype 3D avec navigation, nœuds, tubes, bloom, panel, DA par courant.

### Priorité 2 — En cours
Migration vers back PHP + MySQL. Servir les données via API REST JSON.

### Priorité 3
Enrichissement des fiches : images d'œuvres, exercices pédagogiques, liens entre courants.

### Priorité 4
Vue comparaison : afficher deux courants côte à côte avec leurs différences.

### Priorité 5
Variations visuelles de fond/ambiance 3D selon le courant actif.

## 15. Consignes de travail pour Claude

- commencer par l'architecture la plus simple et la plus robuste
- éviter toute complexité non justifiée
- privilégier la lisibilité avant la sophistication
- proposer un prototype fonctionnel avant les raffinements
- garder la possibilité d'enrichir le système sans refonte complète

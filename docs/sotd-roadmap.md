# Atlas du Graphisme — Roadmap SOTD (Awwwards Site of the Day)

> Objectif : atteindre le niveau Awwwards SOTD.  
> Approche : par phases, pas de code avant validation des décisions structurantes.

---

## ⚠️ RÈGLES TECHNIQUES CRITIQUES — À ne jamais perdre dans un refactor

Ces points ont causé des bugs majeurs par le passé. Les relire avant toute modification de `index.html`.

| Règle | Détail |
|---|---|
| **Three.js init avant tout `await`** | `renderer`, `scene`, `camera`, `controls`, `composer`, `bokehPass` doivent être initialisés avant le premier fetch API |
| **Un seul `animate()`** | Démarre tôt, itère des tableaux vides sans crash — jamais de second loop |
| **`camera.up` intouchable** | Ne JAMAIS modifier `camera.up` — casse l'OrbitControls définitivement |
| **`autoRotateSpeed = 0.25`** | Doit être dans le bloc init controls — à ne pas perdre dans un refactor |
| **`controls.enabled = false` pendant zoom** | Bloque le drag OrbitControls pendant `isZooming` et `camTransition` |
| **Hit-testing cartes** | Via `PlaneGeometry` THREE.js invisible coplanaire à chaque carte CSS3D — ne pas utiliser les événements CSS pour les clics sur les cartes |
| **`db_now()` PHP uniquement** | Jamais `datetime('now')` ni `NOW()` en SQL direct |
| **Positions constellation** | Formule actuelle : `z = 8 - (debut - 1880) × 0.2`, multiplicateurs X/Y ×2.4, Z ×2.1 — à connaître avant de toucher aux positions en Phase 1B |
| **DB : 55 relations** | 22 courants niveau 1 + 15 niveau 2 = 37 nœuds, 55 edges en prod |
| **Admin prod uniquement** | `admin.php` n'existe qu'en prod MySQL — pas de version locale. Éditer directement dans `deploy/admin.php` |
| **sync.ps1** | Copie `index.html`, `style.css`, `api/courants.php`, `scraper/fetch_*.php` — ne touche pas `deploy/scraper/config.php` ni `deploy/admin.php` |

---

## ✅ DÉCISIONS PRÉALABLES — Validées 2026-06-26

- [x] **Langue** — **Anglais** (portée Awwwards maximale + LinkedIn global)
- [x] **Mobile strategy** — **Timeline 3D uniquement sur mobile** (pas de constellation) — décision révisée 2026-06-27

---

## Robustesse & accessibilité ✅ IMPLÉMENTÉ 2026-06-27

- [x] **pixelRatio** — `Math.min(devicePixelRatio, 1.5)` — déjà en place
- [x] **visibilitychange** — `_paused` stoppe animate loop + fade audio à 0 ; reprise au retour
- [x] **prefers-reduced-motion** — `_reducedMotion` flag, `controls.autoRotate = !_reducedMotion`
- [x] **Touch raycaster** — listener `touchend` dédié (seuil 12px / 400ms), bypass `pointerMoved`
- [x] **Seuil tap mobile** — `pointermove` threshold 5px desktop → 15px mobile

---

## Phase 1 — Concept & Onboarding ★★★★★ (la plus critique)

> Le jury Awwwards passe ~2 min sur un site. Les 15 premières secondes décident tout.
> Objectif : que l'utilisateur comprenne *immédiatement* ce que c'est et veuille explorer.

### 1A — Refonte loading screen ✅ IMPLÉMENTÉ + REFONDU 2026-06-27

- Cascade de 37 nœuds en ligne horizontale (ordre chronologique 1880→2020) pendant le loading
- Chaque nœud apparaît à 70ms d'intervalle + flash du nom du courant en overlay
- Texte narratif 3 lignes (Syne) conservé, s'anime en parallèle
- Bouton Enter dès que loading complet (pas de délai forcé)
- **Au clic** : LS fade out 0.5s → nœuds filent vers positions constellation (lerp 0.027, ~4s)
- Une fois en place : labels + tubes apparaissent, 700ms de contemplation → caméra plonge
- Flag `_sceneRevealed` : tubes et labels cachés jusqu'à ce que les nœuds soient en position
- Dark theme : `data-theme` défini dès le départ, bouton Enter adapté au thème, tubes à la bonne couleur

---

### 1B — Autres items onboarding

- [ ] **Axe temporel — Mode "Timeline" à bascule** ✅ IMPLÉMENTÉ 2026-06-27
  - Bouton icône (ligne + 3 dots) à côté de ♫ et ◐ — toggle ON/OFF
  - 37 nœuds lerp vers axe Z trié par date (1880 devant, 2020 au fond, 28 unités)
  - Spread X/Y déterministe par slug (hash) — N1 yRange ±2.4 / N2 yRange ±3.5
  - Tubes redessinés avec la couleur du thème actif, même opacité que constellation
  - Navigation ←→ clavier + 2 boutons flèches → `navigateTo()` complet (zoom, cards, panel, audio, timeline bar)
  - **Navigation ←→ aussi en constellation** — même logique, `tlNodeOrder` partagé
  - `pendingReopen` : nœud actif rouverte automatiquement dans les deux sens (enter ET exit)
  - Caméra mémorisée à l'aller ET au retour (nœud actif conservé entre les deux modes)
  - Tubes constellation réapparaissent APRÈS les nœuds (symétrique avec le mode timeline)
  - OrbitControls actif en permanence, coupé uniquement pendant `isZooming`
  - Escape hiérarchique : panel → exit timeline
- [ ] **Légende des connexions** — ✅ La DB a déjà tout : table `courant_relations` avec `source_id → cible_id` (direction) + `type_relation` ENUM (`influence`, `opposition`, `derivation`, `contemporain`). L'API retourne déjà le `type` par relation. **Le JS l'ignore actuellement.** À exploiter :
  - Visuel par type : `influence` = trait plein, `opposition` = pointillé, `derivation` = tirets, `contemporain` = opacity faible
  - Direction : flèche ou dégradé sur le tube (source → cible)
  - Légende discrète dans l'UI expliquant les 4 types
  - *(Chantier Phase 2 — visual finish des edges)*
- [x] **Hover micro-info** ✅ IMPLÉMENTÉ — Au survol d'un nœud :
  - Scale up 1.0→1.4 (lerp 0.14/frame), couleur accent révélée
  - Edges connectés : opacity ×3 (lerp 0.15/frame)
  - `#hover-tip` : nom + date sous le curseur (Inter, 9px, 0.25em spacing)
  - `hoveredNodeMesh` + `edgeMesh.userData.fromId/toId` ajoutés
- [x] **Hint contextuel post-entrée** — ✅ Décision : **rien**. L'intro narrative + le hover réactif (nœud scale up, curseur coloré) + les labels billboard suffisent. Sur-expliquer = infantiliser un public design-savvy.

---

## Phase 2 — Visual Finish ★★★★☆ ✅ COMPLÈTE 2026-06-27

- [x] Typographie Syne+Inter ✅
- [x] Palette + curseur + transitions panel ✅
- [x] Hover boutons ✅
- [x] **Mode tab centré** — pill `⊹ Constellation / ⇌ Timeline`, actif = fond `var(--fg)`, fade-in post-orbit (delay 1.8s). `updateModeTab()` synchronise l'état.
- [x] **◐ thème** top left, **♫ audio** top right — 22px, opacity 0.5
- Edges animés = décision : non (hover uniquement)

---

## Phase 3 — Contenu éditorial ★★★☆☆

> Sortir du contenu Wikipedia brut. Les jurés lisent ce qu'ils trouvent.

- [x] **Descriptions réécrites** ✅ IMPLÉMENTÉ — 37 descriptions EN dans `description_longue` via `scraper/seed_content_en.sql`. Angle graphic design uniquement.

- [x] **Citations emblématiques** ✅ IMPLÉMENTÉ — Colonnes `citation`+`citation_auteur` ajoutées, API mise à jour, panel affiche le bloc citation (Syne italic + cite Inter). 37 citations seedées.

- [ ] **Artistes** — Script `scraper/trim_artistes.php` prêt (token AtlasRun2024). **Low priority.**

- [x] **Images** ✅ Déjà fait.

- [ ] **Couleurs accent** — À attribuer dans admin.php (à faire plus tard par l'utilisateur).

- [x] **Langue unifiée** ✅ Anglais.

---

## Phase 4 — Mobile Strategy ★★★★☆

> Breakpoint : **1024px** — en dessous = mobile/tablette.

### Architecture mobile ✅ Décisions arrêtées

**Expérience mobile = Timeline 3D uniquement** (pas de constellation)
- La vue constellation reste desktop-only
- La vue timeline 3D (développée pour le toggle desktop) devient l'expérience principale mobile
- Même scène Three.js, caméra repositionnée pour regarder le long de l'axe Z

**Disposition des nœuds en mode Timeline 3D :**
- Z = temps (`periode_debut` normalisé → profondeur dans le couloir)
- Y = léger spread aléatoire par nœud → clusters visuels naturels par ère
- X = léger spread aléatoire pour éviter la ligne parfaite

**Navigation touch :**
- Scroll vertical (swipe up/down) = caméra avance/recule sur l'axe Z (dans le couloir temporel)
- Tap nœud = sélection → bottom sheet monte
- 2 doigts = pinch zoom (OrbitControls natif conservé)

**Panel info mobile — Bottom sheet :**
- Hauteur : 75% de l'écran max, scène 3D visible au-dessus
- Zone haute : **carousel horizontal** des 6 images (swipe gauche/droite), tap = lightbox
- Zone basse : scroll vertical — nom, dates, citation, description, artistes, key points
- Swipe down sur la poignée = fermer

**Bugs à corriger :**
- [ ] **Nœuds non cliquables sur mobile** (bug prod connu) — le raycaster ne reçoit pas les coordonnées touch correctement. Fix : listener `touchend` dédié qui mappe les coordonnées touch vers le raycaster, indépendamment d'OrbitControls.
- [ ] **Audio iOS Safari** — autoplay bloqué sans interaction. Fix : déclencher `AudioContext.resume()` au clic sur le bouton Enter (déjà une interaction user). Bouton mute reste visible et fonctionnel.

**Performance mobile :**
- [ ] `renderer.setPixelRatio(Math.min(devicePixelRatio, 1.5))` — déjà en place ✅
- [ ] Désactiver bokehPass sur mobile (coûteux en GPU) — rendu simple sans post-processing
- [ ] Réduire la géométrie des tubes edge (segments moins nombreux) sur mobile

**Tests :**
- [ ] iOS Safari (iPhone 12+)
- [ ] Android Chrome (mid-range)
- [ ] Tablette portrait + paysage (1024px border case)

---

## Phase 5 — Finitions Awwwards ★★★☆☆

> Ce qui transforme un bon projet en candidat sérieux.

- [ ] **OG tags complets** ✅ Direction arrêtée :
  - `og:title` : *"Atlas of Graphic Design"*
  - `og:description` : à rédiger (max 200 chars, ton éditorial)
  - `og:image` : 1200×630px — screenshot de la constellation + titre Syne superposé, réalisé dans Figma
  - `og:url`, `twitter:card: summary_large_image`
  - À faire après le visual finish (Phase 2) pour que la capture soit au niveau

- [ ] **`<title>` et `<meta description>`** — Actuellement `<title>Atlas du Graphisme</title>` → passer en anglais + ajouter meta description cohérente avec og:description.

- [ ] **Branding + Crédits** ✅ Direction arrêtée :
  - Coin **bas-gauche** : deux liens discrets en petite typo
    - *"Powered by* **Not Human**" — "Not Human" = lien vers `nothuman.be`
    - *"Credits"* — ouvre un **overlay** (lightbox discret) listant les sources des images (attribution copyright)
  - Pas de logo, pas de page About, pas de contact explicite — minimaliste

- [ ] **Analytics** ✅ Direction arrêtée : **Plausible.io ou Fathom** — privacy-first, sans cookies, sans RGPD, sans cookie bar. Zéro friction sur l'expérience d'entrée. Script < 1kb.

- [ ] **Performance audit** — Lighthouse score > 90 sur desktop. Points à vérifier : Three.js CDN (déjà optimisé), lazy loading images dans les panels, preload audio géré.

- [ ] **`visibilitychange`** — Pause audio + stoppage `animate()` quand l'onglet passe en arrière-plan.

- [ ] **`prefers-reduced-motion`** — Désactiver autoRotate + réduire lerp caméra.

- [ ] **Permalien par nœud** — `?courant=bauhaus` dans l'URL au clic sur un nœud. Au chargement, détecter le paramètre et zoomer directement sur le nœud concerné. Fort pour partage LinkedIn.

- [ ] **Soumission Awwwards** — Description projet (max 300 chars), tags (`3D`, `Cultural`, `Educational`, `WebGL`), catégorie, preview vidéo optionnelle (15-30s screen capture de la séquence intro + exploration).

---

## Résumé des priorités

| # | Phase | Effort | Impact SOTD |
|---|---|---|---|
| ✅ | Décisions préalables | — | Validées : EN + Responsive |
| 1 | Concept & Onboarding | Moyen | ★★★★★ |
| 2 | Visual Finish | Moyen | ★★★★☆ |
| 3 | Contenu éditorial | Élevé (humain) | ★★★☆☆ |
| 4 | Mobile — Timeline 3D | Élevé | ★★★★☆ |
| 5 | Finitions Awwwards | Faible | ★★★☆☆ |

**Ordre d'implémentation recommandé :**
1. Phase 1A — Refonte loading screen (intro narrative + Enter + plongeon caméra)
2. Phase 2 — Typo (Syne + Inter) + curseur + hover nœud + couleurs accent
3. Phase 1B — Légende edges + hover tooltip + types de relations visuels
4. Phase 3 — Génération contenu (descriptions + citations) + couleurs 37 courants
5. Phase 1B — Mode Timeline 3D (toggle desktop)
6. Phase 4 — Mobile (timeline 3D + bottom sheet + bugs touch/audio)
7. Phase 5 — OG tags + analytics + permaliens + soumission

---

*Doc complétée : 2026-06-26*

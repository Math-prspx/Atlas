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
- [x] **Mobile strategy** — **Responsive complet** (Phase 4, après Phases 1-2-3 finalisées sur desktop)

---

## Phase 1 — Concept & Onboarding ★★★★★ (la plus critique)

> Le jury Awwwards passe ~2 min sur un site. Les 15 premières secondes décident tout.
> Objectif : que l'utilisateur comprenne *immédiatement* ce que c'est et veuille explorer.

### 1A — Refonte complète du loading screen ✏️ Spécifications validées

**Concept général :** l'écran de chargement devient une séquence narrative cinématique. L'utilisateur lit, anticipe, puis choisit d'entrer. Il agit — il ne subit plus.

**Fond :** blanc pur (thème principal du site = light). Pas de scène 3D derrière.

**Contenu — hiérarchie verticale centrée :**
```
[ligne 1]  Design doesn't happen in a vacuum.
[ligne 2]  37 movements. 150 years of visual history.
[ligne 3]  Atlas of Graphic Design.

           [  %  ]       ← chiffre centré, taille modérée (pas gigantesque)

           [ Enter ]     ← apparaît quand loading + séquence sont terminés
```

**Rythme et timing :**
- Ligne 1 apparaît à t = 0s (fade in)
- Ligne 2 apparaît à t = 2s
- Ligne 3 apparaît à t = 4s
- Durée minimum garantie : **6s** — même si le chargement est instantané
- Si chargement fini avant 6s → on attend la fin de la séquence, puis Enter apparaît
- Si chargement fini après 6s → Enter apparaît dès que le chargement est prêt
- Le `%` monte en parallèle, indépendamment du rythme narratif

**Bouton Enter :**
- Apparaît en fade in une fois les deux conditions remplies (séquence + chargement)
- Au clic : les lignes et le `%` s'effacent (fade out), la scène 3D se révèle
- ✅ **Transition Enter** — plongeon caméra séquencé : l'intro fade out, puis `camTransition = true` déclenche le lerp existant `(0,35,18) → (6.75,5.25,24.75)` en 2s. Réutilise le code existant, zéro rewrite.

**Hint :**
- Remplacer `"Drag pour orbiter · Scroll pour zoomer · Clic pour explorer"` par :
  *"Drag to orbit · Scroll to zoom · Click to explore"*
- Ou supprimer entièrement (à décider en Phase 2 selon le visual finish)

---

### 1B — Autres items onboarding

- [ ] **Axe temporel — Mode "Timeline" à bascule** ✅ Option B retenue
  - Deux modes : **Constellation** (positions manuelles actuelles, effet orbital) + **Timeline** (nœuds réarrangés sur X = temps, animation lerp)
  - Constellation → Timeline : chaque nœud lerpe vers sa position calculée (`X = (periode_debut - 1880) / 140 * spread`), edges mis à jour, labels CSS3D suivent
  - Toggle : bouton discret dans l'UI (position à définir)
  - ⚠️ À décider avant implémentation :
    - Y en mode Timeline : tous à Y=0 (ligne plate) ou spread vertical léger anti-overlap ?
    - La caméra se repositionne-t-elle automatiquement pour avoir la meilleure vue de la ligne ?
    - Position du toggle dans l'UI ?
  - *(Chantier lourd — à faire après Phase 1A et les autres items 1B)*
- [ ] **Légende des connexions** — ✅ La DB a déjà tout : table `courant_relations` avec `source_id → cible_id` (direction) + `type_relation` ENUM (`influence`, `opposition`, `derivation`, `contemporain`). L'API retourne déjà le `type` par relation. **Le JS l'ignore actuellement.** À exploiter :
  - Visuel par type : `influence` = trait plein, `opposition` = pointillé, `derivation` = tirets, `contemporain` = opacity faible
  - Direction : flèche ou dégradé sur le tube (source → cible)
  - Légende discrète dans l'UI expliquant les 4 types
  - *(Chantier Phase 2 — visual finish des edges)*
- [ ] **Hover micro-info** — Au survol d'un nœud :
  - Le nœud scale up légèrement (×1.3) + émission lumineuse douce
  - Ses edges connectés s'illuminent avec leur couleur de type
  - Tooltip léger sous le curseur : nom + date + type de relations connectées
  - *(Données disponibles sans back-end supplémentaire)*
- [x] **Hint contextuel post-entrée** — ✅ Décision : **rien**. L'intro narrative + le hover réactif (nœud scale up, curseur coloré) + les labels billboard suffisent. Sur-expliquer = infantiliser un public design-savvy.

---

## Phase 2 — Visual Finish ★★★★☆

> Les SOTD ont une cohérence visuelle absolue et du micro-détail partout.

- [ ] **Typographie** ✅ Décision arrêtée :
  - **Titres / UI principale** : `Syne` (Google Fonts) — identité visuelle forte, monde art/design, gratuite
  - **Corps de texte / panels** : `Inter` (Google Fonts) — ultra-lisible, écran-first, meilleur rendu que Roboto sur haute résolution
  - Actuellement : Helvetica Neue système partout → à remplacer
  - Usage : Syne sur `#title`, intro narrative, labels 3D, timeline — Inter sur panels info, tooltips, descriptions
  - Le `bg-typo` peut aussi passer en Syne avec letter-spacing extrême pour renforcer la signature
- [ ] **Palette de couleurs** ✅ Direction arrêtée — **couleur comme récompense d'interaction** :
  - Au repos : constellation monochrome (nœuds noir/gris foncé en dark, blanc/gris clair en light) — minimal, high-tech
  - Hover : le nœud révèle sa `couleur_accent` (scale up + couleur)
  - Sélectionné : couleur pleine + ring
  - Panel info : `couleur_accent` utilisée pour titres, séparateurs, accents UI
  - ⚠️ Travail éditorial : attribuer une `couleur_accent` distinctive aux 37 courants dans admin.php (certains évidents : Bauhaus = rouge/noir, De Stijl = primaires, Memphis = multicolore). À faire en Phase 3.
- [ ] **Curseur personnalisé** ✅ Direction : purement visuel (forme + couleur), pas de texte intégré — double emploi avec les labels billboard.
  - Amélioration : ajouter `transition: width 150ms, height 150ms, background 150ms, border-color 150ms` — le curseur "fond" vers la couleur accent au survol au lieu de switcher instantanément
  - Cohérent avec le système couleur : révèle la `couleur_accent` avant même le nœud
- [ ] **Transitions de panel** ✅ Direction arrêtée :
  - Entrée : `scale(0.88) + translateY(12px) → scale(1) + translateY(0)`, durée `480ms`, easing `cubic-bezier(0.16, 1, 0.3, 1)` (ease-out expo)
  - Stagger conservé entre cartes (54ms) — à affiner à l'usage
  - Fermeture : fade-out avant suppression du DOM (actuellement instantané) — ajouter `visible` class removal + setTimeout 300ms avant `cssScene.remove()`
  - À affiner visuellement une fois implémenté
- [ ] **Loading screen** — ✅ Entièrement redéfini par Phase 1A (Syne + Inter, fond blanc, séquence narrative, bouton Enter, plongeon caméra). Pas de décisions supplémentaires ici — implémentation couplée à 1A.
- [ ] **Hover states sur tous les éléments UI** — Base existante correcte (transitions opacity/background 0.3s). À améliorer :
  - Boutons `#btn-mute`, `#btn-theme` : ajouter `transform: scale(1.1)` au hover (statiques actuellement)
  - Nav dots + tl-dots : déjà transitionné, à vérifier cohérence avec nouvelle palette
  - Tous les éléments cliquables : s'assurer que `cursor: pointer` + retour visuel sont présents partout
  - À affiner visuellement pendant l'implémentation — pas de décision bloquante
- [x] **Edges animés** — ✅ Décision : **pas d'animation au repos** (particules = kitsch + lourd, pulse = overhead constant). Les edges restent statiques mais bien traités (épaisseur + couleur par type de relation). L'animation se limite au hover : les edges du nœud survolé s'illuminent (opacity boost instantané). Zéro overhead en idle.

---

## Phase 3 — Contenu éditorial ★★★☆☆

> Sortir du contenu Wikipedia brut. Les jurés lisent ce qu'ils trouvent.

- [ ] **Descriptions réécrites** ✅ Direction arrêtée :
  - **Une seule description** par courant (5-6 phrases), ton hybride : phrase évocatrice d'ouverture + contenu factuel substantiel
  - Remplace à la fois `description_courte` et `description_longue` actuelles (Wikipedia brut)
  - En anglais
  - Généré par Copilot, validé par toi — à faire en une session dédiée sur les 37 courants
  - Colonne DB à utiliser : `description_longue` (on vide `description_courte` ou on la réutilise pour un résumé 1 phrase)

- [ ] **Citations emblématiques** ✅ Direction arrêtée :
  - 1 citation par courant (designer, théoricien, manifeste)
  - Affichage dans le panel : bloc mis en valeur — typographie distinctive (Syne italic, taille +), séparateur visuel, fond léger
  - Nouvelle colonne DB à ajouter : `citation` (TEXT) + `citation_auteur` (VARCHAR)
  - Généré par Copilot, validé par toi
- [ ] **Artistes** ✅ Règle éditoriale : **3 noms max** par courant, les plus représentatifs. À vérifier/ajuster dans admin.php lors de la session contenu.

- [x] **Images** ✅ Déjà fait — 6 images par courant, sélectionnées une à une pour cohérence visuelle et qualité, en prod DB. Rien à faire.

- [ ] **Éliminer les fallback data anglais** — Le `FALLBACK_CONTENT` hardcodé dans index.html (données en anglais) doit être supprimé ou réduit au strict minimum. L'API prod est fiable — le fallback ne doit être qu'un filet de sécurité vide, pas une source de contenu alternatif. À faire lors du chantier Phase 1A (refonte JS).
- [x] **Langue unifiée** ✅ — Anglais, décision validée en amont.

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

<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — Admin images
// ─────────────────────────────────────────────────────────────────
session_start();
require_once __DIR__ . '/scraper/config.php';

define('ADMIN_PASS', 'AtlasAdmin2024'); // Changer si besoin

// Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Login
if (isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin_ok'] = true;
    } else {
        $login_error = true;
    }
}

$logged_in = $_SESSION['admin_ok'] ?? false;

// Save
$saved = false;
if ($logged_in && isset($_POST['save'])) {
    $pdo = db();
    $stmt = $pdo->prepare(
        "UPDATE courants SET image_wikidata=:img1, image_wikipedia=:img2, image_3=:img3, image_4=:img4, image_5=:img5 WHERE slug=:slug"
    );
    $pdo->beginTransaction();
    foreach ($_POST['courants'] ?? [] as $slug => $data) {
        $stmt->execute([
            ':img1' => trim($data['image_1']) ?: null,
            ':img2' => trim($data['image_2']) ?: null,
            ':img3' => trim($data['image_3']) ?: null,
            ':img4' => trim($data['image_4']) ?: null,
            ':img5' => trim($data['image_5']) ?: null,
            ':slug' => $slug,
        ]);
    }
    $pdo->commit();
    $saved = true;
}

// Fetch courants
$courants = [];
if ($logged_in) {
    $pdo = db();
    // Fallback gracieux si colonnes absentes
    try {
        $courants = $pdo->query(
            "SELECT slug, nom, periode_debut, periode_fin, image_wikidata, image_wikipedia, image_3, image_4, image_5 FROM courants ORDER BY id"
        )->fetchAll();
    } catch (\Exception $e) {
        try {
            $courants = $pdo->query(
                "SELECT slug, nom, periode_debut, periode_fin, image_wikidata, image_wikipedia, image_3, image_4, NULL AS image_5 FROM courants ORDER BY id"
            )->fetchAll();
        } catch (\Exception $e2) {
            $courants = $pdo->query(
                "SELECT slug, nom, periode_debut, periode_fin, image_wikidata, image_wikipedia, NULL AS image_3, NULL AS image_4, NULL AS image_5 FROM courants ORDER BY id"
            )->fetchAll();
        }
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — Atlas du Graphisme</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: #0d0d0d;
    color: #e0e0e0;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 14px;
    min-height: 100vh;
  }

  /* ── Login ── */
  .login-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
  }
  .login-box {
    background: #1a1a1a;
    border: 1px solid #2a2a2a;
    border-radius: 8px;
    padding: 40px 48px;
    width: 340px;
    text-align: center;
  }
  .login-box h1 { font-size: 18px; font-weight: 500; margin-bottom: 6px; }
  .login-box p { font-size: 12px; color: #666; margin-bottom: 28px; }
  .login-box input[type="password"] {
    width: 100%;
    background: #111;
    border: 1px solid #333;
    border-radius: 4px;
    color: #e0e0e0;
    font-size: 14px;
    padding: 10px 14px;
    margin-bottom: 12px;
    outline: none;
  }
  .login-box input[type="password"]:focus { border-color: #555; }
  .btn-primary {
    width: 100%;
    background: #e0e0e0;
    color: #0d0d0d;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    padding: 10px;
    cursor: pointer;
    letter-spacing: .04em;
    text-transform: uppercase;
  }
  .btn-primary:hover { background: #fff; }
  .error { color: #e05555; font-size: 12px; margin-top: 10px; }

  /* ── Admin layout ── */
  header {
    background: #111;
    border-bottom: 1px solid #222;
    padding: 16px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
  }
  header h1 { font-size: 15px; font-weight: 500; letter-spacing: .04em; }
  header span { font-size: 12px; color: #555; }
  .header-actions { display: flex; gap: 10px; align-items: center; }
  .btn-save {
    background: #e0e0e0;
    color: #0d0d0d;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 20px;
    cursor: pointer;
    letter-spacing: .05em;
    text-transform: uppercase;
  }
  .btn-save:hover { background: #fff; }
  .btn-logout {
    background: none;
    border: 1px solid #333;
    border-radius: 4px;
    color: #666;
    font-size: 12px;
    padding: 7px 14px;
    cursor: pointer;
  }
  .btn-logout:hover { border-color: #555; color: #aaa; }

  .saved-notice {
    background: #0a2a0a;
    border: 1px solid #1a4a1a;
    border-radius: 4px;
    color: #4caf50;
    font-size: 12px;
    padding: 10px 32px;
    text-align: center;
  }

  main { padding: 32px; max-width: 1600px; margin: 0 auto; }

  /* ── Table ── */
  /* ── Filter bar ── */
  .filter-bar {
    padding: 16px 32px 0;
    max-width: 1600px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .filter-bar label { font-size: 11px; color: #555; letter-spacing: .06em; text-transform: uppercase; white-space: nowrap; }
  .filter-bar select {
    background: #111;
    border: 1px solid #2a2a2a;
    border-radius: 4px;
    color: #ccc;
    font-size: 13px;
    padding: 7px 12px;
    outline: none;
    min-width: 260px;
    cursor: pointer;
  }
  .filter-bar select:focus { border-color: #444; }
  .filter-count { font-size: 11px; color: #444; }

  .grid-header {
    display: grid;
    grid-template-columns: 170px 1fr 1fr 1fr 1fr 1fr;
    gap: 10px;
    padding: 0 12px 10px;
    border-bottom: 1px solid #222;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #555;
  }

  .courant-row {
    display: grid;
    grid-template-columns: 170px 1fr 1fr 1fr 1fr 1fr;
    gap: 10px;
    align-items: start;
    padding: 14px 12px;
    border-bottom: 1px solid #1a1a1a;
  }
  .courant-row:hover { background: #121212; }

  .courant-label { padding-top: 6px; }
  .courant-label strong { display: block; font-size: 13px; font-weight: 500; }
  .courant-label em { font-size: 11px; color: #555; font-style: normal; }

  /* ── Image field ── */
  .img-field {}
  .img-field input[type="text"] {
    width: 100%;
    background: #111;
    border: 1px solid #2a2a2a;
    border-radius: 4px;
    color: #ccc;
    font-size: 12px;
    font-family: 'Helvetica Neue', monospace;
    padding: 7px 10px;
    outline: none;
    margin-bottom: 6px;
  }
  .img-field input[type="text"]:focus { border-color: #444; }
  .img-field input[type="text"]::placeholder { color: #3a3a3a; }

  .preview-wrap {
    position: relative;
    height: 80px;
    background: #0a0a0a;
    border: 1px solid #1e1e1e;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .preview-wrap img {
    max-width: 100%;
    max-height: 78px;
    object-fit: contain;
    display: none; /* shown via JS */
  }
  .preview-empty {
    font-size: 11px;
    color: #333;
    letter-spacing: .04em;
  }
  .preview-error { font-size: 11px; color: #c0392b; }
</style>
</head>
<body>

<?php if (!$logged_in): ?>
<!-- ── LOGIN ───────────────────────────────────────────────── -->
<div class="login-wrap">
  <div class="login-box">
    <h1>Atlas du Graphisme</h1>
    <p>Interface d'administration</p>
    <form method="post" autocomplete="off">
      <input type="password" name="password" placeholder="Mot de passe" autofocus>
      <button type="submit" class="btn-primary">Connexion</button>
      <?php if (isset($login_error)): ?>
      <p class="error">Mot de passe incorrect.</p>
      <?php endif; ?>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ── ADMIN ───────────────────────────────────────────────── -->
<form method="post">
<input type="hidden" name="save" value="1">

<header>
  <div>
    <h1>Atlas du Graphisme — Images</h1>
    <span><?= count($courants) ?> mouvements</span>
  </div>
  <div class="header-actions">
    <button type="submit" class="btn-save">Enregistrer tout</button>
    <button type="submit" name="logout" value="1" class="btn-logout" formnovalidate>Déconnexion</button>
  </div>
</header>

<?php if ($saved): ?>
<div class="saved-notice">Modifications enregistrées avec succès.</div>
<?php endif; ?>

<!-- ── Filter bar ── -->
<div class="filter-bar">
  <label for="movement-filter">Afficher</label>
  <select id="movement-filter" onchange="filterMovement(this.value)">
    <option value="">— Tous les mouvements (<?= count($courants) ?>)</option>
    <?php foreach ($courants as $c): ?>
    <option value="<?= htmlspecialchars($c['slug']) ?>">
      <?= htmlspecialchars($c['nom']) ?><?= $c['periode_debut'] ? ' (' . $c['periode_debut'] . ($c['periode_fin'] ? '–' . $c['periode_fin'] : '+') . ')' : '' ?>
    </option>
    <?php endforeach; ?>
  </select>
  <span class="filter-count" id="filter-count"></span>
</div>

<main>
  <div class="grid-header" id="grid-header">
    <div>Mouvement</div>
    <div>Image 1</div>
    <div>Image 2</div>
    <div>Image 3</div>
    <div>Image 4</div>
    <div>Image 5</div>
  </div>

  <?php foreach ($courants as $c): ?>
  <?php $slug_key = htmlspecialchars($c['slug']); ?>
  <div class="courant-row" data-slug="<?= $slug_key ?>">

    <div class="courant-label">
      <strong><?= htmlspecialchars($c['nom']) ?></strong>
      <?php if ($c['periode_debut']): ?>
      <em><?= $c['periode_debut'] ?><?= $c['periode_fin'] ? '–' . $c['periode_fin'] : '+' ?></em>
      <?php endif; ?>
    </div>

    <?php
    $img_vals = [
      1 => $c['image_wikidata']  ?? '',
      2 => $c['image_wikipedia'] ?? '',
      3 => $c['image_3']         ?? '',
      4 => $c['image_4']         ?? '',
      5 => $c['image_5']         ?? '',
    ];
    $field_keys = [1 => 'image_1', 2 => 'image_2', 3 => 'image_3', 4 => 'image_4', 5 => 'image_5'];
    foreach ($img_vals as $n => $val): ?>
    <div class="img-field">
      <input
        type="text"
        name="courants[<?= $slug_key ?>][<?= $field_keys[$n] ?>]"
        value="<?= htmlspecialchars($val) ?>"
        placeholder="URL image ou commons.wikimedia.org/wiki/File:..."
        onchange="handleInput(this)"
        oninput="handleInput(this)"
      >
      <div class="preview-wrap">
        <span class="preview-empty">aucune image</span>
        <img alt="" src="<?= htmlspecialchars($val) ?>"
             onload="onImgLoad(this)" onerror="onImgError(this)">
      </div>
    </div>
    <?php endforeach; ?>

  </div>
  <?php endforeach; ?>
</main>
</form>

<script>
// ── Filter ──────────────────────────────────────────────────────
function filterMovement(slug) {
  const rows  = document.querySelectorAll('.courant-row');
  const header = document.getElementById('grid-header');
  let visible = 0;
  rows.forEach(row => {
    const show = !slug || row.dataset.slug === slug;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  const count = document.getElementById('filter-count');
  count.textContent = slug ? '1 mouvement affiché' : '';
  // Scroll to first visible row
  if (slug) {
    const row = document.querySelector(`.courant-row[data-slug="${slug}"]`);
    if (row) row.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

// ── Preview ──────────────────────────────────────────────────────
function setPreview(input, url) {
  const wrap  = input.nextElementSibling;
  const img   = wrap.querySelector('img');
  const label = wrap.querySelector('span');
  if (!url) {
    img.style.display = 'none';
    label.className = 'preview-empty'; label.style.display = ''; label.textContent = 'aucune image';
    return;
  }
  label.className = 'preview-empty'; label.textContent = 'chargement…'; label.style.display = '';
  img.src = url;
}

function onImgLoad(img) {
  img.style.display = 'block';
  const label = img.previousElementSibling;
  if (label) label.style.display = 'none';
}

function onImgError(img) {
  img.style.display = 'none';
  const label = img.previousElementSibling;
  if (label) { label.className = 'preview-error'; label.textContent = 'URL invalide / image non trouvée'; label.style.display = ''; }
}

// Résolution via API Wikimedia Commons
function resolveCommonsUrl(input, filename) {
  const apiUrl = 'https://commons.wikimedia.org/w/api.php?action=query'
    + '&titles=File:' + encodeURIComponent(filename)
    + '&prop=imageinfo&iiprop=url&format=json&origin=*';

  const wrap  = input.nextElementSibling;
  const label = wrap.querySelector('span');
  label.className = 'preview-empty'; label.textContent = 'résolution…'; label.style.display = '';

  fetch(apiUrl)
    .then(r => r.json())
    .then(data => {
      const pages = data.query.pages;
      const page  = Object.values(pages)[0];
      const url   = page?.imageinfo?.[0]?.url;
      if (url) {
        input.value = url;
        setPreview(input, url);
      } else {
        label.className = 'preview-error'; label.textContent = 'Fichier introuvable sur Commons';
      }
    })
    .catch(() => {
      label.className = 'preview-error'; label.textContent = 'Erreur API Wikimedia';
    });
}

function handleInput(input) {
  const raw = input.value.trim();
  if (!raw) { setPreview(input, ''); return; }

  // Détecte URL de page Commons → résolution via API
  const commonsMatch = raw.match(/commons\.wikimedia\.org\/wiki\/File:(.+?)(?:\?|$)/i);
  if (commonsMatch) {
    resolveCommonsUrl(input, decodeURIComponent(commonsMatch[1]));
    return;
  }

  // URL directe upload.wikimedia.org ou autre → preview directe
  setPreview(input, raw);
}
</script>

<?php endif; ?>
</body>
</html>

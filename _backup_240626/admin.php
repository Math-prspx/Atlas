<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — Admin images (v2)
// ─────────────────────────────────────────────────────────────────
session_start();
require_once __DIR__ . '/scraper/config.php';

define('ADMIN_PASS', 'AtlasAdmin2024');

// Logout
if (isset($_POST['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

// Login
if (isset($_POST['password'])) {
    $_SESSION['admin_ok'] = ($_POST['password'] === ADMIN_PASS);
    if (!$_SESSION['admin_ok']) $login_error = true;
}

$logged_in = $_SESSION['admin_ok'] ?? false;

// ── Save single record ───────────────────────────────────────────
$saved = false;
if ($logged_in && isset($_POST['save'], $_POST['slug'])) {
    $pdo  = db();
    $slug = trim($_POST['slug']);
    $stmt = $pdo->prepare(
        "UPDATE courants SET
            image_wikidata=:img1, image_wikipedia=:img2,
            image_3=:img3, image_4=:img4, image_5=:img5, image_6=:img6
         WHERE slug=:slug"
    );
    $stmt->execute([
        ':img1' => trim($_POST['img'][1] ?? '') ?: null,
        ':img2' => trim($_POST['img'][2] ?? '') ?: null,
        ':img3' => trim($_POST['img'][3] ?? '') ?: null,
        ':img4' => trim($_POST['img'][4] ?? '') ?: null,
        ':img5' => trim($_POST['img'][5] ?? '') ?: null,
        ':img6' => trim($_POST['img'][6] ?? '') ?: null,
        ':slug' => $slug,
    ]);
    $saved = true;
    header('Location: admin.php?saved=' . urlencode($slug));
    exit;
}

$saved_slug = $_GET['saved'] ?? null;

// ── Load data ────────────────────────────────────────────────────
$edit_slug = isset($_GET['edit']) ? trim($_GET['edit']) : null;
$courant   = null;
$courants  = [];

if ($logged_in) {
    $pdo = db();

    if ($edit_slug) {
        $stmt = $pdo->prepare("SELECT * FROM courants WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $edit_slug]);
        $courant = $stmt->fetch();
        if (!$courant) { header('Location: admin.php'); exit; }
    } else {
        $courants = $pdo->query(
            "SELECT slug, nom, periode_debut, periode_fin,
                    image_wikidata, image_wikipedia, image_3, image_4, image_5, image_6
             FROM courants ORDER BY nom ASC"
        )->fetchAll();
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — Atlas du Graphisme</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: #f5f5f5; color: #111; font: 14px/1.5 'Helvetica Neue', Arial, sans-serif; }
a { color: inherit; text-decoration: none; }

/* ── Nav ── */
nav {
  background: #fff;
  border-bottom: 1px solid #e0e0e0;
  padding: 14px 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
nav strong { font-size: 13px; letter-spacing: .04em; text-transform: uppercase; }
nav span { font-size: 12px; color: #999; }
.btn-logout {
  font-size: 11px; color: #999; background: none;
  border: 1px solid #ddd; border-radius: 3px;
  padding: 5px 12px; cursor: pointer;
}
.btn-logout:hover { border-color: #999; color: #333; }

/* ── Login ── */
.login {
  display: flex; align-items: center; justify-content: center; min-height: 100vh;
}
.login form {
  background: #fff; border: 1px solid #e0e0e0; border-radius: 6px;
  padding: 40px; width: 320px; text-align: center;
}
.login h1 { font-size: 16px; font-weight: 500; margin-bottom: 24px; }
.login input {
  width: 100%; border: 1px solid #ddd; border-radius: 3px;
  font-size: 14px; padding: 9px 12px; margin-bottom: 10px; outline: none;
}
.login input:focus { border-color: #999; }
.btn-primary {
  width: 100%; background: #111; color: #fff; border: none;
  border-radius: 3px; font-size: 13px; font-weight: 600;
  padding: 10px; cursor: pointer; letter-spacing: .04em; text-transform: uppercase;
}
.btn-primary:hover { background: #333; }
.error { font-size: 12px; color: #c0392b; margin-top: 8px; }

/* ── List ── */
.wrap { max-width: 640px; margin: 0 auto; padding: 32px 20px; }
.notice {
  background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 4px;
  color: #2e7d32; font-size: 12px; padding: 10px 16px; margin-bottom: 24px;
}
.list { background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; overflow: hidden; }
.list-item {
  display: flex; align-items: baseline; justify-content: space-between;
  padding: 13px 20px; border-bottom: 1px solid #f0f0f0;
  transition: background .15s;
}
.list-item:last-child { border-bottom: none; }
.list-item:hover { background: #f9f9f9; }
.list-item-nom { font-size: 14px; }
.list-item-meta { font-size: 11px; color: #aaa; }
.list-item-count {
  font-size: 11px; color: #bbb;
  display: flex; gap: 3px; align-items: center;
}
.dot { width: 6px; height: 6px; border-radius: 50%; background: #ddd; }
.dot.filled { background: #4caf50; }

/* ── Edit ── */
.edit-wrap { max-width: 560px; margin: 0 auto; padding: 32px 20px; }
.back { font-size: 12px; color: #999; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 24px; }
.back:hover { color: #333; }
.edit-title { font-size: 20px; font-weight: 300; margin-bottom: 4px; }
.edit-period { font-size: 11px; color: #aaa; margin-bottom: 28px; }
.img-row { margin-bottom: 20px; }
.img-label { font-size: 10px; letter-spacing: .1em; text-transform: uppercase; color: #999; margin-bottom: 6px; }
.img-row input[type="text"] {
  width: 100%; border: 1px solid #ddd; border-radius: 3px;
  font-size: 12px; padding: 8px 10px; margin-bottom: 6px;
  font-family: monospace; outline: none;
}
.img-row input[type="text"]:focus { border-color: #999; }
.preview {
  height: 70px; border: 1px solid #eee; border-radius: 3px;
  background: #fafafa; display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.preview img { max-width: 100%; max-height: 68px; object-fit: contain; display: none; }
.preview span { font-size: 11px; color: #ccc; }
.btn-save {
  background: #111; color: #fff; border: none; border-radius: 3px;
  font-size: 12px; font-weight: 600; padding: 11px 32px; cursor: pointer;
  letter-spacing: .05em; text-transform: uppercase; margin-top: 8px;
}
.btn-save:hover { background: #333; }
</style>
</head>
<body>

<?php if (!$logged_in): ?>
<div class="login">
  <form method="post">
    <h1>Atlas du Graphisme</h1>
    <input type="password" name="password" placeholder="Mot de passe" autofocus>
    <button type="submit" class="btn-primary">Connexion</button>
    <?php if (isset($login_error)): ?>
    <p class="error">Mot de passe incorrect.</p>
    <?php endif; ?>
  </form>
</div>

<?php elseif ($edit_slug && $courant): ?>
<!-- ── EDIT ── -->
<nav>
  <strong>Atlas — Images</strong>
  <form method="post" style="display:inline">
    <button name="logout" value="1" class="btn-logout" formnovalidate>Déconnexion</button>
  </form>
</nav>

<div class="edit-wrap">
  <a class="back" href="admin.php">← Retour à la liste</a>
  <div class="edit-title"><?= htmlspecialchars($courant['nom']) ?></div>
  <div class="edit-period">
    <?= $courant['periode_debut'] ?>
    <?= $courant['periode_fin'] ? ' — ' . $courant['periode_fin'] : '' ?>
  </div>

  <form method="post">
    <input type="hidden" name="save" value="1">
    <input type="hidden" name="slug" value="<?= htmlspecialchars($courant['slug']) ?>">

    <?php
    $imgs = [
      1 => ['label' => 'Image 1 (Wikidata)',  'val' => $courant['image_wikidata']  ?? ''],
      2 => ['label' => 'Image 2 (Wikipedia)', 'val' => $courant['image_wikipedia'] ?? ''],
      3 => ['label' => 'Image 3',             'val' => $courant['image_3']         ?? ''],
      4 => ['label' => 'Image 4',             'val' => $courant['image_4']         ?? ''],
      5 => ['label' => 'Image 5',             'val' => $courant['image_5']         ?? ''],
      6 => ['label' => 'Image 6',             'val' => $courant['image_6']         ?? ''],
    ];
    foreach ($imgs as $n => $f):
      $val = htmlspecialchars($f['val']);
    ?>
    <div class="img-row">
      <div class="img-label"><?= $f['label'] ?></div>
      <input type="text" name="img[<?= $n ?>]" value="<?= $val ?>"
             placeholder="URL directe ou commons.wikimedia.org/wiki/File:…"
             oninput="handleInput(this)" onchange="handleInput(this)">
      <div class="preview" id="preview-<?= $n ?>">
        <img src="<?= $val ?>" alt="" onload="imgOk(this)" onerror="imgErr(this)">
        <span><?= $f['val'] ? 'chargement…' : 'aucune image' ?></span>
      </div>
    </div>
    <?php endforeach; ?>

    <button type="submit" class="btn-save">Enregistrer</button>
  </form>
</div>

<?php else: ?>
<!-- ── LIST ── -->
<nav>
  <strong>Atlas — Images</strong>
  <form method="post" style="display:inline">
    <button name="logout" value="1" class="btn-logout" formnovalidate>Déconnexion</button>
  </form>
</nav>

<div class="wrap">
  <?php if ($saved_slug): ?>
  <div class="notice">✓ <?= htmlspecialchars($saved_slug) ?> enregistré.</div>
  <?php endif; ?>

  <div class="list">
    <?php foreach ($courants as $c):
      $images = array_filter([
        $c['image_wikidata'], $c['image_wikipedia'],
        $c['image_3'], $c['image_4'], $c['image_5'], $c['image_6'] ?? null,
      ]);
      $count = count($images);
    ?>
    <a class="list-item" href="admin.php?edit=<?= urlencode($c['slug']) ?>">
      <span class="list-item-nom"><?= htmlspecialchars($c['nom']) ?></span>
      <span style="display:flex;align-items:center;gap:12px">
        <span class="list-item-meta">
          <?= $c['periode_debut'] ?><?= $c['periode_fin'] ? '–' . $c['periode_fin'] : '' ?>
        </span>
        <span class="list-item-count">
          <?php for ($i = 1; $i <= 6; $i++): ?>
          <span class="dot <?= $i <= $count ? 'filled' : '' ?>"></span>
          <?php endfor; ?>
        </span>
      </span>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script>
function handleInput(input) {
  const raw = input.value.trim();
  const n   = input.name.match(/\[(\d+)\]/)[1];
  const preview = document.getElementById('preview-' + n);
  const img  = preview.querySelector('img');
  const span = preview.querySelector('span');

  if (!raw) { img.style.display='none'; span.textContent='aucune image'; return; }

  const commonsMatch = raw.match(/commons\.wikimedia\.org\/wiki\/File:(.+?)(?:\?|$)/i);
  if (commonsMatch) {
    span.textContent = 'résolution…';
    const api = 'https://commons.wikimedia.org/w/api.php?action=query'
      + '&titles=File:' + encodeURIComponent(decodeURIComponent(commonsMatch[1]))
      + '&prop=imageinfo&iiprop=url&format=json&origin=*';
    fetch(api).then(r=>r.json()).then(data => {
      const page = Object.values(data.query.pages)[0];
      const url  = page?.imageinfo?.[0]?.url;
      if (url) { input.value = url; setImg(img, span, url); }
      else span.textContent = 'introuvable sur Commons';
    }).catch(() => span.textContent = 'erreur API');
    return;
  }
  setImg(img, span, raw);
}

function setImg(img, span, url) {
  span.textContent = 'chargement…';
  img.src = url;
}

function imgOk(img) {
  img.style.display = 'block';
  img.nextElementSibling.style.display = 'none';
}

function imgErr(img) {
  img.style.display = 'none';
  img.nextElementSibling.textContent = 'URL invalide';
}
</script>
</body>
</html>

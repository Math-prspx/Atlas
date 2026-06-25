# Atlas du Graphisme - Sync source -> deploy/
# Usage : .\sync.ps1
# Prepare deploy/ pour upload FTP sur OVH /atlas/
#
# NE PAS toucher :
#   deploy/scraper/config.php  (credentials MySQL prod)
#   deploy/admin.php           (editer directement si besoin)
#   deploy/test_db.php
#   deploy/seed_missing.php

$root   = $PSScriptRoot
$deploy = Join-Path $root "deploy"
$ok     = 0
$skip   = 0

function Sync-File {
    param([string]$Src, [string]$Dst)
    $srcPath = Join-Path $root $Src
    $dstPath = Join-Path $deploy $Dst

    if (-not (Test-Path $srcPath)) {
        Write-Host "  [skip] $Src (not found)" -ForegroundColor Yellow
        $script:skip++
        return
    }

    $dstDir = Split-Path $dstPath
    if (-not (Test-Path $dstDir)) { New-Item -ItemType Directory -Path $dstDir -Force | Out-Null }

    Copy-Item $srcPath $dstPath -Force
    Write-Host "  [ok]   $Src -> deploy/$Dst" -ForegroundColor Green
    $script:ok++
}

Write-Host ""
Write-Host "=== Sync source -> deploy/ ===================================" -ForegroundColor Cyan

# Front
Sync-File "index.html"   "index.html"
Sync-File "style.css"    "style.css"
Sync-File ".htaccess"    ".htaccess"

# API
Sync-File "api\courants.php"   "api\courants.php"

# Scrapers (pas config.php - credentials prod differents)
Sync-File "scraper\fetch_wikidata.php"            "scraper\fetch_wikidata.php"
Sync-File "scraper\fetch_wikipedia.php"           "scraper\fetch_wikipedia.php"
Sync-File "scraper\fetch_cooperhewitt.php"        "scraper\fetch_cooperhewitt.php"
Sync-File "scraper\fetch_artistes_wikipedia.php"  "scraper\fetch_artistes_wikipedia.php"

Write-Host ""
Write-Host "  $ok fichier(s) mis a jour, $skip ignore(s)." -ForegroundColor White
Write-Host ""
Write-Host "=== FTP (FileZilla / WinSCP) =================================" -ForegroundColor Cyan
Write-Host "  Remote : /www/atlas/"
Write-Host "  Local  : deploy\"
Write-Host ""
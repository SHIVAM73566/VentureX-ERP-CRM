#Requires -Version 5.1
<#
.SYNOPSIS
  Build a buyer-ready Acquire/Gumroad/SellAnyCode/Payhip release ZIP for VentureX ERP & CRM.

.OUTPUTS
  dist/VentureX-ERP-CRM-Release-v1.0.0.zip
#>
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot\..

$repoRoot = (Get-Location).Path
$distDir = Join-Path $repoRoot "dist"
$zipPath = Join-Path $distDir "VentureX-ERP-CRM-Release-v1.0.0.zip"
$stagingRoot = Join-Path $env:TEMP ("vx_release_staging_" + [guid]::NewGuid().ToString("N"))
$innerDir = Join-Path $stagingRoot "VentureX-ERP-CRM"

Write-Host "==> Repo: $repoRoot"
Write-Host "==> Output: $zipPath"

# 1) Build production frontend assets
if (Test-Path (Join-Path $repoRoot "package.json")) {
    Write-Host "==> Installing JS deps (npm ci if lock exists, else npm install)..."
    if (Test-Path (Join-Path $repoRoot "package-lock.json")) {
        cmd /c "npm ci"
    } else {
        cmd /c "npm install"
    }
    Write-Host "==> Building frontend (npm run build)..."
    cmd /c "npm run build"
} else {
    Write-Warning "package.json not found; skipping frontend build."
}

if (-not (Test-Path (Join-Path $repoRoot "public\build\manifest.json"))) {
    throw "public/build/manifest.json not found. Run 'npm run build' successfully before packaging."
}

New-Item -ItemType Directory -Force -Path $distDir | Out-Null
if (Test-Path $zipPath) {
    Remove-Item -Force $zipPath
}
New-Item -ItemType Directory -Force -Path $innerDir | Out-Null

Write-Host "==> Staging files to temp..."
$excludeDirNames = @(".git", "node_modules", ".idea", ".vscode", "dist")
$excludeRootFiles = @(".env")

function Test-ShouldSkip {
    param([string]$FullPath, [string]$Root)

    $rel = $FullPath.Substring($Root.Length).TrimStart('\', '/')
    if ([string]::IsNullOrEmpty($rel)) { return $true }

    $parts = $rel -split '[\\/]'
    foreach ($p in $parts) {
        if ($excludeDirNames -contains $p) { return $true }
    }

    $leaf = Split-Path $rel -Leaf

    # .env files (keep .env.example)
    if ($leaf -eq ".env") { return $true }
    if ($leaf -like ".env.*" -and $leaf -ne ".env.example") { return $true }

    # SQLite local files
    if ($leaf -like "*.sqlite" -or $leaf -like "*.sqlite-journal") { return $true }

    # Storage logs: exclude *.log
    if ($rel -match '^storage[\\/]logs[\\/].+\.log$') { return $true }
    # Also exclude any .log under storage/logs
    if ($parts.Length -ge 2 -and $parts[0] -eq "storage" -and $parts[1] -eq "logs" -and $leaf -like "*.log") { return $true }

    # storage/framework/cache/* (exclude contents except .gitignore)
    if ($rel -match '^storage[\\/]framework[\\/]cache[\\/]') {
        if ($leaf -eq ".gitignore") { return $false }
        # Skip all cache data files
        return $true
    }
    if ($rel -match '^storage[\\/]framework[\\/]sessions[\\/]') {
        if ($leaf -eq ".gitignore") { return $false }
        return $true
    }
    if ($rel -match '^storage[\\/]framework[\\/]views[\\/]') {
        if ($leaf -eq ".gitignore") { return $false }
        return $true
    }

    # bootstrap/cache generated
    if ($rel -match '^bootstrap[\\/]cache[\\/]') {
        if ($leaf -eq ".gitignore") { return $false }
        return $true
    }

    # SQL dumps (common patterns)
    if ($leaf -like "*.sql" -or $leaf -like "*.sql.gz") { return $true }

    # Test temp artifacts
    if ($leaf -eq ".phpunit.result.cache" -or $leaf -eq "phpunit.xml.bak") { return $true }

    # Packaging helper scripts we created locally (do not ship)
    if ($leaf -like "build_v131_zips*.py") { return $true }

    return $false
}

Get-ChildItem -LiteralPath $repoRoot -Force | ForEach-Object {
    $name = $_.Name
    if ($excludeDirNames -contains $name) { return }
    if ($excludeRootFiles -contains $name) { return }
    if ($name -like ".env.*" -and $name -ne ".env.example") { return }

    $dest = Join-Path $innerDir $name
    if ($_.PSIsContainer) {
        Copy-Item -LiteralPath $_.FullName -Destination $dest -Recurse -Force
    } else {
        Copy-Item -LiteralPath $_.FullName -Destination $dest -Force
    }
}

# Walk staged tree and remove skipped items (files/dirs that matched skip rules)
Get-ChildItem -LiteralPath $innerDir -Recurse -Force | ForEach-Object {
    $full = $_.FullName
    $rel = $full.Substring($innerDir.Length).TrimStart('\', '/')
    # Map rel back to "VentureX-ERP-CRM/<rel>" style not needed; test against rel parts
    $fakeRoot = $innerDir
    # Build a relative path from innerDir for rule testing
    $testRel = $rel
    # Reuse logic: simulate by checking patterns
    $parts = $testRel -split '[\\/]'
    $leaf = Split-Path $testRel -Leaf

    $skip = $false
    foreach ($p in $parts) {
        if ($excludeDirNames -contains $p) { $skip = $true; break }
    }
    if (-not $skip) {
        if ($leaf -eq ".env") { $skip = $true }
        if ($leaf -like ".env.*" -and $leaf -ne ".env.example") { $skip = $true }
        if ($leaf -like "*.sqlite" -or $leaf -like "*.sqlite-journal") { $skip = $true }
        if ($testRel -match '^storage[\\/]logs[\\/].+\.log$') { $skip = $true }
        if ($parts.Length -ge 2 -and $parts[0] -eq "storage" -and $parts[1] -eq "logs" -and $leaf -like "*.log") { $skip = $true }
        if ($testRel -match '^storage[\\/]framework[\\/]cache[\\/]') { if ($leaf -ne ".gitignore") { $skip = $true } }
        if ($testRel -match '^storage[\\/]framework[\\/]sessions[\\/]') { if ($leaf -ne ".gitignore") { $skip = $true } }
        if ($testRel -match '^storage[\\/]framework[\\/]views[\\/]') { if ($leaf -ne ".gitignore") { $skip = $true } }
        if ($testRel -match '^bootstrap[\\/]cache[\\/]') { if ($leaf -ne ".gitignore") { $skip = $true } }
        if ($leaf -like "*.sql" -or $leaf -like "*.sql.gz") { $skip = $true }
        if ($leaf -eq ".phpunit.result.cache" -or $leaf -eq "phpunit.xml.bak") { $skip = $true }
        if ($leaf -like "build_v131_zips*.py") { $skip = $true }
    }
    if ($skip) {
        Remove-Item -LiteralPath $full -Recurse -Force -ErrorAction SilentlyContinue
    }
}

Write-Host "==> Creating ZIP..."
Compress-Archive -Path (Join-Path $stagingRoot "VentureX-ERP-CRM") -DestinationPath $zipPath -Force

Remove-Item -Recurse -Force $stagingRoot -ErrorAction SilentlyContinue

$sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host "==> Done: $zipPath ($sizeMb MB)"
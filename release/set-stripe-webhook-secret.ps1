# Saves only STRIPE_WEBHOOK_SECRET to the local .env with hidden input.
# Never prints or logs the value.

$ErrorActionPreference = 'Stop'

$root = 'C:\MY_ERP'
$envPath = Join-Path $root '.env'

if (-not (Test-Path -LiteralPath $envPath)) {
    throw '.env is missing. Run ADD-STRIPE-KEY.bat first.'
}

$ptr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR(
    (Read-Host -Prompt 'STRIPE_WEBHOOK_SECRET (whsec_..., input hidden)' -AsSecureString)
)
try {
    $value = [System.Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr)
} finally {
    [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)
}

if ($value -notmatch '^whsec_[A-Za-z0-9]+') {
    throw 'That does not look like a Stripe webhook secret. Nothing was changed.'
}

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
Copy-Item -LiteralPath $envPath -Destination "$envPath.bak-$stamp"

$pattern = '^\s*STRIPE_WEBHOOK_SECRET\s*='
$lines = @(Get-Content -LiteralPath $envPath)
$found = $false
$result = @()

foreach ($line in $lines) {
    if ($line -match $pattern) {
        $found = $true
        $result += "STRIPE_WEBHOOK_SECRET=$value"
    } else {
        $result += $line
    }
}

if (-not $found) {
    $result += "STRIPE_WEBHOOK_SECRET=$value"
}

[System.IO.File]::WriteAllLines($envPath, $result, (New-Object System.Text.UTF8Encoding($false)))

Push-Location $root
try {
    & php artisan config:clear
    & php artisan cache:clear
} finally {
    Pop-Location
}

Write-Host ''
Write-Host 'DONE. Webhook secret saved locally. Do not commit .env.'

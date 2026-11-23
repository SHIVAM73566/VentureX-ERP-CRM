# Stripe local .env setup helper
# Runs on the server. Prompts for secrets with hidden input and never commits them.
param(
    [string] $HostUrl = 'http://127.0.0.1:8000'
)

$ErrorActionPreference = 'Stop'

$root = 'C:\MY_ERP'
$envPath = Join-Path $root '.env'
$examplePath = Join-Path $root '.env.example'

if (-not (Test-Path -LiteralPath $envPath)) {
    if (-not (Test-Path -LiteralPath $examplePath)) {
        throw '.env and .env.example are both missing.'
    }
    Copy-Item -LiteralPath $examplePath -Destination $envPath
    Write-Host 'Created .env from .env.example.'
}

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backup = "$envPath.bak-$stamp"
Copy-Item -LiteralPath $envPath -Destination $backup
Write-Host "Backed up existing .env to: $backup"

function Convert-SecureStringToString {
    param([System.Security.SecureString] $SecureValue)

    $ptr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($SecureValue)
    try {
        return [System.Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr)
    } finally {
        [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)
    }
}

function Set-EnvValue {
    param(
        [string[]] $Lines,
        [string] $Key,
        [string] $Value
    )

    $pattern = '^\s*' + [regex]::Escape($Key) + '\s*='
    $found = $false
    $result = @()

    foreach ($line in $Lines) {
        if ($line -match $pattern) {
            $found = $true
            $result += "$Key=$Value"
        } else {
            $result += $line
        }
    }

    if (-not $found) {
        $result += "$Key=$Value"
    }

    return $result
}

$secret = Convert-SecureStringToString (Read-Host -Prompt 'STRIPE_SECRET (sk_test_..., input hidden)' -AsSecureString)
$webhookSecret = Convert-SecureStringToString (Read-Host -Prompt 'STRIPE_WEBHOOK_SECRET (whsec_..., input hidden)' -AsSecureString)

$base = $HostUrl.TrimEnd('/')
$returnUrl = "$base/admin/settings?tab=plan"

$priceKeys = @(
    @{ Key = 'STRIPE_PRICE_UPDATES_SUPPORT'; Label = 'Updates & Support (Care)' },
    @{ Key = 'STRIPE_PRICE_MANAGED_HOSTING'; Label = 'Managed Install & Maintenance' },
    @{ Key = 'STRIPE_PRICE_AI_CREDITS'; Label = 'AI Credits / Metered AI Add-On' },
    @{ Key = 'STRIPE_PRICE_REPORTING_PACK_ADV'; Label = 'Advanced Reporting' },
    @{ Key = 'STRIPE_PRICE_WORKFLOW_AUTOMATION_PLUS'; Label = 'Workflow automation+ pack' },
    @{ Key = 'STRIPE_PRICE_INTEGRATIONS_PRIORITY'; Label = 'Priority integrations pack' },
    @{ Key = 'STRIPE_PRICE_MULTI_BRANCH_OPS'; Label = 'Multi-branch / advanced operations' },
    @{ Key = 'STRIPE_PRICE_COMPLIANCE_HARDENING'; Label = 'Compliance & hardening add-on' },
    @{ Key = 'STRIPE_PRICE_GRANULAR_RBAC'; Label = 'Granular roles & permissions' },
    @{ Key = 'STRIPE_PRICE_SECURITY_PATCHES'; Label = 'Security updates & audit tooling' },
    @{ Key = 'STRIPE_PRICE_CAPACITY_EXPANSION'; Label = 'Capacity & module expansion' },
    @{ Key = 'STRIPE_PRICE_TPL_MANUFACTURING_PLUS'; Label = 'Industry template: Manufacturing+' },
    @{ Key = 'STRIPE_PRICE_TPL_TRADING_DISTRIBUTION'; Label = 'Industry template: Trading & distribution' },
    @{ Key = 'STRIPE_PRICE_TPL_SERVICES_PROJECTS'; Label = 'Industry template: Services / projects' },
    @{ Key = 'STRIPE_PRICE_LOGISTICS_SUITE_ADV'; Label = 'Advanced logistics suite' },
    @{ Key = 'STRIPE_PRICE_FINANCE_SUITE_ADV'; Label = 'Advanced finance suite' },
    @{ Key = 'STRIPE_PRICE_PARTNER_WHITE_LABEL'; Label = 'Partner/white-label enablement (scoped)' },
    @{ Key = 'STRIPE_PRICE_CUSTOM_DEV_CREDITS'; Label = 'Custom development credits' }
)

$lines = @(Get-Content -LiteralPath $envPath)
$lines = Set-EnvValue -Lines $lines -Key 'STRIPE_SECRET' -Value $secret
$lines = Set-EnvValue -Lines $lines -Key 'STRIPE_WEBHOOK_SECRET' -Value $webhookSecret
$lines = Set-EnvValue -Lines $lines -Key 'STRIPE_API_BASE' -Value 'https://api.stripe.com'
$lines = Set-EnvValue -Lines $lines -Key 'STRIPE_WEBHOOK_TOLERANCE_SECONDS' -Value '300'
$lines = Set-EnvValue -Lines $lines -Key 'STRIPE_ENTITLEMENT_GRACE_DAYS' -Value '7'
$lines = Set-EnvValue -Lines $lines -Key 'STRIPE_CHECKOUT_SUCCESS_URL' -Value $returnUrl
$lines = Set-EnvValue -Lines $lines -Key 'STRIPE_CHECKOUT_CANCEL_URL' -Value $returnUrl
$lines = Set-EnvValue -Lines $lines -Key 'STRIPE_BILLING_PORTAL_RETURN_URL' -Value $returnUrl

foreach ($item in $priceKeys) {
    $value = Read-Host -Prompt "$($item.Key) [$($item.Label)]"
    $lines = Set-EnvValue -Lines $lines -Key $item.Key -Value $value.Trim()
}

[System.IO.File]::WriteAllLines($envPath, $lines, (New-Object System.Text.UTF8Encoding($false)))

Push-Location $root
try {
    & php artisan config:clear
} finally {
    Pop-Location
}

Write-Host ''
Write-Host 'DONE. Do not commit .env.'
Write-Host "Verify in a browser: $returnUrl"

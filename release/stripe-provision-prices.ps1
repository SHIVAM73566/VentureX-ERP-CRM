# VentureX Stripe Product/Price provisioning helper
# -----------------------------------------------------------------------------
# BEGINNER SAFETY:
#   - Preview mode is the default. Nothing is created and .env is untouched.
#   - Run with -Apply only AFTER you have run `stripe login`.
#   - Test-mode keys only. Live keys are refused.
#   - Product/Price IDs are written to C:\MY_ERP\.env only. They are never printed.
#   - Unrelated .env lines are preserved. A timestamped backup is created.
#   - No secret key, webhook secret or Stripe account ID is stored in this file.
# -----------------------------------------------------------------------------
param(
    [string] $Currency = 'usd',
    [string] $HostUrl = '',
    [string] $StripeCli = 'stripe',
    [switch] $Apply,
    [switch] $DryRun,
    [switch] $Force
)

$ErrorActionPreference = 'Stop'

$root = 'C:\MY_ERP'
$envPath = Join-Path $root '.env'

# -----------------------------------------------------------------------------
# EDIT PRICES HERE
# Amounts are in cents. These are ILLUSTRATIVE STARTER values based on the
# published layer guidance, not guaranteed commercial prices. Change any
# AmountCents / Display value before running -Apply. Final price, tax and
# renewal terms are always shown by Stripe Checkout before payment.
#
# Mode must be one of:
#   recurring_year  -> Stripe recurring yearly price
#   recurring_month -> Stripe recurring monthly price
#   one_time        -> Stripe one-time price (used for usage/service bundles)
# -----------------------------------------------------------------------------
$prices = @(
    [pscustomobject]@{ Key = 'updates_support';          EnvName = 'STRIPE_PRICE_UPDATES_SUPPORT';          Name = 'Updates & Support (Care)';                  Mode = 'recurring_year';  AmountCents = 14900; Display = '$149/year' },
    [pscustomobject]@{ Key = 'managed_hosting';          EnvName = 'STRIPE_PRICE_MANAGED_HOSTING';          Name = 'Managed Install & Maintenance';             Mode = 'recurring_month'; AmountCents = 7900;  Display = '$79/month' },
    [pscustomobject]@{ Key = 'ai_credits';               EnvName = 'STRIPE_PRICE_AI_CREDITS';               Name = 'AI Credits / Metered AI Add-On';            Mode = 'one_time';        AmountCents = 4900;  Display = '$49 bundle' },
    [pscustomobject]@{ Key = 'reporting_pack_adv';       EnvName = 'STRIPE_PRICE_REPORTING_PACK_ADV';       Name = 'Advanced Reporting';                       Mode = 'recurring_month'; AmountCents = 2900;  Display = '$29/month' },
    [pscustomobject]@{ Key = 'workflow_automation_plus'; EnvName = 'STRIPE_PRICE_WORKFLOW_AUTOMATION_PLUS'; Name = 'Workflow automation+ pack';                 Mode = 'recurring_month'; AmountCents = 3900;  Display = '$39/month' },
    [pscustomobject]@{ Key = 'integrations_priority';    EnvName = 'STRIPE_PRICE_INTEGRATIONS_PRIORITY';    Name = 'Priority integrations pack';                Mode = 'recurring_month'; AmountCents = 4900;  Display = '$49/month' },
    [pscustomobject]@{ Key = 'multi_branch_ops';         EnvName = 'STRIPE_PRICE_MULTI_BRANCH_OPS';         Name = 'Multi-branch / advanced operations';       Mode = 'recurring_month'; AmountCents = 5900;  Display = '$59/month' },
    [pscustomobject]@{ Key = 'compliance_hardening';     EnvName = 'STRIPE_PRICE_COMPLIANCE_HARDENING';     Name = 'Compliance & hardening add-on';             Mode = 'recurring_year';  AmountCents = 9900;  Display = '$99/year' },
    [pscustomobject]@{ Key = 'granular_rbac';            EnvName = 'STRIPE_PRICE_GRANULAR_RBAC';            Name = 'Granular roles & permissions';              Mode = 'recurring_month'; AmountCents = 2900;  Display = '$29/month' },
    [pscustomobject]@{ Key = 'security_patches';         EnvName = 'STRIPE_PRICE_SECURITY_PATCHES';         Name = 'Security updates & audit tooling';         Mode = 'recurring_year';  AmountCents = 9900;  Display = '$99/year' },
    [pscustomobject]@{ Key = 'capacity_expansion';       EnvName = 'STRIPE_PRICE_CAPACITY_EXPANSION';       Name = 'Capacity & module expansion';               Mode = 'recurring_month'; AmountCents = 3900;  Display = '$39/month' },
    [pscustomobject]@{ Key = 'tpl_manufacturing_plus';   EnvName = 'STRIPE_PRICE_TPL_MANUFACTURING_PLUS';   Name = 'Industry template: Manufacturing+';        Mode = 'one_time';        AmountCents = 19900; Display = '$199 once' },
    [pscustomobject]@{ Key = 'tpl_trading_distribution'; EnvName = 'STRIPE_PRICE_TPL_TRADING_DISTRIBUTION'; Name = 'Industry template: Trading & distribution'; Mode = 'one_time';        AmountCents = 19900; Display = '$199 once' },
    [pscustomobject]@{ Key = 'tpl_services_projects';    EnvName = 'STRIPE_PRICE_TPL_SERVICES_PROJECTS';    Name = 'Industry template: Services / projects';   Mode = 'one_time';        AmountCents = 19900; Display = '$199 once' },
    [pscustomobject]@{ Key = 'logistics_suite_adv';      EnvName = 'STRIPE_PRICE_LOGISTICS_SUITE_ADV';      Name = 'Advanced logistics suite';                  Mode = 'recurring_month'; AmountCents = 7900;  Display = '$79/month' },
    [pscustomobject]@{ Key = 'finance_suite_adv';        EnvName = 'STRIPE_PRICE_FINANCE_SUITE_ADV';        Name = 'Advanced finance suite';                    Mode = 'recurring_month'; AmountCents = 7900;  Display = '$79/month' },
    [pscustomobject]@{ Key = 'partner_white_label';      EnvName = 'STRIPE_PRICE_PARTNER_WHITE_LABEL';      Name = 'Partner/white-label enablement (scoped)';   Mode = 'one_time';        AmountCents = 29900; Display = '$299 once' },
    [pscustomobject]@{ Key = 'custom_dev_credits';       EnvName = 'STRIPE_PRICE_CUSTOM_DEV_CREDITS';       Name = 'Custom development credits';                Mode = 'one_time';        AmountCents = 19900; Display = '$199 bundle' }
)

function Add-SystemPathToEnv {
    foreach ($scope in @('Machine', 'User')) {
        $extra = [Environment]::GetEnvironmentVariable('Path', $scope)
        if (-not [string]::IsNullOrWhiteSpace($extra) -and $env:Path -notlike "*$extra*") {
            $env:Path = "$env:Path;$extra"
        }
    }
}

function Resolve-StripeCli {
    param([string] $CommandName)

    Add-SystemPathToEnv
    $found = Get-Command -Name $CommandName -CommandType Application -ErrorAction SilentlyContinue | Select-Object -First 1
    if (-not $found) {
        throw 'Stripe CLI was not found. Install it, then run this script again.'
    }

    return $found.Source
}

function Get-StripeCliKeyValue {
    param([string] $Cli)

    if (-not [string]::IsNullOrWhiteSpace($env:STRIPE_API_KEY)) {
        return $env:STRIPE_API_KEY.Trim()
    }

    $raw = & $Cli config --list 2>&1 | Out-String
    if ($LASTEXITCODE -ne 0) {
        return ''
    }
    if ($raw -match '(?im)^\s*api_key\s*=\s*(\S+)') {
        return $Matches[1]
    }

    return ''
}

function Get-EnvValue {
    param(
        [string[]] $Lines,
        [string] $Key
    )

    $pattern = '^\s*' + [regex]::Escape($Key) + '\s*=(.*)$'
    foreach ($line in $Lines) {
        if ($line -match $pattern) {
            $value = $Matches[1].Trim()
            if ($value.Length -ge 2) {
                if (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'"))) {
                    $value = $value.Substring(1, $value.Length - 2)
                }
            }
            return $value.Trim()
        }
    }

    return ''
}

function Set-EnvValue {
    param(
        [string[]] $Lines,
        [string] $Key,
        [string] $Value
    )

    $pattern = '^\s*' + [regex]::Escape($Key) + '\s*='
    $found = $false
    $result = New-Object System.Collections.Generic.List[string]

    foreach ($line in $Lines) {
        if ($line -match $pattern) {
            if (-not $found) {
                $result.Add("$Key=$Value")
                $found = $true
            }
        } else {
            $result.Add($line)
        }
    }

    if (-not $found) {
        $result.Add("$Key=$Value")
    }

    return $result.ToArray()
}

function Invoke-StripeCliJson {
    param(
        [string] $Cli,
        [string[]] $Arguments,
        [string] $Purpose
    )

    $output = & $Cli @Arguments 2>&1 | Out-String
    $code = $LASTEXITCODE

    if ($code -ne 0) {
        throw "$Purpose failed (exit code $code). CLI output was suppressed because it can contain account data. Confirm `stripe login`, test mode, and network access, then retry."
    }

    try {
        return ($output | ConvertFrom-Json)
    } catch {
        throw "$Purpose returned unreadable JSON. Output was suppressed for safety."
    }
}

function New-ProductArguments {
    param(
        [object] $Price,
        [switch] $ForDryRun
    )

    $cliArgs = @('products', 'create')
    if ($ForDryRun) { $cliArgs += '--dry-run' } else { $cliArgs += '--confirm' }
    $cliArgs += @(
        '--name', $Price.Name,
        '--description', "VentureX add-on: $($Price.Name)",
        '-d', "metadata[layer_key]=$($Price.Key)",
        '-d', 'metadata[vendor]=VentureX'
    )

    return $cliArgs
}

function New-PriceArguments {
    param(
        [object] $Price,
        [string] $ProductId,
        [switch] $ForDryRun
    )

    $cliArgs = @('prices', 'create')
    if ($ForDryRun) { $cliArgs += '--dry-run' } else { $cliArgs += '--confirm' }
    $cliArgs += @(
        '--currency', $Currency,
        '--product', $ProductId,
        '--unit-amount', ([string] $Price.AmountCents),
        '--nickname', "VentureX $($Price.Display) - $($Price.Name)",
        '-d', "metadata[layer_key]=$($Price.Key)",
        '-d', 'metadata[vendor]=VentureX'
    )

    if ($Price.Mode -eq 'recurring_month') {
        $cliArgs += @('-d', 'recurring[interval]=month')
    } elseif ($Price.Mode -eq 'recurring_year') {
        $cliArgs += @('-d', 'recurring[interval]=year')
    }

    return $cliArgs
}

# -----------------------------------------------------------------------------
# Validate the editable price list before any external call or file write.
# -----------------------------------------------------------------------------
if ($Currency -notmatch '^[a-z]{3}$') {
    throw 'Currency must be a three-letter lowercase code, for example usd.'
}
if ($prices.Count -ne 18) {
    throw "Expected 18 price entries; found $($prices.Count)."
}

$validModes = @('recurring_month', 'recurring_year', 'one_time')
foreach ($price in $prices) {
    if ($price.EnvName -notlike 'STRIPE_PRICE_*') {
        throw "Invalid env name for $($price.Key)."
    }
    if ([int] $price.AmountCents -lt 50) {
        throw "AmountCents for $($price.Key) is too small."
    }
    if ($price.Mode -notin $validModes) {
        throw "Invalid Mode for $($price.Key)."
    }
}

$duplicateKeys = @($prices | Group-Object -Property Key | Where-Object { $_.Count -gt 1 })
if ($duplicateKeys.Count -gt 0) {
    throw 'Duplicate layer keys found in the price list.'
}

$isDryRun = $DryRun -or (-not $Apply)
$cliPath = Resolve-StripeCli -CommandName $StripeCli

$activeKey = Get-StripeCliKeyValue -Cli $cliPath
$hasAuth = -not [string]::IsNullOrWhiteSpace($activeKey)
$liveKeyActive = $activeKey -match '^(sk|rk)_live_'
$activeKey = $null

if ($Apply) {
    if (-not $hasAuth) {
        throw 'Stripe CLI is not authenticated. Run `stripe login` in this Command Prompt, approve the browser window, then retry with -Apply.'
    }
    if ($liveKeyActive) {
        throw 'A live Stripe key is active. This helper refuses live mode. Switch to Test mode and retry.'
    }
}

if (-not (Test-Path -LiteralPath $envPath)) {
    throw 'C:\MY_ERP\.env was not found. Copy .env.example to .env first.'
}

$lines = @(Get-Content -LiteralPath $envPath)
$pending = @()
$skipped = 0

foreach ($price in $prices) {
    $existing = Get-EnvValue -Lines $lines -Key $price.EnvName
    if ($existing -ne '' -and -not $Force) {
        $skipped++
    } else {
        $pending += $price
    }
}

$urlKeysChanged = @()
if (-not [string]::IsNullOrWhiteSpace($HostUrl)) {
    $base = $HostUrl.TrimEnd('/')
    $returnUrl = "$base/admin/settings?tab=plan"
    $urlKeysChanged = @(
        'STRIPE_CHECKOUT_SUCCESS_URL',
        'STRIPE_CHECKOUT_CANCEL_URL',
        'STRIPE_BILLING_PORTAL_RETURN_URL'
    )
}

Write-Host "VentureX Stripe price provisioning"
Write-Host "Mode: $(if ($Apply) { 'APPLY' } else { 'DRY RUN (preview only)' })"
Write-Host "CLI auth: $(if ($hasAuth) { 'configured (value hidden)' } else { 'not configured' })"
Write-Host "Currency: $Currency"
Write-Host "Price entries: $($prices.Count); will create: $($pending.Count); already set: $skipped"
if ($Force) {
    Write-Host 'Force is ON: existing Price IDs may be replaced.'
}
if ($urlKeysChanged.Count -gt 0 -and -not $Apply) {
    Write-Host 'Host URL provided: checkout/portal URLs would also be updated with -Apply.'
}

if ($pending.Count -eq 0 -and $urlKeysChanged.Count -eq 0) {
    Write-Host 'Nothing to do. Every Price ID is already set. Use -Force to recreate prices.'
    return
}

if ($Apply -and ($pending.Count -gt 0 -or $urlKeysChanged.Count -gt 0)) {
    $backup = "$envPath.bak-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
    Copy-Item -LiteralPath $envPath -Destination $backup
    Write-Host "Backup created: $backup"
}

$writtenPrices = 0

foreach ($price in $pending) {
    Write-Host "Processing: $($price.Key) ($($price.Display))"

    if ($isDryRun) {
        $productArgs = New-ProductArguments -Price $price -ForDryRun
        $null = Invoke-StripeCliJson -Cli $cliPath -Arguments $productArgs -Purpose 'Product dry run'

        $priceArgs = New-PriceArguments -Price $price -ProductId 'prod_dry_run' -ForDryRun
        $null = Invoke-StripeCliJson -Cli $cliPath -Arguments $priceArgs -Purpose 'Price dry run'
        continue
    }

    $productArgs = New-ProductArguments -Price $price
    $product = Invoke-StripeCliJson -Cli $cliPath -Arguments $productArgs -Purpose 'Product creation'
    $productId = [string] $product.id
    if ($productId -notmatch '^prod_') {
        throw "Product creation for $($price.Key) returned an unexpected object type."
    }

    $priceArgs = New-PriceArguments -Price $price -ProductId $productId
    $stripePrice = Invoke-StripeCliJson -Cli $cliPath -Arguments $priceArgs -Purpose 'Price creation'
    $priceId = [string] $stripePrice.id
    if ($priceId -notmatch '^price_') {
        throw "Price creation for $($price.Key) returned an unexpected object type."
    }

    $lines = Set-EnvValue -Lines $lines -Key $price.EnvName -Value $priceId
    $writtenPrices++
    $priceId = $null
}

if ($Apply -and $urlKeysChanged.Count -gt 0) {
    foreach ($key in $urlKeysChanged) {
        $lines = Set-EnvValue -Lines $lines -Key $key -Value $returnUrl
    }
}

if ($Apply) {
    [System.IO.File]::WriteAllLines($envPath, $lines, (New-Object System.Text.UTF8Encoding($false)))

    Push-Location $root
    try {
        & php artisan config:clear | Out-Null
    } catch {
        Write-Warning 'Could not run `php artisan config:clear` automatically. Run it manually from C:\MY_ERP.'
    } finally {
        Pop-Location
    }

    Write-Host "Done. Wrote $writtenPrices Price ID(s) to .env (values hidden)."
    Write-Host 'Do not commit .env.'
} else {
    Write-Host 'Dry run complete. No Stripe objects were created and .env was not changed.'
    Write-Host 'To create products/prices and update .env, run the same command with -Apply.'
}

Write-Host 'Remaining manual steps: create the webhook, add the webhook signing secret, and enable the Billing Portal return URL.'

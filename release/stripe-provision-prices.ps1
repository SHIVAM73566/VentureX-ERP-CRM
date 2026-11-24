# VentureX Stripe Product/Price provisioning helper
# -----------------------------------------------------------------------------
# BEGINNER SAFETY:
#   - Preview mode is the default. Nothing is created and .env is untouched.
#   - Run with -Apply only AFTER you have run `stripe login`.
#   - Sandbox/test mode is the default. Live mode requires explicit -AllowLive.
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
    [switch] $Force,
    [switch] $AllowLive
)

$ErrorActionPreference = 'Stop'

$root = 'C:\MY_ERP'
$envPath = Join-Path $root '.env'
$script:stripeDisplayLabel = ''

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

function Invoke-StripeCliText {
    param(
        [string] $Cli,
        [string[]] $Arguments
    )

    # Windows PowerShell 5.1 turns native stderr into ErrorRecord objects. With
    # ErrorActionPreference = Stop that raises NativeCommandError even when the
    # CLI exits 0. Temporarily use Continue, capture both streams, and rely on
    # $LASTEXITCODE for the real success/failure decision.
    $previousPreference = $ErrorActionPreference
    $previousNativePreference = $null
    $hadNativePreference = Test-Path -Path 'variable:PSNativeCommandUseErrorActionPreference'
    if ($hadNativePreference) {
        $previousNativePreference = $PSNativeCommandUseErrorActionPreference
    }

    $exitCode = $null
    $captured = ''
    try {
        $ErrorActionPreference = 'Continue'
        if ($hadNativePreference) {
            $PSNativeCommandUseErrorActionPreference = $false
        }
        $global:LASTEXITCODE = 0
        $captured = (& $Cli @Arguments 2>&1 | Out-String)
        $exitCode = $LASTEXITCODE
    } finally {
        $ErrorActionPreference = $previousPreference
        if ($hadNativePreference) {
            $PSNativeCommandUseErrorActionPreference = $previousNativePreference
        }
    }

    return [pscustomobject]@{
        ExitCode = $exitCode
        Text     = $captured
    }
}

function Convert-CliJsonText {
    param([string] $Text)

    $clean = [regex]::Replace($Text, "`e\[[0-9;?]*[A-Za-z]", '')
    $candidates = @()

    $firstBrace = $clean.IndexOf('{')
    $lastBrace = $clean.LastIndexOf('}')
    if ($firstBrace -ge 0 -and $lastBrace -gt $firstBrace) {
        $candidates += $clean.Substring($firstBrace, ($lastBrace - $firstBrace + 1))
    }

    $firstBracket = $clean.IndexOf('[')
    $lastBracket = $clean.LastIndexOf(']')
    if ($firstBracket -ge 0 -and $lastBracket -gt $firstBracket) {
        $candidates += $clean.Substring($firstBracket, ($lastBracket - $firstBracket + 1))
    }

    foreach ($candidate in $candidates) {
        try {
            return ($candidate | ConvertFrom-Json)
        } catch {
            # Try the next candidate; raw text is never printed.
        }
    }

    throw 'Stripe CLI returned unreadable JSON. Output was suppressed for safety.'
}

function Get-RedactedCliErrorLines {
    param(
        [string] $Text,
        [int] $ExitCode,
        [string] $SensitiveLabel = ''
    )

    $clean = [regex]::Replace($Text, "`e\[[0-9;?]*[A-Za-z]", '')

    # Remove values we know are sensitive before selecting any line for output.
    foreach ($value in @($SensitiveLabel, $env:STRIPE_API_KEY, $env:STRIPE_SECRET)) {
        if (-not [string]::IsNullOrWhiteSpace($value)) {
            $clean = $clean.Replace($value.Trim(), '[REDACTED]')
        }
    }

    $clean = [regex]::Replace($clean, '(?i)\b(sk|rk)_(live|test)_[A-Za-z0-9]+', '$1_$2_[REDACTED]')
    $clean = [regex]::Replace($clean, '\bwhsec_[A-Za-z0-9]+', 'whsec_[REDACTED]')
    $clean = [regex]::Replace($clean, '\bacct_[A-Za-z0-9]+', 'acct_[REDACTED]')
    $clean = [regex]::Replace($clean, '\b(price|prod|cus|sub|pi|evt|in|pm|cs|txn)_[A-Za-z0-9]+', '$1_[REDACTED]')
    $clean = [regex]::Replace($clean, '(?i)(api[_-]?key|secret|token|password|authorization)\s*[:=]\s*(""?)[^"\s,}]+\2', '$1=[REDACTED]')
    $clean = [regex]::Replace($clean, '\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b', '[EMAIL_REDACTED]')
    $clean = [regex]::Replace($clean, 'https?://\S+', '[URL_REDACTED]')

    $messages = New-Object System.Collections.Generic.List[string]

    # Stripe CLI often emits a JSON error object. Prefer its message/code when
    # present, but only after redaction above.
    try {
        $parsed = Convert-CliJsonText -Text $clean
        if ($parsed.error) {
            if ($parsed.error.message) {
                $messages.Add("Stripe: $([string] $parsed.error.message)")
            }
            if ($parsed.error.code) {
                $messages.Add("Stripe code: $([string] $parsed.error.code)")
            }
        }
    } catch {
        # Plain-text CLI errors are handled below.
    }

    # Drop PowerShell native-stderr noise: it contains the account label and
    # script invocation details, which are not useful for troubleshooting.
    $noise = '(?i)(^\s*[\w.-]+\.(exe|cmd|ps1)\s*:|▸|^\s*At line:|^\s*\+|CategoryInfo|FullyQualifiedErrorId|^\s*~|^\s*\{\s*$|^\s*\})'
    $interesting = '(?i)error|invalid|failed|must|require|denied|unauthor|not found|already exists|missing|unexpected|cannot|you''?re|you are|expired|wrong|unrecognized|unknown|stripe code'

    foreach ($line in ($clean -split "`r?`n")) {
        $text = $line.Trim()
        if ($text -eq '' -or $text -match $noise) {
            continue
        }
        if ($text -match $interesting) {
            if ($text.Length -gt 260) {
                $text = $text.Substring(0, 257) + '...'
            }
            $messages.Add($text)
        }
    }

    $seen = @{}
    $result = New-Object System.Collections.Generic.List[string]
    foreach ($message in $messages) {
        $key = $message.ToLowerInvariant()
        if (-not $seen.ContainsKey($key)) {
            $seen[$key] = $true
            $result.Add($message)
        }
    }

    if ($result.Count -eq 0) {
        $result.Add("No non-sensitive CLI error message was returned (exit code $ExitCode).")
    }

    return @($result | Select-Object -First 6)
}

function Get-StripeCliAuthInfo {
    param([string] $Cli)

    $key = ''
    if (-not [string]::IsNullOrWhiteSpace($env:STRIPE_API_KEY)) {
        $key = $env:STRIPE_API_KEY.Trim()
    }

    if ($key -ne '') {
        $mode = if ($key -match '^(sk|rk)_live_') {
            'live'
        } elseif ($key -match '^(sk|rk)_test_') {
            'test'
        } else {
            'unknown'
        }
        $key = $null

        return [pscustomobject]@{ Authenticated = $true; Mode = $mode; DisplayLabel = '' }
    }

    # `stripe login` stores an authenticated session without exposing an API
    # key. `stripe whoami --format json` is the reliable mode source.
    $who = Invoke-StripeCliText -Cli $Cli -Arguments @('whoami', '--format', 'json')
    if ($who.ExitCode -eq 0) {
        try {
            $info = Convert-CliJsonText -Text $who.Text
            $reportedMode = [string] $info.mode
            $mode = if ($reportedMode -match '(?i)^live$') {
                'live'
            } elseif ($reportedMode -match '(?i)^(test|sandbox)$') {
                'test'
            } else {
                'unknown'
            }
            $displayLabel = [string] $info.display_name

            return [pscustomobject]@{ Authenticated = $true; Mode = $mode; DisplayLabel = $displayLabel }
        } catch {
            # Fall through to the config-list fallback below.
        }
    }

    $result = Invoke-StripeCliText -Cli $Cli -Arguments @('config', '--list')
    if ($result.ExitCode -ne 0) {
        return [pscustomobject]@{ Authenticated = $false; Mode = 'unknown'; DisplayLabel = '' }
    }

    $raw = $result.Text
    if ($raw -match '(?im)^\s*api_key\s*=\s*(\S+)') {
        $key = $Matches[1]
    }
    $accountPresent = $raw -match '(?im)^\s*account_id\s*=\s*\S+'
    $displayLabel = ''
    if ($raw -match '(?im)^\s*display_name\s*=\s*(.*)$') {
        $displayLabel = $Matches[1].Trim()
    }
    $mode = if ($key -match '^(sk|rk)_live_') {
        'live'
    } elseif ($key -match '^(sk|rk)_test_') {
        'test'
    } else {
        'unknown'
    }
    $authenticated = (-not [string]::IsNullOrWhiteSpace($key)) -or $accountPresent
    $key = $null

    return [pscustomobject]@{
        Authenticated = $authenticated
        Mode          = $mode
        DisplayLabel  = $displayLabel
    }
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

    $result = Invoke-StripeCliText -Cli $Cli -Arguments $Arguments
    if ($result.ExitCode -ne 0) {
        $details = Get-RedactedCliErrorLines -Text $result.Text -ExitCode ([int] $result.ExitCode) -SensitiveLabel $script:stripeDisplayLabel
        throw "$Purpose failed (exit code $($result.ExitCode)). Stripe CLI reported: $($details -join ' | ')"
    }

    return (Convert-CliJsonText -Text $result.Text)
}

function New-ProductArguments {
    param(
        [object] $Price,
        [switch] $ForDryRun,
        [switch] $Live
    )

    $cliArgs = @('products', 'create')
    if ($ForDryRun) { $cliArgs += '--dry-run' } else { $cliArgs += '--confirm' }
    if ($Live) { $cliArgs += '--live' }
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
        [switch] $ForDryRun,
        [switch] $Live
    )

    $cliArgs = @('prices', 'create')
    if ($ForDryRun) { $cliArgs += '--dry-run' } else { $cliArgs += '--confirm' }
    if ($Live) { $cliArgs += '--live' }
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

$authInfo = Get-StripeCliAuthInfo -Cli $cliPath
$hasAuth = $authInfo.Authenticated
$liveMode = $authInfo.Mode -eq 'live'
$script:stripeDisplayLabel = [string] $authInfo.DisplayLabel

if (-not $hasAuth) {
    if ($Apply) {
        throw 'Stripe CLI is not authenticated. Run `stripe login` in this Command Prompt, approve the browser window, then retry with -Apply.'
    }
} elseif ($liveMode -and $Apply -and -not $AllowLive) {
    throw 'A LIVE Stripe account is active. Preview is safe, but creating prices requires explicit opt-in. Re-run with -Apply -AllowLive after confirming you intend to create live Stripe Products/Prices.'
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

$authLabel = if (-not $hasAuth) {
    'not configured'
} elseif ($liveMode) {
    'authenticated - LIVE account'
} elseif ($authInfo.Mode -eq 'test') {
    'authenticated - TEST account'
} else {
    'authenticated - mode unknown'
}

Write-Host "VentureX Stripe price provisioning"
Write-Host "Mode: $(if ($Apply) { 'APPLY' } else { 'DRY RUN (preview only)' })"
Write-Host "CLI auth: $authLabel"
if ($liveMode -and -not $Apply) {
    Write-Host 'LIVE account detected. This preview sends no create requests and changes no files.'
}
if ($liveMode -and $Apply -and $AllowLive) {
    Write-Host 'WARNING: LIVE MODE CONFIRMED. Real Stripe Products/Prices will be created in your live account.'
} elseif ($authInfo.Mode -eq 'test' -and $Apply) {
    Write-Host 'TEST account detected. Stripe Products/Prices will be created in sandbox/test mode.'
}
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
        $productArgs = New-ProductArguments -Price $price -ForDryRun -Live:$liveMode
        $null = Invoke-StripeCliJson -Cli $cliPath -Arguments $productArgs -Purpose 'Product dry run'

        $priceArgs = New-PriceArguments -Price $price -ProductId 'prod_dry_run' -ForDryRun -Live:$liveMode
        $null = Invoke-StripeCliJson -Cli $cliPath -Arguments $priceArgs -Purpose 'Price dry run'
        continue
    }

    $productArgs = New-ProductArguments -Price $price -Live:$liveMode
    $product = Invoke-StripeCliJson -Cli $cliPath -Arguments $productArgs -Purpose 'Product creation'
    $productId = [string] $product.id
    if ($productId -notmatch '^prod_') {
        throw "Product creation for $($price.Key) returned an unexpected object type."
    }

    $priceArgs = New-PriceArguments -Price $price -ProductId $productId -Live:$liveMode
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

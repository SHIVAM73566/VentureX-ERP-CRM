# VentureX Stripe Price ID mapper
# -----------------------------------------------------------------------------
# BEGINNER SAFETY:
#   - Preview mode is the default. Nothing is written and .env is untouched.
#   - Reads existing Stripe Products/Prices with the authenticated Stripe CLI.
#   - Maps Price IDs into C:\MY_ERP\.env by metadata.layer_key, exact product
#     name, or product name in the price nickname. It never creates Stripe
#     objects and never prints a real Product/Price/account ID.
#   - Live-mode listing requires the CLI's --live flag. Live-mode writes also
#     require explicit -AllowLive.
#   - -Apply backs up .env, skips already-set keys unless -Force is supplied,
#     preserves unrelated lines, and runs php artisan config:clear.
#   - No secret key, webhook secret or real Stripe ID is stored in this file.
# -----------------------------------------------------------------------------
param(
    [string] $EnvPath = 'C:\MY_ERP\.env',
    [string] $Currency = 'usd',
    [string] $HostUrl = '',
    [string] $StripeCli = 'stripe',
    [switch] $Apply,
    [switch] $Force,
    [switch] $AllowLive
)

$ErrorActionPreference = 'Stop'

$expected = @(
    [pscustomobject]@{ Key='updates_support';          EnvName='STRIPE_PRICE_UPDATES_SUPPORT';          Name='Updates & Support (Care)';                  Mode='recurring_year';  AmountCents=14900; Display='$149/year' },
    [pscustomobject]@{ Key='managed_hosting';          EnvName='STRIPE_PRICE_MANAGED_HOSTING';          Name='Managed Install & Maintenance';             Mode='recurring_month'; AmountCents=7900;  Display='$79/month' },
    [pscustomobject]@{ Key='ai_credits';               EnvName='STRIPE_PRICE_AI_CREDITS';               Name='AI Credits / Metered AI Add-On';            Mode='one_time';        AmountCents=4900;  Display='$49 bundle' },
    [pscustomobject]@{ Key='reporting_pack_adv';       EnvName='STRIPE_PRICE_REPORTING_PACK_ADV';       Name='Advanced Reporting';                       Mode='recurring_month'; AmountCents=2900;  Display='$29/month' },
    [pscustomobject]@{ Key='workflow_automation_plus'; EnvName='STRIPE_PRICE_WORKFLOW_AUTOMATION_PLUS'; Name='Workflow automation+ pack';                 Mode='recurring_month'; AmountCents=3900;  Display='$39/month' },
    [pscustomobject]@{ Key='integrations_priority';    EnvName='STRIPE_PRICE_INTEGRATIONS_PRIORITY';    Name='Priority integrations pack';                Mode='recurring_month'; AmountCents=4900;  Display='$49/month' },
    [pscustomobject]@{ Key='multi_branch_ops';         EnvName='STRIPE_PRICE_MULTI_BRANCH_OPS';         Name='Multi-branch / advanced operations';       Mode='recurring_month'; AmountCents=5900;  Display='$59/month' },
    [pscustomobject]@{ Key='compliance_hardening';     EnvName='STRIPE_PRICE_COMPLIANCE_HARDENING';     Name='Compliance & hardening add-on';             Mode='recurring_year';  AmountCents=9900;  Display='$99/year' },
    [pscustomobject]@{ Key='granular_rbac';            EnvName='STRIPE_PRICE_GRANULAR_RBAC';            Name='Granular roles & permissions';              Mode='recurring_month'; AmountCents=2900;  Display='$29/month' },
    [pscustomobject]@{ Key='security_patches';         EnvName='STRIPE_PRICE_SECURITY_PATCHES';         Name='Security updates & audit tooling';          Mode='recurring_year';  AmountCents=9900;  Display='$99/year' },
    [pscustomobject]@{ Key='capacity_expansion';       EnvName='STRIPE_PRICE_CAPACITY_EXPANSION';       Name='Capacity & module expansion';               Mode='recurring_month'; AmountCents=3900;  Display='$39/month' },
    [pscustomobject]@{ Key='tpl_manufacturing_plus';   EnvName='STRIPE_PRICE_TPL_MANUFACTURING_PLUS';   Name='Industry template: Manufacturing+';        Mode='one_time';        AmountCents=19900; Display='$199 once' },
    [pscustomobject]@{ Key='tpl_trading_distribution'; EnvName='STRIPE_PRICE_TPL_TRADING_DISTRIBUTION'; Name='Industry template: Trading & distribution'; Mode='one_time';        AmountCents=19900; Display='$199 once' },
    [pscustomobject]@{ Key='tpl_services_projects';    EnvName='STRIPE_PRICE_TPL_SERVICES_PROJECTS';    Name='Industry template: Services / projects';   Mode='one_time';        AmountCents=19900; Display='$199 once' },
    [pscustomobject]@{ Key='logistics_suite_adv';      EnvName='STRIPE_PRICE_LOGISTICS_SUITE_ADV';      Name='Advanced logistics suite';                  Mode='recurring_month'; AmountCents=7900;  Display='$79/month' },
    [pscustomobject]@{ Key='finance_suite_adv';        EnvName='STRIPE_PRICE_FINANCE_SUITE_ADV';        Name='Advanced finance suite';                    Mode='recurring_month'; AmountCents=7900;  Display='$79/month' },
    [pscustomobject]@{ Key='partner_white_label';      EnvName='STRIPE_PRICE_PARTNER_WHITE_LABEL';      Name='Partner/white-label enablement (scoped)';   Mode='one_time';        AmountCents=29900; Display='$299 once' },
    [pscustomobject]@{ Key='custom_dev_credits';       EnvName='STRIPE_PRICE_CUSTOM_DEV_CREDITS';       Name='Custom development credits';                Mode='one_time';        AmountCents=19900; Display='$199 bundle' }
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
    $firstBrace = $clean.IndexOf('{')
    $lastBrace = $clean.LastIndexOf('}')
    if ($firstBrace -ge 0 -and $lastBrace -gt $firstBrace) {
        $candidate = $clean.Substring($firstBrace, ($lastBrace - $firstBrace + 1))
        try {
            return ($candidate | ConvertFrom-Json)
        } catch {
            # Fall through to the safety error below.
        }
    }

    throw 'Stripe CLI returned unreadable JSON. Output was suppressed for safety.'
}

function Get-RedactedCliErrorLines {
    param(
        [string] $Text,
        [int] $ExitCode
    )

    $clean = [regex]::Replace($Text, "`e\[[0-9;?]*[A-Za-z]", '')
    $clean = [regex]::Replace($clean, '(?i)\b(sk|rk)_(live|test)_[A-Za-z0-9]+', '$1_$2_[REDACTED]')
    $clean = [regex]::Replace($clean, '\bwhsec_[A-Za-z0-9]+', 'whsec_[REDACTED]')
    $clean = [regex]::Replace($clean, '\bacct_[A-Za-z0-9]+', 'acct_[REDACTED]')
    $clean = [regex]::Replace($clean, '\b(price|prod|cus|sub|pi|evt|in|pm|cs|txn)_[A-Za-z0-9]+', '$1_[REDACTED]')
    $clean = [regex]::Replace($clean, '(?i)(api[_-]?key|secret|token|password|authorization)\s*[:=]\s*(""?)[^"\s,}]+\2', '$1=[REDACTED]')
    $clean = [regex]::Replace($clean, '\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b', '[EMAIL_REDACTED]')
    $clean = [regex]::Replace($clean, 'https?://\S+', '[URL_REDACTED]')

    $messages = New-Object System.Collections.Generic.List[string]
    try {
        $parsed = Convert-CliJsonText -Text $clean
        if ($parsed.error -and $parsed.error.message) {
            $messages.Add("Stripe: $([string] $parsed.error.message)")
        }
    } catch {
        # Plain-text CLI errors are handled below.
    }

    $noise = '(?i)(^\s*[\w.-]+\.(exe|cmd|ps1)\s*:|▸|^\s*At line:|^\s*\+|CategoryInfo|FullyQualifiedErrorId|^\s*~|^\s*\{\s*$|^\s*\})'
    $interesting = '(?i)error|invalid|failed|must|require|denied|unauthor|not found|missing|unexpected|cannot|you''?re|you are|expired|wrong|unrecognized|unknown|live mode|add --live'

    foreach ($line in ($clean -split "`r?`n")) {
        $text = $line.Trim()
        if ($text -eq '' -or $text -match $noise) { continue }
        if ($text -match $interesting) {
            if ($text.Length -gt 260) { $text = $text.Substring(0, 257) + '...' }
            $messages.Add($text)
        }
    }

    if ($messages.Count -eq 0) {
        $messages.Add("No non-sensitive CLI error message was returned (exit code $ExitCode).")
    }

    return @($messages | Select-Object -First 6)
}

function Get-StripeCliAuthInfo {
    param([string] $Cli)

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
            return [pscustomobject]@{
                Authenticated = $true
                Mode          = $mode
                DisplayLabel  = [string] $info.display_name
            }
        } catch {
            # Fall through to config-list detection.
        }
    }

    $result = Invoke-StripeCliText -Cli $Cli -Arguments @('config', '--list')
    if ($result.ExitCode -ne 0) {
        return [pscustomobject]@{ Authenticated=$false; Mode='unknown'; DisplayLabel='' }
    }

    $raw = $result.Text
    $apiKey = ''
    if ($raw -match '(?im)^\s*api_key\s*=\s*(\S+)') { $apiKey = $Matches[1] }
    $accountPresent = $raw -match '(?im)^\s*account_id\s*=\s*\S+'
    $displayLabel = ''
    if ($raw -match '(?im)^\s*display_name\s*=\s*(.*)$') { $displayLabel = $Matches[1].Trim() }
    $mode = if ($apiKey -match '^(sk|rk)_live_') {
        'live'
    } elseif ($apiKey -match '^(sk|rk)_test_') {
        'test'
    } else {
        'unknown'
    }
    $apiKey = $null

    return [pscustomobject]@{
        Authenticated = ((-not [string]::IsNullOrWhiteSpace($apiKey)) -or $accountPresent)
        Mode          = $mode
        DisplayLabel  = $displayLabel
    }
}

function Get-EnvValue {
    param([string[]] $Lines, [string] $Key)

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
    param([string[]] $Lines, [string] $Key, [string] $Value)

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
    if (-not $found) { $result.Add("$Key=$Value") }
    return $result.ToArray()
}

function Get-PriceMode {
    param([object] $Price)
    if ($Price.recurring -and $Price.recurring.interval) {
        return "recurring/$($Price.recurring.interval)"
    }
    return 'one-time'
}

function Get-ExpectedPriceMode {
    param([string] $Mode)
    if ($Mode -eq 'recurring_year') { return 'recurring/year' }
    if ($Mode -eq 'recurring_month') { return 'recurring/month' }
    return 'one-time'
}

if ($Currency -notmatch '^[a-z]{3}$') {
    throw 'Currency must be a three-letter lowercase code, for example usd.'
}
if ($expected.Count -ne 18) {
    throw "Expected 18 price entries; found $($expected.Count)."
}
if (-not (Test-Path -LiteralPath $EnvPath)) {
    throw "$EnvPath was not found. Copy .env.example to .env first."
}

$cliPath = Resolve-StripeCli -CommandName $StripeCli
$auth = Get-StripeCliAuthInfo -Cli $cliPath
$liveMode = $auth.Mode -eq 'live'

if (-not $auth.Authenticated) {
    throw 'Stripe CLI is not authenticated. Run `stripe login` in this Command Prompt, approve the browser window, then retry.'
}
if ($Apply -and $liveMode -and -not $AllowLive) {
    throw 'A LIVE Stripe account is active. Preview is safe, but writing live Price IDs requires explicit -AllowLive.'
}

$listArgs = @('prices', 'list', '--limit', '100')
if ($liveMode) { $listArgs += '--live' }
$listArgs += @('-e', 'data.product')

$listResult = Invoke-StripeCliText -Cli $cliPath -Arguments $listArgs
if ($listResult.ExitCode -ne 0) {
    $safe = Get-RedactedCliErrorLines -Text $listResult.Text -ExitCode ([int] $listResult.ExitCode)
    throw "Stripe price listing failed (exit code $($listResult.ExitCode)). Stripe CLI reported: $($safe -join ' | ')"
}

$response = Convert-CliJsonText -Text $listResult.Text
$allPrices = @($response.data)
$currencyLower = $Currency.ToLowerInvariant()
$prices = @($allPrices | Where-Object { ([string] $_.currency).ToLowerInvariant() -eq $currencyLower })

$lines = @(Get-Content -LiteralPath $EnvPath)
$pending = New-Object System.Collections.Generic.List[object]
$report = New-Object System.Collections.Generic.List[string]
$matchedCount = 0
$alreadySet = 0
$needsId = 0

foreach ($item in $expected) {
    $current = Get-EnvValue -Lines $lines -Key $item.EnvName
    $envStatus = if ([string]::IsNullOrWhiteSpace($current)) { 'missing' } else { 'set' }

    $metadataMatches = @($prices | Where-Object { $_.metadata -and $_.metadata.layer_key -eq $item.Key })
    $nameMatches = @($prices | Where-Object {
        $productName = if ($_.product -and $_.product.name) { [string] $_.product.name } else { '' }
        $productName -eq $item.Name
    })
    $nicknameMatches = @($prices | Where-Object { $_.nickname -and $_.nickname -match [regex]::Escape($item.Name) })
    $allMatches = @($metadataMatches + $nameMatches + $nicknameMatches | Group-Object -Property id | ForEach-Object { $_.Group[0] })
    $exactMatches = @($allMatches | Where-Object {
        [int] $_.unit_amount -eq [int] $item.AmountCents -and (Get-PriceMode -Price $_) -eq (Get-ExpectedPriceMode -Mode $item.Mode)
    })
    if ($exactMatches.Count -gt 0) { $allMatches = $exactMatches }

    $candidateCount = $allMatches.Count
    $matchSource = if ($metadataMatches.Count -gt 0) { 'metadata' } elseif ($nameMatches.Count -gt 0) { 'product-name' } elseif ($nicknameMatches.Count -gt 0) { 'nickname' } else { 'none' }

    if ($candidateCount -eq 0) {
        $needsId++
        $action = if ($envStatus -eq 'set') { 'keep-existing' } else { 'needs-id' }
        $report.Add("$($item.Key): stripe_found=False env=$envStatus action=$action match_source=none")
        continue
    }

    $matchedCount++
    $nonCurrent = @($allMatches | Where-Object { $_.active -eq $true } | Sort-Object -Property created -Descending)
    $candidates = if ($nonCurrent.Count -gt 0) { $nonCurrent } else { @($allMatches | Sort-Object -Property created -Descending) }
    $chosen = $candidates[0]
    $chosenId = [string] $chosen.id

    if ($envStatus -eq 'set' -and -not $Force) {
        $alreadySet++
        $action = 'already-set'
    } elseif ($envStatus -eq 'set' -and $Force) {
        $action = 'replace'
        $pending.Add([pscustomobject]@{ EnvName=$item.EnvName; PriceId=$chosenId; Key=$item.Key })
    } else {
        $action = 'write'
        $pending.Add([pscustomobject]@{ EnvName=$item.EnvName; PriceId=$chosenId; Key=$item.Key })
    }

    $amountDisplay = 'unknown'
    if ($null -ne $chosen.unit_amount) {
        $amountDisplay = '$' + ([decimal] $chosen.unit_amount / 100).ToString('0.00') + ' ' + ([string] $chosen.currency).ToUpperInvariant()
    }
    $productDisplay = if ($chosen.product -and $chosen.product.name) { [string] $chosen.product.name } else { '' }
    $modeDisplay = Get-PriceMode -Price $chosen
    $report.Add("$($item.Key): stripe_found=True env=$envStatus action=$action match_source=$matchSource candidates=$candidateCount active=$([bool] $chosen.active) price=$amountDisplay mode=$modeDisplay product='$productDisplay'")
}

$authLabel = if ($liveMode) { 'authenticated - LIVE account' } elseif ($auth.Mode -eq 'test') { 'authenticated - TEST account' } else { 'authenticated - mode unknown' }
Write-Host "VentureX Stripe price ID mapper"
Write-Host "Mode: $(if ($Apply) { 'APPLY' } else { 'DRY RUN (preview only)' })"
Write-Host "CLI auth: $authLabel"
Write-Host "Currency: $Currency"
Write-Host "Stripe prices listed: $($allPrices.Count); matching currency: $($prices.Count)"
Write-Host "Price entries: $($expected.Count); mapped in Stripe: $matchedCount; already set: $alreadySet; needs ID: $needsId; will write: $($pending.Count)"
if ($Force) { Write-Host 'Force is ON: existing Price IDs may be replaced.' }

foreach ($line in $report) { Write-Host $line }

if ($needsId -gt 0) {
    Write-Host 'Warning: some layer keys could not be matched to a Stripe Price. Add metadata[layer_key] to those Products/Prices, or paste the correct Price ID directly into the matching .env key.'
}

if (-not $Apply) {
    Write-Host 'Dry run complete. .env was not changed. Run with -Apply (and -AllowLive for a live CLI account) to write Price IDs.'
    return
}

if ($pending.Count -eq 0 -and [string]::IsNullOrWhiteSpace($HostUrl)) {
    Write-Host 'Nothing to write. Every Price ID is already set.'
    return
}

$backup = "$EnvPath.bak-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
Copy-Item -LiteralPath $EnvPath -Destination $backup
Write-Host "Backup created: $backup"

foreach ($entry in $pending) {
    $lines = Set-EnvValue -Lines $lines -Key $entry.EnvName -Value $entry.PriceId
}

if (-not [string]::IsNullOrWhiteSpace($HostUrl)) {
    $base = $HostUrl.TrimEnd('/')
    $returnUrl = "$base/admin/settings?tab=plan"
    foreach ($key in @('STRIPE_CHECKOUT_SUCCESS_URL', 'STRIPE_CHECKOUT_CANCEL_URL', 'STRIPE_BILLING_PORTAL_RETURN_URL')) {
        $lines = Set-EnvValue -Lines $lines -Key $key -Value $returnUrl
    }
}

[System.IO.File]::WriteAllLines($EnvPath, $lines, (New-Object System.Text.UTF8Encoding($false)))

Push-Location (Split-Path -Parent $EnvPath)
try {
    & php artisan config:clear | Out-Null
} catch {
    Write-Warning 'Could not run `php artisan config:clear` automatically. Run it manually from C:\MY_ERP.'
} finally {
    Pop-Location
}

Write-Host "Done. Wrote $($pending.Count) Price ID(s) to .env (values hidden)."
Write-Host 'Do not commit .env.'

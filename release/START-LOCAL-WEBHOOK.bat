@echo off
cd /d C:\MY_ERP
echo VentureX local Stripe webhook setup
echo.

echo Checking Stripe CLI login...
powershell -NoProfile -Command "$o = & stripe config --list 2>&1 | Out-String; if ($o -notmatch '(?i)(api[_-]?key|account_id)\s*=') { Write-Host 'Stripe CLI is not logged in. Opening stripe login...'; & stripe login }"

echo.
echo Checking app Stripe mode (value is never shown)...
powershell -NoProfile -Command "$c = Get-Content -LiteralPath 'C:\MY_ERP\.env' -Raw; if ($c -match '(?m)^\s*STRIPE_SECRET\s*=\s*(sk|rk)_live_') { Write-Host 'APP_MODE=LIVE' } elseif ($c -match '(?m)^\s*STRIPE_SECRET\s*=\s*(sk|rk)_test_') { Write-Host 'APP_MODE=TEST' } else { Write-Host 'APP_MODE=UNKNOWN' }"

echo.
echo Choose SANDBOX / TEST mode for safe local testing.
echo If the next screen asks, select your sandbox account (not live).
stripe switch

echo.
echo Checking local Laravel server on port 8000...
powershell -NoProfile -Command "$ok = Test-NetConnection -ComputerName 127.0.0.1 -Port 8000 -InformationLevel Quiet -WarningAction SilentlyContinue; if (-not $ok) { Write-Host 'Starting local server in a new window...'; Start-Process cmd -ArgumentList '/c','cd /d C:\MY_ERP && php artisan serve'; Start-Sleep -Seconds 3 } else { Write-Host 'Local server is already running.' }"

echo.
echo If APP_MODE=LIVE above, this sandbox listener is for practice events only.
echo For real checkout events, either add a TEST key with ADD-STRIPE-KEY.bat
echo or run the listen command with --live. Do not use a real card for practice.
echo.
echo Starting Stripe webhook forwarding with all required events.
echo Copy the whsec_ value Stripe prints, then run SET-WEBHOOK-SECRET.bat.
echo.
stripe listen --forward-to http://127.0.0.1:8000/billing/stripe/webhook --events checkout.session.completed,checkout.session.async_payment_succeeded,invoice.paid,invoice.payment_failed,customer.subscription.updated,customer.subscription.deleted
echo.
pause

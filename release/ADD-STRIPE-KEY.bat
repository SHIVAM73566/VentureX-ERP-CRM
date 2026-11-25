@echo off
cd /d C:\MY_ERP
echo Stripe secret setup
echo.
echo Paste your NEW Stripe secret key when asked. It will be hidden while you type.
echo If you do not have the webhook secret yet, just press Enter at the second question.
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "C:\MY_ERP\release\setup-stripe-env.ps1" -HostUrl "http://127.0.0.1:8000"
echo.
pause

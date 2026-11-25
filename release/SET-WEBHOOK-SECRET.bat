@echo off
cd /d C:\MY_ERP
echo Save Stripe webhook secret
echo.
echo Paste the whsec_ value shown by Stripe CLI or Stripe Workbench.
echo It will be hidden while you type and saved only in C:\MY_ERP\.env.
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "C:\MY_ERP\release\set-stripe-webhook-secret.ps1"
echo.
pause

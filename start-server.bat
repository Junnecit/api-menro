@echo off
cd /d "%~dp0"

start "Laravel API" cmd /k "C:\Server\Uniform\1402\core\php83\php.exe" artisan serve --host 0.0.0.0 --port 8000

start "ngrok tunnel" cmd /k "C:\Users\Hp\AppData\Local\Microsoft\WinGet\Packages\Ngrok.Ngrok_Microsoft.Winget.Source_8wekyb3d8bbwe\ngrok.exe" http --url=exodus-grew-chemicals.ngrok-free.dev 8000

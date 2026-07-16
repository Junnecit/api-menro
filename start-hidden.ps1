$php = "C:\Server\Uniform\1402\core\php83\php.exe"
$ngrok = "C:\Users\Hp\AppData\Local\Microsoft\WinGet\Packages\Ngrok.Ngrok_Microsoft.Winget.Source_8wekyb3d8bbwe\ngrok.exe"
$apiDir = "C:\Users\Hp\Tree Monitoring\api-menro"

Start-Process -FilePath $php -ArgumentList "artisan","serve","--host","0.0.0.0","--port","8000" -WorkingDirectory $apiDir -WindowStyle Hidden
Start-Process -FilePath $ngrok -ArgumentList "http","--url=exodus-grew-chemicals.ngrok-free.dev","8000" -WindowStyle Hidden

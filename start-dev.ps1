$php = 'C:\xampp\php\php.exe'
$bindHost = '127.0.0.1'
$port = 8000

if (-not (Test-Path $php)) {
    Write-Error "PHP executable not found at $php"
    exit 1
}

Write-Host "Starting PHP development server at http://${bindHost}:$port/conference.php"
& $php -S "${bindHost}:$port" -t $PSScriptRoot

[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'

$candidatePaths = @(
    'C:\nvm4w\nodejs'
    (Join-Path $env:APPDATA 'npm')
) | Where-Object { $_ -and (Test-Path -LiteralPath $_) }
if ($candidatePaths.Count) {
    $env:PATH = (($candidatePaths + $env:PATH) -join [IO.Path]::PathSeparator)
}

$logDir = Join-Path $PSScriptRoot '..\logs'
New-Item -ItemType Directory -Force -Path $logDir | Out-Null
$logPath = Join-Path $logDir 'n8n.log'

if ((Test-Path -LiteralPath $logPath) -and (Get-Item -LiteralPath $logPath).Length -gt 10MB) {
    $archive = Join-Path $logDir ("n8n-{0}.log" -f (Get-Date -Format 'yyyyMMdd-HHmmss'))
    Move-Item -LiteralPath $logPath -Destination $archive
}

$n8nCommand = Get-Command 'n8n.cmd', 'n8n' -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $n8nCommand) {
    throw 'No se encontró n8n en PATH. Instálalo en el perfil del usuario antes de registrar la tarea.'
}

# Equivalent command: n8n start. No se cambia N8N_USER_FOLDER, por lo que conserva el directorio de datos actual.
& $n8nCommand.Source start *>> $logPath
exit $LASTEXITCODE


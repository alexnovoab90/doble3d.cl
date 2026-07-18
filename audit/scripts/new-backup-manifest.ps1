[CmdletBinding()]
param(
    [Parameter(Mandatory)]
    [string]$BackupDir
)

$ErrorActionPreference = 'Stop'
$resolved = (Resolve-Path -LiteralPath $BackupDir).Path
$manifestPath = Join-Path $resolved 'MANIFEST.md'
$files = Get-ChildItem -LiteralPath $resolved -File -Recurse |
    Where-Object FullName -ne $manifestPath |
    Sort-Object FullName

$lines = [System.Collections.Generic.List[string]]::new()
$lines.Add('# Manifiesto de respaldo')
$lines.Add('')
$lines.Add("Generado: $((Get-Date).ToUniversalTime().ToString('o'))")
$lines.Add('')
$lines.Add('Estado de base de datos: **PENDIENTE**. No se autoriza ninguna escritura remota hasta incorporar y validar un export SQL completo de WordPress.')
$lines.Add('')
$lines.Add('| Origen remoto | Ruta local | Bytes | SHA-256 | Modificado UTC | Restauración |')
$lines.Add('|---|---|---:|---|---|---|')

foreach ($file in $files) {
    $relative = $file.FullName.Substring($resolved.Length).TrimStart('\') -replace '\\', '/'
    $remote = '/' + $relative
    $hash = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash.ToLowerInvariant()
    $restore = "curl --ssl-reqd --ftp-ssl-control --user `<usuario-ftp`>:`<password`> --upload-file `"$relative`" `"ftps://eagle.hostingplus.cl$remote`""
    $lines.Add("| ``$remote`` | ``$relative`` | $($file.Length) | ``$hash`` | $($file.LastWriteTimeUtc.ToString('o')) | ``$restore`` |")
}

$lines.Add('')
$lines.Add('## Validación antes de restaurar')
$lines.Add('')
$lines.Add('1. Verificar el SHA-256 del archivo local.')
$lines.Add('2. Confirmar la ruta remota y conservar una copia adicional fuera de `public_html`.')
$lines.Add('3. Restaurar primero en staging; validar HTTP, PHP y WordPress antes de producción.')
$lines.Add('4. La restauración de base de datos debe usar el procedimiento de cPanel/phpMyAdmin y el export SQL validado.')

$lines | Set-Content -LiteralPath $manifestPath -Encoding utf8
Write-Output "Manifiesto guardado: $manifestPath ($($files.Count) archivos)"

[CmdletBinding()]
param(
    [string]$OutputDir = (Join-Path $PSScriptRoot '..\baseline')
)

$ErrorActionPreference = 'Stop'
New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$outputPath = Join-Path $OutputDir "site-$stamp.json"

$urls = @(
    'https://doble3d.cl/'
    'https://doble3d.cl/blog/'
    'https://doble3d.cl/servicios/realidad-virtual/'
    'https://doble3d.cl/servicios/animacion-3d/'
    'https://doble3d.cl/servicios/core/'
    'https://doble3d.cl/servicios/gamificacion-scorm/'
    'https://doble3d.cl/sitemap_index.xml'
)

$rows = foreach ($url in $urls) {
    $response = Invoke-WebRequest -Uri $url -MaximumRedirection 5 -UseBasicParsing
    $body = [string]$response.Content
    $title = [regex]::Match(
        $body,
        '<title[^>]*>(.*?)</title>',
        [System.Text.RegularExpressions.RegexOptions]'IgnoreCase,Singleline'
    ).Groups[1].Value.Trim()

    $description = ''
    foreach ($meta in [regex]::Matches($body, '<meta\s+[^>]*>', 'IgnoreCase')) {
        if ($meta.Value -notmatch 'name\s*=\s*["'']description["'']') { continue }
        $contentMatch = [regex]::Match($meta.Value, 'content\s*=\s*["'']([^"'']*)', 'IgnoreCase')
        if ($contentMatch.Success) { $description = $contentMatch.Groups[1].Value.Trim() }
        break
    }

    [pscustomobject]@{
        capturedAt       = (Get-Date).ToUniversalTime().ToString('o')
        url              = $url
        status           = [int]$response.StatusCode
        title            = $title
        titleLength      = $title.Length
        description      = $description
        descriptionLength = $description.Length
    }
}

$rows | ConvertTo-Json -Depth 4 | Set-Content -Path $outputPath -Encoding utf8
Write-Output "Baseline guardada: $outputPath"
$rows | Select-Object url, status, titleLength, descriptionLength | Format-Table -AutoSize


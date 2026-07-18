[CmdletBinding()]
param(
    [Parameter(Mandatory)]
    [string]$InventoryCsv,
    [string]$OutputDir = (Join-Path $PSScriptRoot '..\editorial')
)

$ErrorActionPreference = 'Stop'
New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null
$posts = Import-Csv -LiteralPath $InventoryCsv
$allowed = 'realidad virtual|\bvr\b|\bxr\b|realidad mixta|animaci[oó]n 3d|scorm|\bcore\b|capacitaci[oó]n|minería|mineria|industrial|seguridad|gemelo digital|webgl|inteligencia artificial'
$blocked = 'iphone|ipad|macbook|switch 2|cirug[ií]a|gaming de consumo|precios de celulares'

$matrix = foreach ($post in $posts) {
    $searchText = "$($post.title) $($post.slug)"
    $relevance = if ($searchText -match $blocked) { 'low' } elseif ($searchText -match $allowed) { 'high' } else { 'medium' }
    $quality = if ([int]$post.words -ge 800 -and [int]$post.internalLinks -ge 1 -and [int]$post.externalSources -ge 1) {
        'strong'
    } elseif ([int]$post.words -ge 500) {
        'medium'
    } else {
        'weak'
    }

    $decision = if ($post.malformedTitle -eq 'True') {
        'improve'
    } elseif ($relevance -eq 'high' -and $quality -eq 'strong') {
        'keep'
    } else {
        'improve'
    }

    $reason = if ($post.malformedTitle -eq 'True') {
        'Sanear HTML del título; conservar URL y slug.'
    } elseif ($relevance -eq 'low') {
        'Fuera del nicho principal, pero sin métricas de 90 días no se permite retirar; requiere revisión manual.'
    } elseif ($decision -eq 'keep') {
        'Relevante y con señales técnicas suficientes en el inventario público.'
    } else {
        'Revisar profundidad, fuentes, metadatos y enlazado antes de decidir consolidación.'
    }

    [pscustomobject][ordered]@{
        id = $post.id
        url = $post.link
        title = if ($post.sanitizedTitle) { $post.sanitizedTitle } else { $post.title }
        organicClicks90d = ''
        impressions90d = ''
        backlinks = ''
        relevance = $relevance
        quality = $quality
        canibalization = ''
        decision = $decision
        targetUrl = ''
        reason = $reason
        approvedBy = ''
        implementedAt = ''
    }
}

$matrixPath = Join-Path $OutputDir 'editorial-decision-matrix.csv'
$previewPath = Join-Path $OutputDir 'malformed-title-preview.csv'
$redirectPath = Join-Path $OutputDir 'redirect-map.csv'
$matrix | Export-Csv -LiteralPath $matrixPath -NoTypeInformation -Encoding utf8
$posts | Where-Object malformedTitle -eq 'True' |
    Select-Object id,link,@{n='currentTitle';e={$_.title}},@{n='proposedTitle';e={$_.sanitizedTitle}},slug |
    Export-Csv -LiteralPath $previewPath -NoTypeInformation -Encoding utf8
'sourceUrl,targetUrl,status,reason,approvedBy,implementedAt' | Set-Content -LiteralPath $redirectPath -Encoding utf8

Write-Output "Matriz: $matrixPath ($($matrix.Count) posts)"
Write-Output "Títulos a revisar: $(@($posts | Where-Object malformedTitle -eq 'True').Count)"
Write-Output 'Métricas no disponibles: no se asignaron decisiones retire.'


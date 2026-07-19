[CmdletBinding()]
param(
    [string]$OutputDir
)

$ErrorActionPreference = 'Stop'
if ([string]::IsNullOrWhiteSpace($OutputDir)) {
    $OutputDir = Join-Path $PSScriptRoot '..\baseline'
}
New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$outputPath = Join-Path $OutputDir "posts-$stamp.csv"
$api = 'https://doble3d.cl/wp-json/wp/v2/posts?per_page=100&_fields=id,date,modified,slug,link,title,content,excerpt,yoast_head_json'
$posts = Invoke-RestMethod -Uri $api -MaximumRedirection 5

$rows = foreach ($post in $posts) {
    $title = [string]$post.title.rendered
    $sanitizedTitle = [Net.WebUtility]::HtmlDecode(($title -replace '<[^>]+>', ' '))
    $sanitizedTitle = ($sanitizedTitle -replace '\s+', ' ').Trim()
    $content = [string]$post.content.rendered
    $plainText = (($content -replace '<[^>]+>', ' ') -replace '\s+', ' ').Trim()
    $wordCount = if ([string]::IsNullOrWhiteSpace($plainText)) { 0 } else { ($plainText -split '\s+').Count }

    [pscustomobject]@{
        id              = $post.id
        date            = $post.date
        modified        = $post.modified
        slug            = $post.slug
        link            = $post.link
        title           = $title
        malformedTitle  = ($title -match '^\s*<')
        sanitizedTitle  = $sanitizedTitle
        words           = $wordCount
        internalLinks   = ([regex]::Matches($content, 'href=["''](?:https://doble3d\.cl/|/)')).Count
        externalSources = ([regex]::Matches($content, 'href=["'']https?://(?!doble3d\.cl|wa\.me)')).Count
        metaTitle       = [string]$post.yoast_head_json.title
        metaDescription = [string]$post.yoast_head_json.description
    }
}

$rows | Export-Csv -Path $outputPath -NoTypeInformation -Encoding utf8
$malformedCount = @($rows | Where-Object malformedTitle).Count
Write-Output "Inventario guardado: $outputPath"
Write-Output "Publicaciones: $($rows.Count); títulos con HTML: $malformedCount"

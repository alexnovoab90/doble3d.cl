[CmdletBinding()]
param(
    [uri]$Uri = 'http://localhost:5678/healthz',
    [ValidateRange(1, 30)]
    [int]$TimeoutSec = 5
)

$ErrorActionPreference = 'Stop'
$response = Invoke-WebRequest -UseBasicParsing -Uri $Uri -TimeoutSec $TimeoutSec
if ($response.StatusCode -ne 200) {
    throw "n8n health check failed: $($response.StatusCode)"
}

Write-Output 'n8n healthy'


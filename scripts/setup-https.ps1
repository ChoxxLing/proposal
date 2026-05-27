param(
    [string] $IpAddress
)

$ErrorActionPreference = 'Stop'

function Get-ProjectRoot {
    return (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
}

function Test-IPv4Address {
    param([string] $Value)

    $parsed = $null
    return [System.Net.IPAddress]::TryParse($Value, [ref] $parsed) -and $parsed.AddressFamily -eq [System.Net.Sockets.AddressFamily]::InterNetwork
}

function Get-LanIPv4Candidates {
    $adapterNamePattern = 'docker|wsl|hyper-v|vmware|virtualbox|vethernet|loopback|bluetooth|npcap'
    $addresses = @()

    try {
        $addresses = Get-NetIPAddress -AddressFamily IPv4 |
            Where-Object {
                $_.IPAddress -notlike '127.*' -and
                $_.IPAddress -notlike '169.254.*' -and
                $_.IPAddress -notlike '172.16.*' -and
                $_.IPAddress -notlike '172.17.*' -and
                $_.IPAddress -notlike '172.18.*' -and
                $_.IPAddress -notlike '172.19.*' -and
                $_.IPAddress -notlike '172.2?.*' -and
                $_.IPAddress -notlike '172.3?.*' -and
                $_.InterfaceAlias -notmatch $adapterNamePattern
            } |
            Sort-Object -Property InterfaceMetric, IPAddress |
            Select-Object -ExpandProperty IPAddress
    } catch {
        $currentAdapter = ''
        $addresses = ipconfig | ForEach-Object {
            $line = $_
            if ($line -match 'adapter\s+(.+):$') {
                $currentAdapter = $Matches[1]
                return
            }

            if ($currentAdapter -match $adapterNamePattern) {
                return
            }

            if ($line -match 'IPv4 Address.*:\s*([0-9.]+)') {
                $Matches[1]
            }
        } | Where-Object {
            $_ -and
            $_ -notlike '127.*' -and
            $_ -notlike '169.254.*'
        }
    }

    return @($addresses | Select-Object -Unique)
}

$mkcert = Get-Command mkcert -ErrorAction SilentlyContinue
if (-not $mkcert) {
    Write-Error 'mkcert was not found on PATH. Install mkcert first, then rerun this script. See README.md for install notes.'
}

if ($IpAddress) {
    if (-not (Test-IPv4Address $IpAddress)) {
        Write-Error "Invalid IPv4 address: $IpAddress"
    }
    $selectedIp = $IpAddress
} else {
    $candidates = Get-LanIPv4Candidates
    if ($candidates.Count -eq 0) {
        Write-Error 'No LAN IPv4 address was detected. Rerun with -IpAddress <computer-ip>.'
    }

    $selectedIp = $candidates[0]
    if ($candidates.Count -gt 1) {
        Write-Host 'Multiple possible LAN IP addresses were found:'
        $candidates | ForEach-Object { Write-Host "  $_" }
        Write-Host "Using $selectedIp. If that is wrong, rerun with -IpAddress <computer-ip>."
    }
}

$projectRoot = Get-ProjectRoot
$certDir = Join-Path $projectRoot 'docker\certs'
$certFile = Join-Path $certDir 'local-cert.pem'
$keyFile = Join-Path $certDir 'local-key.pem'

New-Item -ItemType Directory -Force -Path $certDir | Out-Null

& $mkcert.Source -install
& $mkcert.Source -cert-file $certFile -key-file $keyFile localhost 127.0.0.1 $selectedIp

Write-Host ''
Write-Host 'HTTPS certificate generated for Docker.'
Write-Host "Computer URL: https://localhost:8443/admin.php"
Write-Host "Phone URL:    https://$selectedIp`:8443/admin.php"
Write-Host ''
Write-Host 'If your phone does not trust the page, install the mkcert root CA on the phone.'
Write-Host 'Find the CA folder with: mkcert -CAROOT'

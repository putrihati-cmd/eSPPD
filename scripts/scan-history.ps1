# PowerShell helper to scan repo history with gitleaks (prefers Docker, fallback to download)
Set-StrictMode -Version Latest

$repo = (Get-Location).Path
$gitleaks = "$repo\bin\gitleaks"

function Run-Gitleaks($args){
    $useBinary = $false
    if ((Test-Path "$env:ProgramFiles\Docker\Docker\docker.exe") -or (Get-Command docker -ErrorAction SilentlyContinue)){
        Write-Host "Attempting to run gitleaks via Docker..."
        try {
            $vol = "${repo}:/repo"
            docker run --rm -v "$vol" zricethezav/gitleaks detect --repo-path /repo --report-path /repo/gitleaks-report.json --redact -v
        } catch {
            Write-Host "Docker run failed: $_. Falling back to binary download."
            $useBinary = $true
        }
    } else {
        $useBinary = $true
    }

    if ($useBinary) {
        if (-not (Test-Path $gitleaks)){
            Write-Host "Downloading gitleaks binary..."
            $tmp = "$repo/gitleaks.tmp"
            try {
                Write-Host "Resolving latest gitleaks release via GitHub API..."
                $release = Invoke-RestMethod -Uri "https://api.github.com/repos/zricethezav/gitleaks/releases/latest" -ErrorAction Stop
                $asset = $release.assets | Where-Object { $_.name -match 'windows.*(x64|x86_64|x64.zip|x64.tar.gz)' } | Select-Object -First 1
                if (-not $asset) { throw "No suitable Windows asset found in gitleaks releases" }
                $downloadUrl = $asset.browser_download_url
                Write-Host "Downloading: $($asset.name)"
                Invoke-WebRequest -Uri $downloadUrl -OutFile $tmp -UseBasicParsing -ErrorAction Stop
            } catch {
                Write-Host "Download via API/Invoke-WebRequest failed: $_. Trying curl fallback..."
                try { curl -sL $downloadUrl -o $tmp } catch { Write-Host "curl fallback failed: $_"; return }
            }
            mkdir bin -ErrorAction SilentlyContinue
            if ($tmp -like "*.zip") { Expand-Archive -Path $tmp -DestinationPath bin -Force } else { tar -xzf $tmp -C bin }
        }
        Write-Host "Running gitleaks binary..."
        & $gitleaks detect --repo-path . --report-path gitleaks-report.json --redact
    }
}

Run-Gitleaks $null
Write-Host "Report written to gitleaks-report.json if any findings were found."

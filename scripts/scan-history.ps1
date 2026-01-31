# PowerShell helper to scan repo history with gitleaks (prefers Docker, fallback to download)
Set-StrictMode -Version Latest

$repo = (Get-Location).Path
$gitleaks = "$repo\bin\gitleaks"

function Run-Gitleaks($args){
    if (Test-Path "$env:ProgramFiles\Docker\Docker\docker.exe" -or (Get-Command docker -ErrorAction SilentlyContinue)){
        Write-Host "Running gitleaks via Docker..."
        docker run --rm -v $repo:/repo zricethezav/gitleaks detect --repo-path /repo --report-path /repo/gitleaks-report.json --redact
    } else {
        if (-not (Test-Path $gitleaks)){
            Write-Host "Downloading gitleaks binary..."
            $tmp = "$repo/gitleaks.tgz"
            Invoke-WebRequest -Uri "https://github.com/zricethezav/gitleaks/releases/latest/download/gitleaks_Windows_x86_64.tar.gz" -OutFile $tmp -UseBasicParsing
            mkdir bin -ErrorAction SilentlyContinue
            tar -xzf $tmp -C bin
        }
        Write-Host "Running gitleaks binary..."
        & $gitleaks detect --repo-path . --report-path gitleaks-report.json --redact
    }
}

Run-Gitleaks $null
Write-Host "Report written to gitleaks-report.json if any findings were found."

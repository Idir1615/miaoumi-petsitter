$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
Push-Location -LiteralPath $projectRoot
try {
    $history = & git -c core.quotePath=false log --date=iso-strict --pretty=format:'%n## %h — %s%n%nDate : %aI%nAuteur : %an%n' --name-status
    if ($LASTEXITCODE -ne 0) { throw 'Impossible de lire les commits Git.' }
    $logDirectory = Join-Path $projectRoot 'logs'
    New-Item -ItemType Directory -Force -Path $logDirectory | Out-Null
    $header = "# Historique Git généré automatiquement`n`nRecréé depuis les commits accessibles sur la branche courante. Ne pas modifier manuellement.`n"
    $utf8 = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText((Join-Path $logDirectory 'git-history.md'), ($header + ($history -join "`n") + "`n"), $utf8)
    Write-Output 'Historique généré : logs/git-history.md'
}
finally {
    Pop-Location
}

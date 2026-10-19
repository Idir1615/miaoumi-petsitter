$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
Push-Location -LiteralPath $projectRoot
try {
    $currentHooks = & git config --get core.hooksPath
    if ($LASTEXITCODE -notin @(0, 1)) { throw 'Impossible de lire la configuration Git.' }
    if ($currentHooks -and $currentHooks -ne '.githooks') {
        throw "Des hooks sont déjà configurés ($currentHooks). Installation interrompue pour les préserver."
    }
    $legacyHook = Join-Path $projectRoot '.git/hooks/post-commit'
    if (-not $currentHooks -and (Test-Path -LiteralPath $legacyHook)) {
        throw 'Un hook post-commit existe déjà dans .git/hooks. Installation interrompue pour le préserver.'
    }
    & git config --local core.hooksPath .githooks
    if ($LASTEXITCODE -ne 0) { throw 'Impossible de configurer les hooks Git.' }
    & (Join-Path $PSScriptRoot 'generate-history.ps1')
    Write-Output 'Journaux automatiques activés après chaque commit.'
}
finally {
    Pop-Location
}

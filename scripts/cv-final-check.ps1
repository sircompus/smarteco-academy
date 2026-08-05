$ErrorActionPreference = "Stop"

function Invoke-Step {
    param(
        [Parameter(Mandatory = $true)]
        [string] $Title,

        [Parameter(Mandatory = $true)]
        [scriptblock] $Command
    )

    Write-Host ""
    Write-Host "=== $Title ===" -ForegroundColor Cyan

    & $Command

    if ($LASTEXITCODE -ne 0) {
        throw "Échec : $Title"
    }
}

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

Write-Host "Validation finale du module CV" -ForegroundColor Green
Write-Host "Projet : $projectRoot"

Invoke-Step "Nettoyage des caches Laravel" {
    php artisan optimize:clear
}

Invoke-Step "Vérification syntaxique du support CV" {
    php -l .\app\Support\CvTextFormatter.php
}

Invoke-Step "Pint sur les fichiers PHP du module CV" {
    .\vendor\bin\pint `
        .\app\Support\CvTextFormatter.php `
        .\app\Http\Controllers\Student\CvController.php `
        .\app\Http\Controllers\Admin\CvController.php `
        .\app\Http\Controllers\Admin\CvBuilderController.php `
        .\tests\Feature\Cv `
        .\tests\Unit\Cv
}

Invoke-Step "Tests complets du module CV" {
    php artisan test tests\Feature\Cv tests\Unit\Cv
}

Invoke-Step "Construction des assets front-end" {
    npm.cmd run build
}

Invoke-Step "Vérification des espaces et conflits Git" {
    git diff --check
}

Write-Host ""
Write-Host "VALIDATION CV RÉUSSIE" -ForegroundColor Green
Write-Host "Le module est prêt pour la vérification visuelle et le commit."

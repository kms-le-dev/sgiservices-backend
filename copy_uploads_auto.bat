@echo off
REM Script à placer dans le dossier backend
REM Ce script surveille le dossier uploads et copie automatiquement les nouveaux fichiers dans public/storage/uploads

set "SRC=%~dp0storage\app\public\uploads"
set "DST=%~dp0public\storage\uploads"

REM Boucle infinie pour surveiller les nouveaux fichiers
:loop
robocopy "%SRC%" "%DST%" /E /XO /NFL /NDL /NJH /NJS >nul
REM Attendre 10 secondes avant de vérifier à nouveau
ping -n 11 127.0.0.1 >nul
GOTO loop

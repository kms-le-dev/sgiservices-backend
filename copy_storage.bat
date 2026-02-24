@echo off
REM Copie les images uploadées dans le dossier public pour Windows
robocopy "%~dp0storage\app\public" "%~dp0public\storage" /E
exit /b 0

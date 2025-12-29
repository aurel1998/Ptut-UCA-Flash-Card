@echo off
echo ========================================
echo   Demarrage du serveur PHP
echo ========================================
echo.
echo Le serveur sera accessible sur :
echo   http://localhost:8001
echo   http://127.0.0.1:8001
echo.
echo Pages de test :
echo   http://localhost:8001/test.php
echo   http://localhost:8001/verifier_tables.php
echo.
echo Appuyez sur Ctrl+C pour arreter le serveur
echo.
cd /d "%~dp0"
echo Repertoire : %CD%
echo.
php -S localhost:8001 router.php
pause


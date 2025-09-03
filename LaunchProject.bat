@echo off
REM =========================================
REM CONFIGURATION
REM =========================================
set PROJECT_PATH=D:\ProjetSymfony7.3\projetSymfonySortie
set PHP_PATH=C:\wamp64\bin\php\php8.3.14\php.exe
set SYMFONY_PATH=C:\wamp64\bin\php\php8.3.14\symfony.exe
set CONSOLE=%PROJECT_PATH%\bin\console
set BATCH_UPDATE=%PROJECT_PATH%\update_sortie.bat

REM =========================================
REM 1. LANCER LE SERVEUR SYMFONY
REM =========================================
echo Vérification du serveur Symfony...
tasklist /FI "WINDOWTITLE eq Symfony Server*" | find /I "Symfony Server" >nul
if errorlevel 1 (
    echo Serveur non trouvé, lancement...
    start "Symfony Server" cmd /k "%SYMFONY_PATH% server:start --no-tls --dir=%PROJECT_PATH%"
) else (
    echo Serveur déjà en cours d'exécution.
)

REM =========================================
REM 2. LANCER LE WORKER MESSENGER
REM =========================================
echo Vérification du worker Messenger...
tasklist /FI "WINDOWTITLE eq Messenger Worker*" | find /I "Messenger Worker" >nul
if errorlevel 1 (
    echo Worker non trouvé, lancement...
    start "Messenger Worker" cmd /k ":loop
%PHP_PATH% %CONSOLE% messenger:consume async --time-limit=3600 --memory-limit=128M --limit=50
goto loop"
) else (
    echo Worker déjà en cours d'exécution.
)

REM =========================================
REM 3. CRÉER LE BAT POUR LA TÂCHE PLANIFIÉE (UpdateSortie)
REM =========================================
echo Création du fichier bat pour la tâche planifiée...
(
echo @echo off
echo cd /d %PROJECT_PATH%
echo "%PHP_PATH%" "%CONSOLE%" app:update-sortie --env=dev ^> "%PROJECT_PATH%\var\log\update_sortie.log" 2^>^&1
) > "%BATCH_UPDATE%"

REM =========================================
REM 4. CRÉER OU METTRE À JOUR LA TÂCHE PLANIFIÉE
REM =========================================
echo Création / mise à jour de la tâche planifiée...
schtasks /Create /SC HOURLY /TN "UpdateSortieTask" /TR "%BATCH_UPDATE%" /F

echo =========================================
echo Tout est prêt !
pause

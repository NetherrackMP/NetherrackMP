@echo off

echo Creating the phar file...
pushd .
cd ..\..\
".\bin\php\php" "./composer.phar" -q make-server
popd
if not "%ERRORLEVEL%"=="0" (
	echo Failed to run composer.
	exit 1
)
echo Netherrack-MP.phar has been created!
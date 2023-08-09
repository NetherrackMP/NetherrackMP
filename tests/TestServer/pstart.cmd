@echo off
cd /d %~dp0
node ../../build && copy "..\..\Netherrack-MP.phar" Netherrack-MP.phar > nul && start.cmd
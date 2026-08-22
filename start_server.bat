@echo off
echo ===================================================
echo   Bigmonks Tech Labs - Local PHP Web Server
echo ===================================================
echo.
echo Starting PHP Development Server at http://localhost:8000
echo.
echo  - Live Blog:   http://localhost:8000/blogs.php
echo  - CMS Admin:   http://localhost:8000/admin.php
echo.
echo Opening browser...
start http://localhost:8000/blogs.php
echo.
php -S localhost:8000
pause

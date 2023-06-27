# Kantodo - maturitní práce

<img src="icon.png" width="200" margin="0 auto" />

- "jednoduchá" webová todo aplikace
- aplikace není určena pro mobily

## Požadavky na server

- \>= PHP 7.1
- extension `gmp`

## Screenshots

<img src="img/login.png">
<img src="img/dashboard.png">
<img src="img/task_info.png">
<img src="img/project.png">
<img src="img/project_members.png">

## Instalace

1. `composer install`
2. zkompilujte sass soubory do složky styles
    - `sass "sass/main.scss" "styles/main.min.css" --style compressed`
    - `sass "sass/pages/NAZEV.scss" "styles/NAZEV.min.css" --style compressed`

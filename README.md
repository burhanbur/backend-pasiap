# PASIAP | PALUTA SIGAP

Application Programming Interface for PASIAP Apps.

## Requirements

- PHP 7.4^
- DBMS MySQL 
- Laravel Framework 8.6.12
- Redis

## Installation

Clone this repository into your local machine

```bash
git clone https://github.com/burhanbur/backend-pasiap.git
```
Copy file `.env.example` and paste into your root folder and rename it to `.env` and then configure the environment according to your local machine settings

Run composer install or update

```bash
composer install
```
Create database according to what you want (ex: db_pasiap)

Run migration database

```bash
php artisan migrate
```

Run database seeding

```bash
php artisan db:seed
```
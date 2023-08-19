# PASIAP | PALUTA SIGAP

Application Programming Interface for PASIAP Apps.

## Requirements

- PHP 7.4^
- DBMS MySQL 
- Laravel Framework 8.6.12
- Firebase

## Installation

Clone this repository into your local machine

```bash
git clone https://github.com/burhanbur/backend-pasiap.git
```
Copy file `.env.example` and paste into your root folder and rename it to `.env` and then configure the environment according to your local machine settings

Put your Firebase Server Key to your `.env` in `FIREBASE_KEY`

Run composer install or update

```bash
composer install
```

Generate your APP_KEY to your `.env`

```bash
php artisan key:generate
```

Generate your JWT_SECRET to your `.env`

```bash
php artisan jwt:secret
```

Create database in MySQL according to what you want (ex: db_pasiap)

Run migration database

```bash
php artisan migrate
```

Run database seeding

```bash
php artisan db:seed
```
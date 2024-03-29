<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

Introduction 
=============

Laravel Socialite provides an expressive, fluent interface to OAuth authentication with Facebook, Twitter, Google, LinkedIn, GitHub, GitLab and Bitbucket. It handles almost all of the boilerplate social authentication code you are dreading writing.

We are not accepting new adapters.

Adapters for other platforms are listed at the community driven Socialite Providers website.

Requirements
============
- PHP 7.4 & above
- MySQL 5.7 or 8

## Installation

Clone the repo locally:
```
git clone https://github.com/ILBCEdTech/Saungpokki-Backend.git
```
`cd` into cloned directory and install dependencies. run below command one by one.
```bash
composer install
``````
```
cp .env.example .env
```
```
php artisan key:generate
```

### Configuration in `.env` file

Database **eg.**

``````
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=social_login
DB_USERNAME=root
DB_PASSWORD=
``````
Google ID and Secret Key
```
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```
Create Google ID in following link:
> https://console.cloud.google.com/apis/dashboard 
<br>

Database Migration
==================
Run database migrations:
```
php artisan migrate
```
## Server Run

Run the dev server:
```
php artisan serve
```

Visit below url:
```
http://localhost:8000
```

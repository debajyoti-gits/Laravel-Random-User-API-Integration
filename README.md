# Laravel User List + Export Application

## Overview
This Laravel application fetches and displays user data from [https://randomuser.me/api](https://randomuser.me/api).  
It supports filtering by gender, paginating the results, and exporting the current page to CSV.

---

## Technology Stack
- Laravel 12.x  
- PHP 8.x  
- Blade Templates + Bootstrap 5  
- External API: [randomuser.me](https://randomuser.me)  
- No database used  

---

## Features

- Fetches 50 users from API and caches for 10 minutes.
- Filter users by gender (male/female).
- Paginated list of users (10 per page).
- Exports current paginated + filtered users as CSV.
- Graceful error handling when API fails.
- Service class UserApiService handles API logic.
- Fail-safe pagination: Always returns a LengthAwarePaginator object to prevent Blade crashes.
- UTF-8 compatibility: in CSV to ensure Excel compatibility.
- Route-safe Export: Export gracefully redirects if cache expired.

---

## Setup Instructions

```bash
git clone https://github.com/debajyoti-gits/Laravel-Random-User-API-Integration.git
cd Laravel-Random-User-API-Integration
composer install
cp .env.example .env
php artisan key:generate
php artisan serve

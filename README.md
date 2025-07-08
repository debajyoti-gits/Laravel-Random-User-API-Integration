# Laravel User API App

A Laravel application that fetches and displays users from the [Random User API](https://randomuser.me), with support for filtering, pagination, caching, and CSV export.

---

## 🚀 Features

- Fetch 50 users from API
- Paginate 10 users per page
- Filter by gender (male/female)
- 10-minute response caching (per page & gender)
- Export current view to CSV
- Graceful API error handling
- Includes basic test for filtering logic

---

## ⚙️ Setup

```bash
git clone https://github.com/debajyoti-gits/Laravel-Random-User-API-Integration.git
cd Laravel-Random-User-API-Integration
composer install
cp .env.example .env
php artisan key:generate
php artisan serve

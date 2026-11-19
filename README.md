# MCTBS – Motal and Contero's Transient Booking System

MCTBS is a web-based transient booking system developed using Laravel. It supports the management of transient bookings and related guest, payment, and accommodation workflows.

> This repository is the development version of MCTBS. Features and workflows may change as the group continues development.

## Tech Stack

- PHP 8.2+
- Laravel 12
- MySQL / MariaDB
- Livewire
- Node.js and npm
- Vite

## Requirements

Install the following on your computer before setting up the project:

- Git
- PHP 8.2 or newer
- Composer
- Node.js (LTS) and npm
- MySQL or MariaDB (XAMPP is one option)

## Setup Instructions

### 1. Clone the repository

```bash
git clone https://github.com/mrtk-gelo/mctbs-laravel.git
cd mctbs-laravel
```

If you already cloned the repository, open its project folder and pull the latest changes from the shared branch:

```bash
git pull origin main
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Create your local environment file

Copy `.env.example` to `.env`:

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**macOS/Linux:**

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

### 4. Create the database

1. Start your MySQL/MariaDB server (for example, through XAMPP).
2. Open phpMyAdmin or your preferred MySQL client.
3. Create a database named `mctbs`.
4. Open the project's `.env` file and configure the database settings to match your local MySQL setup:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mctbs
DB_USERNAME=root
DB_PASSWORD=
```

Change the username or password if your local MySQL setup uses different credentials. **Do not commit your `.env` file.**

### 5. Run database migrations

```bash
php artisan migrate
```

This creates the tables in your local `mctbs` database. Make sure you are using your own local development database, not a database containing important data.

### 6. Build the frontend assets

```bash
npm run build
```

### 7. Run the application

In your project terminal:


```bash
php artisan serve
```

Open the local URL shown in the terminal, usually:


http://127.0.0.1:8000

For frontend development with Vite hot reload, use a second terminal in the project folder and run:


```bash
npm run dev
```

Keep both development commands running while working with hot reload.

## Running Automated Tests

Run the Laravel test suite with:


```bash
php artisan test
```

## Updating Your Local Copy

Before starting new work, get the latest changes from the shared branch:


```bash
git pull origin main
```

If dependencies have changed, run:


```bash
composer install
npm install
```

If new database migrations were added, run:


```bash
php artisan migrate
```

Then rebuild frontend assets when needed:


```bash
npm run build
```

## Important Notes

- Each group member should use their own local `.env` file and database configuration.
- Never commit `.env`, passwords, API keys, or other secrets.
- Avoid running destructive database commands such as `php artisan migrate:fresh` on a database containing data you need. It drops and recreates tables.
- Coordinate before merging or pushing changes to the shared `main` branch. Use feature branches and pull requests when working on separate features.

## Project Repository

https://github.com/mrtk-gelo/mctbs-laravel

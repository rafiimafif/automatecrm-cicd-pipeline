# automateCRM — Setup & Build Guide

Complete guide to set up, build, and run automateCRM locally.

---

## Prerequisites

Make sure you have the following installed:

| Tool       | Version  | Check Command        |
|------------|----------|----------------------|
| PHP        | >= 8.0.2 | `php -v`             |
| Composer   | Latest   | `composer -V`        |
| Node.js    | >= 16    | `node -v`            |
| NPM        | >= 8     | `npm -v`             |
| MySQL      | >= 5.7   | `mysql --version`    |

---

## 1. Clone the Repository

```bash
git clone https://github.com/rafiimafif/automateCRM.git
cd automateCRM
```

---

## 2. Install PHP Dependencies

```bash
composer install
```

---

## 3. Install Node.js Dependencies

```bash
npm install
```

---

## 4. Environment Configuration

Copy the example environment file and generate the app key:

```bash
cp .env.example .env
php artisan key:generate
```

Then open `.env` and configure the following:

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=raficrm
DB_USERNAME=root
DB_PASSWORD=your_password
```

> **Supported databases:** MySQL (default), PostgreSQL, SQLite, SQL Server.

### Mail (Optional)

For service expiration email reminders:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME=automateCRM
```

> For Gmail, use an [App Password](https://support.google.com/accounts/answer/185833) instead of your regular password.

---

## 5. Database Setup

Create the database first:

```sql
CREATE DATABASE raficrm;
```

Then run migrations:

```bash
php artisan migrate
```

### Seed Sample Data (Optional)

This creates a default admin user and sample services:

```bash
php artisan db:seed
```

**Default admin credentials after seeding:**
- Email: `admin@admin.com`
- Password: `password`

**Seeded data includes:**
- 10 sample customers
- 4 services: Website Hosting, Email Hosting, Domain, Web Development

---

## 6. Build Frontend Assets

For production build:

```bash
npm run build
```

For development with hot reload:

```bash
npm run dev
```

---

## 7. Start the Application

```bash
php artisan serve
```

The app will be available at **http://localhost:8000**

---

## Project Structure

```
automateCRM/
├── app/
│   ├── Console/Commands/     # Artisan commands (email reminders)
│   ├── Exports/              # Excel export classes
│   ├── Http/Controllers/     # Route controllers
│   ├── Imports/              # Excel import classes
│   ├── Mail/                 # Email templates (Mailable classes)
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic (renewal service)
├── config/                   # App configuration files
├── database/
│   ├── migrations/           # Database schema
│   ├── factories/            # Model factories for testing
│   └── seeders/              # Database seeders
├── public/
│   ├── css/                  # Compiled stylesheets
│   ├── img/                  # Static images
│   └── js/                   # Compiled JavaScript
├── resources/
│   ├── js/                   # Vue.js source files
│   ├── scss/                 # SCSS source (theme variables)
│   ├── sass/                 # SASS entry point
│   └── views/                # Blade templates
├── routes/
│   ├── web.php               # Web routes
│   └── api.php               # API routes
└── storage/                  # Logs, cache, file uploads
```

---

## Key Features

| Feature                   | Description                                        |
|---------------------------|----------------------------------------------------|
| Customer Management       | Add, edit, delete, import/export customers          |
| Service Tracking          | Create services and assign them to customers        |
| Payment Management        | Record and track payments per customer              |
| Service Renewal           | Track expiration dates with automated reminders     |
| Activity Log              | Audit trail of all system actions                   |
| Excel Import/Export       | Bulk customer data via spreadsheet                  |
| Email Notifications       | Automated service expiration reminder emails        |
| REST API                  | JSON API endpoints for customers and services       |
| Dashboard                 | Overview metrics and charts                         |

---

## API Endpoints

All API routes are prefixed with `/api/`:

| Method | Endpoint                        | Description                    |
|--------|---------------------------------|--------------------------------|
| GET    | `/api/customers`                | List all customers             |
| GET    | `/api/customers/{id}`           | Get single customer            |
| GET    | `/api/customers-service/{id}`   | Get customer with services     |
| GET    | `/api/services`                 | List all services              |
| GET    | `/api/services/{id}`            | Get single service             |
| GET    | `/api/servicetocustomer`        | List all service assignments   |
| GET    | `/api/user`                     | Get authenticated user (Sanctum) |

---

## Scheduled Tasks

automateCRM includes a scheduled command for service expiration reminders.

To activate the scheduler, add this cron entry on your server:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Or run it manually:

```bash
php artisan schedule:run
```

---

## Tech Stack

- **Backend:** Laravel 9, PHP 8.0+
- **Frontend:** Vue.js 3, Bootstrap 5, Vite
- **Database:** MySQL
- **Auth:** Laravel UI + Sanctum (API)
- **Exports:** Maatwebsite/Excel

---

## Troubleshooting

### Common Issues

**"No application encryption key has been specified"**
```bash
php artisan key:generate
```

**"SQLSTATE Connection refused"**
- Verify MySQL is running
- Check DB credentials in `.env`

**"Vite manifest not found"**
```bash
npm run build
```

**Permission errors on storage/**
```bash
chmod -R 775 storage bootstrap/cache
```

---

## Author

**Rafii Muhammad Afif**
- LinkedIn: [rafii-muhammad-afif](https://www.linkedin.com/in/rafii-muhammad-afif/)
- GitHub: [rafiimafif](https://github.com/rafiimafif)
- Portfolio: [rafii-afif.vercel.app](https://rafii-afif.vercel.app)

<!-- PROJECT SHIELDS -->
[![LinkedIn][linkedin-shield]][linkedin-url]

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/rafiimafif/automateCRM">
    <img src="/public/img/simplecrmlogo.jpg" alt="Logo" width="80" height="80">
  </a>

<h3 align="center">automateCRM</h3>

  <p align="center">
    A modern CRM system built with Laravel for managing customers, services, and payments.
    <br />
    <br />
    <a href="https://github.com/rafiimafif/automateCRM/issues">Report Bug</a>
    ·
    <a href="https://github.com/rafiimafif/automateCRM/pulls">Request Feature</a>
  </p>
</div>

<!-- ABOUT THE PROJECT -->
## About The Project

[![Product Name Screen Shot][product-screenshot]](https://rafii-afif.vercel.app)

automateCRM is a streamlined customer relationship management platform designed for managing customers, services, and payments efficiently. Built as a portfolio project to demonstrate full-stack development skills with Laravel.

### Key Features
* **Customer Management** — Add, edit, import/export customer data with ease
* **Service Tracking** — Monitor active services, expiration dates, and renewals
* **Payment Management** — Track payments and financial records
* **Activity Logging** — Keep track of all system activities
* **Email Notifications** — Automated service expiration reminders
* **Dashboard Analytics** — Visual overview of business metrics

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Built With
* [![Laravel][Laravel.com]][Laravel-url]
* [![Bootstrap][Bootstrap.com]][Bootstrap-url]
* [![JQuery][JQuery.com]][JQuery-url]

### DevOps & Infrastructure
* **Docker** — Multi-stage containerized build with Nginx + PHP-FPM + Supervisor
* **Docker Compose** — Full local stack (MySQL, Redis, Mailhog, phpMyAdmin)
* **GitHub Actions** — 6-stage CI/CD pipeline (Lint → Test → Security → SonarCloud → Build → Deploy)
* **Jenkins** — Alternative CI/CD with declarative pipeline
* **Terraform** — AWS ECS Fargate infrastructure as code (VPC, ALB, RDS, ElastiCache)
* **SonarCloud** — Continuous code quality & security analysis
* **Trivy** — Container vulnerability scanning

> See [docs/DEVOPS.md](docs/DEVOPS.md) for the complete DevOps & infrastructure guide.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- GETTING STARTED -->
## Getting Started

Follow these steps to set up the project locally.

### Quick Start with Docker

```sh
git clone https://github.com/rafiimafif/automateCRM.git
cd automateCRM
bash scripts/docker-setup.sh dev
# App: http://localhost:8000  |  Login: admin@admin.com / password
```

Or use Make: `make docker-up-dev`

### Prerequisites (Manual Setup)

* PHP 8.0+ and Composer installed
* MySQL database
* Node.js and NPM

### Installation

1. Clone the repo
   ```sh
   git clone https://github.com/rafiimafif/automateCRM.git
   ```
2. Install Composer packages
   ```sh
   composer install
   ```
3. Install NPM packages
   ```sh
   npm install
   ```
4. Copy `.env.example` to `.env` and configure your database credentials
   ```sh
   cp .env.example .env
   ```
5. Generate application key
   ```sh
   php artisan key:generate
   ```
6. Run migrations
   ```sh
   php artisan migrate
   ```
7. Build frontend assets
   ```sh
   npm run build
   ```
8. Start the server
   ```sh
   php artisan serve
   ```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- USAGE -->
## Usage

After setting up, register a new account and log in to access the dashboard. From there you can:
- Manage customers and their service subscriptions
- Track payments and generate reports
- Import/export customer data via Excel
- Send email notifications for expiring services

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE.txt` for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTACT -->
## Contact

Rafii Muhammad Afif - [LinkedIn](https://www.linkedin.com/in/rafii-muhammad-afif/) - rafii.afif@gmail.com

Portfolio: [https://rafii-afif.vercel.app](https://rafii-afif.vercel.app)

Project Link: [https://github.com/rafiimafif/automateCRM](https://github.com/rafiimafif/automateCRM)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- MARKDOWN LINKS & IMAGES -->
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://www.linkedin.com/in/rafii-muhammad-afif/
[product-screenshot]: public/img/demo.png
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[JQuery.com]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com

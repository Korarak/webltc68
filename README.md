# Loei Technical College Website

Official website project for Loei Technical College, built with PHP (Procedural/Modern Mix), MySQL, and Tailwind CSS.

## 🚀 Prerequisites

- [Docker](https://www.docker.com/) & Docker Compose
- [Composer](https://getcomposer.org/) (optional if running locally, recommended via Docker)

## 🛠️ Setup Instructions

### 1. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit `.env` and fill in your database credentials:

```ini
MYSQL_ROOT_PASSWORD=your_secure_password
MYSQL_USER=your_user
MYSQL_PASSWORD=your_password
secret_key=your_app_key
```

### 2. Start Services

Run the application using Docker Compose:

```bash
docker-compose up -d --build
```

This will start:
- **Web Server** (Apache/PHP 8.2) on `http://localhost:8001`
- **Database** (MariaDB)
- **phpMyAdmin** on `http://localhost:8002`

### 3. Install Dependencies

Install PHP dependencies using Composer (inside the container):

```bash
docker-compose exec web composer install
```

### 4. Database Initialization

Import the SQL files to set up the database structure and initial data:

1. Access phpMyAdmin at `http://localhost:8002` (Login with root credentials).
2. Import `create_databases.sql` to create the required databases.
3. Import other SQL files as needed (e.g., `migrate_badges.sql` for the badges feature).

## 📂 Project Structure

- `www/`: Core application source code
  - `admin/`: Admin panel logic
  - `condb/`: Database connection scripts
  - `includes/`: Shared functions and libraries
  - `vendor/`: Composer dependencies (ignored)
  - `uploads/`: User uploaded content (ignored)
- `docker-compose.yml`: Container orchestration
- `Dockerfile`: PHP/Apache image configuration

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is proprietary software of Loei Technical College.

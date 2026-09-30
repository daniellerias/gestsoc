

# GestSoc - Sistema de Gestão de Sócios

**Association Member Management System**

GestSoc is a web-based association management system built with PHP and MySQL in portuguese native language. It provides a complete solution for managing members, quotas, reports, and data exports.

<img src="assets/img/SocGestLogo.png" alt="GestSoc Logo" width="200" />

## 🚀 Features

### Core Modules

| Module | Description |
|--------|-------------|
| **Members Management** | Add, edit, delete, and organize association members |
| **Quotas Management** | Track membership fees and payment status |
| **Quotas Printing** | Print quota receipts and statements |
| **Members List Print** | Generate printable member directories |
| **Backups** | Database backup functionality for data preservation |
| **Export** | Export data in various formats for external use |

### Administration
- Secure login system
- Admin dashboard with overview statistics
- Configuration management

## 🛠️ Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.3+)
- Web server (Apache/Nginx)
- PHP extensions: `pdo_mysql`, `mbstring`, `openssl`

### Quick Start

1. Clone or download the repository
2. Copy `config_default.php` to `config.php`:
   ```bash
   cp config_default.php config.php
   ```
3. Configure database credentials in `config.php`
4. Run the installation wizard:
   ```
   http://your-server/install.php
   ```
5. Access the application via `index.php`

## 📁 Project Structure

```
gestsoc/
├── admin/          # Administration panel
├── assets/         # Static assets (CSS, JS, images)
├── includes/       # Reusable components and functions
├── login/          # Authentication module
├── quotas/         # Quota management module
├── seeds/          # Database seeding data
├── socios/         # Members management module
├── templates/      # Email/document templates
├── views/          # View components
├── config.php      # Active configuration
├── config_default.php # Default configuration template
├── index.php       # Main entry point
└── install.php     # Installation wizard
```

## 🔧 Configuration

Edit `config.php` to set:
- Database connection (host, username, password, database name)
- Application settings
- SMTP configuration for email notifications

## 📄 License

This project is open source software.

## 👨‍💻 Development

### Current Version
0.4b (Beta)

### Contributing

Contributions are welcome! Please feel free to submit issues and pull requests.

## 🆘 Support

For installation issues or questions:
- Check the installation wizard at `/install.php`
- Review configuration in `config.php`

---

**Made with 💜 for Association Management**
```

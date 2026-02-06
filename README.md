<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License"></a>
</p>

# Web-POS Retail

A modern, robust Point of Sale (POS) application tailored for retail businesses. Built with **Laravel 12**, **Tailwind CSS v4**, and **Alpine.js**, focused on performance, strict data integrity, and excellent user experience.

## 🚀 Technology Stack

- **Framework**: [Laravel 12](https://laravel.com)
- **Frontend**: [Tailwind CSS v4](https://tailwindcss.com) + [Alpine.js](https://alpinejs.dev)
- **Database**: MySQL 8.0+ (Strict Decimal Typing)
- **Templating**: Laravel Blade Components

## ✨ Key Features

### 📦 Product Management
- **Hierarchical Categories**: Organize products effectively.
- **Multi-Unit Support**: Handle different units of measure (pcs, kg, liter, etc.).
- **Multi-Barcode**: Support multiple barcodes per product for flexibility.
- **Strict Pricing**: Decimal-precise pricing and stock management.

### 🏪 Inventory Control
- **Stock Receiving**: Track incoming goods with supplier management.
- **Stock Opname**: Audit actual stock vs system stock.
- **Stock Movements**: Complete "Kartu Stok" history for every item change.
- **Suppliers**: Manage supplier data and history.

### 💰 Point of Sale (POS)
- **Transactions**: Fast and secure checkout process.
- **Void Tracking**: Strict control over voided transactions with reasons and admin approval tracking.
- **User Roles**: distinct access for Admins and Cashiers (with PIN support).

### 🛡️ Security & Auditing
- **Audit Logs**: Track critical actions across the system.
- **Settings**: Dynamic application configuration.

## 🛠️ Setup Instructions

For detailed installation and configuration steps, please refer to the [Setup Instructions](SETUP_INSTRUCTIONS.md).

Quick Start:
1. Configure `.env`
2. Run Migrations: `php artisan migrate`
3. Generate Key: `php artisan key:generate`

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

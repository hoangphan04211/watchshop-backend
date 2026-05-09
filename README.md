# WatchShop Backend - API Documentation

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

## 📌 Overview
**WatchShop Backend** is a high-performance RESTful API built with **Laravel 12**, designed to power a modern e-commerce platform specializing in luxury and fashion timepieces. It provides a comprehensive suite of features for both customers and administrators, ensuring a seamless shopping experience and efficient business management.

## 🚀 Key Features

### 🛍️ Client-Side Features
- **Product Discovery:** Advanced filtering and searching by categories, new arrivals, and sales.
- **Dynamic Content:** Real-time fetching of banners, menus, and blog posts.
- **Shopping Experience:** Robust cart management and a secure checkout workflow.
- **User Accounts:** Personal profile management and detailed order history tracking.
- **Contact & Feedback:** Integrated contact system for customer inquiries.

### 🛡️ Admin Dashboard (CMS)
- **Inventory Management:** Full control over products, attributes, images, and stock levels.
- **Sales & Promotions:** Manage product sales, discounts, and promotional banners.
- **Content Management:** Organize the storefront with customizable menus, topics, and blog posts.
- **Order Processing:** Comprehensive order management system to track and update customer purchases.
- **System Settings:** Global configuration for site-wide metadata and contact information.
- **Data Integrity:** Soft-delete system with trash management for all major entities.

## 🛠️ Technology Stack
- **Framework:** [Laravel 12.x](https://laravel.com)
- **PHP Version:** 8.2+
- **Authentication:** [Laravel Sanctum](https://laravel.com/docs/sanctum)
- **Database:** MySQL / MariaDB
- **API Architecture:** RESTful with JSON responses

## 📂 Project Structure
- `app/Http/Controllers`: Contains the business logic for all API endpoints.
- `app/Models`: Defines the database schema and Eloquent relationships.
- `routes/api.php`: The entry point for all RESTful routes.
- `database/migrations`: Version control for the database schema.

## 🔧 Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd watchshop-backend
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Environment Configuration:**
   ```bash
   cp .env.example .env
   # Update your .env file with database and mail credentials
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Database Migration & Seeding:**
   ```bash
   php artisan migrate --seed
   ```

6. **Serve the Application:**
   ```bash
   php artisan serve
   ```

## 🔐 API Authentication
This project uses **Laravel Sanctum** for secure token-based authentication.
- **Login:** `POST /api/login` (Returns a Bearer Token)
- **Logout:** `POST /api/logout` (Requires Authentication)
- **Protected Routes:** Include `Authorization: Bearer <your_token>` in your request headers.

## 📝 License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---
*Developed with ❤️ for the WatchShop Project.*

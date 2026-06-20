<div align="center">
  <img src="Vyora V.png" alt="Vyora Premium E-commerce Core" width="100%" style="border-radius: 12px; margin-bottom: 20px;">
  
  <h1>Vyora API - Premium E-commerce Core</h1>

  <p>
    <strong>A high-performance, SKU-centric e-commerce backend built with Laravel 12.</strong><br>
    Designed for scalability, speed, and premium retail experiences.
  </p>

  <a href="https://youtu.be/2Sa1cV6LdFU">
    <img src="https://img.shields.io/badge/YouTube-Watch_the_2--Minute_Architecture_%26_Feature_Overview-FF0000?style=for-the-badge&logo=youtube&logoColor=white" alt="Watch the 2-Minute Architecture & Feature Overview">
  </a>
  <br><br>

  <p>
    <img src="https://img.shields.io/badge/Laravel-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/React-20232A?style=flat-square&logo=react&logoColor=61DAFB" alt="React">
    <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white" alt="Tailwind">
  </p>
</div>

---

## 🚀 Key Features

### 📦 Advanced Product Management
- **SKU-Level Control**: Manage inventory with precision using a robust SKU system.
- **Dynamic Attributes**: Full support for product variants including colors, sizes, and custom attributes.
- **Product Collections**: Organize products into curated collections and multi-level categories.

### 🖼️ Media & SEO
- **Color-Synced Galleries**: Automatically group and display product images based on selected color variants.
- **SEO Optimized**: Custom metadata controls for every product, category, and page.
- **Image Processing**: On-the-fly image optimization and resizing using Intervention Image.

### 💳 Payments & Security
- **Payment Integration**: Native support for **Razorpay** for seamless checkout experiences.
- **API Authentication**: Secure access via **Laravel Sanctum**.
- **Admin Workspace**: A premium, high-fidelity administrative dashboard for managing operations.

### 🛠️ Extensible Architecture
- **API-First**: Built to power headless frontends (Vue, React, Next.js).
- **Clean Code**: Follows Laravel best practices and modern PHP 8.2 standards.

---

## 🛠️ Technology Stack

| Technology | Description |
| --- | --- |
| **Framework** | [Laravel 12](https://laravel.com) |
| **Language** | PHP 8.2+ |
| **Database** | MySQL / PostgreSQL |
| **Payments** | Razorpay |
| **Media** | Intervention Image |
| **Auth** | Laravel Sanctum |

---

## ⚙️ Installation & Setup

To get started with Vyora API, follow these steps:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/WitReach/vyora-api.git
   cd vyora-api
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   > 💡 *Note: Update your database and Razorpay credentials in the `.env` file.*

4. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Start the Development Server:**
   ```bash
   php artisan serve
   ```
   ```bash
   npm run dev
   ```

---

## 🤝 Contributing

As an open-source project, we welcome contributions! 
1. **Fork** the Project
2. **Create** your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** your Changes (`git commit -m 'Add some AmazingFeature'`)
4. **Push** to the Branch (`git push origin feature/AmazingFeature`)
5. **Open** a Pull Request

## 📄 License

This project is open-sourced software licensed under the **MIT license**.


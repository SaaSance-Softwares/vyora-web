<div align="center">
  <img src="Vyora V.png" alt="Vyora Premium E-commerce Core" width="100%" style="border-radius: 12px; margin-bottom: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
  
  <h1 align="center">✨ Vyora API - Premium E-commerce Core ✨</h1>

  <p align="center">
    <strong>A blazing fast, SKU-centric e-commerce backend built with Laravel 12.</strong><br>
    <em>Crafted for modern retail experiences, ultimate scalability, and developer joy.</em>
  </p>

  <p align="center">
    <a href="https://youtu.be/2Sa1cV6LdFU">
      <img src="https://img.shields.io/badge/YouTube-Watch_the_2--Minute_Architecture_%26_Feature_Overview-FF0000?style=for-the-badge&logo=youtube&logoColor=white" alt="Watch Overview">
    </a>
  </p>

  <p align="center">
    <img src="https://img.shields.io/badge/Laravel-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/React-20232A?style=flat-square&logo=react&logoColor=61DAFB" alt="React">
    <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white" alt="Tailwind">
    <img src="https://img.shields.io/github/license/WitReach/vyora-api?style=flat-square" alt="License">
    <img src="https://img.shields.io/github/stars/WitReach/vyora-api?style=flat-square" alt="Stars">
  </p>
</div>

<hr>

## 📖 Table of Contents
- [Why Vyora?](#-why-vyora)
- [Key Features](#-key-features)
- [Technology Stack](#-technology-stack)
- [Installation \& Setup](#-installation--setup)
- [Project Structure](#-project-structure)
- [Contributing](#-contributing)
- [License](#-license)

---

## 💡 Why Vyora?

> Most e-commerce backends are either too bloated or too simple. **Vyora** hits the sweet spot.

Designed with an **API-First** approach, it provides a highly robust foundation for any headless storefront (Next.js, Nuxt, React Native, etc.). It abstracts away the complex logic of SKU management, variant pricing, media galleries, and checkout flows, so you can focus on building beautiful user experiences.

---

## 🚀 Key Features

### 📦 Advanced Product Engine
- 🎯 **SKU-Level Control**: Unmatched precision in inventory management. Every variant is tracked flawlessly.
- 🧬 **Dynamic Attributes**: Effortlessly manage sizes, colors, materials, and any custom attributes.
- 🗂️ **Smart Collections**: Build multi-level category trees and curated product groupings.

### 🖼️ Media & SEO Mastery
- 🎨 **Color-Synced Galleries**: Magically group and display product images tied to specific color variants.
- 🔍 **Technical SEO Built-In**: Custom metadata controls for products, categories, and custom pages.
- ⚡ **Fly Image Processing**: Automatic optimization and resizing via *Intervention Image*.

### 💳 Payments, Security & Operations
- 💸 **Razorpay Native**: Frictionless checkout and payment gateway integration out of the box.
- 🛡️ **Bulletproof Security**: Fortified API endpoints with **Laravel Sanctum** authentication.
- 👔 **Premium Admin Dashboard**: A high-fidelity, polished workspace for seamless day-to-day operations.

---

## 🛠️ Technology Stack

| Domain | Technology / Library | Version / Details |
| :--- | :--- | :--- |
| **Core Framework** | [Laravel](https://laravel.com) | `v12.x` |
| **Language** | PHP | `8.2+` |
| **Database** | MySQL / PostgreSQL | Highly Optimized Schema |
| **Frontend (Admin)**| React + Tailwind CSS | Via Inertia.js |
| **Authentication** | Laravel Sanctum | Token & Cookie based |
| **Payments** | Razorpay SDK | Integrated |

---

## ⚙️ Installation & Setup

Get your local environment up and running in minutes.

<details>
<summary><b>Click to view Step-by-Step Instructions</b></summary>

<br>

**1. Clone the repository**
```bash
git clone https://github.com/WitReach/vyora-api.git
cd vyora-api
```

**2. Install dependencies**
```bash
composer install
npm install
```

**3. Configure Environment**
```bash
cp .env.example .env
php artisan key:generate
```
> ⚠️ **Important:** Don't forget to configure your `DB_*` and `RAZORPAY_*` credentials in the `.env` file before proceeding.

**4. Run Migrations & Seeders**
```bash
php artisan migrate --seed
```
*This will set up your database schema and populate it with essential starting data.*

**5. Start the Development Servers**
Open two terminal windows:
```bash
# Terminal 1 - Start the PHP server
php artisan serve
```
```bash
# Terminal 2 - Start Vite for frontend assets
npm run dev
```

</details>

---

## 📂 Project Structure

A quick glance at the core directories you'll interact with:

```text
vyora-api/
├── app/
│   ├── Http/Controllers/Api/  # Core API Logic
│   ├── Models/                # Eloquent Models (Product, SKU, Variant)
│   └── Services/              # Business Logic (Payments, Media)
├── routes/
│   ├── api.php                # RESTful API Endpoints
│   └── web.php                # Admin & Auth Routes
├── resources/
│   └── js/                    # React Admin Dashboard Components
└── database/
    └── migrations/            # E-commerce Schema Definitions
```

---

## 🤝 Contributing

We believe in the power of open source! Whether it's fixing bugs, adding new features, or improving documentation, your help is welcome.

1. **Fork** the repository.
2. **Create** a new branch (`git checkout -b feature/amazing-feature`).
3. **Commit** your changes (`git commit -m 'Add some amazing feature'`).
4. **Push** to the branch (`git push origin feature/amazing-feature`).
5. **Open a Pull Request**! 🎉

---

## 📄 License

This project is open-sourced software licensed under the [**MIT License**](LICENSE).

<div align="center">
  <sub>Built with ❤️ by the Vyora Team & Contributors.</sub>
</div>

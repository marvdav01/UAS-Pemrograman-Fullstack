# Smart-Hub Management System

Sistem manajemen peralatan berbasis web menggunakan **Laravel** + **Inertia.js** + **Vue 3**.

## Arsitektur Repositori

```
/
├── frontend/    → Laravel + Inertia.js (Web Application)
└── backend/     → Backend API (Laravel / your existing API)
```

Strategi ini memisahkan kode frontend dan backend dalam satu monorepo menggunakan Git, sehingga keduanya dapat di-*maintain* tanpa bersinggungan.

## Teknologi

| Layer     | Teknologi                     |
|-----------|-------------------------------|
| Frontend  | Laravel 12, Inertia.js, Vue 3 |
| CSS       | Vanilla CSS (Custom Design System) |
| Build     | Vite                          |
| Database  | Supabase (via Backend API)    |
| Auth      | Token-based (via Backend API) |

## Fitur

- **Login** – Autentikasi terintegrasi dengan backend API (token-based)
- **Dashboard** – Statistik real-time dari backend
- **Data Master: Peralatan** – List, Create, Delete
- **Data Master: Kategori** – List, Create, Delete
- **Transaksi** – List, Create (Check-in), Update (Check-out), validasi stok

## Cara Menjalankan

### 1. Setup

```bash
cd frontend
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Konfigurasi API

Edit `.env`:
```
API_BASE_URL=http://your-backend-api-url/api
```

### 3. Jalankan Development Server

```bash
# Terminal 1 – PHP
php artisan serve

# Terminal 2 – Vite (Assets)
npm run dev
```

Buka browser di: **http://localhost:8000**

## Struktur Frontend

```
frontend/
├── app/Http/Controllers/
│   ├── AuthController.php        ← Login, Logout
│   ├── DashboardController.php   ← Stats dashboard
│   ├── ItemController.php        ← CRUD peralatan
│   ├── CategoryController.php    ← CRUD kategori
│   └── TransactionController.php ← Transaksi (checkin/checkout)
├── app/Http/Middleware/
│   └── Authenticate.php          ← Session-based auth guard
├── resources/js/
│   ├── Layouts/AppLayout.vue     ← Layout utama (sidebar + navbar)
│   └── Pages/
│       ├── Auth/Login.vue
│       ├── Dashboard.vue
│       ├── Items/Index.vue
│       ├── Categories/Index.vue
│       └── Transactions/Index.vue
└── routes/web.php                 ← Semua routing
```

## Git & Version Control

```bash
# Initial commit
git add .
git commit -m "feat: initial frontend setup - Laravel + Inertia.js + Vue3"

# Tambah remote
git remote add origin <your-github-repo-url>
git push -u origin main
```

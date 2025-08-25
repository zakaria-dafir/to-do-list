# 📝 Todo App - Setup Guide for Development Supervisor

Complete Laravel + Vue.js Todo Application with Authentication, Task Management, and Profile Features.

## 🚀 Quick Setup Commands

### 1. Clone Repository
```bash
git clone https://github.com/zakaria-dafir/to-do-list-.git
cd todo-app
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file (Windows)
copy .env.example .env

# Generate Laravel app key
php artisan key:generate

# Generate JWT secret key (REQUIRED for authentication)
php artisan jwt:secret
```

### 4. Database Setup
```bash
# Create database in phpMyAdmin or MySQL:
# Database name: todo_app

# Run migrations
php artisan migrate

# Create storage symlink for image uploads
php artisan storage:link
```

### 5. Configure .env File
Edit `.env` file with these settings:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo_app
DB_USERNAME=root
DB_PASSWORD=

# Pusher REQUIRED for real-time notifications
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY=your_app_key
VITE_PUSHER_APP_CLUSTER=mt1
```

### 6. Setup Pusher (REQUIRED)
1. **Create free account**: https://pusher.com
2. **Create new app** in Pusher dashboard
3. **Copy credentials** to `.env` file above
4. **Restart servers** after updating .env

### 7. Start Application

**Option A - Development Mode (Recommended):**
```bash
# Terminal 1 - Backend
php artisan serve

# Terminal 2 - Frontend
npm run dev
```
✅ **Access app at**: http://localhost:5173

**Option B - Laravel Only:**
```bash
# Build frontend first
npm run build

# Start Laravel server
php artisan serve
```
✅ **Access app at**: http://localhost:8000

## 🧪 Test the Application

1. **Open**: http://localhost:8000 (Laravel mode)
2. **Register** new user account
3. **Create tasks** and test CRUD operations
4. **Upload profile image** and edit profile
5. **Check notifications** functionality

## 📋 Prerequisites

- **XAMPP** (PHP + MySQL)
- **Composer** (PHP package manager)
- **Node.js** (JavaScript runtime)
- **Git** (version control)

## ✨ Features

- ✅ User Authentication (Register/Login/Logout)
- ✅ Task CRUD Operations
- ✅ Profile Management with Image Upload
- ✅ Real-time Notifications
- ✅ Responsive Design

## 🔧 Troubleshooting

**Database connection error:**
- Start XAMPP and ensure MySQL is running
- Create `todo_app` database in phpMyAdmin

**Storage symlink error:**
```bash
php artisan storage:link --force
```

**JWT secret missing:**
```bash
php artisan jwt:secret --force
```

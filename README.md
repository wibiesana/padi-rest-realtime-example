# PADI Real-Time CRUD Demo Application

This application is a high-performance, real-time reactive application demo built using the **PADI PHP Framework** on the backend and **Quasar Framework (Vue 3)** on the frontend. It synchronizes Posts, Comments, and Tags data instantly across all connected browser clients without requiring a page refresh.

---

## 🚀 Key Features
* **Real-time Sync**: Instant client-to-client synchronization powered by Server-Sent Events (SSE) via **Mercure Hub** in FrankenPHP.
* **Full CRUD Management**: Interactive dashboards to manage **Posts**, **Comments**, and **Tags**.
* **Secure Auth**: Full registration, confirmation validation, login with **Remember Me** capability, and JWT Token-based authentication.
* **Multi-Language (i18n)**: Global language switcher (English & Bahasa Indonesia) located in the navigation header.
* **Premium Design Assets**: A clean dark-mode interface employing glassmorphism panels with smooth micro-animations.

---

## 🛠️ Tech Stack

### Backend
* **PADI MVC Framework** (PHP 8.4+)
* **FrankenPHP** (Modern Go-based PHP application server with built-in Mercure Hub support)
* **Mercure Hub** (SSE protocol for real-time publishing)
* **MySQL / MariaDB / SQLite** (Main relational database)

### Frontend
* **Vue 3** (Composition API)
* **Quasar Framework v2** (For premium UI components)
* **Pinia** (State Management)
* **Vue Router** & **Vue i18n** (Language localization)

---

## 📋 System Prerequisites
Ensure you have the following installed on your machine:
1. **PHP 8.4 or higher**
2. **Composer**
3. **Node.js** (LTS / v18+)
4. **FrankenPHP** (Installed on system or present in PATH)
5. **MySQL Server** (Or fallback to SQLite)

---

## ⚙️ Step-by-Step Startup Guide

Follow these steps in sequential order to run the application on your local machine:

### Step 1: Configure Backend Environment
1. Open a new terminal window and navigate to the `backend/` directory.
2. Run the following interactive Setup Wizard command to automatically generate the `.env` file, configure your database connection, and generate secure JWT keys:
   ```bash
   php padi init
   ```
3. Follow the interactive prompts in your terminal to complete the configuration.

### Step 2: Run Database Migrations (Optional)
If you did **not** select the option to run migrations during the `php padi init` setup wizard in Step 1, execute this command inside the `backend/` directory to create the database tables:
```bash
php padi migrate
```

### Step 3: Start the Web Server (FrankenPHP)
In the main root directory (`coba-realtime/`), double-click or run one of the batch scripts to launch the FrankenPHP web server with Mercure:
* **Normal Mode**: Run `init_frankenphp_normal_mode.bat`.
* **Worker Mode (High Performance)**: Run `init_frankenphp_worker_mode.bat`.

*The backend API will be available at: `http://localhost:8085`*

### Step 4: Run the Queue Worker (Optional)
If you configure backend models to use background queue instead of Direct Mode for broadcasting real-time updates:
Run the `init_queue.bat` script in the root directory to process database jobs in the background.

### Step 5: Setup & Start the Frontend
1. Open a new terminal and navigate to the `frontend/` directory.
2. Install all Node.js dependencies:
   ```bash
   npm install
   ```
3. Run the Quasar frontend development server:
   ```bash
   npm run dev
   ```

*The frontend application will be available at: `http://localhost:9000`*

---

## 💻 How to Test Real-Time Sync

To test if real-time synchronization is working:
1. Open the frontend app (`http://localhost:9000`) in **two different browser windows** side-by-side (or use an Incognito/Private window for one of them).
2. Register a new account or log in on both windows.
3. Navigate to the **Posts Dashboard** or **Tags Dashboard**.
4. Try creating, editing, or deleting a post in Browser A.
5. Observe that Browser B immediately updates its grid and shows a notification pop-up without any manual page reload!

---

## 📖 Documentation
For complete documentation on using the PADI REST API Framework, please visit our official website at:
🔗 [PADI Software Documentation](https://padisoftware.my.id/)

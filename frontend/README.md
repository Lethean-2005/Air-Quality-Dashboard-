# 🌍 Air Quality Dashboard

A full-stack dashboard that displays air quality data using Laravel and Vue.js.

---

## 🧱 Tech Stack

- **Frontend**: Vue 3 + Vite + Tailwind CSS  
- **Backend**: Laravel 10 (REST API)  
- **Database**: MySQL (optional)

---

## 📁 Project Structure

air-quality-dashboard/
├── backend/ # Laravel REST API
└── frontend/ # Vue.js UI (Tailwind CSS)

---

## 🚀 Getting Started

### 🖥️ Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
Make sure to update your .env file with database credentials.

🌐 Frontend (Vue.js + Tailwind CSS)

cd frontend
npm install
npm run dev
Make sure the Laravel API (http://localhost:8001) allows CORS from Vue (http://localhost:5173).

🔌 Example API Endpoint

GET /api/air-quality
Returns air quality data in JSON format.

✅ Features (Planned)
 REST API with Laravel

 Vue frontend with Tailwind

 Dashboard view for air quality

 City/location filtering

 Charts for trends (e.g., Chart.js)

🧑‍💻 Author
GitHub: https://github.com/your-username


Let me know if you'd like a Khmer version of the README, or want to include deployment steps (e.g. H

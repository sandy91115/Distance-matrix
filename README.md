# 🚦 Laravel Google Maps – Route & Traffic Analyzer

Stop overengineering this. This project does **one thing well**: it replicates core Google Maps routing features **inside a Laravel + Filament admin panel**, with traffic, caching, and cost control baked in.

If you’re expecting magic without proper API setup or basic Laravel knowledge, this repo is not for you.

---

## 🧩 What This Project Does (Clearly)

* Calculates **multiple routes** between two locations
* Shows **distance, duration, and real-time traffic impact**
* Uses **Google Maps APIs properly** (no hacks, no scraping)
* Stores API keys **securely (encrypted in DB)**
* Avoids unnecessary API cost using **smart caching & throttling**

---

## 🛠️ Tech Stack

* **Laravel** (Backend)
* **Filament Admin** (Settings + Admin UI)
* **Google Maps Platform**
* **MySQL** (Encrypted settings storage)
* **Blade + JS** (Frontend maps rendering)

---

## 🚀 Installation & Setup

### 1️⃣ Clone the Repository

```bash
git clone <repo-url>
cd <project-folder>
```

### 2️⃣ Install Dependencies

```bash
composer install
npm install && npm run build
```

### 3️⃣ Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database properly. If you mess this up, nothing else matters.

---

## 🔧 Complete Setup Commands

```bash
# 1. Run migrations
php artisan migrate

# 2. Create admin user (only if not exists)
php artisan make:filament-user

# 3. Clear cache
php artisan config:clear
php artisan cache:clear

# 4. Start server
php artisan serve
```

---

## 🔑 Google Cloud Setup (Mandatory)

If APIs are not enabled, **nothing will work**. No shortcuts here.

### Enable Required APIs

From **Google Cloud Console**:

* Maps JavaScript API
* Places API
* Directions API
* Geocoding API (optional but recommended)

### Create API Key

* Go to **Credentials → Create Credentials → API Key**
* (Recommended) Restrict the key:

  * Application: HTTP Referrers (for production)
  * API Restrictions: Limit to APIs listed above

---

## 📖 Application Usage

### Step 1: Configure Google Maps API Key

1. Visit: `http://localhost:8000/admin`

2. Login using Filament admin credentials

3. Navigate to:

   **Settings → API Settings → Create**

4. Add:

   * **Key:** `google_maps_api_key`
   * **Value:** Your Google Maps API Key
   * **Encrypt:** ✅ Keep checked (don’t be careless)

5. Save

---

### Step 2: Use the Maps Feature

Visit: `http://localhost:8000/maps`

* Enter **Starting Location** (Autocomplete enabled)
* Enter **Destination**
* Toggle **Real-Time Traffic** on/off
* Click **Find Routes**

You’ll see:

* Multiple route options
* Distance
* Duration (with traffic)
* Traffic severity

Click any route card to render it on the map.

---

## 🎯 Features (No Marketing Fluff)

* ✅ Real-time Address Autocomplete
* ✅ Live Traffic-aware Route Calculation
* ✅ Multiple Alternative Routes
* ✅ Interactive Google Map with Polylines
* ✅ Secure API Key Storage (Encrypted)
* ✅ Rate Limiting (120 req/min)
* ✅ Mobile & Desktop Responsive

### 🚦 Traffic Status Logic

| Status      | Delay    |
| ----------- | -------- |
| 🟢 Light    | < 5 min  |
| 🟡 Moderate | 5–15 min |
| 🔴 Heavy    | > 15 min |

---

## 💰 Cost Optimization (Read This)

Google APIs aren’t cheap if abused. This app avoids stupidity:

* **Route Caching:**

  * Without traffic → 24 hours
  * With traffic → 30 minutes
* **Autocomplete Debounce:** 300ms
* **Place ID reuse** instead of raw coordinates
* **Traffic toggle** (users decide when it matters)

If you still burn money, that’s on you.

---

## 🐛 Troubleshooting

### Map Not Loading?

* Check `.env` (if applicable)
* Verify API key is active
* Inspect browser console errors

### Autocomplete Not Working?

* Places API enabled?
* API key restrictions correct?
* `/api/maps/autocomplete` reachable?

### Routes Not Showing?

* Directions API enabled?
* Traffic toggle affecting results?
* Migrations actually ran?

---

## 📝 License

Open-source. Free to use.

If you break it, fix it.

---

## 🤝 References

* Google Maps API Documentation
* Laravel Documentation
* Filament Documentation

---

## ⚠️ Final Note

This project assumes:

* You understand Laravel basics
* You know how Google APIs work
* You can read logs instead of guessing

If not — learn first, then complain.

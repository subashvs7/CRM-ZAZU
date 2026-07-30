# CRM-ZAZU — Enterprise Field Staff CRM

A full-featured Field Staff CRM built with **CodeIgniter 3**, **MySQL 8**, **Bootstrap 3 / AdminLTE 2**, and **jQuery 3**.

## Features

| Module | Description |
|---|---|
| **Dashboard** | Role-aware KPIs, revenue charts, lead pipeline |
| **Customers** | Full customer management with contact persons & map |
| **Leads** | Kanban pipeline (7 stages), activity log, CSV import |
| **Orders** | Multi-item orders with approval workflow & PDF |
| **Visits** | Visit planning, check-in/out with GPS, planned vs actual |
| **Live Tracking** | Real-time GPS tracking via browser (auto-ping every 10 min) |
| **Geofence** | Zone management, auto check-in, violation alerts |
| **Attendance** | Punch in/out with GPS + selfie verification |
| **Shifts** | Shift management and assignment calendar |
| **Leave** | Apply / approve / track leave with balance |
| **Reports** | 8 report types with charts and coverage map |
| **Admin** | Users, teams, products, notification templates, settings |

## Stack

- **Backend**: CodeIgniter 3.1.x (PHP 7.4+)
- **Database**: MySQL 8.0
- **Frontend**: Bootstrap 3.3.7, AdminLTE 2.4, jQuery 3.6
- **Maps**: Leaflet.js + OpenStreetMap (no API key required)
- **Charts**: ApexCharts
- **Tables**: DataTables (server-side)

## Quick Start

### 1. Requirements
- PHP 7.4+
- MySQL 8.0
- Apache with `mod_rewrite` enabled (or Laragon)

### 2. Database Setup
```bash
mysql -u root -p -e "CREATE DATABASE field_crm CHARACTER SET utf8mb4;"
mysql -u root -p field_crm < field_crm.sql
```

### 3. Configuration
Edit `application/config/database.php`:
```php
$db['default']['hostname'] = 'localhost';
$db['default']['username'] = 'root';
$db['default']['password'] = 'your_password';
$db['default']['database'] = 'field_crm';
```

Edit `application/config/config.php`:
```php
$config['base_url'] = 'http://localhost/crm-zazu/';
```

### 4. Seed Demo Data
```bash
php index.php seeder run
```

### 5. Login
| Role | Email | Password |
|---|---|---|
| Admin | admin@fieldcrm.com | password123 |
| Manager | manager1@fieldcrm.com | password123 |
| Field Staff | staff1@fieldcrm.com | password123 |

## Role Access

| Feature | Admin | Manager | Field Staff |
|---|---|---|---|
| Dashboard | ✅ | ✅ | ✅ |
| Customers / Leads / Orders | ✅ | ✅ | ✅ (own) |
| Visits / Attendance / Leave | ✅ | ✅ | ✅ |
| Live Tracking / Geofence | ✅ | ✅ | ❌ |
| Shifts / Selfie Verify | ✅ | ✅ | ❌ |
| Reports | ✅ | ✅ | ❌ |
| Admin Panel | ✅ | ❌ | ❌ |

## GPS Auto-Tracking

Field staff browsers automatically send GPS coordinates every 10 minutes (configurable in Admin → Settings). No mobile app required — works from any browser with location permission.

## License

MIT
## UI Design Prompt (For ChatGPT)

If you want to generate UI design concepts or improvements for this project using ChatGPT, you can copy and paste the prompt below:

---

**Prompt:**

> Act as an expert UI/UX designer. I am building "CRM-ZAZU", an Enterprise Field Staff CRM using CodeIgniter 3, Bootstrap 3 / AdminLTE 2, and jQuery. I need modern, clean, and highly usable UI design recommendations, color palettes, and layout structures for the following modules:
> 
> 1. **Dashboard**: Role-aware KPIs, revenue charts, lead pipeline.
> 2. **Customers**: Full customer management with contact persons & map.
> 3. **Leads**: Kanban pipeline (7 stages), activity log, CSV import.
> 4. **Orders**: Multi-item orders with approval workflow & PDF generation.
> 5. **Visits**: Visit planning, check-in/out with GPS, planned vs actual.
> 6. **Live Tracking**: Real-time GPS tracking via browser.
> 7. **Geofence**: Zone management, auto check-in, violation alerts.
> 8. **Attendance**: Punch in/out with GPS + selfie verification.
> 9. **Shifts**: Shift management and assignment calendar.
> 10. **Leave**: Apply / approve / track leave with balance.
> 11. **Reports**: 8 report types with charts and coverage map.
> 12. **Admin**: Users, teams, products, notification templates, settings.
> 
> Please provide a cohesive design system, including:
> - A recommended color palette (primary, secondary, success, warning, danger).
> - Typography choices (fonts for headings and body).
> - Layout structure (Sidebar, Header, Main Content area) keeping AdminLTE 2 in mind but making it look more modern.
> - Detailed UI layout descriptions for the **Dashboard** and **Leads Kanban** views.
> - Recommendations for micro-interactions and animations.

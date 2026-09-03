# Smart Farm Management and Market Linkage Decision Support System

An ICT-based farm record keeping system built for small-scale farmers in Kenya, developed as a
final year BBIT research project at The Technical University of Kenya.

## What this system does

- **Farm Records** — log crops, planting/harvest dates, input/labour/other costs, and yields
- **Market Linkage** — list produce for sale and browse a shared marketplace
- **Decision Support** — rule-based recommendations on the best time to sell, based on seasonal market prices
- **Reports** — cost, revenue, and profit/loss breakdowns per crop, with a printable view
- Bonus features: harvest countdown widget, crop photo uploads, English/Swahili toggle

## Requirements

- [XAMPP](https://www.apachefriends.org/) (includes Apache, MySQL/MariaDB, and PHP)
- A web browser

## Setup instructions

1. Clone or download this repository into your XAMPP `htdocs` folder, so the path looks like:
   `C:\xampp\htdocs\farm-system\`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin` in your browser.
4. Click **Import**, choose `database.sql` from this project, and click **Import/Go**.
   This creates the `farm_system` database and all required tables with sample data.
5. Open `http://localhost/farm-system/` in your browser.
6. Register a new account (or use the sample account seeded in `database.sql`), then log in.

## Project structure

```
farm-system/
├── index.php              Login page
├── register.php           Registration page
├── dashboard.php          Main dashboard after login
├── records.php            Farm Records module
├── market.php              Market Linkage module
├── decision.php            Decision Support module
├── reports.php              Reports module
├── logout.php / delete_record.php / delete_listing.php / toggle_lang.php
├── database.sql           Full database schema + sample data (fresh install)
├── database_update.sql    Incremental update (adds photo column, keeps existing data)
├── includes/               Shared PHP: config, auth check, header/footer, sidebar, translations
├── css/style.css          All styling
└── uploads/                Crop photos uploaded through the Farm Records module
```

## Notes

- Revenue in Reports is estimated from market listings marked "Sold" (quantity × asking price),
  as this is a prototype using simulated data, per the study's scope.

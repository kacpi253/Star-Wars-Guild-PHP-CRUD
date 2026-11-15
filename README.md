# Star Wars Bounty Hunter Guild - Management System

A web-based CRUD (Create, Read, Update, Delete) application designed to manage a fictional Bounty Hunter Guild database set in the Star Wars universe. This project demonstrates backend web development skills utilizing **PHP**, a relational **MySQL** database, and pure CSS for styling.

## 🌟 Features

* **Relational Database Design:** A fully structured MySQL database featuring foreign key constraints, cascading deletes, and ENUM types for data integrity (e.g., Guild Ranks, Factions).
* **Dynamic Table Views:** Browse complex data including Hunters, Bounties, Clients, and Equipment directly from the web interface.
* **CRUD Operations:**
  * Add new records (e.g., register a new Bounty Hunter or post a new Bounty) via dynamically generated HTML forms.
  * Delete records safely.
* **Complex SQL Queries:** Pre-built complex queries accessible via the UI (e.g., JOINs to find Hunters and their active bounties, filtering by Guild Rank).
* **Data Seed:** Includes CSV data dumps (`database/data_imports`) to quickly populate the database with lore-friendly Star Wars data (Mandalorian, Boba Fett, IG-11, etc.).

## 🛠️ Technology Stack
* **Backend:** PHP 8+
* **Database:** MySQL / MariaDB (SQL script provided)
* **Frontend:** HTML5, pure CSS (`assets/css/style.css`)
* **Architecture:** Modular PHP includes (`header.php`, `navbar.php`, `footer.php`) and a central database configuration (`config/db.php`).

## 🗄️ Database Schema

The database relies on several interconnected tables:
* `Bounty_Hunters` - Registered guild members.
* `Clients` - Individuals or factions (e.g., Empire, New Republic) issuing bounties.
* `Targets` - The individuals being hunted.
* `Bounties` - The core contract linking a Client, a Target, and a Reward.
* `Missions` - The assignment of a specific Bounty to a Hunter.
* `Equipment` & `Hunter_Gear` - Many-to-many relationship tracking a hunter's arsenal.

## 🚀 How to Run (Local Environment)

1. **Install a Local Server:** Use [XAMPP](https://www.apachefriends.org/index.html) or a similar LAMP/WAMP stack.
2. **Setup Database:**
   * Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
   * Import the SQL script: `database/schema.sql`. This will automatically create the `star_wars_guild` database and build the table structures.
   * *(Optional)* Import the provided `.csv` files from `database/data_imports` into their respective tables if you want initial data.
3. **Deploy the App:**
   * Clone or copy this repository into your local web root folder (e.g., `C:\xampp\htdocs\gildia`).
4. **Configure Connection:**
   * Ensure the credentials in `config/db.php` match your local MySQL setup (default for XAMPP is user: `root`, password: `[empty]`).
5. **Run:**
   * Open your browser and navigate to `http://localhost/gildia/index.php`.
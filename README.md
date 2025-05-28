# 🎓 Student Registration System

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.2-7952B3?logo=bootstrap)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A complete student registration system built with PHP and Bootstrap for efficient academic administration.

![Student Registration System Preview](https://via.placeholder.com/800x400/2D3748/FFFFFF?text=Student+Registration+System)  
*(Replace with actual screenshot of your system)*

## ✨ Features

### 👨‍🎓 Student Management
- Student registration and profile creation
- Course enrollment system
- Academic records tracking
- ID card generation

### 📚 Course Administration
- Course creation and management
- Class scheduling
- Prerequisite system
- Capacity monitoring

### 👩‍🏫 Faculty Tools
- Instructor assignment
- Grade submission portal
- Attendance tracking
- Student performance reports

### 📊 Dashboard & Reporting
- Real-time enrollment statistics
- Student demographic reports
- Academic performance analytics
- Exportable reports (PDF/Excel)

### ⚡ Tech Stack
- **Frontend**: Bootstrap 5 (Responsive Design)
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0
- **Security**: Prepared statements, password hashing
- **Additional**: jQuery for AJAX functionality

## 🛠️ Setup

### Prerequisites
- Web server (Apache/Nginx)
- PHP 8.0+
- MySQL 8.0+
- Composer (for optional dependencies)
- Git

### Installation
```bash
# Clone repository
git clone https://github.com/dansan-dsn/student-registration-system.git
cd student-registration-system

# Import database
mysql -u username -p database_name < sql/student_system.sql

# Configure database connection
cp config/db.php
# Edit with your credentials

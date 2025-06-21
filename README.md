# 🐝 Knowledge Bee - Social Learning Platform

A barter-based learning ecosystem where users share skills and earn virtual coins to access content from others.

## 🌟 Features

- **Skill Sharing**: Upload tutorials, blogs, and quizzes
- **Buzz Coins**: Virtual currency earned through contributions
- **Badge System**: Recognition for quality content creators
- **Leaderboards**: Global and skill-wise rankings
- **Moderation**: Community-driven content quality control
- **Quiz System**: Interactive learning assessments

## 🛠️ Tech Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Server**: Apache (XAMPP)
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Authentication**: Session-based

## 📁 Project Structure

```
knowledgebee/
├── assets/
│   ├── css/
│   ├── js/
│   └── uploads/
├── includes/
│   ├── config.php
│   ├── database.php
│   ├── functions.php
│   └── auth.php
├── templates/
│   ├── header.php
│   ├── footer.php
│   └── navbar.php
├── pages/
│   ├── home.php
│   ├── profile.php
│   ├── upload.php
│   ├── skill.php
│   ├── search.php
│   ├── leaderboard.php
│   └── admin/
├── auth/
│   ├── login.php
│   ├── signup.php
│   └── logout.php
└── index.php
```

## 🚀 Setup Instructions

### 1. XAMPP Installation
- Download and install XAMPP from https://www.apachefriends.org/
- Start Apache and MySQL services

### 2. Project Setup
1. Clone/download this project to `C:\xampp\htdocs\knowledgebee\`
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create a new database named `knowledgebee`
4. Import the SQL file: `database/knowledgebee.sql`

### 3. Configuration
1. Edit `includes/config.php` with your database credentials
2. Ensure upload directory has write permissions

### 4. Access the Application
- Open browser and go to: http://localhost/knowledgebee/

## 🎯 User Flow

1. **Registration/Login** → Profile setup
2. **Upload Content** → Earn Buzz Coins
3. **Browse Skills** → Find content to learn
4. **Spend Coins** → Access premium content
5. **Earn Badges** → Build reputation
6. **Leaderboards** → Compete with community

## 🔐 Default Admin Account
- Username: `admin`
- Password: `admin123`

## 📝 License
This project is for educational purposes.

## 🤝 Contributing
Feel free to contribute to improve the platform! 
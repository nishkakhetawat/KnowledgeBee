# 🐝 Knowledge Bee - Installation Guide

This guide will help you set up the Knowledge Bee platform on your local machine using XAMPP.

## 📋 Prerequisites

- **XAMPP** (Apache + MySQL + PHP) - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
- **PHP 7.4 or higher**
- **MySQL 5.7 or higher**
- **Web browser** (Chrome, Firefox, Safari, Edge)

## 🚀 Installation Steps

### Step 1: Install XAMPP

1. Download XAMPP from the official website
2. Run the installer and follow the setup wizard
3. Make sure to install Apache and MySQL components
4. Start XAMPP Control Panel

### Step 2: Start Services

1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
4. Both services should show green status

### Step 3: Download Knowledge Bee

1. Download or clone this repository
2. Extract the files to: `C:\xampp\htdocs\knowledgebee\`
3. Your folder structure should look like:
   ```
   C:\xampp\htdocs\knowledgebee\
   ├── assets/
   ├── auth/
   ├── database/
   ├── includes/
   ├── pages/
   ├── templates/
   ├── index.php
   ├── setup.php
   └── README.md
   ```

### Step 4: Create Database

1. Open your web browser
2. Go to: `http://localhost/phpmyadmin`
3. Click "New" to create a new database
4. Enter database name: `knowledgebee`
5. Click "Create"

### Step 5: Import Database Schema

1. In phpMyAdmin, select the `knowledgebee` database
2. Click the "Import" tab
3. Click "Choose File" and select `database/knowledgebee.sql`
4. Click "Go" to import the schema and sample data

### Step 6: Configure Database Connection

1. Open `includes/config.php` in a text editor
2. Update the database settings if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'knowledgebee');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Leave empty for default XAMPP setup
   ```

### Step 7: Set File Permissions

1. Make sure the `assets/uploads/` directory is writable
2. On Windows, right-click the folder → Properties → Security → Edit → Add your user → Full Control
3. On Linux/Mac: `chmod 755 assets/uploads/`

### Step 8: Run Setup Script

1. Open your web browser
2. Go to: `http://localhost/knowledgebee/setup.php`
3. This will check your system requirements and verify the installation

### Step 9: Access the Application

1. Go to: `http://localhost/knowledgebee/`
2. You should be redirected to the login page
3. Use the default admin credentials:
   - **Username:** `admin`
   - **Password:** `admin123`

## 🔐 Default Accounts

After installation, you'll have these demo accounts:

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |
| john_doe | password | Regular User |
| jane_smith | password | Regular User |
| mike_dev | password | Regular User |

## 🛠️ Configuration Options

### Database Configuration
Edit `includes/config.php` to customize:

```php
// Buzz Coin rewards
define('COINS_UPLOAD_REWARD', 10);
define('COINS_UPVOTE_REWARD', 5);
define('COINS_QUIZ_PASS_REWARD', 7);

// Badge requirements
define('BADGE_UPLOAD_MIN', 3);
define('BADGE_UPVOTES_MIN', 30);

// Content moderation
define('MAX_REPORTS_BEFORE_HIDE', 3);
define('NEW_USER_UPLOAD_LIMIT', 3);
```

### Site Configuration
```php
define('SITE_NAME', 'Knowledge Bee');
define('SITE_URL', 'http://localhost/knowledgebee');
```

## 🔧 Troubleshooting

### Common Issues

**1. Database Connection Error**
- Verify MySQL is running in XAMPP
- Check database credentials in `includes/config.php`
- Ensure database `knowledgebee` exists

**2. Upload Directory Not Writable**
- Check folder permissions on `assets/uploads/`
- On Windows: Right-click → Properties → Security → Edit → Add user with Full Control
- On Linux: `chmod 755 assets/uploads/`

**3. Page Not Found (404)**
- Verify files are in correct location: `C:\xampp\htdocs\knowledgebee\`
- Check Apache is running in XAMPP
- Ensure `.htaccess` file exists (if using URL rewriting)

**4. PHP Errors**
- Check PHP version: `http://localhost/knowledgebee/setup.php`
- Verify required extensions: pdo, pdo_mysql, json, mbstring
- Check error logs in XAMPP

**5. Session Issues**
- Clear browser cookies and cache
- Check PHP session configuration
- Verify `includes/config.php` has session settings

### Error Logs

**XAMPP Error Logs:**
- Apache: `C:\xampp\apache\logs\error.log`
- PHP: `C:\xampp\php\logs\php_error_log`

**Application Logs:**
- Check browser console for JavaScript errors
- Review PHP error output (if enabled)

## 🔒 Security Recommendations

1. **Change Default Passwords**
   - Update admin password immediately after installation
   - Use strong, unique passwords

2. **Database Security**
   - Create a dedicated database user (not root)
   - Use strong database passwords
   - Limit database user permissions

3. **File Permissions**
   - Set proper file permissions
   - Don't make sensitive files publicly accessible

4. **Production Deployment**
   - Use HTTPS
   - Set `error_reporting(0)` in production
   - Regular database backups
   - Keep PHP and MySQL updated

## 📁 File Structure

```
knowledgebee/
├── assets/                 # Static assets
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── uploads/           # User uploads
├── auth/                  # Authentication pages
│   ├── login.php
│   ├── signup.php
│   └── logout.php
├── database/              # Database files
│   └── knowledgebee.sql   # Database schema
├── includes/              # Core PHP files
│   ├── config.php         # Configuration
│   ├── database.php       # Database connection
│   └── functions.php      # Utility functions
├── pages/                 # Main application pages
│   ├── home.php
│   ├── profile.php
│   ├── upload.php
│   ├── skill.php
│   └── explore.php
├── templates/             # HTML templates
│   ├── header.php
│   └── footer.php
├── index.php              # Entry point
├── setup.php              # Setup script
└── README.md              # Documentation
```

## 🆘 Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review error logs
3. Verify all prerequisites are met
4. Ensure proper file permissions
5. Test with default configuration first

## 📝 License

This project is for educational purposes. Feel free to modify and use for learning.

---

**Happy Learning with Knowledge Bee! 🐝** 
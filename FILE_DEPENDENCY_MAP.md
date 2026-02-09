# Loei Technical College Website - File Dependency Map

## 📊 ภาพรวมการเชื่อมโยงไฟล์

### 🏠 หน้าแรก (Frontend)
```
index.php (main entry)
  ├─ ob_start()
  ├─ app-news/88years.php
  ├─ app-news/section-register/pr2569.html
  ├─ app-news/main-news.php
  ├─ app-news/annonce-news.php
  └─ base.php (template)
```

### 📄 base.php (Template Master)
```
base.php (Template Layout)
  ├─ app-menu/pop-up.php (popup menu)
  ├─ app-menu/sidebar.php (sidebar navigation)
  ├─ app-menu/top_nav.php (top navigation)
  ├─ app-menu/carousel.php (carousel slider - only on homepage)
  ├─ app-footer/footer.php (footer)
  └─ floating-badges.php (floating badges from database)
       ├─ condb/condb.php (database connection)
       └─ SELECT * FROM badges WHERE visible=1
```

### 🔐 Admin Panel
```
admin/dashboard.php (Admin Base Layout)
  ├─ middleware.php (authentication check)
  │   └─ config.php
  └─ Each admin page:
      ├─ admin/admin-manage.php (manage users)
      ├─ admin/badge_manage.php (manage badges) ✨ NEW
      ├─ admin/carousel_manage.php (manage carousel)
      ├─ admin/footer_manage.php (manage footer)
      ├─ admin/navbar_menu_manage.php (manage navbar menu)
      ├─ admin/personel_manage.php (manage personnel)
      ├─ admin/news_manage.php (manage news)
      ├─ admin/webpages_manage.php (manage web pages)
      └─ ... more admin pages
```

### 📚 Database Connections
```
condb/condb.php (Main Database)
  ├─ $mysqli - users database (ltc_web.users)
  ├─ $mysqli2 - news database (ltc_news)
  ├─ $mysqli3 - letters database (ltc_letter)
  └─ $mysqli4 - web database (ltc_web)
       ├─ badges table (used by floating-badges.php)
       ├─ footer_info table
       ├─ footer_links table
       ├─ main_menus table
       ├─ sub_main_menus table
       ├─ users table
       └─ web_pages table
```

## 📋 ไฟล์ที่สำคัญ

### Core Files
| ไฟล์ | วัตถุประสงค์ | เชื่อมโยงไปยัง |
|------|-----------|----------|
| index.php | หน้าแรก | base.php, app-news/* |
| base.php | Template master | app-menu/*, app-footer/footer.php, floating-badges.php |
| config.php | Configuration | middleware.php |
| condb/condb.php | Database connections | ทุกไฟล์ที่ต้องเข้าถึง DB |

### Frontend Pages
| ไฟล์ | วัตถุประสงค์ |
|------|-----------|
| floating-badges.php | Floating badges (แทน messenger-button.php) |
| app-news/main-news.php | Main news section |
| app-news/annonce-news.php | Announcements section |
| app-news/annonce_detail.php | News detail page |
| app-menu/carousel.php | Homepage carousel |
| app-footer/footer.php | Footer section |

### Admin Pages
| ไฟล์ | วัตถุประสงค์ |
|------|-----------|
| admin/dashboard.php | Admin dashboard layout |
| admin/middleware.php | Authentication & authorization |
| admin/badge_manage.php | ✨ **Manage badges (NEW)** |
| admin/admin-manage.php | Manage admin users |
| admin/carousel_manage.php | Manage carousel |
| admin/footer_manage.php | Manage footer |
| admin/navbar_menu_manage.php | Manage navigation menu |
| admin/personel_manage.php | Manage personnel |
| admin/news_manage.php | Manage news |
| admin/webpages_manage.php | Manage web pages |

## 🗄️ Database Tables

### ltc_web Database
```
├─ badges (✨ NEW)
│  ├─ id (INT)
│  ├─ badge_name (VARCHAR 100)
│  ├─ badge_description (TEXT)
│  ├─ badge_icon (VARCHAR 100) - Font Awesome
│  ├─ badge_image (VARCHAR 255) - Image path ✨ NEW
│  ├─ badge_color (VARCHAR 7) - Hex color
│  ├─ badge_url (TEXT) ✨ NEW - Link target
│  ├─ visible (INT) - 0/1
│  └─ created_at (TIMESTAMP)
│
├─ footer_info
├─ footer_links
├─ main_menus
├─ sub_main_menus
├─ users
├─ web_pages
└─ ...

### ltc_news Database
├─ news
├─ categories
└─ attachments

### ltc_letter Database
├─ letters
└─ carousel

### ltc_personal Database
├─ personel_data
├─ positions
├─ workbranch
├─ worklevel
├─ work_detail
├─ education_level
├─ gender
├─ department
└─ position_level
```

## 📂 File Upload Directories

```
uploads/
├─ badges/ (✨ NEW) - Badge images
├─ pages/ - Web page files
└─ carousel/ (in admin/uploads/)

admin/uploads/
├─ carousel/ - Carousel images
└─ ltc_personal/ - Personnel data
```

## 🔄 Data Flow

### Frontend Badge Display
```
1. User visits homepage (index.php)
   ↓
2. base.php includes floating-badges.php
   ↓
3. floating-badges.php:
   - Connects to database (condb.php)
   - Queries: SELECT * FROM badges WHERE visible=1
   - Loops through results
   - Displays each badge with icon or image
   - Shows on right side with float animation
```

### Admin Badge Management
```
1. Admin accesses /admin/badge_manage.php
   ↓
2. middleware.php checks authentication
   ↓
3. badge_manage.php:
   - Checks for POST requests (add/edit/delete)
   - Handles image upload to uploads/badges/
   - Executes queries on badges table
   - Shows paginated list with search
   ↓
4. dashboard.php provides layout
```

## 🔐 Authentication Flow

```
User Request
   ↓
middleware.php
   ├─ require config.php
   ├─ Check JWT token in $_SESSION
   ├─ Verify user role/permissions
   └─ Allow/Deny access
   ↓
If allowed: Load admin page
If denied: Redirect to login
```

## ⚙️ Configuration

### config.php
```php
- JWT secret key
- Database credentials
- API endpoints
- Site settings
```

### condb.php
```php
- $mysqli (ltc_web - users)
- $mysqli2 (ltc_news)
- $mysqli3 (ltc_letter)
- $mysqli4 (ltc_web - content)
```

## 🚀 Recent Changes

### Added ✨
- `floating-badges.php` - New badge system (replaces messenger-button.php)
- `admin/badge_manage.php` - Admin panel for managing badges
- Database columns: `badge_image`, `badge_url`
- Image upload support to `uploads/badges/`

### Removed ✌️
- `messenger-button.php` - Old hardcoded buttons

### Modified 🔄
- `base.php` - Changed from floating-badges.php
- Database schema - Added columns to badges table

## 📝 Notes

- All HTML special characters are escaped with `htmlspecialchars()`
- All database queries use prepared statements (mysqli_stmt)
- Image uploads validated by file type and size
- Badge system fetches data from database (no hardcoding)
- Floating badges show only on homepage (/)
- Animation updates: different delay times for staggered effect

## 🔗 Related Documentation

- [Floating Badges README](FLOATING_BADGES_README.md)
- [Migration File](migrate_badges.sql)

---
**Last Updated:** December 23, 2025
**Status:** All systems operational ✅

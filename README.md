# NewsMyJob - News Portal CMS

Production-ready News Portal CMS built with **Core PHP 8+**, **MySQL**, **Bootstrap 5**, **jQuery**, and **AJAX**.

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` enabled (XAMPP)
- PDO MySQL extension
- GD extension (optional, for image resize)

## Installation (XAMPP)

1. Copy project to `C:\xampp\htdocs\newsmyjob`
2. Start **Apache** and **MySQL** from XAMPP Control Panel
3. Open phpMyAdmin → Import → select `database/newsmyjob.sql`
4. Update database credentials in `config/database.php` if needed (default: root, no password)
5. Update `BASE_URL` in `config/config.php` if your path differs
6. Ensure `uploads/` is writable

## Default Admin Login

| Field    | Value                  |
|----------|------------------------|
| URL      | http://localhost/newsmyjob/admin/login.php |
| Email    | admin@newsmyjob.com    |
| Password | Admin@123              |

## Project Structure

```
newsmyjob/
├── admin/           Admin panel pages
├── ajax/            AJAX endpoints
├── api/             JSON API endpoints
├── assets/          CSS, JS, images
├── config/          App & database config
├── controllers/     MVC controllers
├── database/        SQL schema file
├── functions/       Helper & validation functions
├── includes/        Bootstrap, Auth, header, footer, sidebar
├── models/          OOP data models (PDO)
├── uploads/         User uploaded files
├── views/           View templates
├── index.php        Homepage
├── news.php         News detail page
├── .htaccess        SEO URL rewriting
└── robots.txt
```

## Features

- **Admin:** Secure login, roles (Super Admin, Admin, Editor, Author), dashboard analytics, news CRUD with CKEditor, categories, breaking/featured/trending news, media library, users, settings, ads, SEO, reports, backup/restore
- **Frontend:** Hero slider, breaking news bar, search, dark mode, newsletter, bookmarks, comments, responsive design
- **Security:** PDO prepared statements, CSRF, XSS protection, password hashing, login/activity logs

## SEO URLs

- `/news/article-slug`
- `/category/politics`
- `/search?q=keyword`
- `/sitemap.xml`

## API Endpoints

- `GET /api/news.php?page=1&featured=1`
- `GET /api/categories.php?subcategories=1`

## License

Open source — use freely for personal and commercial projects.

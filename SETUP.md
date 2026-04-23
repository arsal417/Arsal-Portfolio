# 🚀 Portfolio Backend Setup Guide

## What This Does
Your portfolio now has a full backend system:
- ✅ Contact form saves to database + sends email to you + auto-reply to client
- ✅ Review form — clients submit reviews, you approve them in admin panel
- ✅ Admin dashboard to manage everything at `yourdomain.com/admin/`

---

## Step 1 — Upload to Web Server
Upload ALL files to your web host (cPanel, Hostinger, etc.)  
Your server must support **PHP 7.4+** and **MySQL**.

---

## Step 2 — Create the Database
1. Open **phpMyAdmin** on your hosting panel
2. Create a new database named `arsalan_portfolio`
3. Import the file: `database/setup.sql`

---

## Step 3 — Edit Your Config
Open `api/config.php` and fill in:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'arsalan_portfolio');
define('DB_USER', 'your_db_username');   // from your hosting panel
define('DB_PASS', 'your_db_password');   // from your hosting panel
define('ADMIN_EMAIL', 'arsalify@gmail.com'); // YOUR email
define('SITE_URL',    'https://yourdomain.com');
```

---

## Step 4 — Set Your Admin Password
The default password in the SQL file is `password` (just a placeholder).

To set your own secure password:
1. Go to phpMyAdmin → `admins` table
2. Edit the row for `arsalan`
3. In the `password` field, select **Function: PASSWORD_HASH** or run:

```bash
php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT);"
```

4. Paste the output hash into the password field and save.

---

## Step 5 — Access Your Admin Panel
Go to: `https://yourdomain.com/admin/`

- **Username:** `arsalan`
- **Password:** the one you set above

---

## Admin Panel Features
| Page | URL | What it does |
|------|-----|--------------|
| Dashboard | `/admin/` | Stats overview |
| Inquiries | `/admin/contacts.php` | View all form submissions, reply via email |
| Reviews | `/admin/reviews.php` | Approve/reject client reviews |

---

## How to Add Your Video URLs (Work Page)
In `work.html`, find any `data-video-url=""` attribute and add your YouTube link:
```html
data-video-url="https://www.youtube.com/watch?v=YOUR_VIDEO_ID"
```

---

## How to Add Your Photo
In `index.html`, find:
```html
<img src="" alt="Arsalan Abbas" class="photo-img" id="profile-photo" .../>
```
Change `src=""` to `src="your-photo.jpg"` and upload the image file.

---

## Troubleshooting
- **Form not sending?** Check `api/config.php` credentials
- **Emails not arriving?** Your host may require SMTP — use PHPMailer or an SMTP plugin
- **Admin login fails?** Reset the password hash in phpMyAdmin

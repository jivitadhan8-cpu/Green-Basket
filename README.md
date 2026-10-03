# GreenBasket - Startup Online Grocery Store

A modern online grocery web app built with pure HTML, CSS, JavaScript, PHP, MySQL, AJAX, and XAMPP.

## Tech Stack
- Frontend: HTML5, CSS3 (Flexbox + Grid), JavaScript (Fetch AJAX)
- Backend: Core PHP (PDO prepared statements)
- Database: MySQL
- Libraries: Font Awesome, SweetAlert2, Chart.js

## Features Included
- User: registration/login, session auth, hero + banners, categories, featured/trending/recent/best sellers, product details, search + live suggestions, wishlist, AJAX cart, quantity update, checkout with coupon, fake payment UI, timeline tracking, order history, user dashboard, profile and notifications, contact page, dark mode.
- Admin: separate login, dashboard cards, low stock alerts, products/categories/orders/customers/reviews/coupons management, analytics charts, settings/profile.

## Default Credentials
- Admin:
  - Email: `admin@grocery.com`
  - Password: `admin123`

## Setup on XAMPP
1. Copy project into: `C:\xampp\htdocs\grocery`
2. Start Apache and MySQL from XAMPP Control Panel.
3. Open phpMyAdmin and import:
   - `C:\xampp\htdocs\grocery\database\grocery_startup.sql`
4. Open browser:
   - User app: `http://localhost/grocery/`
   - Admin login: `http://localhost/grocery/admin/login.php`

## Project Structure
- `assets/css/style.css` : Global responsive UI, cards, dashboard, timeline, dark mode.
- `assets/js/main.js` : AJAX cart/wishlist/search, SweetAlert2, dark mode toggle.
- `includes/` : Reusable app components and helpers.
- `ajax/` : Cart/wishlist/search async endpoints.
- `auth/` : User authentication pages.
- `user/` : User dashboard.
- `admin/` : Complete admin panel modules.
- `database/` : MySQL schema + seed data.

## Notes
- Images are loaded from free Unsplash URLs.
- Uses session-based authentication for users and admins.
- Uses secure password hashing (`password_hash` / `password_verify`) and prepared statements.
- Extend by adding image upload handling to store files in `assets/uploads/`.

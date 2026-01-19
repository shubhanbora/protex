# 🔐 Admin Login Credentials

## Default Admin Login

**Admin Panel URL:**
```
http://localhost/client/admin/login.php
```

**Login Credentials:**
```
Email/Username: admin
Password: password
```

---

## Kaise Login Karein:

1. Browser mein ye URL open karein:
   ```
   http://localhost/client/admin/login.php
   ```

2. Login form mein enter karein:
   - **Username or Email**: `admin`
   - **Password**: `password`

3. "Login" button click karein

4. Dashboard open ho jayega! 🎉

---

## Agar Login Nahi Ho Raha:

### Option 1: Database Check Karein
1. phpMyAdmin open karein: `http://localhost/phpmyadmin`
2. `ecommerce_db` database select karein
3. `admins` table pe click karein
4. Check karein ki admin entry hai ya nahi

### Option 2: Admin Reset Karein
1. phpMyAdmin mein `ecommerce_db` select karein
2. "SQL" tab pe click karein
3. Ye query run karein:

```sql
DELETE FROM admins;

INSERT INTO admins (username, email, password) VALUES 
('admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
```

4. "Go" button click karein
5. Ab login try karein

---

## Password Change Karna Hai?

Agar aap password change karna chahte ho:

1. phpMyAdmin open karein
2. `ecommerce_db` → `admins` table
3. Admin row pe "Edit" click karein
4. Password field mein naya hash dalein

**Ya phir:**

PHP se naya hash generate karein:
```php
<?php
echo password_hash('your_new_password', PASSWORD_DEFAULT);
?>
```

---

## Security Tips:

⚠️ **Production mein:**
- Strong password use karein
- Default credentials change kar dein
- Regular password updates karein
- Admin panel ko secure karein

---

## Quick Reference:

| Field | Value |
|-------|-------|
| URL | http://localhost/client/admin/login.php |
| Username | admin |
| Email | admin |
| Password | password |

---

**Happy Managing! 💪🚀**

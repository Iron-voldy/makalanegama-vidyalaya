# InfinityFree Deployment Guide for Makalanegama School Website

## Database Setup

### 1. Create Database
- Login to your InfinityFree control panel
- Go to "MySQL Databases"
- Create a database with name: `if0_39408289_makalanegama_school`
- Note down the full database name (it will be: `if0_39408289_makalanegama_school`)

### 2. Database Configuration (Already Updated)
The following files have been updated with your hosting details:
- `admin/config.php` - Main configuration file
- `admin/database.php` - Database connection class

**Database Credentials Used:**
- Host: `sql201.infinityfree.com`
- Username: `if0_39408289`
- Password: `Hasindu2002`
- Database: `if0_39408289_makalanegama_school`
- Port: `3306`

### 3. Import Database Schema
Upload and import the `database/school.sql` file into your newly created database:

1. Access phpMyAdmin from your InfinityFree control panel
2. Select your database: `if0_39408289_makalanegama_school`
3. Click "Import" tab
4. Choose file: `database/school.sql`
5. Click "Go" to import

## File Upload Setup

### 1. Create Upload Directories
Create these directories on your hosting:
```
/htdocs/assets/uploads/
/htdocs/assets/uploads/2025/
/htdocs/assets/uploads/2025/01/
/htdocs/assets/uploads/2025/02/
... (monthly folders as needed)
```

### 2. Set Permissions
Set folder permissions to 755 for upload directories:
- `/assets/uploads/` - 755
- All subdirectories - 755

## Admin Panel Setup

### 1. Create Admin User
After database import, create an admin user by running this SQL in phpMyAdmin:

```sql
INSERT INTO admin_users (username, password_hash, full_name, role, is_active, created_at) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 1, NOW());
```

**Default Login:**
- Username: `admin`
- Password: `password` (change this immediately after first login)

### 2. Admin Panel Access
- URL: `https://yourdomain.com/admin/`
- Login with the credentials above
- Change the default password immediately

## File Upload Configuration

### 1. PHP Settings
InfinityFree has these limitations:
- Max file size: 10MB (already configured in config.php)
- Allowed file types: jpg, jpeg, png, webp (already configured)

### 2. Upload Path
- Upload path is set to: `assets/uploads/`
- Files are organized by year/month automatically

## Security Notes

### 1. Change Default Password
Immediately change the admin password after first login.

### 2. File Permissions
Ensure upload directories have correct permissions (755).

### 3. Database Security
- Never share database credentials
- Use strong passwords
- Regularly backup your database

## Testing Checklist

After deployment, test these features:

### Frontend
- [ ] Home page loads correctly
- [ ] Teachers page shows 3 cards per row on desktop
- [ ] Achievements page works
- [ ] Events page works
- [ ] News page works
- [ ] Contact form works
- [ ] All images load correctly

### Admin Panel
- [ ] Admin login works
- [ ] Teachers management works
- [ ] Image uploads work
- [ ] Achievements management works
- [ ] Events management works
- [ ] News management works
- [ ] Contact messages display

### Database
- [ ] All tables imported correctly
- [ ] Teachers data displays on frontend
- [ ] Admin can add/edit/delete records

## Troubleshooting

### Database Connection Issues
If you get database connection errors:
1. Verify database name is exactly: `if0_39408289_makalanegama_school`
2. Check hostname: `sql201.infinityfree.com`
3. Verify username: `if0_39408289`
4. Verify password: `Hasindu2002`

### File Upload Issues
1. Check directory permissions (755)
2. Verify upload directories exist
3. Check file size limits

### Teachers Page Not Showing 3 Cards
1. Clear browser cache
2. Check if Bootstrap CSS is loading
3. Verify JavaScript console for errors

## Support
For any issues:
1. Check InfinityFree documentation
2. Verify all file paths are correct
3. Check error logs in control panel
4. Ensure all required PHP extensions are available

## Production Optimizations
- Environment is set to 'production' (errors hidden from users)
- Database errors are logged but not displayed
- Upload security is enforced
- CSRF protection is enabled
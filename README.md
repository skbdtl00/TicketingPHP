# Tozei Ticketing System

A PHP-based ticketing system with OAuth authentication and MySQL database.

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx with mod_rewrite enabled

## Setup Instructions

### 1. Database Configuration

Create a MySQL database for the application:

```sql
CREATE DATABASE ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Environment Configuration

Edit `config/config.php` to set your database credentials:

```php
'db_host' => 'localhost',
'db_name' => 'ticketing',
'db_user' => 'root',
'db_pass' => '',
```

Or use environment variables:
- `DB_HOST` - MySQL host (default: localhost)
- `DB_NAME` - Database name (default: ticketing)
- `DB_USER` - Database user (default: root)
- `DB_PASS` - Database password (default: empty)

### 3. Run Database Migration

Option A - Using PHP migration script:

```bash
php migrate.php
```

Option B - Import SQL schema directly:

```bash
mysql -u root -p < schema.sql
```

### 4. OAuth Configuration

The system uses Tozei OAuth for authentication. The OAuth callback URL should be:

```
https://your-domain.com/oauth/callback
```

Configure this callback URL in the Tozei OAuth provider settings at `https://tozei.com/oauth/`

The OAuth secret key is configured in `config/config.php`:

```php
'oauth_secret' => 'FWV9agSoDqnlFWV9agSoDqnl',
```

### 5. File Permissions

Ensure the uploads directory is writable:

```bash
chmod 755 uploads
```

## Features

- **OAuth Authentication**: Secure login via Tozei OAuth
- **Ticket Management**: Create, view, and manage support tickets
- **File Attachments**: Upload files to tickets (JPG, PNG, PDF, ZIP)
- **Admin Dashboard**: Manage all tickets and users
- **Multi-theme Support**: Light and dark themes
- **Thai Language Interface**: All UI elements in Thai

## Security Features

- CSRF protection on all forms
- File type and size validation
- SQL injection prevention via prepared statements
- Path traversal protection for file downloads
- OAuth token signature verification

## OAuth Token Format

The OAuth callback expects a token in the format: `{payload}.{signature}`

Where:
- `payload` = base64-encoded JSON with user data
- `signature` = HMAC-SHA256 of payload with OAuth secret

Expected user data fields:
- `user_id` - Unique user identifier
- `username` - Username
- `role` - User role (user/admin)
- `realname` - User's first name (optional)
- `surname` - User's last name (optional)

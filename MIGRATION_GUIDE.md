# Migration Guide: SQLite to MySQL with OAuth

This guide explains how to migrate from the old SQLite + local authentication system to the new MySQL + OAuth authentication system.

## What Changed

### Database Changes

1. **Database Engine**: SQLite → MySQL
   - Better performance and scalability
   - Proper foreign key constraints
   - Native UTF-8 support

2. **User Table Schema**:
   - Added `oauth_user_id` - Unique identifier from OAuth provider
   - Added `username` - Username from OAuth
   - Added `realname` - User's first name
   - Added `surname` - User's last name
   - Removed `password_hash` - No longer using local passwords
   - Changed `id` from INTEGER to INT AUTO_INCREMENT
   - Changed `created_at` from DATETIME to TIMESTAMP

3. **Tickets Table Schema**:
   - Changed `id` from INTEGER to INT AUTO_INCREMENT
   - Changed timestamps to TIMESTAMP with auto-update
   - Added indexes for better query performance

4. **Replies Table Schema**:
   - Changed `id` from INTEGER to INT AUTO_INCREMENT
   - Changed `created_at` from DATETIME to TIMESTAMP
   - Added indexes for better query performance

### Authentication Changes

1. **Removed Features**:
   - `/register` endpoint - Users are automatically created on first OAuth login
   - `/login` POST handler - Login now redirects to OAuth provider
   - Local password authentication

2. **New Features**:
   - OAuth integration with https://tozei.com/oauth/
   - Automatic user creation/update on OAuth login
   - OAuth token validation with HMAC-SHA256 signatures

3. **User Flow**:
   - User clicks "Login" → Redirects to OAuth provider
   - User authenticates at OAuth provider → Redirects back with token
   - System validates token → Creates/updates user → Sets session

## Migration Steps

### For New Installations

1. **Create MySQL Database**:
   ```bash
   mysql -u root -p -e "CREATE DATABASE ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

2. **Configure Database**:
   - Copy `.env.example` to `.env` and edit values
   - Or set environment variables

3. **Run Migration**:
   ```bash
   php migrate.php
   ```
   Or import schema directly:
   ```bash
   mysql -u root -p ticketing < schema.sql
   ```

4. **Configure OAuth**:
   - Register your application at https://tozei.com/oauth/
   - Set callback URL: `https://your-domain.com/oauth/callback`
   - Update `OAUTH_SECRET` in config

### For Existing Installations

**WARNING**: This migration will require re-authentication of all users.

1. **Backup Existing Data**:
   ```bash
   sqlite3 config/database.sqlite .dump > backup.sql
   ```

2. **Export User Data** (if you want to preserve users):
   ```bash
   sqlite3 config/database.sqlite "SELECT email, name, role FROM users" > users.csv
   ```

3. **Export Ticket Data**:
   ```bash
   sqlite3 config/database.sqlite .dump > tickets_backup.sql
   ```

4. **Create MySQL Database**:
   ```bash
   mysql -u root -p -e "CREATE DATABASE ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

5. **Run Migration**:
   ```bash
   php migrate.php
   ```

6. **Manual Data Migration**:
   Since the schema changed (removed password_hash, added oauth fields), you'll need to manually migrate ticket data:
   
   - Export tickets from SQLite
   - Manually map users to their OAuth IDs when they first login
   - Import tickets with updated user_id references

   Alternatively, you can write a custom migration script to automate this.

## OAuth Configuration

### Environment Variables

Set these environment variables or update `config/config.php`:

```bash
DB_HOST=localhost
DB_NAME=ticketing
DB_USER=root
DB_PASS=your_password
OAUTH_SECRET=FWV9agSoDqnlFWV9agSoDqnl
OAUTH_EMAIL_DOMAIN=@tozei.com
```

### OAuth Token Format

The OAuth provider should return a token in the format:

```
{base64_payload}.{hmac_signature}
```

Payload should contain:
```json
{
  "user_id": "unique_oauth_user_id",
  "username": "username",
  "role": "user",
  "realname": "First Name",
  "surname": "Last Name"
}
```

The signature is: `hash_hmac('sha256', $payload, $oauth_secret)`

## Testing OAuth Locally

Use the standalone callback script for testing:

```bash
# Set the OAuth secret
export OAUTH_SECRET='FWV9agSoDqnlFWV9agSoDqnl'

# Create a test token (PHP)
php -r "
\$data = ['user_id' => '123', 'username' => 'testuser', 'role' => 'user', 'realname' => 'Test', 'surname' => 'User'];
\$payload = base64_encode(json_encode(\$data));
\$signature = hash_hmac('sha256', \$payload, 'FWV9agSoDqnlFWV9agSoDqnl');
echo \$payload . '.' . \$signature;
"

# Visit: http://localhost/oauth_callback_standalone.php?token={token}
```

## Security Considerations

1. **OAuth Secret**: Never commit the production OAuth secret to version control. Always use environment variables.

2. **Token Validation**: The system validates OAuth tokens using HMAC-SHA256 signatures. Ensure the secret matches between the OAuth provider and your application.

3. **HTTPS Required**: OAuth flows should always use HTTPS in production to prevent token interception.

4. **Session Security**: Set appropriate session settings in production:
   ```php
   ini_set('session.cookie_secure', 1);
   ini_set('session.cookie_httponly', 1);
   ini_set('session.cookie_samesite', 'Lax');
   ```

## Troubleshooting

### "Invalid token" Error
- Check that OAUTH_SECRET matches between provider and application
- Verify token format is `{payload}.{signature}`
- Check that payload is valid base64-encoded JSON

### Database Connection Error
- Verify MySQL is running
- Check database credentials in config
- Ensure database exists and user has proper permissions

### Missing OAuth Fields
- Verify OAuth provider sends all required fields
- Check that token payload contains user_id and username at minimum
- Optional fields (realname, surname) will default to empty strings

## Rolling Back

If you need to roll back to SQLite:

1. Restore the previous code version
2. Restore SQLite database from backup
3. Update config to use SQLite DSN

Note: Any tickets created during MySQL/OAuth period will be lost unless manually exported.

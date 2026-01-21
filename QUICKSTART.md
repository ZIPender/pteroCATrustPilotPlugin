# Quick Reference

## Installation (5 Minutes)

```bash
# 1. Clone plugin
cd /path/to/pteroca/plugins
git clone https://github.com/ZIPender/pteroCATrustPilotPlugin.git TrustpilotReview

# 2. Register in config/app.php
# Add: Plugins\TrustpilotReview\TrustpilotPlugin::class,

# 3. Run migrations
php artisan migrate

# 4. Publish assets
php artisan vendor:publish --tag=trustpilot-assets

# 5. Configure .env
TRUSTPILOT_ENABLED=true
TRUSTPILOT_DAYS_BEFORE_EXPIRY=7
TRUSTPILOT_REVIEW_URL=https://www.trustpilot.com/evaluate/your-business
```

## Admin Access

**URL:** `/admin/trustpilot`

**Settings:**
- Days before expiry: 1-365
- Review URL: Your Trustpilot business URL
- Enabled: On/Off toggle
- API Key: Optional

## API Endpoints

### User APIs (Authenticated)
```
GET  /api/trustpilot/check/{serverId}
POST /api/trustpilot/dismiss/{serverId}
```

### Admin APIs (Admin Role)
```
GET  /api/admin/trustpilot/settings
POST /api/admin/trustpilot/settings
GET  /api/admin/trustpilot/stats
```

## Frontend Integration

### React
```javascript
import { TrustpilotPopup } from '@plugins/trustpilot';

<TrustpilotPopup serverId={server.id} userId={user.id} />
```

### Vanilla JS
```javascript
import { initTrustpilotPlugin } from '/plugins/trustpilot/js/index.js';

initTrustpilotPlugin({
  id: serverId,
  userId: currentUserId
});
```

## Database Tables

**trustpilot_dismissals**
- Tracks which users dismissed which servers
- Unique constraint: user_id + server_id

**trustpilot_settings**
- Stores plugin configuration
- Key-value pairs

## Common Tasks

### Update Settings
```php
// Via AdminController
POST /api/admin/trustpilot/settings
{
  "days_before_expiry": 14,
  "review_url": "https://...",
  "enabled": true
}
```

### Check Popup Status
```php
// Via TrustpilotController
GET /api/trustpilot/check/123
// Returns: { show: true/false, days_until_expiry: 5, review_url: "..." }
```

### Reset User Dismissals
```sql
DELETE FROM trustpilot_dismissals WHERE user_id = ?;
```

### View Statistics
```sql
SELECT COUNT(*) as total_dismissals FROM trustpilot_dismissals;
SELECT COUNT(DISTINCT user_id) as unique_users FROM trustpilot_dismissals;
```

## Troubleshooting

### Popup Not Showing
1. Check plugin enabled: `SELECT * FROM trustpilot_settings WHERE key = 'enabled'`
2. Check server expiry: `SELECT expires_at FROM servers WHERE id = ?`
3. Check dismissal: `SELECT * FROM trustpilot_dismissals WHERE user_id = ? AND server_id = ?`
4. Check browser console for errors

### Settings Not Saving
1. Check admin permissions
2. Check database connection
3. View logs: `storage/logs/laravel.log`
4. Validate input format

### Assets Not Loading
1. Re-publish: `php artisan vendor:publish --tag=trustpilot-assets --force`
2. Check permissions: `chmod -R 755 public/plugins/trustpilot/`
3. Clear cache: `php artisan cache:clear`

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| TRUSTPILOT_ENABLED | true | Enable plugin |
| TRUSTPILOT_DAYS_BEFORE_EXPIRY | 7 | Days threshold |
| TRUSTPILOT_REVIEW_URL | - | Trustpilot URL |
| TRUSTPILOT_API_KEY | null | API key (optional) |

## File Locations

```
plugins/TrustpilotReview/
├── TrustpilotPlugin.php          # Main plugin class
├── app/
│   ├── Controllers/              # HTTP controllers
│   ├── Models/                   # Database models
│   └── Services/                 # Business logic
├── config/trustpilot.php         # Config file
├── database/migrations/          # Migrations
├── resources/
│   ├── css/                      # Stylesheets
│   ├── js/                       # React components
│   └── views/                    # Blade templates
└── routes/                       # API & web routes
```

## Support

**Documentation:**
- [README.md](README.md) - Overview
- [INSTALL.md](INSTALL.md) - Installation
- [ARCHITECTURE.md](ARCHITECTURE.md) - Technical details

**Issues:** https://github.com/ZIPender/pteroCATrustPilotPlugin/issues

## Version

Current: **1.0.0**
Released: **2024-01-21**
Compatibility: **PteroCA Panel v0.6+**

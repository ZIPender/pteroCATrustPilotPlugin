# Installation Guide

## Prerequisites

- PteroCA Panel v0.6 or higher
- PHP 7.4+ or 8.0+
- MySQL 5.7+ or MariaDB 10.2+
- Node.js and NPM (for building assets if needed)

## Step-by-Step Installation

### Method 1: Upload via Panel (Recommended)

PteroCA supports installing plugins via ZIP file upload in the admin panel. This is the easiest and recommended method.

#### Step 1: Create the ZIP File

**Option A: Download from GitHub Releases**
1. Go to the [Releases](https://github.com/ZIPender/pteroCATrustPilotPlugin/releases) page
2. Download the latest release ZIP file (e.g., `TrustpilotReview-v1.0.0.zip` or similar)
3. Skip to Step 2 below

**Option B: Create ZIP from Repository (Linux/macOS)**
```bash
# Clone the repository
git clone https://github.com/ZIPender/pteroCATrustPilotPlugin.git

# Navigate into the directory
cd pteroCATrustPilotPlugin

# Create the ZIP file (excluding .git and unnecessary files)
zip -r TrustpilotReview.zip . -x "*.git*" -x "node_modules/*" -x "vendor/*" -x "*.log" -x ".DS_Store" -x "*.swp" -x "*.tmp" -x ".env*"
```

**Option C: Create ZIP from Repository (Windows PowerShell)**
```powershell
# Clone the repository
git clone https://github.com/ZIPender/pteroCATrustPilotPlugin.git

# Navigate into the directory
cd pteroCATrustPilotPlugin

# Remove .git folder before zipping (required to reduce size)
Remove-Item -Recurse -Force .git -ErrorAction SilentlyContinue

# Create the ZIP file
Compress-Archive -Path * -DestinationPath TrustpilotReview.zip -Force
```

#### Step 2: Upload to PteroCA

1. Log in to your PteroCA admin panel
2. Navigate to **Settings → Plugins**
3. Click the **"Upload Plugin"** button
4. Select the `TrustpilotReview.zip` file you created or downloaded
5. Wait for the upload and installation to complete
6. The plugin will appear in your plugins list

#### Step 3: Enable the Plugin

1. In the plugins list, find "TrustpilotReview"
2. Click the enable/activate toggle or button
3. The plugin is now active

#### Step 4: Configure the Plugin

1. Click on the plugin settings/configure option
2. Set your Trustpilot review URL
3. Configure the days before expiry threshold
4. Save your settings

### Method 2: Manual Installation

If you prefer manual installation or need more control:

#### 1. Download the Plugin

**Option A: Git Clone**
```bash
cd /path/to/pteroca/plugins
git clone https://github.com/ZIPender/pteroCATrustPilotPlugin.git TrustpilotReview
```

**Option B: Manual Download**
1. Download the latest release from GitHub
2. Extract to `plugins/TrustpilotReview` directory

#### 2. Register Service Provider

Edit `config/app.php` and add the plugin to the providers array:

```php
'providers' => [
    // ... existing providers
    
    /*
     * Plugin Service Providers...
     */
    Plugins\TrustpilotReview\TrustpilotPlugin::class,
],
```

#### 3. Run Database Migrations

Create the required database tables:

```bash
php artisan migrate
```

You should see output like:
```
Migrating: 2024_01_01_000001_create_trustpilot_dismissals_table
Migrated:  2024_01_01_000001_create_trustpilot_dismissals_table
Migrating: 2024_01_01_000002_create_trustpilot_settings_table
Migrated:  2024_01_01_000002_create_trustpilot_settings_table
```

#### 4. Publish Plugin Assets

Publish CSS and JavaScript files:

```bash
php artisan vendor:publish --tag=trustpilot-assets
```

Publish configuration file:

```bash
php artisan vendor:publish --tag=trustpilot-config
```

#### 5. Configure Environment

Add these lines to your `.env` file:

```env
# Trustpilot Plugin Configuration
TRUSTPILOT_ENABLED=true
TRUSTPILOT_DAYS_BEFORE_EXPIRY=7
TRUSTPILOT_REVIEW_URL=https://www.trustpilot.com/evaluate/your-business-name
TRUSTPILOT_API_KEY=
```

**Important**: Replace `your-business-name` with your actual Trustpilot business URL slug.

#### 6. Get Your Trustpilot Review URL

1. Go to https://www.trustpilot.com/
2. Log in to your business account
3. Navigate to "Get Reviews" section
4. Copy your review invitation link
5. Update `TRUSTPILOT_REVIEW_URL` in `.env`

Example URLs:
- `https://www.trustpilot.com/evaluate/yourdomain.com`
- `https://www.trustpilot.com/evaluate/your-business-name`

#### 7. Clear Cache

Clear all caches to load the new plugin:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

#### 8. Verify Installation

Check that the plugin is loaded:

```bash
php artisan route:list | grep trustpilot
```

You should see routes like:
```
GET|HEAD  api/trustpilot/check/{serverId}
POST      api/trustpilot/dismiss/{serverId}
GET|HEAD  api/admin/trustpilot/settings
POST      api/admin/trustpilot/settings
GET|HEAD  api/admin/trustpilot/stats
GET|HEAD  admin/trustpilot
```

#### 9. Access Admin Panel

1. Log in to your PteroCA Panel as an administrator
2. Navigate to `/admin/trustpilot`
3. Verify settings are loaded correctly
4. Adjust configuration as needed

## Frontend Integration

### For React-Based Panels

If your panel uses React, add the component to your server view:

```javascript
import { TrustpilotPopup } from '@plugins/trustpilot';

function ServerView({ server, user }) {
  return (
    <div>
      {/* Your server view content */}
      
      <TrustpilotPopup 
        serverId={server.id} 
        userId={user.id} 
      />
    </div>
  );
}
```

### For Vue-Based Panels

If your panel uses Vue, create a wrapper component:

```vue
<template>
  <div id="trustpilot-popup-container"></div>
</template>

<script>
export default {
  mounted() {
    // Load and initialize the popup
    const script = document.createElement('script');
    script.src = '/plugins/trustpilot/js/index.js';
    document.head.appendChild(script);
  }
}
</script>
```

### For Blade Templates

Add this to your server view blade template:

```blade
@section('scripts')
    <script src="{{ asset('plugins/trustpilot/js/index.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initTrustpilotPlugin({
                id: {{ $server->id }},
                userId: {{ Auth::user()->id }}
            });
        });
    </script>
@endsection
```

## Post-Installation Configuration

### 1. Test the Plugin

Create a test server with an expiration date within 7 days:

```sql
UPDATE servers 
SET expires_at = DATE_ADD(NOW(), INTERVAL 5 DAY) 
WHERE id = YOUR_TEST_SERVER_ID;
```

Visit the server page and verify the popup appears.

### 2. Adjust Settings

In the admin panel (`/admin/trustpilot`):
- Set appropriate days before expiry (recommended: 7-14 days)
- Verify your Trustpilot URL is correct
- Enable/disable as needed

### 3. Monitor Statistics

Check the admin dashboard to see:
- Total dismissals
- Unique users who dismissed
- Engagement metrics

## Troubleshooting

### Plugin Not Loading

**Check service provider registration:**
```bash
php artisan config:show app.providers
```

**Verify plugin files exist:**
```bash
ls -la plugins/TrustpilotReview/
```

### Migrations Failed

**Check database connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

**Re-run migrations:**
```bash
php artisan migrate:rollback --step=2
php artisan migrate
```

### Assets Not Loading

**Re-publish with force:**
```bash
php artisan vendor:publish --tag=trustpilot-assets --force
```

**Check file permissions:**
```bash
chmod -R 755 public/plugins/trustpilot/
```

### Popup Not Appearing

1. **Check browser console for errors**
2. **Verify API endpoint responds:**
   ```bash
   curl -H "Authorization: Bearer YOUR_TOKEN" \
        http://your-panel.com/api/trustpilot/check/1
   ```
3. **Check server has expiration date:**
   ```sql
   SELECT id, expires_at FROM servers WHERE id = YOUR_SERVER_ID;
   ```

### Permission Errors

**Fix ownership:**
```bash
chown -R www-data:www-data plugins/TrustpilotReview/
```

**Fix permissions:**
```bash
find plugins/TrustpilotReview -type f -exec chmod 644 {} \;
find plugins/TrustpilotReview -type d -exec chmod 755 {} \;
```

## Updating

To update the plugin:

```bash
cd plugins/TrustpilotReview
git pull origin main
php artisan migrate
php artisan vendor:publish --tag=trustpilot-assets --force
php artisan config:clear
php artisan cache:clear
```

## Uninstalling

To remove the plugin:

1. **Remove from config:**
   ```php
   // Remove from config/app.php providers array
   Plugins\TrustpilotReview\TrustpilotPlugin::class,
   ```

2. **Rollback migrations:**
   ```bash
   php artisan migrate:rollback --step=2
   ```

3. **Remove files:**
   ```bash
   rm -rf plugins/TrustpilotReview
   rm -rf public/plugins/trustpilot
   rm config/trustpilot.php
   ```

4. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## Getting Help

If you encounter issues:

1. Check the troubleshooting section above
2. Review logs: `storage/logs/laravel.log`
3. Check browser console for JavaScript errors
4. Create an issue on GitHub with:
   - PteroCA Panel version
   - PHP version
   - Error messages
   - Steps to reproduce

## Next Steps

- [Configuration Guide](CONFIGURATION.md)
- [API Documentation](API.md)
- [Customization Guide](CUSTOMIZATION.md)

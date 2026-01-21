# PteroCA Trustpilot Review Plugin

A plugin for PteroCA Panel (v0.6+) that prompts users to leave a Trustpilot review when their server is approaching its expiration date.

## Features

- 🎯 **Smart Timing**: Automatically shows review popup when servers are expiring within X days
- ⚙️ **Configurable**: Admin panel to customize days threshold and review URL
- 🚫 **User-Friendly**: "No thanks" option that persists per user+server
- 🔒 **Privacy-Focused**: Only tracks dismissals, no personal data collected
- 📊 **Analytics**: View statistics on popup dismissals and user engagement
- 🎨 **Beautiful UI**: Modern, responsive design with smooth animations

## Installation

### Method 1: Upload via Panel (Recommended)

PteroCA supports installing plugins via ZIP file upload in the admin panel.

#### Creating the ZIP File

**Option A: Download from GitHub Releases**
1. Go to the [Releases](https://github.com/ZIPender/pteroCATrustPilotPlugin/releases) page
2. Download the latest `.zip` file

**Option B: Create ZIP from Repository**

```bash
# Clone the repository
git clone https://github.com/ZIPender/pteroCATrustPilotPlugin.git

# Navigate into the directory
cd pteroCATrustPilotPlugin

# Create the ZIP file (excluding .git and unnecessary files)
zip -r TrustpilotReview.zip . -x "*.git*" -x "node_modules/*" -x "vendor/*" -x "*.log" -x ".DS_Store" -x "*.swp" -x "*.tmp" -x ".env*"
```

Or on Windows (PowerShell):
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

#### Uploading to PteroCA

1. Navigate to **Settings → Plugins** in the PteroCA admin panel
2. Click the **"Upload Plugin"** button
3. Select the `TrustpilotReview.zip` file you created
4. The plugin will be automatically installed
5. Enable the plugin in the plugins list
6. Configure plugin settings (see [Configuration](#configuration) below)

### Method 2: Manual Installation

```bash
# Clone directly into plugins directory
git clone https://github.com/ZIPender/pteroCATrustPilotPlugin.git /path/to/pteroca/plugins/TrustpilotReview

# Run migrations
php artisan migrate

# Publish assets
php artisan vendor:publish --tag=trustpilot-assets
```

## Configuration

After installation, configure the plugin via the PteroCA admin panel or by adding these environment variables to your `.env` file:

```env
TRUSTPILOT_ENABLED=true
TRUSTPILOT_DAYS_BEFORE_EXPIRY=7
TRUSTPILOT_REVIEW_URL=https://www.trustpilot.com/evaluate/your-business
```

## Documentation

- [Installation Guide](INSTALL.md) - Detailed installation instructions
- [Configuration](config/trustpilot.php) - Configuration options
- [API Endpoints](#api-endpoints) - API documentation

## How It Works

1. Plugin monitors servers approaching expiration
2. When a server is within X days of expiring, shows popup
3. User can choose to review or dismiss
4. Dismissal is persisted per user+server pair
5. Admin can configure settings and view statistics

## Admin Panel

Access at: `/admin/trustpilot`

Configure:
- Days before expiry threshold
- Trustpilot review URL
- Enable/disable plugin
- View dismissal statistics

## API Endpoints

### User Endpoints
- `GET /api/trustpilot/check/{serverId}` - Check if popup should show
- `POST /api/trustpilot/dismiss/{serverId}` - Dismiss popup

### Admin Endpoints
- `GET /api/admin/trustpilot/settings` - Get settings
- `POST /api/admin/trustpilot/settings` - Update settings
- `GET /api/admin/trustpilot/stats` - Get statistics

## Requirements

- PteroCA Panel v0.6+
- PHP 7.4+ or 8.0+
- MySQL 5.7+ or MariaDB 10.2+
- React 17+ or 18+ (for frontend)

## License

MIT License - See LICENSE file for details

## Support

- GitHub Issues: https://github.com/ZIPender/pteroCATrustPilotPlugin/issues
- Documentation: See INSTALL.md for detailed guides

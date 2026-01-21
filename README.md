# PteroCA Trustpilot Review Plugin

A plugin for PteroCA Panel (v0.6+) that prompts users to leave a Trustpilot review when their server is approaching its expiration date.

## Features

- 🎯 **Smart Timing**: Automatically shows review popup when servers are expiring within X days
- ⚙️ **Configurable**: Admin panel to customize days threshold and review URL
- 🚫 **User-Friendly**: "No thanks" option that persists per user+server
- 🔒 **Privacy-Focused**: Only tracks dismissals, no personal data collected
- 📊 **Analytics**: View statistics on popup dismissals and user engagement
- 🎨 **Beautiful UI**: Modern, responsive design with smooth animations

## Quick Start

```bash
# Install
git clone https://github.com/ZIPender/pteroCATrustPilotPlugin.git plugins/TrustpilotReview

# Setup
php artisan migrate
php artisan vendor:publish --tag=trustpilot-assets

# Configure
# Add to .env:
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

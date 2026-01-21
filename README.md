# PteroCA Trustpilot Review Plugin

A plugin for PteroCA Panel (v0.6+) that prompts users to leave a Trustpilot review when their server is approaching its expiration date.

## Features

- 🎯 **Smart Timing**: Automatically shows review widget when servers are expiring within X days
- ⚙️ **Configurable**: Admin panel to customize days threshold, review URL, and popup messages
- 🚫 **User-Friendly**: "No thanks" option that persists per user+server
- 🔒 **Privacy-Focused**: Only tracks dismissals, no personal data collected
- 📊 **Analytics**: View statistics on popup dismissals and user engagement
- 🎨 **Beautiful UI**: Modern, responsive design with smooth animations
- 🌙 **Dark Mode**: Supports dark mode automatically

## Requirements

- PteroCA Panel v0.6.0 or higher
- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.2+

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
zip -r TrustpilotReview.zip . -x "*.git*" -x "node_modules/*" -x "vendor/*" -x "*.log" -x ".DS_Store"
```

#### Uploading to PteroCA

1. Navigate to **Settings → Plugins** in the PteroCA admin panel
2. Click the **"Upload Plugin"** button
3. Select the `TrustpilotReview.zip` file
4. The plugin will be automatically installed
5. Enable the plugin in the plugins list
6. Configure plugin settings (see [Configuration](#configuration) below)

### Method 2: Manual Installation

```bash
# Clone directly into plugins directory
git clone https://github.com/ZIPender/pteroCATrustPilotPlugin.git /path/to/pteroca/plugins/trustpilot-review

# Scan for new plugins
php bin/console plugin:scan

# Enable the plugin
php bin/console plugin:enable trustpilot-review
```

## Configuration

After installation, configure the plugin via the PteroCA admin panel:

**Settings → Plugin: trustpilot-review**

| Setting | Description | Default |
|---------|-------------|---------|
| Enable Plugin | Enable or disable the plugin globally | `true` |
| Days Before Expiry | Days before server expiry to show popup | `7` |
| Trustpilot Review URL | URL where users leave reviews | Required |
| Enable Dashboard Widget | Show widget on user dashboard | `true` |
| Popup Title | Title displayed on the review popup | "Enjoying our service?" |
| Popup Message | Message displayed on the review popup | Customizable |

### Getting Your Trustpilot Review URL

1. Go to [Trustpilot Business](https://business.trustpilot.com/)
2. Log in to your business account
3. Navigate to "Get Reviews" section
4. Copy your review invitation link
5. Paste it in the plugin settings

Example URLs:
- `https://www.trustpilot.com/evaluate/yourdomain.com`
- `https://www.trustpilot.com/evaluate/your-business-name`

## How It Works

1. Plugin monitors servers approaching expiration
2. When a server is within X days of expiring, shows the review widget on dashboard
3. User can choose to review or dismiss
4. Dismissal is persisted per user+server pair
5. Admin can configure settings and view statistics

## Plugin Structure

```
trustpilot-review/
├── plugin.json                    # Plugin manifest
├── Bootstrap.php                  # Plugin initialization
├── Migrations/
│   └── Version20240101000001.php # Database migrations
├── src/
│   ├── Entity/
│   │   ├── Dismissal.php         # Dismissal entity
│   │   └── Repository/
│   │       └── DismissalRepository.php
│   ├── EventSubscriber/
│   │   └── ServerEventSubscriber.php
│   ├── Service/
│   │   └── TrustpilotService.php # Business logic
│   └── Widget/
│       └── TrustpilotWidget.php  # Dashboard widget
├── Resources/
│   └── config/
│       └── services.yaml         # Service definitions
├── templates/
│   └── widgets/
│       └── trustpilot.html.twig  # Widget template
├── assets/
│   ├── css/trustpilot.css        # Plugin styles
│   └── js/trustpilot.js          # Plugin JavaScript
└── translations/
    └── plugin_trustpilot_review.en.yaml
```

## Capabilities

This plugin uses the following PteroCA capabilities:
- **entities**: Custom database entities for tracking dismissals
- **migrations**: Doctrine migrations for database setup
- **ui**: Dashboard widget for displaying review prompts
- **eda**: Event-driven architecture for server events

## Development

### Building from Source

```bash
# Clone the repository
git clone https://github.com/ZIPender/pteroCATrustPilotPlugin.git
cd pteroCATrustPilotPlugin

# No build step required - PHP plugins work out of the box
```

### Testing the Plugin

1. Install the plugin in a PteroCA development environment
2. Create a test server with an expiration date within 7 days
3. Visit the dashboard and verify the widget appears
4. Test the dismiss functionality
5. Verify dismissal persists on page reload

## Troubleshooting

### Plugin Not Showing in Admin

- Ensure plugin is placed in the correct `plugins/` directory
- Run `php bin/console plugin:scan` to discover new plugins
- Check PteroCA logs for any plugin loading errors

### Widget Not Appearing

1. Verify plugin is enabled in Settings → Plugins
2. Check that "Enable Dashboard Widget" setting is true
3. Ensure you have servers expiring within the configured threshold
4. Clear PteroCA cache: `php bin/console cache:clear`

### Database Errors

If migrations fail, check:
- Database connection is working
- MySQL/MariaDB version compatibility
- Run `php bin/console doctrine:migrations:status` for details

## License

MIT License - See [LICENSE](LICENSE) file for details

## Support

- GitHub Issues: https://github.com/ZIPender/pteroCATrustPilotPlugin/issues
- PteroCA Documentation: https://docs.pteroca.com

## Credits

- [PteroCA Team](https://pteroca.com) - For the amazing plugin system
- [Trustpilot](https://trustpilot.com) - For review platform integration

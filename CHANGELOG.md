# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-21

### Added
- Initial release of PteroCA Trustpilot Review Plugin
- Automatic popup trigger when server is expiring within X days
- Admin settings panel for configuration
- Database tables for settings and dismissals
- User dismissal persistence (per user+server)
- Beautiful, responsive UI with animations
- Admin statistics dashboard
- API endpoints for checking and dismissing popups
- Environment-based configuration
- Full documentation (README, INSTALL guide)

### Features
- **Smart Popup System**
  - Shows only when server expires within configured days
  - Respects user dismissals (never shows again for dismissed servers)
  - Clean, non-intrusive design
  
- **Admin Configuration**
  - Set days before expiry threshold (1-365 days)
  - Configure Trustpilot review URL
  - Enable/disable plugin globally
  - Optional API key support
  - View dismissal statistics

- **Developer-Friendly**
  - Clean, documented code
  - Laravel best practices
  - React components for frontend
  - RESTful API design
  - Easy integration

### Security
- All endpoints require authentication
- Admin endpoints require admin privileges
- Input validation on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection in templates

### Performance
- Efficient database queries with proper indexing
- Lightweight frontend (< 10KB gzipped)
- Lazy loading of components
- Minimal server impact

## [Unreleased]

### Planned Features
- Multi-language support
- Email notification integration
- A/B testing for popup messages
- Advanced analytics dashboard
- Custom popup templates
- Scheduling options

---

## Version History

### Versioning Scheme
- **Major version**: Breaking changes
- **Minor version**: New features, backward compatible
- **Patch version**: Bug fixes, backward compatible

### Release Notes Format
Each release includes:
- **Added**: New features
- **Changed**: Changes to existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security improvements

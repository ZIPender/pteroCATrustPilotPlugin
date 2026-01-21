# Plugin Architecture

## Overview
The Trustpilot Review Plugin follows a modern Laravel-based architecture with a React frontend.

## Directory Structure

```
pteroCATrustPilotPlugin/
├── app/
│   ├── Controllers/         # HTTP controllers
│   ├── Models/             # Database models
│   └── Services/           # Business logic
├── config/                 # Configuration files
├── database/
│   └── migrations/         # Database migrations
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/                # React components
│   └── views/             # Blade templates
├── routes/
│   ├── api.php            # API routes
│   └── web.php            # Web routes
└── TrustpilotPlugin.php   # Main plugin class
```

## Backend Architecture

### Service Layer
- **TrustpilotService**: Core business logic
  - `shouldShowPopup()`: Determines if popup should display
  - `dismissPopup()`: Records user dismissal
  - `getSettings()`: Retrieves current settings
  - `updateSettings()`: Updates plugin settings

### Controllers
- **TrustpilotController**: User-facing endpoints
  - Check popup status
  - Dismiss popup
- **AdminController**: Admin endpoints
  - Manage settings
  - View statistics

### Models
- **TrustpilotDismissal**: User dismissal tracking
- **TrustpilotSetting**: Plugin configuration

### Migrations
- `create_trustpilot_dismissals_table`: User+server dismissal tracking
- `create_trustpilot_settings_table`: Plugin configuration storage

## Frontend Architecture

### Components
- **TrustpilotPopup**: User-facing review popup
  - Displays when server expires soon
  - Allows review or dismissal
- **AdminSettings**: Admin configuration panel
  - Manage plugin settings
  - View statistics

### Styling
- Responsive CSS with animations
- Mobile-first design
- Accessible UI elements

## Data Flow

### Popup Display Flow
1. User views server page
2. Frontend calls `/api/trustpilot/check/{serverId}`
3. Backend checks:
   - Plugin enabled?
   - User dismissed?
   - Server expiring soon?
4. Returns show/hide decision with data
5. Frontend renders popup if needed

### Dismissal Flow
1. User clicks "No thanks"
2. Frontend calls `/api/trustpilot/dismiss/{serverId}`
3. Backend records dismissal in database
4. Frontend hides popup
5. Popup never shows again for this user+server

### Settings Management Flow
1. Admin accesses `/admin/trustpilot`
2. Frontend loads current settings
3. Admin modifies settings
4. Frontend posts to `/api/admin/trustpilot/settings`
5. Backend validates and saves to database
6. Changes take effect immediately

## Security

### Authentication
- All endpoints require authentication
- Admin endpoints require admin privileges
- Laravel middleware handles authorization

### Input Validation
- Server-side validation on all inputs
- Type checking and sanitization
- SQL injection prevention via ORM

### Data Protection
- No sensitive data stored
- Dismissals scoped to user+server
- Settings stored securely in database

## Performance

### Database
- Indexed columns for fast lookups
- Unique constraint on user+server dismissals
- Efficient queries with proper joins

### Frontend
- Lazy loading of components
- Conditional rendering
- Minimal bundle size

### Caching
- Settings cached after first load
- Can be extended with Redis/Memcached

## Extensibility

### Adding Features
1. Add methods to TrustpilotService
2. Create new controller methods
3. Define routes in api.php/web.php
4. Create React components as needed

### Customization Points
- Popup message text
- Styling via CSS
- Settings validation rules
- Display conditions

## Integration Points

### Panel Integration
- Service provider auto-registration
- Route registration via Laravel
- Asset compilation via panel build process

### External Services
- Trustpilot review URL
- Optional API integration support
- Extensible for webhooks/callbacks

## Testing Strategy

### Unit Tests
- Service layer logic
- Model relationships
- Validation rules

### Integration Tests
- API endpoints
- Database operations
- Authentication flow

### Frontend Tests
- Component rendering
- User interactions
- API mocking

## Deployment

### Installation
1. Copy plugin to plugins directory
2. Register service provider
3. Run migrations
4. Publish assets
5. Configure environment

### Updates
1. Pull latest changes
2. Run migrations (if any)
3. Republish assets
4. Clear cache

## Maintenance

### Monitoring
- Track dismissal rates
- Monitor API response times
- Log errors for debugging

### Updates
- Regular security updates
- Feature enhancements
- Bug fixes

## Future Enhancements

### Planned Features
- Multi-language support
- Email notifications
- A/B testing
- Advanced analytics
- Custom templates
- Scheduling options

### Potential Integrations
- Trustpilot API for verified reviews
- Email service providers
- Analytics platforms
- CRM systems

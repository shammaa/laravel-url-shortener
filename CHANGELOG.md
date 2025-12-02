# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-XX

### Added
- Initial release of Laravel URL Shortener package
- Password protection for links
- QR code generation for all links
- Advanced analytics tracking (geographic, device, browser, platform)
- Hidden UTM parameter tracking
- Link expiry functionality
- Click limits per link
- Custom key support
- RESTful API endpoints
- Beautiful password protection UI
- Real-time visit tracking
- Geographic location tracking
- Device and browser tracking
- Referrer tracking
- Session-based password verification
- Cache support for improved performance
- Support for custom domains
- Link grouping and tagging
- Polymorphic user relationships
- Soft deletes support
- Comprehensive migrations
- **HasShortLink trait** - Automatically create short links for any Eloquent model
- Automatic key generation with model prefix + random code format (e.g., `post-xyz1`)
- Custom prefix field support for flexible key naming
- Automatic URL detection from model routes
- Polymorphic relationship support for linking models to short links

### Features
- High-performance link generation with caching
- Smart key generation with collision detection
- Automatic QR code generation
- Advanced analytics with aggregated data
- Hidden UTM tracking for marketing campaigns
- Flexible link configuration options
- Full API support for integration
- Professional UI for password protection

### Security
- Password hashing with bcrypt
- Session-based password verification
- Secure redirect handling

### Performance
- Smart caching for link lookups
- Optimized database queries
- Efficient visit tracking
- Minimal overhead

### Configuration
- Comprehensive configuration file
- Environment variable support
- Flexible tracking options
- Customizable settings


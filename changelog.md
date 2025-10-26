# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Removed
- Removed willdurand/negotiation dependency as it was barely used and had breaking API changes
- Removed PHP 8.1 from GitHub Actions workflow (no longer supported)

### Changed
- Implemented internal Accept header parsing for content negotiation
- Added composer minimum-stability and prefer-stable configuration
- Updated minimum PHP version requirement from ^8.1 to ^8.2

### Added
- Added comprehensive unit tests for getBestMediaType method in ApiVersion middleware
- Added comprehensive unit tests for getBestMediaType method in Hateoas Util trait
- Added doctrine/annotations ^2.0 as explicit dependency for PHP 8.2 compatibility

### Fixed
- Fixed missing doctrine/annotations dependency that was previously pulled transitively

## [0.1.0] - 2015-09-22
First release.

# Changelog

All Notable changes to `IcoFileLoader` will be documented here. 
Releases follow [Semantic Versioning](http://semver.org/) princples,
and this changelog follows [Keep a CHANGELOG](http://keepachangelog.com/) 
principles.

## 1.0.0-alpha - 2017-01-22

### Added
- support for icons containing PNG files

### Change
- performance improvements
- complete refactoring into smaller, separate classes

### Fixed
- fixed issue where requested background colour was made transparent 
  even in opaque pixels which used the same colour


## 0.0.2 - 2017-01-20

### Change
- Refactored original classes to follow PSR-2 and test coverage to enable more significant refactoring to occur.


### Added
- Added tests for 32, 24, 8, 4 and 1 bit ico loading. This will allow more significant refactoring to occur.

### Fixed
- 4/8 bit icons load palette correctly


## 0.0.1 - 2017-01-20

### Added
- Original 2005 sources from Diogo Resende
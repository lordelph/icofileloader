# Changelog

All Notable changes to `IcoFileLoader` will be documented here. 
Releases follow [Semantic Versioning](http://semver.org/) princples,
and this changelog follows [Keep a CHANGELOG](http://keepachangelog.com/) 
principles.

## 2.0.1 - 2017-06-01

### Fixed

- PR #12 fixes issues with loading icons from URLs  

## 2.0.0 - 2017-05-14

### Change

- Identical to v1.0.2 but bumping version to v2.0.0 - v1.1.0 now adds additional code
  for supporting php5.4, which is obsolete. 

## 1.1.0 - 2017-05-14

### Added
- PR #11 adds backwards compatibility to php 5.4
- As php5.4 is past its end-of-life, new developments will occur in v2.0.0 onwards

## 1.0.2 - 2017-05-11

### Fixed
- PR #10 ensures exception thrown when empty .ico file is loaded

## 1.0.1 - 2017-01-24

### Fixed
- issue #8 icons with zero bit depth in header failed to load
- issue #9 loading png files directly as if they were .ico files


## 1.0.0 - 2017-01-23

First stable release

### Added
- iterator support to Icon class

### Change
- folder structure simplified to take advantage of PSR-4
- documentation polished


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

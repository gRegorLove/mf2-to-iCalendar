# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Changed
* Add property type declarations
* Use local $url variable in loop

### Added
* Option for direct HTML input
* Experimental support for status property [#3](https://github.com/gRegorLove/mf2-to-iCalendar/issues/3)

### Fixed
* TypeError if microformat has no `published` property [#4](https://github.com/gRegorLove/mf2-to-iCalendar/issues/4)

## [0.0.4] - 2024-02-29
### Changed
* Update dependencies
* Add type declarations and strict typing
* Fix errors

## [0.0.3] - 2020-12-23
### Changed
* No longer throws an Exception if no h-event microformats found when converting. Instead will generate an "empty" iCalendar.
* Changed default domain to example.com

## [0.0.2] - 2018-03-29
### Changed
* Now prefers `content` h-event property over `description`
* Adds support for dates with local time
* Adds unit tests

## [0.0.1] - 2017-07-27
* initial release

[Unreleased]: https://github.com/gRegorLove/mf2-to-iCalendar/compare/v0.0.4...HEAD
[0.0.4]: https://github.com/gRegorLove/mf2-to-iCalendar/compare/v0.0.3...v0.0.4
[0.0.3]: https://github.com/gRegorLove/mf2-to-iCalendar/compare/v0.0.2...v0.0.3
[0.0.2]: https://github.com/gRegorLove/mf2-to-iCalendar/compare/v0.0.1...v0.0.2
[0.0.1]: https://github.com/gRegorLove/mf2-to-iCalendar/tree/v0.0.1

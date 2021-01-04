# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [3.0.1] - 2020-01-04

## [3.0.0] - 2020-11-29

I am dropping support for PHP 7.2.

### Changed

- Changed options `fields` to `data`

### Added

- Added testing for PHP 8.0

### Removed

- Removed testing for PHP 7.2

## [2.0.1] - 2020-08-01

### Fixed

- Fixed missing docblock for `httpErrors` argument

## [2.0.0] - 2020-06-05

### Added

- Added `httpErrors` option to constructor
- Added `HttpClientException`
- Added `RequestException` which extends `HttpClientException`
- Added `ConnectionException` which extends `RequestException`
- Added `TooManyRedirectsException` which extends `RequestException`
- Added `HttpException` which extends `RequestException`
- Added `ClientErrorException` which extends `HttpException`
- Added `ServerErrorException` which extends `HttpException`

### Changed

- Changed HTTP protocol errors trigger either Client (4xx) or Server (5xx) exception by default. This is a breaking change, 
and can be disabled by setting the `httpErrors` when creating the `Http` object.

## [1.0.3] - 2020-05-24

### Fixed

- Fixed strict error when using `CURLOPT_FILE` which returned a bool

## [1.0.2] - 2020-05-21

### Fixed
- Fixed bug with extra spacing

## [1.0.1] - 2020-01-15

Updated license dates, moved strict declaration and formatted source code in the README.md.

## [1.0.0] - 2019-10-12

This component has been decoupled from the [OriginPHP framework](https://www.originphp.com/).

# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

_..._

## 1.8.0 - 2016-04-28

- Add optional support for `LoggerInterface` to log exceptions caught by `ExceptionHandler`
- Add `MonologConfiguration`

## 1.7.0 - 2016-04-13

- Add `PlatesConfiguration`

## 1.6.0 - 2016-04-11

- Bump nikic/fast-route dependency version to 0.8

## 1.5.1 - 2016-03-11

- Bump equip/adr dependency version to 1.3

## 1.5.0 - 2016-03-01

- Add Redis configuration

## 1.4.0 - 2016-03-01

- Add support for `getHttpStatus` for any exception caught by `ExceptionHandler`
- Switch to Relay.Middleware content handling

## 1.3.0 - 2016-02-13

- Upgrade Whoops exception handler to v2
- Switch from `destrukt/destrukt` to `equip/structure`

## 1.2.0 - 2016-02-11

- `AbstractFormatter` and all its subclasses can now use the new `PayloadInterface` status codes added to equip/adr v1.1.0

## 1.1.0 - 2016-01-08

- Ensure that sorting on `FormattedResponder` formatters is preserved by using `SortedDictionary`

## 1.0.0 - 2016-01-05

- Changed from `Spark` to `Equip` namespace

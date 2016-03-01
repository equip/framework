# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

_..._

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

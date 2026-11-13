# Security Policy

## Supported versions

Security fixes are applied to the latest released version. Please make sure you
are running an up-to-date release before reporting an issue.

## Reporting a vulnerability

Please **do not report security vulnerabilities through public issues, pull
requests, or discussions.**

Instead, report them privately to the maintainers. Include:

- a description of the vulnerability and its impact,
- the affected version(s),
- steps to reproduce, and
- any suggested mitigation or fix.

You will receive an acknowledgement as soon as possible. The maintainers will
investigate the report and keep you informed about the progress of a fix.

## Disclosure policy

Please do not publicly disclose the vulnerability before a fix has been released
and the maintainers have agreed on a disclosure date. Once a fix is available,
the issue will be documented in the release notes and, where appropriate, in
`HISTORY.md`.

## Handling of API keys

This library is a client and never stores, logs, or persists real API keys at
rest. The bearer token is passed in by the consumer when constructing the client
and is only attached to outgoing HTTP requests. Never commit API keys to the
repository; supply them through your application's own configuration or secret
management.

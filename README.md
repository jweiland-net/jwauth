# TYPO3 Extension `jwauth`

[![CI - TYPO3 12](https://github.com/jweiland-net/jwauth/actions/workflows/typo3_12.yml/badge.svg)](https://github.com/jweiland-net/jwauth/actions/workflows/typo3_12.yml)
[![CI - TYPO3 11](https://github.com/jweiland-net/jwauth/actions/workflows/typo3_11.yml/badge.svg)](https://github.com/jweiland-net/jwauth/actions/workflows/typo3_11.yml)

TYPO3 extension providing a Frontend authentication service that logs in a
`fe_users` record automatically if the visitor's client IP address matches
the IP address configured on that record.

You will find the full documentation in the `Documentation` folder in reST
format, or rendered at [docs.typo3.org](https://docs.typo3.org/p/jweiland/jwauth/main/en-us/).

## 1 Features

* Authenticate to the TYPO3 Frontend based on the visitor's static IP address
  (IPv4 and IPv6 are supported).
* Also matches partial IP addresses (e.g. only the first octets/segments),
  so a whole IP range can be granted access with a single `fe_users` record.
* No password or login form required for these users.

## 2 How it works

* A new TCA input field `ip_address` is added to `fe_users` (see
  `Configuration/TCA/Overrides/fe_users.php`). It is appended to every
  `fe_users` TCA type and shown on the last tab of the record, there is no
  dedicated tab for it.
* `Classes/Service/IpAuthService.php` registers itself as a TYPO3
  authentication service of subtype `getUserFE,authUserFE` (see
  `ext_localconf.php`). It runs with `priority = 70` / `quality = 70`, which is
  higher than `felogin`/`rsaauth` (50/60) but lower than OpenID (75). If the
  IP address does not match any `fe_users` record, authentication simply
  falls through to the next configured service (e.g. felogin).
* Matching is done in two steps:
  1. `getBestMatchingUser()` looks for a `fe_users` record with an **exact**
     match on `ip_address`.
  2. If none is found, `getBestPartlyMatchingUser()` progressively strips the
     last IPv4 octet (`.`) or IPv6 segment (`:`) from the visitor's address
     and does a `LIKE` lookup, validating each candidate with
     `GeneralUtility::cmpIP()` so that partial `fe_users.ip_address` entries
     (e.g. `192.168.` ) also match.
* `authUser()` re-checks the IP address with `GeneralUtility::cmpIP()` and
  returns status `200` (directly authenticated, no further checks) on match.
* `ext_localconf.php` also forces
  `$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;`
  so the authentication chain — and therefore the IP check — runs on
  **every** request instead of relying on the PHP session.
* `Classes/FeUser.php` hooks into `hook_eofe` (end-of-frontend-user hook) and
  removes the Frontend user session (`fe_sessions`) again after each request,
  but only if the previously authenticated user's `ip_address` still matches
  `$_SERVER['REMOTE_ADDR']`. This is a deliberate security measure: an
  administrator must always be able to revoke this kind of access simply by
  deactivating the extension or clearing the IP field — the visitor must not
  stay logged in via a lingering session.

## 3 Security notes

* Because the session is cleared after every request, IP-authenticated users
  are effectively re-authenticated on every single page call. There is no
  persistent login for them — deactivating `jwauth` or removing the IP
  address immediately revokes access.
* As a consequence there is no real "Logout" for these users: clicking
  logout just reloads the page, and the next request logs them in again via
  their IP address.
* Re-running the full authentication chain on every request can add a small
  amount of overhead per request.

## 4 Usage

### 4.1 Installation

#### Installation using Composer

The recommended way to install the extension is using Composer.

Run the following command within your Composer based TYPO3 project:

```
composer require jweiland/jwauth
vendor/bin/typo3 extension:setup --extension=jwauth
```

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install `jwauth` with the extension manager module.

### 4.2 Minimal setup

1) Install and activate `jwauth`
2) Add your static IP address to the `IP address` field of the desired
   `fe_users` record (last tab of the record). Partial IPv4/IPv6 addresses
   are allowed; for IPv6 you must write out the full address (no `::`
   shorthand), padding missing segments with `0`, e.g.
   `4324:0000:0000:0000:A5B3`.

## 5 Requirements

* TYPO3 `^11.5.23 || ^12.4` (see `composer.json` / `ext_emconf.php`). This
  checkout of `jwauth` has **not** been upgraded to TYPO3 13 yet — unlike
  most other extensions in this repository, its `composer.json` still
  targets 11/12 and needs the usual deprecation/API pass before it can run
  on TYPO3 13.4.

## 6 Support

Free Support is available via [GitHub Issue Tracker](https://github.com/jweiland-net/jwauth/issues).

For commercial support, please contact us at [support@jweiland.net](mailto:support@jweiland.net).

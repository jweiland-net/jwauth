# TYPO3 Extension `jwauth`

[![CI](https://github.com/jweiland-net/jwauth/actions/workflows/ci.yml/badge.svg)](https://github.com/jweiland-net/jwauth/actions/workflows/ci.yml)

TYPO3 extension providing a Frontend authentication service that logs in a
`fe_users` record automatically if the visitor's client IP address matches
one of the IP addresses configured on that record.

You will find the full documentation in the `Documentation` folder in reST
format, or rendered at [docs.typo3.org](https://docs.typo3.org/p/jweiland/jwauth/main/en-us/).

## 1 Features

* Authenticate to the TYPO3 Frontend based on the visitor's static IP address
  (IPv4 and IPv6 are supported).
* A `fe_users` record can have any number of IP addresses/patterns — the
  visitor is logged in if **any one** of them matches (OR logic).
* Also matches partial IP addresses (e.g. only the first octets/segments) or
  CIDR-masked IPv6 ranges, so a whole IP range can be granted access with a
  single entry.
* No password or login form required for these users.

## 2 How it works

* A new table `tx_jwauth_domain_model_ipaddress` holds one IP address/pattern
  per record (see `Configuration/TCA/tx_jwauth_domain_model_ipaddress.php`,
  field `ip_address`, `max => 43` to fit a full IPv6 address). `fe_users` is
  linked to any number of these via a `select`/`selectMultipleSideBySide`
  field `ip_addresses` and an MM table `tx_jwauth_fe_users_ipaddress_mm` (see
  `Configuration/TCA/Overrides/fe_users.php`). There is no `ext_tables.sql` —
  TYPO3 derives both the new table's schema and the MM table's schema
  automatically from the TCA configuration.
* `Classes/Service/IpAuthService.php` registers itself as a TYPO3
  authentication service of subtype `getUserFE,authUserFE` (see
  `ext_localconf.php`). It runs with `priority = 70` / `quality = 70`, which is
  higher than `felogin`/`rsaauth` (50/60) but lower than OpenID (75). If none
  of a visitor's IP addresses match any `fe_users` record, authentication
  simply falls through to the next configured service (e.g. felogin).
* `IpAuthService::getUser()` joins `fe_users` with the MM table and the new
  IP-address table in a single query, then checks each candidate address with
  `GeneralUtility::cmpIP()` — the one function that understands the `*`
  wildcard and `/nn` CIDR mask syntax the field supports, which plain SQL
  can't express. `authUser()` and the middleware below both delegate the same
  check to the shared `Classes/Service/IpAddressMatcher.php`.
* `ext_localconf.php` also forces
  `$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;`
  so the authentication chain — and therefore the IP check — runs on
  **every** request instead of relying on the PHP session.
* `Classes/Middleware/ClearIpAuthenticatedSessionMiddleware.php` (registered
  in `Configuration/RequestMiddlewares.php`, running right after TYPO3's own
  `typo3/cms-frontend/authentication` middleware) logs the Frontend user off
  again — via the core `FrontendUserAuthentication::logoff()` API — right
  after the response has been built, but only if `IpAddressMatcher` confirms
  one of the fe_user's IP addresses still matches the visitor's remote
  address. This replaces the `hook_eofe` hook that older TYPO3 versions
  offered for this purpose and that no longer exists on TYPO3 13; middlewares
  are the current API for running code around the whole Frontend
  request/response cycle. This is a deliberate security measure: an
  administrator must always be able to revoke this kind of access simply by
  deactivating the extension or removing the matching IP addresses — the
  visitor must not stay logged in via a lingering session.
* Upgrading from a version before 5.0.0 (single `ip_address` column)? Run the
  upgrade wizard `jwauth_migrateIpAddress` once (Install Tool → Upgrade
  Wizards, or `vendor/bin/typo3 upgrade:run jwauth_migrateIpAddress`) to
  migrate existing values into the new relation.

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

* TYPO3 `^13.4` (see `composer.json` / `ext_emconf.php`).

## 6 Support

Free Support is available via [GitHub Issue Tracker](https://github.com/jweiland-net/jwauth/issues).

For commercial support, please contact us at [support@jweiland.net](mailto:support@jweiland.net).

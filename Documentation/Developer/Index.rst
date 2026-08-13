:navigation-title: Developer corner

..  include:: /Includes.rst.txt


..  _developer:

================
Developer corner
================

..  _developer-structure:

Structure
=========

`Classes/Service/IpAuthService.php` is registered as a TYPO3 authentication
service (subtype `getUserFE,authUserFE`) in :file:`ext_localconf.php`, with a
priority and quality of `70` each. These values are higher than the services
of `felogin` and `rsaauth` (`50`/`60`), but lower than OpenID (`75`). So, if
none of the visitor's IP addresses match, the other configured services still
get a chance to authenticate the visitor.

Example: Visitor A is logged into the frontend automatically if their IP
address matches one of the addresses configured on a `fe_users` record. When
visitor A is online from home, none of the addresses will match, but visitor A
can still log in via `felogin` or a similar authentication method.

`ext_localconf.php` also forces
:php:`$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;`,
so the whole authentication chain - and therefore the IP check - runs on
every single request instead of relying on the PHP session.

Each `fe_users` record can be linked to any number of
`tx_jwauth_domain_model_ipaddress` records through the MM table
`tx_jwauth_fe_users_ipaddress_mm` (field `ip_addresses`). The actual matching
against `GeneralUtility::cmpIP()` - which alone understands the `*` wildcard
and `/nn` CIDR mask syntax - is done by the shared
`Classes/Service/IpAddressMatcher.php`, used both by
`IpAuthService::authUser()` and by the middleware described below.

Because matching is now evaluated per address instead of via the exact/prefix
SQL search jwauth used before this feature, an edge case changed
deliberately: if two *different* `fe_users` records could both be logged in
by the same remote address (one via an exact address, another via a wildcard
or CIDR pattern), the record with the lowest `uid` now wins. Earlier versions
always preferred an exact match over a wildcard/CIDR match, but this was never
a documented guarantee - just a side effect of the old two-step search
algorithm - and is not preserved.

..  _developer-security:

Security
========

If a visitor is logged in via `jwauth`, their frontend user session is
terminated again right after the response has been built, using the PSR-15
middleware `Classes/Middleware/ClearIpAuthenticatedSessionMiddleware.php`
(registered in :file:`Configuration/RequestMiddlewares.php`). It asks the same
`IpAddressMatcher` whether any of the fe_user's configured IP addresses still
matches the current remote address, and logs the user off if not one of them
does. So with every request the visitor is logged in again and again.

This is done for security reasons: without it, an administrator could
deactivate `jwauth` in the extension manager, but visitors who are already
logged in could still browse the website using their existing session. In our
opinion, an administrator must always have the opportunity to revoke such a
feature immediately - deactivating the extension or removing all matching IP
addresses from a `fe_users` record takes effect on the very next request.

It could be that browsing with `jwauth` activated slows down your website by
a few milliseconds, since matching the visitor's IP addresses means the full
user authentication - including one extra database lookup by the middleware -
has to be processed on every request.

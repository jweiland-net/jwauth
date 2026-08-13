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
the IP address does not match, the other configured services still get a
chance to authenticate the visitor.

Example: Visitor A is logged into the frontend automatically if their IP
address matches a `fe_users` record with the same IP address. When visitor A
is online from home, the IP address will not match, but visitor A can still
log in via `felogin` or a similar authentication method.

`ext_localconf.php` also forces
:php:`$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;`,
so the whole authentication chain - and therefore the IP check - runs on
every single request instead of relying on the PHP session.

..  _developer-security:

Security
========

If a visitor is logged in via `jwauth`, their frontend user session is
terminated again right after the response has been built, using the PSR-15
middleware `Classes/Middleware/ClearIpAuthenticatedSessionMiddleware.php`
(registered in :file:`Configuration/RequestMiddlewares.php`). So with every
request the visitor is logged in again and again.

This is done for security reasons: without it, an administrator could
deactivate `jwauth` in the extension manager, but visitors who are already
logged in could still browse the website using their existing session. In our
opinion, an administrator must always have the opportunity to revoke such a
feature immediately - deactivating the extension or removing the IP address
from a `fe_users` record takes effect on the very next request.

It could be that browsing with `jwauth` activated slows down your website by
a few milliseconds, since matching the visitor's IP address means the full
user authentication has to be processed on every request.

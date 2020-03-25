.. include:: ../Includes.txt


.. _developer:

Developer Corner
================

Target group: **Developers**

.. _structure:

Structure
---------

All classes are based on namespaces. So you can't use this extension on TYPO3 Versions below 6.0.

We register this service with a priority of 70 and a quality of 70. With these values we are higher than the
services of felogin and saltedpasswords. So, if IP does not match, we give the other services a try
to login the user.

Example: User A will login to frontend automatically, if his IP address matches a fe_user record with same IP address.
When User A is online at home the IP address will not match, but User A has still the possibility to login
via felogin or similar authentication methods.

Security
--------

If a user logs in via jwauth, his user session will be deleted after EACH request! So with each request, the user
will be logged in again and again. This is for security reasons. Without that part you as an administrator can
deactivate jwauth in extension manager, but these users can still browse to your website. In our opinion an
administrator must always have the opportunity to deactivate such a feature directly.

It could be that browsing with activated jwauth can slow down your website some milliseconds. That's because users
theirs IP address matches have to process the complete user authentication with each request.

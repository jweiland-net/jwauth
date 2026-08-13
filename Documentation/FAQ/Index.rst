:navigation-title: FAQ

..  include:: /Includes.rst.txt


..  _faq:

===
FAQ
===

..  _faq-ipv6:

Is this extension IPv6 compatible?
==================================

Yes, it is. It uses the :php:`GeneralUtility::cmpIP()` method, which can
validate both IPv4 and IPv6 addresses.

..  _faq-logout:

What about "Logout"?
====================

Ah yeah, that is funny. Visitors with a matching IP address are authenticated
on every single request. You cannot log out: if you press "Logout" the page
reloads, a new request is made, and you are logged in again right away.

..  _faq-multiple-ip-addresses:

Can I configure more than one IP address per fe_user?
=====================================================

Yes. Add as many entries as you like to the `IP addresses` field of a
`fe_users` record. The visitor is logged in automatically if their remote
address matches **any one** of them.

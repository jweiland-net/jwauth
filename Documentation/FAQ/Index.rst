.. include:: ../Includes.rst.txt


.. _faq:

===
FAQ
===

Is this extension IPv6 compatible?
==================================

Yes it is. It uses the comIP() method of GeneralUtility which can validate
IPv4 and IPv6 addresses.

What about "Logout"?
====================

Ah yeah. That's funny? Users with matched IP addresses are authenticated with
EACH request. You can't logout. So, if you press "Logout" the page reloads, we
have a new request and you're logged in again.

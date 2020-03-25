.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================

Target group: **Administrators**

.. _admin-installation:

Installation
------------

To install the extension, perform the following steps:

#. Go to the Extension Manager
#. Install the extension

.. _admin-configuration:

Configuration
-------------

There is a new textfield called "IP Address" in FE-User records available. We haven't created a special tab for this
field, so you should find it on tab "Extended". If it is not there you may have installed some additional extensions
which brings its own tabs. Please have a look there, too.

.. _admin-faq:

FAQ
---

Is this extension IPv6 compatible
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Yes it is. It uses the comIP() method of GeneralUtility to validate and compare IPv6 addresses.

What about "Logout"?
^^^^^^^^^^^^^^^^^^^^

Ah yeah. That's funny? Users with matched IP addresses are authenticated with EACH request. You can't logout. So,
if you press "Logout" the page reloads, we have a new request and you're logged in again.

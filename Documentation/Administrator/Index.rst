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

The extension jwauth adds a new textfield called "IP Address" in FE-User records. We haven't created a special tab
for this column, so you will find it on the last tab of fe_user record.

.. _admin-faq:

FAQ
---

Is this extension IPv6 compatible
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Yes it is. It uses the comIP() method of GeneralUtility which can validate IPv4 and IPv6 addresses.

What about "Logout"?
^^^^^^^^^^^^^^^^^^^^

Ah yeah. That's funny? Users with matched IP addresses are authenticated with EACH request. You can't logout. So,
if you press "Logout" the page reloads, we have a new request and you're logged in again.

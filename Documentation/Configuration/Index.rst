.. include:: ../Includes.rst.txt


.. _configuration:

=============
Configuration
=============

The extension `jwauth` adds a new textfield called "IP Address" in FE-User
records. We haven't created a special tab for this column, so you will find it
on the last tab of fe_user record.

We prefer to enter the full IP address, but, if needed, you also can enter just
parts of your IP address. `jwauth` also supports the use of IPv6 addresses.

..  note::

    If you're working with IPv6 address you have to enter the full IPv6
    address. No `4324:::A5B3` parts are allowed. Such parts you have to fill
    with `0`. Example: :code:`4324:0000:0000:0000:A5B3`.

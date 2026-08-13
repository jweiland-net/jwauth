:navigation-title: Configuration

..  include:: /Includes.rst.txt


..  _configuration:

=============
Configuration
=============

The extension `jwauth` adds a new text field called `IP address` to `fe_users`
records. No dedicated tab was created for this column, so you will find it on
the last tab of the `fe_users` record.

We recommend entering the full IP address, but, if needed, you can also enter
just parts of it. `jwauth` also supports IPv6 addresses.

..  note::

    If you are working with an IPv6 address, you have to enter the full IPv6
    address. Parts abbreviated with `::` are not allowed and must be filled
    with `0` instead, for example :code:`4324:0000:0000:0000:A5B3`.

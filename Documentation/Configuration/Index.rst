:navigation-title: Configuration

..  include:: /Includes.rst.txt


..  _configuration:

=============
Configuration
=============

The extension `jwauth` adds a new relation field called `IP addresses` to
`fe_users` records. No dedicated tab was created for this field, so you will
find it on the last tab of the `fe_users` record.

Each entry in this field is its own small record holding a single IP address
or pattern. Use the `+` control next to the field to create a new entry
without leaving the `fe_users` form, or pick an already existing one from the
list. A `fe_users` record can reference any number of these entries - the
visitor is logged in automatically as soon as their remote address matches
**any one** of them (OR logic).

We recommend entering the full IP address, but, if needed, you can also enter
just parts of it. `jwauth` also supports IPv6 addresses.

..  note::

    If you are working with an IPv6 address, you have to enter the full IPv6
    address. Parts abbreviated with `::` are not allowed and must be filled
    with `0` instead, for example :code:`4324:0000:0000:0000:A5B3`.

:navigation-title: Upgrade

..  include:: /Includes.rst.txt


..  _upgrade:

=======
Upgrade
=======

..  _upgrade-from-before-5-0-0:

Upgrading from a version before 5.0.0
=====================================

Versions before 5.0.0 stored a single IP address per `fe_users` record in the
column `ip_address`. jwauth 5.0.0 replaces this with the `IP addresses`
relation described in :ref:`Configuration <configuration>`. An upgrade
wizard, `Classes/Upgrade/MigrateIpAddressToMMUpgrade.php` (identifier
`jwauth_migrateIpAddress`), migrates every existing value into the new
relation automatically - run it once via the Install Tool's "Upgrade Wizards"
module, or on the command line:

..  code-block:: bash

    ddev exec vendor/bin/typo3 upgrade:run jwauth_migrateIpAddress

The wizard can safely be run more than once; it only migrates values that
have not been migrated yet.

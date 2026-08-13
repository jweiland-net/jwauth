:navigation-title: Introduction

..  include:: /Includes.rst.txt


..  _introduction:

============
Introduction
============

..  _what-it-does:

What does it do?
================

This extension adds a new authentication service that logs a visitor into the
TYPO3 frontend automatically if the IP address of their client matches the IP
address configured on a `fe_users` record.

Since jwauth re-checks the IP address on every single request instead of
relying on a persisting login session, deactivating the extension or removing
the IP address from a `fe_users` record immediately revokes access again.

..  _screenshot:

Screenshot
==========

Here you see the new TCA field `IP address` in the `fe_users` record.

..  image:: /Images/Introduction.png
    :alt: New IP address field in fe_users record
    :class: with-shadow
    :width: 400px

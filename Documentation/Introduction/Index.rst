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
TYPO3 frontend automatically if the IP address of their client matches one of
the IP addresses configured on a `fe_users` record. A `fe_users` record can
have any number of IP addresses - the visitor is logged in if ANY of them
matches (OR logic).

Since jwauth re-checks the IP addresses on every single request instead of
relying on a persisting login session, deactivating the extension or removing
all matching IP addresses from a `fe_users` record immediately revokes access
again.

..  _screenshot:

Screenshot
==========

Here you see the new `IP addresses` relation field in the `fe_users` record.
Each entry is its own small record holding one IP address or pattern.

..  figure:: /Images/Introduction.png
    :alt: New IP addresses field in fe_users record
    :class: with-shadow
    :zoom: lightbox

    The `IP addresses` field on the `fe_users` edit form

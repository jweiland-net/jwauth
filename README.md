# TYPO3 Extension `jwauth`

[![CI](https://github.com/jweiland-net/jwauth/actions/workflows/ci.yml/badge.svg)](https://github.com/jweiland-net/jwauth/actions/workflows/ci.yml)

TYPO3 extension with a service to authenticate via IP address match.

You will find the documentation in folder "Documentation" in rst format.

## 1 Features

* Authenticate to your TYPO3 system with your static IP address

## 2 Usage

### 2.1 Installation

#### Installation using Composer

The recommended way to install the extension is using Composer.

Run the following command within your Composer based TYPO3 project:

```
composer require jweiland/jwauth
```

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install `jwauth` with the extension manager module.

### 2.2 Minimal setup

1) Install and activate `jwauth`
2) Add your static IP address to the user record

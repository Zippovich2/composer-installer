# Composer Installer

Allow add variables in package urls using environment variables and `.env` files.

[![Build Status](https://travis-ci.org/Zippovich2/composer-installer.svg?branch=master)](https://travis-ci.org/Zippovich2/composer-installer)
[![Packagist](https://img.shields.io/packagist/v/zippovich2/composer-installer.svg)](https://packagist.org/packages/zippovich2/composer-installer)

## Installation

```
$ composer require zippovich2/composer-installer 
```

## Usage

Most common usage is ACF Pro plugin for WordPress:

```json
{
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "acf/acf-pro",
                "version": "5.8.7",
                "type": "wordpress-plugin",
                "dist": {
                    "type": "zip",
                    "url": "https://connect.advancedcustomfields.com/index.php?p=pro&a=download&k={%ACF_PRO_KEY%}&t=5.8.7"
                }
            }
        }

    ],
    "require": {
        "zippovich2/composer-installer": "^1.0"
    }
}
```

And just add `ACF_PRO_KEY` to any of `.env` files (`.env`, `.env.local`, `.env.%APP_ENV%`, `.env.%APP_ENV%.local`) 
or provide it as environment variable (note that `.env` variables overwrite environment variables):

```dotenv
# .env.local
ACF_PRO_KEY=your key
```

Now you can install package:

```
$ composer require acf/acf-pro
```

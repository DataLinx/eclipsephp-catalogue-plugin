# Eclipse Filament Catalogue plugin

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/eclipsephp/catalogue-plugin)
![Packagist Version](https://img.shields.io/packagist/v/eclipsephp/catalogue-plugin)
![Packagist Downloads](https://img.shields.io/packagist/dt/eclipsephp/catalogue-plugin)
[![Tests](https://github.com/DataLinx/eclipsephp-catalogue-plugin/actions/workflows/test-runner.yml/badge.svg)](https://github.com/DataLinx/eclipsephp-catalogue-plugin/actions/workflows/test-runner.yml)
[![codecov](https://codecov.io/gh/DataLinx/eclipsephp-catalogue-plugin/graph/badge.svg?token=1HKSY5O6IW)](https://codecov.io/gh/DataLinx/eclipsephp-catalogue-plugin)
[![Conventional Commits](https://img.shields.io/badge/Conventional%20Commits-1.0.0-%23FE5196?logo=conventionalcommits&logoColor=white)](https://conventionalcommits.org)
![Packagist License](https://img.shields.io/packagist/l/eclipsephp/catalogue-plugin)

## About
Filament plugin for product catalogue data.

🟧 Products (WIP)  
🟧 Categories (WIP)  
🟧 Price lists (WIP)    
🟧 Tax classes (WIP)  
🟧 Statuses (WIP)  
⏳ Types and properties (planned)  
⏳ Product groups (planned)  
⏳ Promo/landing pages (planned)  
⏳ Tariff codes (planned)  
⏳ Other resources coming when needed ([suggest a feature](https://github.com/DataLinx/eclipsephp-catalogue-plugin/discussions) and/or [hire us](https://www.datalinx.si))

## Requirements
- PHP >= 8.2 (due to Pest 3 requirement)
- Filament 3
- Filament Shield plugin (to manage permissions)

See [composer.json](composer.json) for details.

## Getting started
Download it with composer:
```shell
  composer require eclipsephp/catalogue-plugin
````

## Contributing

### Issues
If you have some suggestions how to make this package better, please open an issue or even better, submit a pull request.

Should you want to contribute, please see the development guidelines in the [DataLinx PHP package template](https://github.com/DataLinx/php-package-template).

### Development

1. All development is subject to our [PHP package development guidelines](https://github.com/DataLinx/php-package-template/blob/bc39ae340e7818614ae2aaa607e97088318dd754/docs/Documentation.md).
2. Our [Filament app development docs](https://github.com/DataLinx/eclipsephp-core/blob/cae7143c8f745f142bba2bb4cf1483cf09401509/docs/Documentation.md) will also be helpful.
3. Any PRs will generally need to adhere to these before being merged.

#### Requirements
* Linux, Mac or Windows with WSL
* [Lando](https://lando.dev/) (optional, but easier to start with)

#### Get started
1. Clone the git repo
2. Start the Lando container
    ```shell
    lando start
    ````
3. Install dependencies (this also runs the setup composer script)
    ```shell
    lando composer install
    ````
4. You can now develop and run tests. Happy coding 😉

💡 To manually test the plugin in the browser, see our [recommendation](https://github.com/DataLinx/eclipsephp-core/blob/main/docs/Documentation.md#-plugin-development), which is also [how Filament suggests package development](https://filamentphp.com/docs/3.x/support/contributing#developing-with-a-local-copy-of-filament).  
However, the plugin should be universal and not dependent on our app setup or core package.

### Changelog
All notable changes to this project are automatically documented in the [CHANGELOG.md](CHANGELOG.md) file using the release workflow, based on the [release-please](https://github.com/googleapis/release-please) GitHub action.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

For all this to work, commit messages must follow the [Conventional commits](https://www.conventionalcommits.org/) specification, which is also enforced by a Git hook.

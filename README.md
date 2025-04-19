[![Latest Stable Version](https://poser.pugx.org/axute/hetzner-cloud-php-sdk/version)](https://packagist.org/packages/lkdevelopment/hetzner-cloud-php-sdk)
[![License](https://poser.pugx.org/axute/hetzner-cloud-php-sdk/license)](https://packagist.org/packages/axute/hetzner-cloud-php-sdk)
[![Total Downloads](https://poser.pugx.org/axute/hetzner-cloud-php-sdk/downloads)](https://packagist.org/packages/axute/hetzner-cloud-php-sdk)
[![Actions Status](https://github.com/axute/hetzner-cloud-php-sdk/workflows/CI/badge.svg)](https://github.com/axute/hetzner-cloud-php-sdk/actions)
# Hetzner Cloud PHP (8.3+) SDK
A PHP (8.3+) SDK for the Hetzner Cloud API: https://docs.hetzner.cloud/
## Installation

You can install the package via composer:

```bash
composer require axute/hetzner-cloud-php-sdk
```

## Usage

``` php
$hetznerClient = new \LKDev\HetznerCloud\HetznerAPIClient($apiKey);
foreach ($hetznerClient->servers()->all() as $server) {
    echo 'ID: '.$server->id.' Name:'.$server->name.' Status: '.$server->status.PHP_EOL;
}
```
### PHP Support

It is tested on PHP Versions 8.3 and 8.4.

### Testing

You can just run `phpunit`. The whole library is based on unit tests and sample responses from the official Hetzner Cloud documentation.

### Changelog

Please see [CHANGELOG](https://github.com/LKDevelopment/hetzner-cloud-php-sdk/releases) for more information what has changed recently.

### Security

If you discover any security related issues, please use the issue tracker.

## Credits

- [Lukas Kämmerling](https://github.com/lkaemmerling)
- [All Contributors](https://github.com/LKDevelopment/hetzner-cloud-php-sdk/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

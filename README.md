# MoMo - Payment Platform

Library package to integrate MoMo E-Wallet as payment method
- Online Payment: Desktop, Mobile website
- Offline payment: POS, Static QR, Dynamic QR
- Mobile Payment: App to App, In MoMo Application  

### Prerequisites
- PHP >= 7.2
- Composer 
- phpseclib/phpseclib, at least version 2.0

```    
"require": {
    "php": ">=7.2",
    "phpseclib/phpseclib": "^2"
 },
```

For development purposes, we use phpunit/phpunit for testing, monolog/monolog for logging. 

Please check the composer.json file for detailed information on libraries for development as well as suggested packages

### Installing
Make sure you have the correct PHP version and Composer installed. 
Then, simply clone the repo to your chosen IDEA and set up Composer and have the required packages. 
 
## Documention

https://developers.momo.vn

## Usage 

### Setting Up MoMo Environment 
MoMo provides 2 environments for integration: development(```dev```) and production(```prod```). 
The model for environment is located at ```MService\Payment\Shared\SharedModels\Environment```

Example configuration is provided in ```.env.example``` and sample code on how to set up your environment can be found in ```SampleEnvironment.php``` file

By default, log is turned off. But you can use `MoMoLogger` by setting the `$loggingOff` property to `false` during environment setup. 

### Integration 
The library provides functions to conduct transactions through the All-In-One (AIO) Payment Gateway (```Mservice\Payment\PayGate```) and all other Payment (```Mservice\Payment\Pay```) options (App-In-App, POS, Dynamic QR Code)

For each payment options, you can choose to either use the provided code in ```Processors``` folder to immediately use MoMo services or extend from the models located in `Models` folder. To have a better sense of how the processors work, we recommend uncommented and run the code in ```PayGate.php``` and ```Pay.php``` 

For `Pay.php`, please ensure that the URI you are calling to is correct for the processes you are trying to run accoring to [MoMo's documentation](https://developers.momo.vn/#/). 

## Running The Tests
Install phpunit/phpunit library to the downloaded project:

From the terminal, with Composer: 
```
composer require-dev "phpunit/phpunit":"^8"
```
Or you can directly add to ```composer.json```, and then update and dump-autoload the composer:
```    
"require-dev": {
    "phpunit/phpunit": "^8"
 },
```

Run the tests with phpunit command


## Acknowledgments
### Security Aalgorithms
- [HMAC 256](https://en.wikipedia.org/wiki/HMAC)
- [RSA - Rivest–Shamir–Adleman](https://en.wikipedia.org/wiki/RSA_(cryptosystem))
- [AES - Advanced Encryption Standard](https://en.wikipedia.org/wiki/Advanced_Encryption_Standard)

### More
- [IPN - Instant Payment Notification](https://developer.paypal.com/docs/classic/products/instant-payment-notification/)

- [JSON - JavaScript Object Notation](https://www.json.org/)

## Languages
- PHP

## Versioning

```
Version 0.1
``` 

## Authors

* **Linh Nguyễn** - linh.nguyen7@mservice . com . vn

## License
(c) MoMo 

## Contact
itc.payment@mservice.com.vn

## Support
If you have any issues when integrate MoMo API, please find out in [`F.A.Q`](https://developers.momo.vn/#/docs/aio/?id=faq) or [`Exception handling`](https://developers.momo.vn/#/docs/error_code) section in our [documention](https://developers.momo.vn)


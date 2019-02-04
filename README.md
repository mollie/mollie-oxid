# mollie-oxid
Mollie Payments for OXID eSales

## Manual Installation
1. Create the folder "mollie" in the "source/modules" folder of the Oxid 6 installation
2. Create the folder "molliepayment" in the new "source/modules/mollie" folder of the Oxid 6 installation
3. Copy the content of this Git repository in this newly created "molliepayment" folder.
4. In the composer.json file in the base folder of the shop add the autoload configuration or extend if already existing:
```
  "autoload": {
    "psr-4": {
      "Mollie\\Payment\\": "./source/modules/mollie/molliepayment"
    },
    "files": ["./source/modules/mollie/molliepayment/lib/mollie-api-php/vendor/autoload.php"]
  },
```
5. Connect to the webserver with a console, navigate to the shop base folder and execute the following command to regenerate the autoloader files:
```
vendor/bin/composer dump-autoload
```
6. Log in to the shop admin area and enable and configure the module

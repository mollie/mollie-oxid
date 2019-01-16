# mollie-oxid
Mollie Payments for OXID eSales

## Manual Installation
1. Create the folders "mollie/molliepayment" in the "source/modules" folder of the Oxid 6 installation
2. Copy the content of this Git repository in this newly created molliepayment folder.
3. In the composer.json file in the base folder of the shop add the autoload configuration or extend if already existing:
```
  "autoload": {
    "psr-4": {
      "Mollie\\Payment\\": "./source/modules/mollie/molliepayment"
    }
  },
```
4. Connect to the webserver with a console, navigate to the shop base folder and execute the following command:
```
composer dump-autoload
```
5. Login into the shop admin area and enable and configure the module

## One library for all your application storage requirements

TurboDepot gives you total control over your application storage requirements: Anything you may need to store as part of your daily application development is managed in a super easy and centralized way. It is also designed to be cross-language: Several versions for Php, TypeScript, JS (Node) are implemented with exactly the same features, APIs, and classes. Super reusable and cross-language. Learn once, code forever!

### Language support

- Php (7 or more)
- Javascript or Typescript (on NodeJs)
- Mysql / MariaDb

We want to increase this list. So! if you want to translate the library to your language of choice, please contact us! We need your help to port this library to as many languages as possible, and more importantly, we need to code the SAME unit tests across all the implemented languages. This is the only way to guarantee that the library delivers exactly the same behavior everywhere.

### Features

- All the library configuration is centralized on a single JSON file (not mandatory)
- Super easy ORM: Save, read, list, filter, and manipulate application DB objects without caring about writing SQL queries (plain SQL can still be executed when necessary)
- Database tables and columns are created automatically when new properties are defined on the objects being saved.
- All the stored database or file system elements are saved in a human-readable way, so you can still easily manipulate them outside of this library at any time
- Write and read to log files and manage their lifetime and space usage
- Save, read, list, and manage users, their permissions, tokens login, and security checks
- Save and read application temporary files
- Save and read from an easy to use file system cache
- Operate with the OS terminal with classes that emulate its features
- Operate with the application main console with classes that emulate its features
- Easy but still powerful: Tested with massive amounts of stored objects. Heavily optimized under the hood for best performance
- Multiple depot instances can be managed and configured on the same JSON setup file. Each one with its own isolated storage space
- Multiple file and database systems are supported
- Windows and Linux support

### Documentation

**A detailed code specification is available online. You can check it [here](https://turboframework.org/en/libs/turbodepot)**

### How to use it

- Php (available as a .phar file)

```
require '.../turbocommons-php-X.X.X.phar';
require '.../turbodepot-php-X.X.X.phar';

use org\turbodepot\src\main\php\managers\FilesManager;

$filesManager = new FilesManager();
$filesManager->mirrorDirectory('path/to/source/directory', 'path/to/destination/directory');
```

- Javascript (NodeJS)

```
npm install turbodepot-node

const { FilesManager } = require('turbodepot-node');

let filesManager = new FilesManager();
filesManager.mirrorDirectory('path/to/source/directory', 'path/to/destination/directory');
```

### Dependencies

This library only requires the latest [turbocommons](https://turboframework.org/en/libs/turbocommons) library version

### Support

TurboDepot is 100% free and open-source, but we will be really pleased to receive any help, support, comments, or donations to help us improve this library. If you like it, spread the word!

> You can get more info at the official site: [https://turboframework.org/en/libs/turbodepot](https://turboframework.org/en/libs/turbodepot)

### Donate
	
[![Donate](https://turboframework.org/view/views/home/donate-button.png)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=53MJ6SY66WZZ2&lc=ES&item_name=TurboDepot&no_note=0&cn=A%c3%b1adir%20instrucciones%20especiales%20para%20el%20vendedor%3a&no_shipping=2&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted)

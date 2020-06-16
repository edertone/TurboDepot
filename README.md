# TurboDepot

## One library for all your application storage requirements

This library gives you total control over your application storage requirements: Anything you may need to store as part of your application development is managed in a super easy and centralized way. It is also designed to be cross language: Several versions like Php or Node are implemented with exactly the same features, APIs and classes. Just like all the other components of the turboframework platform: Super reusable and cross language. Learn once, code forever!

### Features

- All the library configurations are centralized on a single json setup file.
- Super easy ORM: Save, read, list, filter and manipulate you application objects without caring about writing complex SQL queries (you can still execute plain SQL when necessary).
- All the stored database or file system elements are saved in a human readable way, so you can still easily manipulate them outside of this library when needed.
- Write and read to log files and manage their lifetime and space usage.
- Save, read, list and manage users, their permissions, tokens and security checks.
- Manage the application temporary files
- Manage the application cached data
- Operate with the OS terminal with classes that emulate its features
- Operate with the main application console with classes that emulate its features
- Easy but still powerful: Tested with massive amounts of stored objects. Heavily optimized under the hood for best performance.
- Multiple depot instances can be managed and configured on the same json setup file. Each one with its own isolated storage space.
- Multiple file and database systems are supported.
- Windows and Linux support.

### Language support

- Php (7 or more)
- NodeJs

We want to increase this list. So! if you want to translate the library to your language of choice, please contact us! We need your help to port this library to as many languages as possible, and more important, we need to code the SAME unit tests across all the implemented languages. This is the only way to guarantee that the library delivers exactly the same behavior everywhere.

### Example with PHP

Make sure you've downloaded the latest turbocommons phar version from [turbocommons.org](https://turbocommons.org) and turbodepot phar version from [turbodepot.org](https://turbodepot.org)

```
use org\turbodepot\src\main\php\managers\DepotManager;

require_once 'some/filesystem/path/to/your/turbocommons-php-X.X.X.phar';
require_once 'some/filesystem/path/to/your/turbodepot-php-X.X.X.phar';

$pathToSetup = 'some/filesystem/path/to/your/turbodepot.json';

// Connect to a depot instance configured with the name 'my_depot'
$dpm = new DepotManager($pathToSetup, 'my_depot');

// Write something to two different log files
$dpm->getLogsManager()->write('this is a log line', 'logfile.txt');
$dpm->getLogsManager()->write('this is another log line', 'logfile2.txt');

// Save some objects to database
// TODO

// Perform some user operations
// TODO

// Create and write to a temporary file
// TODO

// Connect to another depot instance configured with the name 'my_depot_2'
$dpm2 = new DepotManager($pathToSetup, 'my_depot_2');

// All operations can now be performed the same way for this second instance
// ...
```

### Dependencies

This library only requires the latest turbocommons library.

### Contribute

TurboDepot is 100% free and open source, but we will be really pleased to receive any help, support, comments or donations to help us improve this library. If you like it, spread the word!

- You can get more info at the official site:

	- [turbodepot.org](https://turbodepot.org)

### Donate
	
[![Donate](https://turbocommons.org/view/views/home/donate-button.png)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=53MJ6SY66WZZ2&lc=ES&item_name=TurboDepot&no_note=0&cn=A%c3%b1adir%20instrucciones%20especiales%20para%20el%20vendedor%3a&no_shipping=2&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted)

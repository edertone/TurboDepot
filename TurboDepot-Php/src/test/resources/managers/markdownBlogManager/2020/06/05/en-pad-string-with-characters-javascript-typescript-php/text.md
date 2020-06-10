# Pad a string to a certain length with another string on Javascript, Typescript and Php

A common operation that we sometimes need to perform when developing is to add several characters to a string so it gets adjusted to a fixed length.

For example, we may have a numeric string with the value '1234' and we want to add as many zeros to the left as necessary to reach a length of 10 characters. Applying a pad, we would get '0000001234' as a result.

Using the TurboCommons library this process is as simple as calling a method and getting it's result.

## Padding a string with the TurboCommons library

There's a general purpose **pad()** method inside the StringUtils class which lets you fill a string with the desired characters till the desired length is reached. Let's see how to do it by adding zeros to the right with different programming languages:

### Pad zeros to a string in Php

Download the latest TurboCommons phar file from the [downloads](https://turbocommons.org/en/download) section, place it on your project as a dependency and run the following code:

```
require '%path-to-your-project-dependencies-folder%/turbocommons-php-X.X.X.phar';
use org\turbocommons\src\main\php\utils\StringUtils;
echo StringUtils::pad('1234', 5)."<br>";
echo StringUtils::pad('1234', 10, '0', 'LEFT')."<br>";
echo StringUtils::pad('1234', 5, '0', 'RIGHT')."<br>";
echo StringUtils::pad('abcd', 10, '0', 'RIGHT')."<br>";
```

The output of the previous code should be:

```
01234
0000001234
12340
abcd000000
```

### Pad zeros to a string on a website

Download the latest turbocommons-es5.js file from the [downloads](https://turbocommons.org/en/download) section or use npm to add the dependecy to your project (npm install turbocommons-es5). Then run the following code: 

```
<script src="turbocommons-es5/turbocommons-es5.js"></script>
<script>
var StringUtils = org_turbocommons.StringUtils;
console.log(StringUtils.pad('1234', 5));
console.log(StringUtils.pad('1234', 10, '0', 'LEFT'));
console.log(StringUtils.pad('1234', 5, '0', 'RIGHT'));
console.log(StringUtils.pad('abcd', 10, '0', 'RIGHT'));
</script>
```

The console log for the previous code should be:

```
01234
0000001234
12340
abcd000000
```

### Pad zeros to a string in Typescript (TS)

The recommended way is to use npm to obtain the turbocommons dependency by executing the following command at the root of your project:

```
npm install turbocommons-ts
```

Or you can download the latest turbocommons-ts files from the [downloads](https://turbocommons.org/en/download) section and copy the dependency by yourself. Then run the following code:

```
import { StringUtils } from 'turbocommons-ts';
console.log(StringUtils.pad('1234', 5));
console.log(StringUtils.pad('1234', 10, '0', 'LEFT'));
console.log(StringUtils.pad('1234', 5, '0', 'RIGHT'));
console.log(StringUtils.pad('abcd', 10, '0', 'RIGHT'));
```

The output of the previous code should be:

```
01234
0000001234
12340
abcd000000
```

### Pad zeros to a string in a NodeJs App

Install the dependency by executing the following command at the root of your project:

```
npm install turbocommons-ts
```

And then run the following code:

```
const {StringUtils} = require('turbocommons-ts');
console.log(StringUtils.pad('1234', 5));
console.log(StringUtils.pad('1234', 10, '0', 'LEFT'));
console.log(StringUtils.pad('1234', 5, '0', 'RIGHT'));
console.log(StringUtils.pad('abcd', 10, '0', 'RIGHT'));
```

Which should output:

```
01234
0000001234
12340
abcd000000
```
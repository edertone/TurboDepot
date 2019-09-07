# Convert string to CamelCase, UpperCamelCase or LowerCamelCase in Javascript, typescript and Php

The TurboCommons library contains lots of methods which are helpful if we want to format some arbitrary string with a standarized letter case convention. In this post, we are going to learn how to turn any text into a valid camel case string with any of it's variants (Upper and Lower). But first of all, let's learn a bit more about how camel case works:

## What is Camel Case

Camel case is a textual convention that defines a way to use letter case when compound words or phrases are written. It is also called camel caps or medial capitals, but the most commonly used denomination is the one we're using here. The camel case rules are fairly simple: When writting a compound word or phrase, we must use a capital letter at the beginning of each word or abbreviation in the middle of the phrase. No intervening spaces or punctuation signs shall be used on a camel case text. Following examples ilustrate this:

- HelloWord
- ThisIsACamelCaseText
- lowerCamelCase

Note that all the examples (except the last one) use Capital letters for each word. This is because there are two alternative camel case notations: Upper camel case (initial uppercase letter, also known as Pascal case) and lower camel case (initial lowercase letter). While both are valid camel case notations, they basically differ in the first letter of the sentence.

## Camel case conversion in TurboCommons library

There's a general purpose **formatCase()** method inside the StringUtils class which lets you convert a string into lots of common case formats, like CamelCase, UpperCamelCase, lowerCamelCase, snake_case, Title Case and many more. Let's see how we would do it for the two camel case variations we previously exposed:

### Convert a string to Camel Case in Php

Download the latest TurboCommons phar file from the [downloads](https://turbocommons.org/en/download) section, place it on your project as a dependency and run the following code:

```
require '%path-to-your-project-dependencies-folder%/TurboCommons-x.x.x.phar';
use org\turbocommons\src\main\php\utils\StringUtils;
echo StringUtils::formatCase('sNake_Case', StringUtils::FORMAT_CAMEL_CASE)."<br>";
echo StringUtils::formatCase('SNake_Case', StringUtils::FORMAT_CAMEL_CASE)."<br>";
echo StringUtils::formatCase('sNake_Case', StringUtils::FORMAT_UPPER_CAMEL_CASE)."<br>";
echo StringUtils::formatCase('SNake_Case', StringUtils::FORMAT_LOWER_CAMEL_CASE)."<br>";
```

The output of the previous code should be:

```
sNakeCase
SNakeCase
SNakeCase
sNakeCase
```

### Convert a string to Camel Case in Javascript on a website

Download the latest turbocommons-es5.js file from the [downloads](https://turbocommons.org/en/download) section or use npm to add the dependecy to your project (npm install turbocommons-es5). Then run the following code: 

```
<script src="turbocommons-es5/turbocommons-es5.js"></script>
<script>
var StringUtils = org_turbocommons.StringUtils;
console.log(StringUtils.formatCase('sNake_Case', StringUtils.FORMAT_CAMEL_CASE));
console.log(StringUtils.formatCase('SNake_Case', StringUtils.FORMAT_CAMEL_CASE));
console.log(StringUtils.formatCase('sNake_Case', StringUtils.FORMAT_UPPER_CAMEL_CASE));
console.log(StringUtils.formatCase('SNake_Case', StringUtils.FORMAT_LOWER_CAMEL_CASE));
</script>
```

The console log for the previous code should be:

```
sNakeCase
SNakeCase
SNakeCase
sNakeCase
```

### Convert a string to Camel Case in Typescript (TS)

The recommended way is to use npm to obtain the turbocommons dependency by executing the following command at the root of your project:

```
npm install turbocommons-ts
```

Or you can download the latest turbocommons-ts files from the [downloads](https://turbocommons.org/en/download) section and copy the dependency by yourself. Then run the following code:

```
import { StringUtils } from 'turbocommons-ts';
console.log(StringUtils.formatCase('some text TO Camel CASE', StringUtils.FORMAT_CAMEL_CASE));
console.log(StringUtils.formatCase('Some text TO Camel CASE', StringUtils.FORMAT_CAMEL_CASE));
console.log(StringUtils.formatCase('some text TO Camel CASE', StringUtils.FORMAT_UPPER_CAMEL_CASE));
console.log(StringUtils.formatCase('Some text TO Camel CASE', StringUtils.FORMAT_LOWER_CAMEL_CASE));
```

The output of the previous code should be:

```
someTextToCamelCase
SomeTextToCamelCase
SomeTextToCamelCase
someTextToCamelCase
```

### Convert a string to Camel Case in a NodeJs App

Install the dependency by executing the following command at the root of your project:

```
npm install turbocommons-ts
```

And then run the following code:

```
const {StringUtils} = require('turbocommons-ts');
console.log(StringUtils.formatCase('some text TO Camel CASE', StringUtils.FORMAT_CAMEL_CASE));
console.log(StringUtils.formatCase('Some text TO Camel CASE', StringUtils.FORMAT_CAMEL_CASE));
console.log(StringUtils.formatCase('some text TO Camel CASE', StringUtils.FORMAT_UPPER_CAMEL_CASE));
console.log(StringUtils.formatCase('Some text TO Camel CASE', StringUtils.FORMAT_LOWER_CAMEL_CASE));
```

Which should output:

```
someTextToCamelCase
SomeTextToCamelCase
SomeTextToCamelCase
someTextToCamelCase
```
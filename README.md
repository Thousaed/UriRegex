# URIREGEX
---

> UriRegex is simple library that transform URI matching expression to a regular expression 
> pattern which can be used to match a URI or it's segment.

## Installation
---

The package can be installed via composer using.

```bash
composer require DoxExon\UriRegex
```

## Usage
---

```php
$regex = new Pattern('/profile/@{name(\w+)}', [
  'sensitive' => true,
  'capturePattern' => true
]);
  
$target = '/profile/@johndeo';

//match
$match = $regex->test($target); //true

//test
$match = $regex->match($target); //[0 => '/profile/@johndeo', [name] => 'johndeo']
```
<br/>

### Methods
---

**testing**

- Use the test method for testing if a path matches the expresssion.
It returns a boolean value 

**matching**

- Use the match method for return the matches and extracted segment of
the uri

<br/>

### Params
---

The pattern object take two argument, a source and an optional config.

**Source**

- This is the expression that will be used to match a target URI.

**Config**

- sensitive: ```bool``` (default: true) 
  This option set regular expression to be case sensitivity.
  If set to true it will match both upper or lowercase characters
  
- pattern: ```string``` (default: [^#?\/]) 
  The pattern option set the pattern used in named params and wildcard

- ignore_space: ```bool``` (default: true)
  It's specify if param and pattern block should ignore spaces inside the block.
  
- trail: ```bool``` (default: true)
  This option when set to true add a optional trailng slash to the pattern.

- capturePattern: ```bool``` (default: false)
  This includes pattern's to the array of parameter returned as a result
  of matching the expression

## How to use
---

Here is a simple guide on how to use the UriRegex Object.

**Params**

Params are denoted by the block {name} and it possibe to change how named pattern
are matched by adding a pattern at the end of the param name inside the block.

without pattern
```php
$source = '/profile/{username}';
```

with pattern
```php
$source = '/profile/{username(\w+)}';
```
<br/>

> **Note**: Named parameter can only contain alphabets and underscore (_)

**Pattern**

Patterns allows for including inline regular expression in the pattern source string.

```php
$source = '/profile/(\w+)';
$target = '/profile/john';
$uri_regex = new Pattern($source);
$uri_regex->test($target); //true
```
**Wilcard**

Wildcard ```*``` is use to match any URI segment except for delimeters ```/#?``` and it's not affected
by the pattern option and also not return in the match as a captured segment.

```php
$source = '/address/*';
```

**Optional**

Optional specifier ```?``` is used for making dynamic segment of the URI optional
```php
$source = '/user/{mame}?';
$source = '/user/(\w+)?';
```

adding the optional flag to segment that's not named or a pattern will raise an
exception

This code will raise an Exception.
```php
$source = '/profile?/(\w+)';
$target = '/profile/john';
$uri_regex = (new Pattern($source))->test($target); //fatal error: Uncaught Exception: Unexcepted "?" at index 8. ...
```

> **Note**: characters which are used  by the Regex object should be excaped if you
> dont want it to be treated as special character. By adding slash to the beginning of 
> that character it will be ignored by the Regex Object 

```$source = '/home/\*asterick/';```
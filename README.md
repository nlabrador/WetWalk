# WetWalk
WetWalk PHP Command-line tool that parses PHP files and transforms into a "dry-run" (echo) mode

This will help to easily create a "dryrun" version of your classes or scripts. It finds function or method calls and convert that call into an echo with the function call to display.

If your scripts or classes is calling system commands like `exec`, `mkdir`, `system`, etc. Creating a "dryrun" version of them is the best way to test them out.

Once you already have "dryrun" version all you need is to call them in your scripts where applicable.

###Installation
Download or clone https://github.com/nlabrador/WetWalk.git
`cd WetWalk_direcory`
`composer install`

###Usage
`php console create:dryrun --other-methods=otherMethod tests/files/TestConvertClass.php`

`php console create:dryrun --other-methods=otherMethod tests/files/TestNamespaceConvertClass.php`

`php console create:dryrun --other-methods=otherMethod tests/files/testscript.php`

###Example usage of the "DryRun" converted file
```php
    if ($mode == 'dryrun')
    {
        //Call my new DryRun/MyClass object
    }
    else {
        //Call my old MyClass object 
    }
```

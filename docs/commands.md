# Commands

[equip/command](https://github.com/equip/command) is an optional library used for implementing [commands](https://sourcemaking.com/design_patterns/command) in Equip.

## Components

[`CommandInterface`](https://github.com/equip/command/blob/master/src/CommandInterface.php) is an interface implemented by all command classes. It provides an [immutable](https://en.wikipedia.org/wiki/Immutable_object) structure containing command options that can ultimately be executed.

Many of the methods of [`CommandInterface`](https://github.com/equip/command/blob/master/src/CommandInterface.php) are implemented in [`AbstractCommand`](https://github.com/equip/command/blob/master/src/AbstractCommand.php), which serves as a base class for command class implementations.

### Getting Options

The `options()` method can be used at any time to obtain an associative array of the options held by a command instance.

```php
$options = $command->options();
```

### Changing Options

Using the `withOptions()` method, it's possible to obtain a new command instance from an existing one with its options completely replaced.

```php
$new_command = $old_command->withOptions([ /* ... */ ]);
```

The `addOptions()` method functions in a similar fashion except that it merges the provided options with those of the existing command instance rather than replacing them.

```php
$new_options = [ /* ... */ ];
$new_command = $old_command->addOptions($new_options);
// $new_command now contains options from both the return value of
// $old_command->options() and $new_options
```

Note that, in the above example, if a key exists in both `$old_command->options()` and `$new_options`, the value from `$new_options` would be used in `$new_command`.

### Checking for Options

If you need to check for a value without retrieving its value, use the `hasOption()` method.

```php
if ($command->hasOption('option_name')) {
    // ...
}
```

The `requiredOptions()` method is intended to be used internally by the `options()` method to confirm that all required options are present. More specifically, `requiredOptions()` returns an array of strings and `options()` checks that each value from that array exists as a key in the options of the command instance. If not, `options()` will throw an instance of [`CommandException`](https://github.com/equip/command/blob/master/src/CommandException.php).

### Executing Commands

Once a command class has been instantiated and option values provided in whatever way is appropriate for that particular command class, the `execute()` method can be invoked to execute the command. `execute()` may return a value if it's appropriate to the command class implementation.

```php
$result = $command->execute();
```

## Basic Example

Here's an example of a command class.

```php
use Equip\Command\AbstractCommand;

class FizzbuzzCommand extends AbstractCommand
{
    public function requiredOptions()
    {
        return [
            'quantity',
        ];
    }

    public function execute()
    {
        $options = $this->options();
        $quantity = $options['quantity'];

        // ...
    }
}
```

Command classes are tended to be injected (using [Auryn](http://equipframework.readthedocs.org/en/latest/#dependencies)) into and receive options from other classes. Here's an example of a domain class that does this.

```php
use Equip\Adr\DomainInterface;

class MyDomain implements DomainInterface
{
    private $command;

    public function __construct(FizzbuzzCommand $command)
    {
        $this->command = $command;
    }

    public function __invoke(array $input)
    {
        $options = [];

        // populate $options using $input

        $result = $this->command
            ->withOptions($options) // or addOptions($options)
            ->execute();

        // populate and return payload like normal
    }
}
```

Command classes can handle assigning any applicable default option values internally. Other classes can add to those defaults using the command `addOptions()` method or replace them entirely using the command `withOptions()` method.

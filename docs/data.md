# Data

[equip/data](https://github.com/equip/data) is an optional library for creating a data layer in Equip applications.

## Basic Example

```php
namespace Acme;

use Equip\Data\EntityInterface;
use Equip\Data\Traits\EntityTrait;

class User implements EntityInterface
{
    use EntityTrait;
    
    private $id;
    private $email;
    private $password;
    private $registered_on;
    
    private function types()
    {
        return [
            'id' => 'int',
        ];
    }
}
```

The `EntityTrait` is a composition of all of the available traits:

- `ImmutableValueObjectTrait`
- `DateAwareTrait`
- `JsonAwareTrait`
- `SerializeAwareTrait`

## Usage

The properties of classes `ImmutableObjectValueTrait` will be publicly accessible but read only:

```php
$user = new User([
    'id' => 1,
    ...
]);

echo $user->id; // 1
```

These objects can only be modified by copying the object using `withData`:

```php
$user = $user->withData([
    'email' => 'user@example.com',
]);
```

To check if an entity has a value, use the `has` method:

```php
$user->has('email'); // true
$user->has('role'); // false
```

*Note that this a check to see if the entity has a property defined. It will return `true`
even if the value is currently `null` or otherwise empty.*

To get an array of values from the object, use the `toArray` method:

```php
$data = $user->toArray();
```

## Additional Features

The `EntityTrait` will allow an entity to be passed to `json_encode`. It can also be
passed through `serialize` and `unserialize`.

Properties that represent dates can be fetched as [`Carbon`](http://carbon.nesbot.com/)
objects by using the `date` method:

```php
$registered = $user->date('registered_on'); // Carbon
```

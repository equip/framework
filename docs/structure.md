# Structure

[equip/structure](https://github.com/equip/structure) is an optional library for
using [immutable][wiki-immutable] data structures. It can be used with Equip or
as a stand alone dependency.

**`Dictionary`** is an implementation of a [associative array][wiki-dict] that
stores values identified by a key. Only associative arrays can be used to
initialize the structure. Any value can be defined by a string key.

**`SortedDictionary`** is an implementation of a [associative array][wiki-dict]
that also sorts the array. When the dictionary is modified it will be sorted.
By default the [`asort`][php-asort] function is used.

**`OrderedList`** is an implementation of a [list][wiki-list] that stores ordered
values. Only an indexed array can be used to initialize the structure. Any value
can be added. When the list is modified it will be sorted. By default the
[`sort`][php-sort] function will be used.

**`UnorderedList`** is an implementation of a [list][wiki-list] that stores
unordered values. The same value may appear more than once. Only an indexed array
can be used to initialize the structure. Any value can be added.

**`Set`** is an implementation of a [set][wiki-set] that stores a unique values.
The same value will *not* appear more than once. Only an indexed array can be used
to initialize the structure. Adding an existing value to the set will have no effect.
A value can also be inserted into a set before or after an existing value.

[wiki-immutable]: https://en.wikipedia.org/wiki/Immutable_object
[wiki-dict]: https://en.wikipedia.org/wiki/Associative_array
[wiki-list]: https://en.wikipedia.org/wiki/List_(abstract_data_type)
[wiki-set]: https://en.wikipedia.org/wiki/Set_(abstract_data_type)

[php-sort]: http://php.net/sort
[php-asort]: http://php.net/asort

## Common Functionality

These structures can be used directly or customized for specific scenarios. Structures
can be combined in various ways for more complex structures. All structures implement
the following interfaces: [`ArrayAccess`][php-array-access], [`Countable`][php-countable],
[`Iterator`][php-iterator], [`JsonSerializable`][php-json-serializable],
and [`Serializable`][php-serializable].

[php-array-access]: http://php.net/ArrayAccess
[php-countable]: http://php.net/Countable
[php-iterator]: http://php.net/Iterator
[php-json-serializable]: http://php.net/JsonSerializable
[php-serializable]: http://php.net/Serializable

All of these interfaces are required by the [`StructureInterface`][struct-interface]
that all structures implement. This interface provides two additional methods:

* **`toArray`** can be used to convert any structure to an array.
* **`isSimilar`** can be used to check if two structures are of the same type.

[struct-interface]: https://github.com/equip/structure/blob/master/src/StructureInterface.php

Each structure also has its own distinct interface but all structures have the
following methods, with variations in signature:

* **`withValues`** copy the current structure with new values.
* **`withValue`**  copy the current structure with one new value.
* **`withoutValue`** copy the current structure with one value removed.
* **`hasValue`** check if a value exists.
* **`getValue`** get a single value.

This makes all structures familiar and easy to work with.

## Usage

### Dictionary

Dictionaries can be created from associative arrays:

```php
use Equip\Structure\Dictionary;

$dict = new Dictionary([
    'color' => 'yellow',
]);

$dict->hasValue('color'); // true
$dict->hasValue('speed'); // false
```

And modified with associative arrays:

```
$dict = $dict->withValues([
    'speed' => 'fast',
]);

$dict->hasValue('color'); // false
$dict->hasValue('speed'); // true
```

Values are added by defining a key:

```php
$dict = $dict->withValue('color', 'red');


$dict->hasValue('color'); // true
$dict->hasValue('speed'); // true
```

And removed by the same key:

```php
$dict = $dict->withoutValue('speed');

$dict->hasValue('color'); // true
$dict->hasValue('speed'); // false
```

### Lists

Lists can be created from non-associative arrays:

```php
use Equip\Structure\UnorderedList;

$list = new UnorderedList([
    'eggs',
]);

$list->hasValue('eggs'); // true
$list->hasValue('spinach'); // false
```

And modified with other indexed arrays:

```
$list = $list->withValues([
    'spinach',
]);

$list->hasValue('eggs'); // false
$list->hasValue('spinach'); // true
```

Values can be added:

```php
$list = $list->withValue('eggs');

$list->hasValue('eggs'); // true
$list->hasValue('spinach'); // true
```

And removed:

```php
$list = $list->withoutValue('spinach');

$list->hasValue('eggs'); // true
$list->hasValue('spinach'); // false
```

#### Ordered List

The primary difference between an `OrderedList` and `UnorderedList` is that an
ordered list will always be sorted as new values are added:

```php
use Equip\Structure\OrderedList;

$list = new OrderedList([
    'rice',
    'beans',
    'corn',
    'wheat',
    'oats',
]);

print_r($list->toArray()); // beans, corn, oats, rice, wheat
```

A custom ordering method can be defined by extending the `OrderedList` class and
changing the `sortValues` method:

```php
use Equip\Structure\OrderedList;

class UserList extends OrderedList
{
    protected function sortValues()
    {
        // Sort by user last name, then first name
        usort($this->values, static function ($a, $b) {
            $sort = strcmp($a['last_name'], $b['last_name']);
            if ($sort === 0) {
                $sort = strcmp($a['first_name'], $b['first_name']);
            }
            return $sort;
        });
    }
}
```

Now you can use your custom list to automatically sort users by first and last
name when populated:

```php
$users = new UserList([
    [
        'first_name' => 'John',
        'last_name' => 'Smith',
    ],
    [
        'first_name' => 'Margie',
        'last_name' => 'Vos',
    ],
    [
        'first_name' => 'John',
        'last_name' => 'Abrams',
    ],
    [
        'first_name' => 'Sally',
        'last_name' => 'Smith',
    ],
]);

print_r($users->toArray());
```

### Set

Sets can be created from non-associative arrays:

```php
use Equip\Structure\Set;

$set = new UnorderedList([
    'eggs',
]);

$set->hasValue('eggs'); // true
$set->hasValue('spinach'); // false
```

And modified with other indexed arrays:

```
$set = $set->withValues([
    'spinach',
]);

$set->hasValue('eggs'); // false
$set->hasValue('spinach'); // true
```

Values can be added:

```php
$set = $set->withValue('eggs');

$set->hasValue('eggs'); // true
$set->hasValue('spinach'); // true
```

And removed:

```php
$set = $set->withoutValue('spinach');

$set->hasValue('eggs'); // true
$set->hasValue('spinach'); // false
```

#### Differences from Lists

So far, all of these examples are no different than lists. The real difference with
sets comes into play when you add values that already exist in the set. This will
result in the same set being returned without modification:

```php
$set = $set->withValue('spinach');

print_r($set->toArray()); // eggs, spinach

$copy = $set->withValue('spinach');

print_r($copy->toArray()); // eggs, spinach
print_r($copy === $set); // true
```

Sets are particularly useful in situations where you have a number of objects and
want to ensure that no duplicates are included.

#### Inserting Values at Specific Locations

Another difference is that sets have the ability to insert values before or after
existing values. This can be useful when the order of the values matters, or when
using a set as a dependency chain.

```php
$set = $set->withValueBefore('tomato', 'spinach');

print_r($set->toArray()); // eggs, tomato, spinach

$set = $set->withValueAfter('cheese', 'eggs');

print_r($set->toArray()); // eggs, cheese, tomato, spinach
```

**Note:** When inserting before a value that does not exist, the new value will be _prepended_ to the set. When inserting after a value that does not exist, the value will be _appended_ to the set.

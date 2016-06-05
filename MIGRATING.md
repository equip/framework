Migrating
=========

Use this document to help you transition away from deprecated code.

## Switching from Routing to Dispatching

Version 3.0.0 replaces the routing callback with dispatching callables, which
are typically defined as classes with an `__invoke` method in your project.

### Changes

Routing that used to be defined as:

```php
Application::build()
// ...
->setRouting(function (Equip\Directory $directory) {
    return $directory
        ->get('/', Domain\Welcome::class)
        ->get('/hello[/{name}]', Domain\Hello::class)
        ->post('/hello[/{name}]', Domain\Hello::class)
        ; // End of routing
})
```

Would now be defined as:

```php
Application::build()
// ...
->setDispatching([
    Project\Hello\HelloDispatcher::class,
    Project\Welcome\WelcomeDispatcher::class,
])
```

### Temporary Solution

As a temporary solution, you may reuse the routing function for dispatching:

```php
Application::build()
// ...
->setDispatching([
    function (Equip\Directory $directory) {
        return $directory
            ->get('/', Domain\Welcome::class)
            ->get('/hello[/{name}]', Domain\Hello::class)
            ->post('/hello[/{name}]', Domain\Hello::class)
            ; // End of routing
    },
])
```


### Splitting Dispatchers

The long term solution for handling dispatching is to add separate classes for
each resource you want to dispatch. Splitting the current routing would become:

```php
namespace Equip\Project\Hello;

use Equip\Directory;

class HelloDispatcher
{
    public function __invoke(Directory $directory)
    {
        return $directory
            ->get('/hello[/{name}]', HelloDomain::class)
            ->post('/hello[/{name}]', HelloDomain::class)
            ;
    }
}
```

```php
namespace Equip\Project\Welcome;

use Equip\Directory;

class WelcomeDispatcher
{
    public function __invoke(Directory $directory)
    {
        return $directory->get('/', WelcomeDomain::class);
    }
}
```

For simplicity, you may only want to have a single dispatcher when starting a
new project and as your application grows you can switch to separate files.


### Resource Based Structure

This also implies a change towards resource-based code structure, as opposed to
type-based structure. Instead of organizing your files by the type:

```
src/
    Domain/
        Hello.php
        Welcome.php
    Exceptions/
        HelloError.php
        WelcomeError.php
```

Files are logically grouped by the resource they represent:

```
src/
    Hello/
        HelloDomain.php
        HelloError.php
    Welcome/
        WelcomeDomain.php
        WelcomeError.php
```

It is recommended that your organize your Equip projects this way but not required.
The benefit to doing so that it becomes very easy to determine what code can be
found based on its name.

If you have common code that is shared between different resource contexts, feel
free to create a "common" directory:

```
src/
    Common/
        Filesystem.php
        Database.php
    Hello/
        ...
    Welcome/
        ...
```

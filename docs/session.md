# Session

[equip/session](https://github.com/equip/session) is an optional library for using sessions in Equip applications.

The benefit of using session objects instead of a global variable is primarily in testing, where the session object can be mocked and operations verified. Using session objects also makes it much easier to switch to a distributed session storage as your application scales.

## Configuration

To use the [native session](https://github.com/equip/session/blob/master/src/NativeSession.php) implementation the [configuration](https://github.com/equip/session/blob/master/src/Configuration/SessionConfiguration.php) must be enabled in the [application bootstrap](https://equipframework.readthedocs.org/en/latest/#bootstrap):

```php
Equip\Application::build()
->setConfiguration([
    // ...
    Equip\Configuration\SessionConfiguration::class,
])
// ...
```

## Basic Example

```php
namespace Acme\Domain;

use Equip\SessionInterface;
use Equip\Adr\DomainInterface;

class WidgetDomain implements DomainInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    public function __invoke(array $input)
    {
        // Do things with $this->session, etc...
    }
}
```

## Usage

The [session object](https://github.com/equip/session/blob/master/src/SessionInterface.php) can be modified using methods or [with array operations](http://php.net/arrayaccess):

```php
// Set a value using object methods:
$session->set('foo', 'bar');
// Or using array operations:
$session['foo'] = 'bar';

// Get a value:
$foo = $session->get('foo');
// Or:
$foo = $session['foo'];

// Check for a value:
if ($session->has('foo')) { /* ... */ }
// Or:
if (isset($session['foo'])) { /* ... */ }

// Delete a value
$session->del('foo');
// Or:
unset($session['foo']);
```

The entire session can also be cleared at any time:

```php
$session->clear();
```

# VortexMVC

Tiny PHP MVC Framework made for myself

## How to use?

### Front Controller

Front Controller uses Zend-style of URL to determine controller and action:
```
http://yourweb.com/%controller%/%action%/%param_name1%/%param_val1%/...
```
and than call `%action%` action of `%controller%` Controller.
`%action%` and `%controller%` params, as well as other params, parsed from url, available from Vortex\Request object.

#### Controller

Controller supports actions. Every controller must have at least one:
```php
public indexAction();
```

#### View

Very simple. Just create the View obj:
```php
use Vortex\View;
$view = new View(%view_file_name%);
```
than add data:
```php
$view->data->yourvarname = 'HELLO WORD!';
```
and render it:
```php
$view->render();
```
That's all! See IndexController for example.

Framework also has a simple __Layout system__:
You should config it in application.ini file. Explore `application/views/layouts/*`, `Vortex\View` for more info =P

#### DB connection

If you need a DB connection, just take it from Vortex\Connection!
```php
use Vortex\Connection;
$db = Connection::getConnection();
```
Than you can use it. Here it is an example:
```php
$data = $db->from('article')->select('user.name');
```
`$db` is an instance of FluentPDO - a wrapper of PDO, so you can use regular PDO things with new ones (https://github.com/lichtner/fluentpdo)

#### Caching

VortexMVC has some tools for caching. It's in development stage now, but it can do some things right now.
Firstly, choose `Driver` that your need. For example, if your need caching in file, that `FileBackend` is your choice!

OK, lets use factory to setup cache object:
```php
use Vortex\Cache\CacheFactory;
$cache = CacheFactory::build(CacheFactory::FILE_DRIVER, array('namespace' => 'vConfig'));
```
factory method takes 2 params: `Driver` name, and `Options` array, that contains settings for that driver.
All `Drivers` are defined in CacheFactory as a `const`s, and `Options` array is specific for particular driver. But all driver options has __at least 2 params__:
 * Namespace (default: 'vf')
 * Cache life time (default: 300)
If they are not specified, than default values is used.

That, you can save data with it's id:
```php
$cache->save('mydata#05', $data);
```
And that retrieve it by id in the same state it was:
```php
$mydata = $cache->load('mydata#05');
```

Check PHPDoc's of `Vortex\Cache\Cache` for more information.

#### Credits
VortexMVC uses such work of other developers:
* __FluentPDO__ :: https://github.com/lichtner/fluentpdo
* __IniParser__ :: https://github.com/austinhyde/IniParser
* __SplClassLoader__ :: https://gist.github.com/jwage/221634
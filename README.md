# VortexMVC

Tiny PHP MVC Framework made for myself to be fast and lite!

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
Each controller's action can be customized with some annotations. As of 1.0 version, `vf` supports only 3 annotations:
```
/**
* @RequestMapping('/customMapping', 'METHOD');
* @Redirect('error', 'index');
* @PermissionLevels('-1', '0');
*/
public awesomeAction();
```
 * `@RequestMapping` says `FrontController` to bind path __/customMapping__ to `awesomeAction`.

 * `@Redirect` means, that any request to `awesomeAction` should be redirected to __error__ controller, and it's __index__ action.

 * `@PermissionLevels' sets allowed userlevels for `awesomeAction`, where __0 - Admin level__, and __-1 - Guest__.

#### View

Very simple. Just create the View obj:
```php
use Vortex\View;
public function awesomeAction() {
   $view = new View(%view_file_name%);
}
```
than add data:
```php
$view->data->yourvarname = 'WORLD!';
```
and render it:
```php
$view->render();
```
in `views/%view_file_name%.tpl` you can echo you this way:
```php
<?=$this->data->yourvarname?>
```
That's all! See `IndexController` for example.

Framework also has a simple __Layout system__

Layouts are a backbone for all your views. They allows to copy less repetitive view elements.

They are placed in `views/layouts` folder, and must be decelerated in `application.ini` with `view.layout.templates` param. See config file for more details.  

Here is a typical layout:
```php
<?= $this->partial('layouts/header') ?>
<section>
    <?= $this->content() ?>
</section>
```
It looks like a regular view template, but it has some distinctions. Lets find out what it does:
 * `$this->partial('layouts/header')` includes another layout `layouts/header.tpl` and places it's content into this layout. Let `header` layout has `<h1>Hello</h1>` text.
 * `<?= $this->content() ?>` is a placeholder for any other regular view. In other words, when you render any view in controller, using this example layout, it's content will be placed here.

As a result of using this layout and the view of our example `awesomeAction` rendered view will look like:
```html
<h1>Hello</h1>
<section>
    WORLD!
</section>
```

#### DB connection

If you need a DB connection, just take it from Vortex\Database!
```php
use Vortex\Database;
$db = Database::getConnection();
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

#### Something else?
VortexMVC has many other classes that I don't mention here. Here the list some of them:
 * `Vortex\Annotation` - class, that can parse any annotation for any class/method
 * `Vortex\Registry` - a simple registry implementation
 * `Vortex\GlobalRegistry` - the same with `Vortex\Registry` but made as a singleton with global scope
 * `Vortex\Logger` - a simple logger, made in the best tradition of log4j
 * `Vortex\Request` and `Vortex\Response` - the HTTP Request/Response wrappers, with many convenient methods
 * `Vortex\Session` - a PHP Sessions wrapper, with namespace support
 * `Vortex\Router` - simple router class, with annotation support
Feel free to look into it's code and read PHPDoc. They are written simple as much as possible to be lite and easy to understand.

#### Credits
VortexMVC uses some work of other developers:
* __FluentPDO__ :: https://github.com/lichtner/fluentpdo
* __IniParser__ :: https://github.com/austinhyde/IniParser
* __SplClassLoader__ :: https://gist.github.com/jwage/221634

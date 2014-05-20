# VortexMVC

Tiny PHP MVC Framework made for myself

## How to use?

### Router

Router is very simple. No RegExp routes, only full math of URL.
Check application/application.php for configs.

The autoload URL is:
    http://yourweb.com/%controller%/%action%/%param_name1%/%param_val1%/...

#### Controller

Controller supports actions. Every controller must have at least one:
```php
public indexAction();
```

All actions should ends with 'Action' (case!)

#### View

Very simple. Just create the View obj:
```php
$view = new Vortex_View(%view_file_name%);
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

#### Model

If you need a DB connection, extend `Vortex_Model` class and then connect to database:
```php
$this->connect();
```

Than you can use:
```php
$this->db
```
for making queries. `$this->db` is an instanse of FluentPDO - a wrapper of PDO, so you can use regular PDO things with new ones (https://github.com/lichtner/fluentpdo)




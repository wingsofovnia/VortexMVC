# VortexMVC

Tiny PHP MVC Framework made for myself to be fast and lite!

## How to use?

### Routes

Routes are defined in `application/routes.cfg` file in PLAY! Framework-like format: 
```
GET         /raw           application\controllers\IndexController::raw
```
that routes all GET requests to `mysite.com/raw` to IndexController's ```raw()``` action.

#### Controller

Every controller contains actions that represent a View or plain text. The Dispatcher will determine what to do with action based on return data.
```
class IndexController extends Controller {
    public function viewAction() {
        $view = View::factory('index/main');
        return $view;   // Dispatcher will render and draw index/main template and apply layout if it's enabled.  
    }

    public function directWrite() {
        $this->response->body("This is the only text that will be displayed. Dispatcher will not wrap response with layout and treats this text as raw");
    }

    public function raw() {
        return "Hello!"; // the smae with directWrite() method - only "Hello!" will be rendered.
    }
}
```

#### View

Very simple. Just create the View obj and return it from action so `Dispatcher` can render it.
```php
use Vortex\View;
class IndexController extends Controller {
    public function index() {
       $view = new View(%view_file_name%);
       $view->data->yourvarname = 'WORLD!'; // passing data to view
       return $view;
    }
}
```

in `views/templates/%view_file_name%.tpl` you can echo your variable this way:
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

There is also a special type of view - `Widget`. Widgets are designed to be a building blocks/components for your layout and may have its own logic. See `application/widgets/TestWidget` and `application/views/templates/index/index.tpl` for example. 


#### Something else?
VortexMVC has many other classes that I don't mention here. Here the list some of them:
 * `Vortex\GlobalRegistry` - the same with `Vortex\Registry` but made as a singleton with global scope
 * `Vortex\Logger` - a simple logger, made in the best tradition of log4j
 * `Vortex\Request` and `Vortex\Response` - the HTTP Request/Response wrappers, with many convenient methods
 * `Vortex\Session` - a PHP Sessions wrapper, with namespace support
Feel free to look into it's code and read PHPDoc. They are written simple as much as possible to be lite and easy to understand.

#### Credits
VortexMVC uses some work of other developers:
* __IniParser__ :: https://github.com/austinhyde/IniParser
* __SplClassLoader__ :: https://gist.github.com/jwage/221634

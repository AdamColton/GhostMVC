# GhostMVC

GhostMVC is a lightweight MVC. The most basic version allows you to create Models, Views, Controllers and routing to map URI's to specific controller methods. GhostMVC also comes with the ability to load libraries to extend the functionality to include tools like templating and databases.

GhostMVC is largely modeled on the CodeIgniter framework with the goal of a simplier file structure and cleaner interface. It also adopts some concepts from the ASP.NET MVC.

## Routes

Routes are the means by which a URI is translated into what controller to load and instantiate and which method of that controller to call. There are three means by which GhostMVC can arrive at that decision:
Conditional Routes -> Standard Routes -> Default Route

### Conditional Routes

GhostMVC will first look through the conditional routes defined in the config.php file. A Conditional Route must define a URI. It may optionally include a controller, a method, required POST, GET and SESSION values and the arguments to pass. Required GET, POST and SESSION values are set by passing in the key values. GhostMVC will check is_set against all required keys. The URI defines the type of URIs that will match the route. Each segment can be an asterisk or a token. A token must match its segment exactly. An asterisk indicates that the segment is required. If these conditions are met, the routes controller and method will be called and passed any defined arguments.

#### Examples

```php
Route::URI("*/login/*");
```

Any URI with three or more segments where the middle segment is "login" will match this route. Because a controller and method are not defined, the default controller defined in config.php will be used, and the index method will be called.

```php
Route::URI("metaData")
  ->ControllerMethod("Ajax", "_get_meta_data")
  ->PostArgs("url");
```

This matches any URI where the first segment is "metaData". It requires the $_POST array contain a value for "url". If these conditions are met, it will call the "_get_meta_data" method of the "Ajax" controller, and pass in "$_POST['url']" as an argument.


#### Conditional Route Methods

ControllerMethod
: Defines which controller and which method should be executed
Post
: Takes a variable number of keys and will only execute the route if all keys are present in the $_POST
Get
: Takes a variable number of keys and will only execute the route if all keys are present in the $_GET
Session
: Takes a variable number of keys and will only execute the route if all keys are present in the $_SESSION
Args
: Takes a varible number of arugments which will be passed along to the method if the route is executed
PostArgs
: Combines Post and Args - take a variable number of keys and if they are all present in $_POST, those values are passed as aruments to the method.
GetArgs
: Combines Get and Args - take a variable number of keys and if they are all present in $_GET, those values are passed as aruments to the method.

### Standard Routes

If GhostMVC does not find a Conditional Route whose conditions are met, it will try to call a Standard Route. The Standard Route is Path/Controller/Method/Data. Path represents the directory path under the controllers directory. If teh controller being called is directly in the controllers directly, there will be nothing in the URI for Path. Controller is the name of the controller. If there is a path and controller with the same name, path will win - but this is not advisable. Method is the name of the method being called. If no method is present, Ghost will call index() if it is defined and fall through to the default route otherwise. Anything after the Method segment is data and will not be used for routing. Controllers and Methods can be hidden from the Standard Router by prefixing them with an underscore. Hidden methods and controllers are still accessible to Conditional routes and the Default route. Hidden methods do not need to be private and if they are to be called from a conditional route, they need to be public.
Default Route

### Default Route

If no Conditional Route or Standard Route is found, the Default route, which is defined in config.php will be called. In this case, everything in the URI can be treated as data or the condition can be treated as a 404.

This behavior can be good for giving a site default functionality. For instance, if you were writing a blog, the Default Route may perform a search. This way www.myblog.com/elephant would search the blog for the value "elephant" even though no "elephant" controller is defined.
Technical Notes

## Controllers

A controller is simply a class placed in the controllers folder. Like any class, the constructor will be called when the class is instanciated. Beyond that, a controller should have an index method. The index method acts as the default method that will be called if no method is explicitly defined. A controller file should contain exactly one class and the file and class should both have the same name - all lower case. You can put sub directories in the controller folder and they will be reachable through all routing methods.

## Views

Views are used the generate the output. Ideally, views should be fragments of HTML with small bits of php to display calculated values. Views are accessed through the Render class.

### Standard View

The standard view is called with Render::view(string $viewName[, mixed $viewData]);. This will call the view defined by $viewName (you can include path, though the view folder is already prepended). $viewData is optional. It allows data to be passed to the view. It should be an array of key value pairs. When the view is called, the viewData array will be unwrapped. If Array("pi" => 3.1415) is passed in as viewData, then $pi will have the value 3.1415 in the view.
#### Example
Controller
```php
Class foo{
  function hello(){
    $data = Array(
      "firstName" => "Ada",
      "lastName" => "Loveless"
    );
    Render::view("sayHello", $data);
  }
}
```
View (sayHello.php)
```php
  Hello, <?= $firstName ?><?= $lastName ?><br />
```

Together these will produce
```html
Hello, Ada Loveless<br />
```

### Map View

Map View is a way to call a single view repeatdly for a set of data. The call to map view is Render::mapView(string $viewName, mixed $mapData);. Unlike view, $mapData is required. It should be an array of viewData arrays, though it will handle an empty array (and just not render anything).
#### Example

Controller
```php
Class foo{
  function manyHellos(){
    $data = Array(
      Array("firstName" => "Ada", "lastName" => "Loveless");
      Array("firstName" => "Linus", "lastName" => "Torvalds");
      Array("firstName" => "Steve", "lastName" => "Wozniak");
      Array("firstName" => "Rasmus", "lastName" => "Lerdorf");
    );
    Render::mapView("sayHello", $data);
  }
}
```
Will produce
```html
Hello, Ada Loveless<br />
Hello, Linus Torvalds<br />
Hello, Steve Wozniak<br />
Hello, Rasmus Lerdorf<br />
```
### Multiple Views

It is possible(and often a good practice) to call multiple views. They will be executed as they are called, in order.

'''php
Class foo{
  function manyHellos(){
    $data = Array(
      Array("firstName" => "Ada", "lastName" => "Loveless");
      Array("firstName" => "Linus", "lastName" => "Torvalds");
      Array("firstName" => "Steve", "lastName" => "Wozniak");
      Array("firstName" => "Rasmus", "lastName" => "Lerdorf");
    );
    Render::view("header", Array("title" => "Saying Hello"));
    Render::mapView("sayHello", $data);
    Render::view("footer");
  }
}
'''

### JSON View

The Json view is called with Render::json($data); where data is an array or object. The object is encoded as json and rendered as output.

## Models

A model is just a class placed in the models folder. It can only be called by a controller. It is included using
```php
Load::model(string $modelName);
```

## Config
This is meant to encapsulate server level configurations. Any differences between a development and a live server should exist here. Values that often live in this file are things like database connections, session path and root url.

The three required values are
* HOME_CONTROLLER
* DEFAULT_CONTROLLER
* DEFAULT_METHOD

These values currently live in config.php but would be more appropriate in hooks.php. In some future revision, this change will be made.

The two methods that must be present in Config are before() and after() - see Before, After and Execution Order.

## Hooks
Hooks provides three methods that Ghost MVC will call during its execution - before, after and setConditionalRoutes. For inormation on before and after see Before, After and Execution Order. The method setConditionalRoutes should only be used to set conditional routes. Following this convention prevents any unintended side effects as well as makes the method a singular reference for all conditional routing on a site thereby easing team communication.

## Before, After and Execution Order

The before and after static methods are both present in config.php and hooks.php. The execution path of Ghost MVC is as follows

1. URI parsing
2. Config::before()
3. Hooks::before()
4. MVC route execution
5. Hooks::after()
6. Config::after

In keeping with the nature of the relative files - config should hold server (as in local, dev, stage and live) level methods. The Config::before() method, for instance, is a good place to include a file for debugging or testing - something you would not want deployed to a live server. The methods in hooks should be common to all servers. It is also worth noting that URI parsing happens before either so the URI methods are available in these functions.

# GhostMVC

GhostMVC was influenced primarily by CodeIgniter and the ASP.NET MVC. I ended up writing the core almost accidentally while experimenting with how to parse URIs in PHP. I was inspired to take my experiment and build it out to an MVC when I ran into a problem in CodeIgniter that I could not access $_GET.

When I decided to take my experiments and build an MVC engine on them, I had a few objectives. I wanted a small core with just a few files and a simple file structure. I felt that a good MVC should run on a small, solid core and have its abilities augmented with libraries and utilities.


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

## Libraries

Libraries are used to extend the functionality of GhostMVC. The built in functionality is intentionally limited, so you will probably need at least a few libraries. A library can be loaded with
```php
Load::library($libraryName);
```

## Directories

The directory structure is important to Ghost MVC. There must be directories for models, views and controllers.

### Root

The root should contain index.php, config.php, hooks.php and possibly (reocmmended) a .htaccess file. The root will also contain your other directories. There are a few other types of files you may want in the root such as robots.txt, favicon.ico and other standard files but it is inadvisable to place any other application code in the root.

### Controllers

The controllers directory is required. Controllers are placed in this directory. You can put sub-directories in the controllers directory and standard routing will pick up on it. Be for-warned that sub-directories take precident over controllers, so if you have a controller named foo and sub-directory named foo, you will not be able to access the controller foo through standard routing. It is not advisable to use this as way to mask controllers as it will only lead to confusion.

### Models

The models directory is required, and models are placed in this directory. Sub-directories are allowed, but they must be included manually when models are loaded (Load::model('subdirectory/model');).

### Views

Required, sub-directory. Call manually.

### Libraries

Not required, sub-directory. Call manually.

### Resources

Resources is not required, nor even references. It is a good practice to include a resources folder and then put sub-folder for things like scripts, css, images and any other files your MVC app may need. Because resources is a suggestion and not a standard, it can have another name, however, in the future this may become a standard directory.

### Permissions

It is recommended that you set the permissions on the controllers, models, views and libraries to 0700 - give the owner full permission and remove permission from all others. This will prevent users from browsing to these directories. PHP will still be able to include them in the index.php which is the only script that is run directly. It is recommended that you leave normal permissions on the root, utilities and resources.

## Execution Path

### Index.php

This technical overview covers the execution path of index.php in the root. Looking at the source code, you will see, very near the top that the execution is divided into URI::_class_init_(); and Route::execute(). The first part initilizes URI - a singleton. The second part executes the route, which includes evaluating which route will be called, constructing an instance of the controller object and calling the correct method.

### URI

The purpose of the URI class is to parse the query string based on whether a .htaccess file was used. Because _class_init_ is public and could be called from a controller, the entire execution block is wrapped in a check to make sure this has not run before. To determine if a .htaccess file was used, ($_ENV['REDIRECT_STATUS'] == 200) is used. If a .htaccess file was used, the $_GET data will not be correct. This is fixed by calling __resetGetArray() which will manually rebuild the GET data and store it in the global $_GET array. From here, the only difference between using a .htaccess file and not using one is where the REST data is stored. If we are using a .htaccess file, REST data will be in $_SERVER['REDIRECT_QUERY_STRING'], if not, it will be in $_SERVER['PATH_INFO'].

### Route

Just like URI, Routes::execute() is public and so the whole method is wrapped in a check to ensure that it is only executed once. The first thing that the execute method does is call the before methods in config and hook. After this, it evaluates which route to use.

Evaluation of coniditional routes is a rather inelegant process. GET, POST SESSION and URI are all checked against defined parameters. If at any point a non-match is found, we continue to the evaluating the next route. If we reach the bottom without encountering a non-match, that conditional route is returned. If we evaluate all conditional routes without finding a match, then we look for a standard route

Evaluating a standard route is simply checking that the file exists and that neither the folders, controller or method are prefixed with an underscore. If any of these conditions fail, the default route is returned. If everything does check out, the standard route is returned.

Now that we have a route, Route::execute() can finish. The controller file is included and an instance of the class is created. If the controller does not have the method specified by the route, we default to the index method. We now have all the data to execute the call. The last step is to call the Config::after() hook.

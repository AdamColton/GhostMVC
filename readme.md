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

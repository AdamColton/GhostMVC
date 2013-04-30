GhostMVC
========

GhostMVC is a lightweight MVC. The most basic version allows you to create Models, Views, Controllers and routing to map URI's to specific controller methods. GhostMVC also comes with the ability to load libraries to extend the functionality to include tools like templating and databases.

GhostMVC is largely modeled on the CodeIgniter framework with the goal of a simplier file structure and cleaner interface. It also adopts some concepts from the ASP.NET MVC.

Routes
======

Routes are the means by which a URI is translated into what controller to load and instantiate and which method of that controller to call. There are three means by which GhostMVC can arrive at that decision:
Conditional Routes -> Standard Routes -> Default Route

Conditional Routes
------------------

GhostMVC will first look through the conditional routes defined in the config.php file. A Conditional Route must define a URI. It may optionally include a controller, a method, required POST, GET and SESSION values and the arguments to pass. Required GET, POST and SESSION values are set by passing in the key values. GhostMVC will check is_set against all required keys. The URI defines the type of URIs that will match the route. Each segment can be an asterisk or a token. A token must match its segment exactly. An asterisk indicates that the segment is required. If these conditions are met, the routes controller and method will be called and passed any defined arguments.

Conditional Route Methods
-------------------------

Post
Get
Session
Controller
Method
ControllerMethod
Args
PostArgs
GetArgs


Examples
--------
'''php
Route::URI("*/login/*");
'''

Any URI with three or more segments where the middle segment is "login" will match this route. Because a controller and method are not defined, the default controller defined in config.php will be used, and the index method will be called.

Route::URI("metaData")
  ->ControllerMethod("AJAX", )
  ->PostArgs("url");

This matches any URI where the first segment is "metaData". It requires the $_POST array contain a value for "url". If these conditions are met, it will call the "_get_meta_data" method of the "AJAX" controller, and pass in "$_POST['url']" as an argument.

The other way to create a rounte is to use the Add method. This is less verbose, but may be desirable if you are using many routes. The following is the function signature for Add:
Route::Add(URI, Controller, Method, Args, Post, Get, Session);

The only required argument is URI. Also, take note that the last four arguments expect arrays. Here is the previous example using the Add method:
Route::Add("metaData", "test", "_get_meta_data", Array($_POST['url']), Array("url"));
Standard Routes

If GhostMVC does not find a Conditional Route whose conditions are met, it will try to call a Standard Route. The Standard Route is Controller/Method/Data. The controller can be a single segment for a controller that sits in the root of the Controllers folder, or it can be a path into subdirectories of the Controllers folder. Method will be a single segment. If method is not defined in the given URI, Index will be called. Anything after the Method segment is data and will not be used for routing. Controllers and Methods can be hidden from the Standard Router by prefixing them with an underscore. Hidden methods and controllers are still accessible to user defined routes and the Default route.
Default Route

If no Conditional Route or Standard Route is found, the Default route, which is defined in config.php will be called. In this case, everything in the URI can be treated as data or the condition can be treated as a 404.

This behavior can be good for giving a site default functionality. For instance, if you were writing a blog, the Default Route may perform a search. This way www.myblog.com/elephant would search the blog for the value "elephant" even though no "elephant" controller is defined.
Technical Notes

For any type of route, if the controller exists, but the specified method cannot be found, the index method will be called instead. This means that the index method, like the default route, can be used as a catch-all. It also means that if you mis-type a method name in a route, you will not throw an error, but will end up in the index method.
<?php

  include 'config.php';
  include 'hooks.php';

  URI::_class_init_();
  Route::execute();
  
  /* Class Route
   *
   * Handles all routing and the execution of the controller method.
   * Instances of this class represent user defined routes. When executing
   * if no user defined route is found, the default route will be taken
   */
  Class Route{
    private static $_routes;
    private static $_hasRun = False;
    private $_uri = '';
    private $_get = array();
    private $_post = array();
    private $_session = array();
    private $_controller = '';
    private $_method = '';
    private $_args = array();

    public static function execute(){
      if (!Route::$_hasRun){
        Hooks::before();
        $route = Route::getRoute();
        include ($route['controller']);
        $controllerName = Route::_getControllerName($route['controller']);
        $controller = new $controllerName;
        if (!method_exists($controller, $route['method'])) $route['method'] = 'index';
        call_user_func_array(array($controller, $route['method']), $route['args']);
        Hooks::after();
      }
    }
    
    private static function array_keys_exist(&$keys, &$array){
      foreach($keys as $key){
        if (!array_key_exists($key, $array)) return false;
      }
      return true;
    }

    public static function getRoute(){
      if (Route::$_routes == Null) Route::$_routes = array();
      Hooks::setConditionalRoutes();
      foreach(Route::$_routes as $route){
        if (!Route::array_keys_exist($route->_get, $_GET)) continue;
        if (!Route::array_keys_exist($route->_post, $_POST)) continue;
        if (!Route::array_keys_exist($route->_session, $_SESSION)) continue;
        //Check that route matches URI
        $uri = array_values(array_filter(explode('/', $route->_uri), 'strlen')); // Split on forward-slash
        $i = 0;
        $routeMatches = true;
        foreach($uri as $uriSegment){
          if ( !(($uriSegment == URI::segment($i)) || ($uriSegment == '*' && URI::segment($i) != '')) ){
            $routeMatches = False;
            break;
          }
          ++$i;
        }
        if (!$routeMatches) continue;
        if ($route->_controller == '' or $route->_method == ''){
          $standarRoute = Route::_standardRoute();
          if ($route->_controller == '') $route->_controller = $standarRoute['controller'];
          if ($route->_method == '') $route->_method = '_' .$standarRoute['method'];
        }
        return $route->routeArray();
      }
      return Route::_standardRoute();
    }

    private static function _standardRoute(){
      $route = array();
      $i = 0;
      $pathToController = 'controllers/';
      while(is_dir($pathToController . '/' . URI::segment($i)) && URI::segment($i) != '') {
        if ( substr(URI::segment($i),0,1) == '_') return Route::_defaultRoute();
        $pathToController .= URI::segment($i) . '/';
        ++$i;
      }
      if ( substr(URI::segment($i),0,1) == '_') return Route::_defaultRoute();
      $controllerName = URI::segment($i);
      if (URI::segment($i) == ''){
        $pathToController .= Config::HOME_CONTROLLER . '.php';
        $controllerName = Config::HOME_CONTROLLER;
      } else {
        $pathToController .= URI::segment($i) . '.php';
      }
      if (!file_exists($pathToController)) return Route::_defaultRoute();
      $route['controller'] = $pathToController;
      ++$i;
      if ( substr(URI::segment($i),0,1) == '_') return Route::_defaultRoute();
      $methodName = URI::segment($i);
      if ($methodName == '') $methodName = 'index';
      $route['method'] = $methodName;
      $route['args'] = array();
      return $route;
    }

    private static function _defaultRoute(){
      return array(
        'controller' => 'controllers/' . Config::DEFAULT_CONTROLLER . '.php',
        'method' => Config::DEFAULT_METHOD,
        'args' => Array()
      );
    }
    
    private function _getControllerName(&$controllerPath){
      preg_match('/\/(?<className>[a-zA-Z0-9_]+).php$/', $controllerPath, $matches);
      return $matches['className'];
    }
    
    private function Route(&$uri){
      $this->_uri = $uri;
      Route::$_routes[] = $this;
    }
    
    public static function URI($uri){
      $route = new Route($uri);
      return $route;
    }
    
    public function Get(){
      $this->_get = func_get_args();
      return $this;
    }
    
    public function Post(){
      $this->_post = func_get_args();
      return $this;
    }
    
    public function Session(){
      $this->_session = func_get_args();
      return $this;
    }
    
    public function ControllerMethod($controller, $method){
      $this->_method = $method;
      $this->_controller = $controller;
      return $this;
    }
    
    public function Args(){
      $this->_args = func_get_args();
      return $this;
    }
    
    public function PostArgs(){
      $this->_post = func_get_args();
      foreach($this->_post as $post){
        $this->_args[] = $_POST[$post];
      }
      return $this;
    }
    
    public function GetArgs(){
      $this->_get = func_get_args();
      foreach($this->_get as $get){
        $this->_args[] = $_GET[$get];
      }
      return $this;
    }
    
    public function routeArray(){
      if ($this->_controller == '') $route->_controller = 'controllers/' . Config::HOME_CONTROLLER . '.php';
      return array(
        'controller' => $this->_controller,
        'method' => $this->_method,
        'args' => $this->_args
      );
    }
  } //End Route
  
  /* Class Render
   *
   * A Collection of static functions for rendering views
   */
  class Render{
    private function Render(){ /* DOES NOT GET CALLED */}
    public static function view($viewName, $viewData = Null){
      // demux variables out of the viewdata
      if ($viewData != Null){
        foreach($viewData as $varName=>$varValue){
          if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $varName)){
            ${$varName} = $varValue;
          }
        }
      }
      include('views/' . $viewName . '.php');
    }
    public static function json($data){
      echo json_encode($data);
    }
    public static function mapView($viewName, $mapData){
      foreach($mapData as $viewData){
        Render::view($viewName, $viewData);
      }
    }
  }//End Render
  
  /* Class Load
   *
   * Static class responsible for loading resources
   */
  
  class Load{
    private function Load(){ /* DOES NOT GET CALLED */ }
    public static function model($modelName){
      include_once('models/' . $modelName . '.php');
    }
    
    public static function library($libraryName){
      include_once('libraries/' . $libraryName . '.php');
    }
  }//End Load
  
  /* Class URI
   *
   * Static class responsible for parsing the URI and making the data accessible
   */
  class URI{
    private function URI(){ /* DOES NOT GET CALLED */ }
    private static $uriArray = NULL;
    private static $uriString = NULL;
    public static function segment($i){
      return self::$uriArray[$i];
    }
    public static function full(){
      return self::$uriString;
    }
    public static function _class_init_(){
      if ($_ENV['REDIRECT_STATUS'] == 200){
        self::__resetGetArray(); // .htaccess file was used - $_GET needs to be fixed
        self::$uriString = $_SERVER['REDIRECT_QUERY_STRING'];
      } else {
        self::$uriString = $_SERVER['PATH_INFO'];
      }
      $getStrippedFromURI = explode('&', self::$uriString, 2);
      self::$uriArray = array_values(array_filter(explode('/', $getStrippedFromURI[0]), 'strlen'));
    }
    private static function __resetGetArray(){
      $queryString = explode('?', $_SERVER['REQUEST_URI'], 2);
      $queryString = explode('&', $queryString[1]);
      foreach($queryString as $keyValuePair){
        $keyValuePair = explode('=', $keyValuePair);
        $_GET[$keyValuePair[0]] = urldecode($keyValuePair[1]);
      }
    }
  }//End URI
?>
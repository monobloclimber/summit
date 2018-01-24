<?php
/**
 * Return true if the protocol used is https
 * @return boolean
 */
function isSecure(){
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
		return true;
	}else{
		return false;
	}
}

/**
 * Return the corresponding URL of the road name
 * @param  string $routeName
 * @param  array  $parameters
 * @return string
 */
function route($routeName, $parameters = []){
	if(isSecure()){
		$protocol = 'https://';
	}else{
		$protocol = 'http://';
	}
	return $protocol . $_SERVER['HTTP_HOST'] . '/' . \App::get()->router->url($routeName, $parameters);
}

/**
 * Return the corresponding URL of the asset file
 * @param  string $path
 * @return string
 */
function asset($path){
	if(isSecure()){
		$protocol = 'https://';
	}else{
		$protocol = 'http://';
	}
	return $protocol . $_SERVER['HTTP_HOST'] . '/assets/' . $path;
}

/**
 * Built and return the link
 * @param  string $url
 * @param  string $anchor
 * @param  array  $attributes
 * @return string
 */
function linkTo($url, $anchor, $attributes = []){
	$atts = [];
	foreach ($attributes as $key => $value) {
		$atts[] = $key . '=' . '"' . $value . '"';
	}
	$attributes = implode(' ', $atts);

	return '<a href="' . $url . '" ' . $attributes . '>' . $anchor . '</a>';
}

/**
 * Built and return the route link
 * @param  string $routeName
 * @param  string $anchor
 * @param  array  $parameters
 * @param  array  $attributes
 * @return string
 */
function linkToRoute($routeName, $anchor, $parameters = [], $attributes = []){
	$url = route($routeName, $parameters);
	return linkTo($url, $anchor, $attributes);
}

/**
 * Cutting a string according to limit planned
 * @param  string $string
 * @param  integer $limit
 * @param  string $end
 * @return string
 */
function strLimit($string, $limit, $end = '...'){
	if($limit > strlen($string)){
		$end = '';
	}
	$string = strip_tags($string);
	return mb_substr($string, 0, $limit, 'UTF-8') . $end;
};

/**
 * Redirect to the url with the error code
 * @param  string $url
 * @param  string $code
 * @return
 */
function redirect($url, $code = '302'){
	return header("Location: " . $url, true, $code);
}

/**
 * Redirect to the url of referer
 * @return
 */
function redirectBack(){
	if(isset($_SERVER['HTTP_REFERER']))
		return redirect($_SERVER['HTTP_REFERER'], 302);
}

/**
 * Dump the given variable and end execution of the script
 * @param  [type] $value
 * @return
 */
function dd($value){
	var_dump($value);
	die();
}

/**
 * Return the views path
 * @return string
 */
function viewsPath(){
	return ROOT . '/app/views/';
}

/**
 * Return the app path
 * @return string
 */
function appPath(){
	return ROOT . '/app/';
}

/**
 * Return the public path
 * @return string
 */
function publicPath(){
	return ROOT . '/public/';
}

/**
 * Return the 404 page if debug mode is false
 * @return
 */
function ifNoDebug404(){
	if(!DEBUG){
        $controller = new \App\Controller\AppController();
        return $controller->notFound();
    }
}

/**
 * Return a 404 error
 * @return
 */
function error404(){
	return header('HTTP/1.0 404 Not Found');
}

/**
 * Return a string encrypted by bcrypt
 * @param  string $string
 * @return string
 */
function cryptPwd($string){
	return password_hash($string, PASSWORD_BCRYPT);
}

/**
 * Return true if the string matches the encrypted string
 * @param  string $string
 * @param  string $hash
 * @return boulean
 */
function verifyPwd($string, $hash){
	return password_verify($string, $hash);
}

/**
 * Return current URL
 * @return string
 */
function currentUrl(){
	if(isSecure()){
		$protocol = 'https://';
	}else{
		$protocol = 'http://';
	}

	return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Return true if the request use AJAX
 * @return boolean
 */
function isAjax(){
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')){
		return true;
	}
	return false;
}

/**
 * Return an URI segment
 * @return string
 */
function segmentUri($segment){
	$segment = $segment - 1;
	$uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
	if(isset($uri[$segment])){
		$pos = strpos($uri[$segment], '?');
		if($pos === false){
			return $uri[$segment];
		}else{
			return substr($uri[$segment], 0, $pos);
		}
	}
	
	return false;
}

/**
 * Applies the htmlentities function
 * @param  string $string 
 * @return string
 */
function c($string){
	return htmlentities($string);
}
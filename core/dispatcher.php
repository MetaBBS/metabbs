<?php

// Usage :
// /free 
// => $routes = array('container'=>'metabbs', 'controller'=>'board', 'action'=>'index') ,$params => array('board_name'=>'free') 
// /free/new
// => $routes = array('container'=>'metabbs', 'controller'=>'board', 'action'=>'new') ,$params => array('board_name'=>'free') 
// /free/1
// => $routes = array('container'=>'metabbs', 'controller'=>'board', 'action'=>'view') ,$params => array('board_name'=>'free', id=> '1') 
// /free/1/edit
// => $routes = array('container'=>'metabbs', 'controller'=>'board', 'action'=>'edit') ,$params => array('board_name'=>'free', id=> '1') 
// /users => /metabbs/users/index
// /users/new => /metabbs/users/new
// /users/1 => /metabbs/users/1/view
// /users/1/edit => /metabbs/users/1/edit
// /admin/users => /admin/users/index
// /admin/users/new => /admin/users/new
// /admin/users/1 => /admin/users/1/view
// /admin/users/1/edit => /admin/users/1/edit

class Dispatcher {
	var $routes;
	var $params;
	var $parts;
	var $part_count = 0;

	function Dispatcher($uri = null) {
		if(empty($uri)) $uri = rtrim($_SERVER['PATH_INFO'], '/');
		$this->params = get_params(null, false, array('_GET', '_POST'));
		$this->routes = array('container'=>null, 'controller'=> null, 'action'=>null);
		$this->parts = explode('/',$uri, 5);
		$this->parts[0] = $uri;
		// part중에 공백이 있으면 뺀다.
		$this->part_count = count($this->parts) - 1;

		$this->left_route_map($this->parts, $this->part_count, $this->routes, $this->params);
	}

	// path => route
	function left_route_map($parts, $part_count, &$routes, &$params) {
		// reserverd container
		if (preg_match('/^\/(admin)/', $parts[0], $match)) {
			// default container		
			$routes['container'] = $match[1];
			if (isset($parts[2])) {
				// custom route mappings

				// default route mappings
				$routes['controller'] = isset($parts[2]) && !empty($parts[2]) ? $parts[2]:'index';
				if ($part_count > 4) {
					$params['id'] = $parts[3];
					$routes['action'] = isset($parts[4]) && !empty($parts[4]) ? $parts[4]:'view';
				} else {
					$routes['action'] = isset($parts[3]) && !empty($parts[3]) ? $parts[3]:'index';
				}
			} else {
				$routes['controller'] = 'index';
			}

		} else {
			// default container		
			$routes['container'] = 'metabbs';
			if (isset($parts[1])) {
				// custom route mappings
				$board = Board::find_by_name($parts[1]);
				if ($board->exists()) {
					$params['board_name'] = $parts[1];
					$parts[1] = isset($parts[2]) && is_numeric($parts[2]) ? 'post':'board'; 
				}

				// default route mappings
				$routes['controller'] = isset($parts[1]) && !empty($parts[1]) ? $parts[1]:'index';
				if ($part_count > 3) {
					$params['id'] = $parts[2];
					$routes['action'] = isset($parts[3]) && !empty($parts[3]) ? $parts[3]:'view';
				} else {
					$routes['action'] = isset($parts[2]) && !empty($parts[2]) ? $parts[2]:'index';
				}
			} else {
				$routes['controller'] = 'index';
			}
		}
	}
	// route => path
	function right_route_map($routes, $params) {
		$parts = array();

		// custom route mappings
		if ($routes['container'] == 'metabbs' 
			&& (isset($params['board_name']) || isset($this->params['board_name']))
			&& ($routes['controller'] == 'board' || $routes['controller'] == 'post')) {
			if (isset($params['board_name']) && !empty($params['board_name'])) {
				array_push($parts, $params['board_name']);
			} else {
				if (isset($this->params['board_name']) && !empty($this->params['board_name'])) {
					array_push($parts, $this->params['board_name']);
				}
			}
			if (isset($params['id']) && !empty($params['id']))
				array_push($parts, $params['id']);
			if (!empty($routes['action']) && !in_array($routes['action'],array('index', 'view')))
				array_push($parts, $routes['action']);
			return $parts;
		}


		// default route mappings
		if (!empty($routes['container'])) {
			// reserverd container
			if (in_array($routes['container'], array('admin')))
				array_push($parts, $routes['container']);

			if (!empty($routes['controller'])) {
				array_push($parts, $routes['controller']);
				if (isset($params['id']) && !empty($params['id']))
					array_push($parts, $params['id']);
				if (!empty($routes['action']))
					array_push($parts, $routes['action']);
			}
		}

		return $parts;
	}

	function url_for($routes = array(), $params = array()){
		$url = METABBS_BASE_URI;

		$out_routes = array();
		$out_params = array();

		// 1. container이 존재 안할 경우, metabbs
		$out_routes['container'] = isset($routes['container']) && !empty($routes['container']) ? $routes['container'] : 'metabbs';

		// 2. 컨트롤러이 존재 할 경우 액션, param는 기존 것으로
		if(isset($routes['controller']) && !empty($routes['controller'])) {
			$out_routes['controller'] = $routes['controller'];
		}

		// 3. 액션이 존재 할 경우 컨트롤러는 기존 것으로
		if(isset($routes['action']) && !empty($routes['action'])) {

			if(!isset($routes['controller']) || empty($routes['controller'])) {
				$out_routes['controller'] = $this->routes['controller'];
			}
			$out_routes['action'] = $routes['action'];
		}

		// 4. params이 존재 할 경우 액션, 컨트롤로는 기존 것으로
		if(count($params) > 0) {
			if(!isset($routes['controller']) || empty($routes['controller'])) {
				$out_routes['controller'] = $this->routes['controller'];
			}
			if(!isset($routes['action']) || empty($routes['action'])) {
				$out_routes['action'] = $this->routes['action'];
			}
			$out_params = $params;
		}

		return METABBS_BASE_URI . implode('/', $this->right_route_map($out_routes, $out_params)) . ($out_params ? $this->query_string_for($out_params):'');
	}


	function get_routes() {
		return $this->routes;
	}

	function get_params() {
		return $this->params;
	}

	function query_string_for($params) {
		$_params = array();

		foreach ($params as $key => $value) {
			if(!in_array($key, array('id', 'board_name')))
				$_params[] = "$key=$value";
		}
		return $_params ? '?' . implode('&', $_params) : '';
	}
}

function url_for_new($routes = array(), $params = array()){
	global $dispatcher;
	return $dispatcher->url_for($routes, $params);
}

function url_with_referer_for_new($routes = array(), $params = array()){
	global $dispatcher;
	$params['url'] = isset($params['url']) ? urlencode($params['url']) : urlencode($_SERVER['REQUEST_URI']);
	return $dispatcher->url_for($routes, $params);
}

/* DEBUG CODE */

//$dispatcher = new Dispatcher('/admin/free/new');
//var_dump($dispatcher->parts);
//var_dump($dispatcher->part_count);
//var_dump($dispatcher->routes);
//var_dump($dispatcher->params);
//var_dump($dispatcher->url_for());
?>

<?php
 namespace Fuel\Core;

abstract class Controller_Rest_Crud extends \Controller_Rest
{
	/**
	 * Abstract default get function
	 *
	 * @param INT $id ID of the element you want to get
	 *
	 * @return None
	 * @example
	 *  /rest/example/ OR /rest/example/32 OR rest/example/?foo=bar
	 * /example
	 */
    abstract public function get_index($id='');

	/**
	 * Abstract default post function
	 *
	 * @return None
	 * @example
	 *   POST to /rest/example/
	 * /example
	 */
    abstract public function post_index();

	/**
	 * Abstract default put function
	 *
	 * @param INT $id ID of the element you want to update, required
	 *
	 * @return None
	 * @example
	 *   PUT to /rest/example/32
	 * /example
	 */
    abstract public function put_index($id);

	/**
	 * Abstract default delete function
	 *
	 * @param INT $id ID of the element you want to delete, required
	 *
	 * @return None
	 * @example
	 *   DELETE to /rest/example/32
	 * /example
	 */
    abstract public function delete_index($id);

	/**
	 * Wrapper for response(), allows us to have a
	 * standard format for the response
	 *
	 * @param BOOL   $success whether the request was successful or not
	 * @param STRING $message string message to go with status
	 * @param INT    $total   the total number of results found
	 * @param MIXED  $data    The raw data
	 *
	 * @return None
	 * @example
	 *   $this->detailed_response(true,'Example Message',20,array('foo'=>'bar'));
	 * OR
	 *   $this->detailed_response(false,'Example Error',0,'');
	 * /example
	 */
	protected function detailed_response($success,$message,$total,$data)
	{
		$response = array();
		$response['success'] = $success;
		$response['message'] = $message;
		$response['total']   = $total;
		$response['data']    = $data;
		$this->response($response);
	}

	/**
	 * Helper function for getting OFFSET
	 *
	 * @return INT    the offset that should be started at
	 * @example
	 *   $this->page();
	 * /example
	 */
	protected function page()
	{
		return (\Input::get("page",1) -1) * $this->perpage();
	}

	/**
	 * Helper function get getting LIMIT
	 *
	 * @return INT    the limit that should be used
	 * @example
	 *  $this->perpage();
	 * /example
	 */
	protected function perpage()
	{
		return \Input::get("limit",20);
	}


    public function router($resource, array $arguments){
        array_unshift($arguments, $resource);
        \Config::load('rest', true);

        $pattern = '/\.(' . implode('|', array_keys($this->_supported_formats)) . ')$/';

        // Check if a file extension is used
        if (preg_match($pattern, $resource, $matches))
        {
            // Remove the extension from arguments too
            $resource = preg_replace($pattern, '', $resource);
            $arguments[count($arguments) - 1] = preg_replace($pattern, '', $arguments[count($arguments) - 1]);

            if($arguments[count($arguments) - 1] == ""){
                array_pop($arguments);
            }

            $this->format = $matches[1];
        }
        else
        {
            // Which format should the data be returned in?
            $this->format = $this->_detect_format();
        }

        //Check method is authorized if required
        if (\Config::get('rest.auth') == 'basic')
        {
            $valid_login = $this->_prepare_basic_auth();
        }
        elseif (\Config::get('rest.auth') == 'digest')
        {
            $valid_login = $this->_prepare_digest_auth();
        }

        //If the request passes auth then execute as normal
        if(\Config::get('rest.auth') == '' or $valid_login)
        {
            // Go to $this->method(), unless there are no args and it's a GET, in which case, go to $this->get_list();

			// default to {method}_index for restful service!
            $controller_method = strtolower(\Input::method()).'_index';
            $default_method = strtolower(\Input::method()).'_'.$resource;
            if(\Input::method() == "GET" && $resource == ""){

                // Add the page/search variables to the method where necessary
                $search = Input::get("search", FALSE);

                if($search !== FALSE && method_exists($this, "get_search")){
                    $controller_method = "get_search";

                    array_push($arguments, $search);
                }else if(method_exists($this, "get_list")){
                    $controller_method = "get_list";
                }
            }

            // check to see if default {Input::method()}_{resource} method exists
            if (method_exists($this, $default_method))
            {
                call_user_func_array(array($this, $default_method), $arguments);
            }
            // check to see controller method exists
            else if (method_exists($this, $controller_method))
            {
                call_user_func_array(array($this, $controller_method), $arguments);
            }
            else
            {
                $this->response->status = 404;
                return;
            }
        }
        else
        {
            $this->response(array('status'=>0, 'error'=> 'Not Authorized'), 401);
        }
    }

}
?>
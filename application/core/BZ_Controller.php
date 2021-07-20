<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class BZ_Controller extends REST_Controller {

    // var $sessionData;
    function __construct($config = 'rest') {
        parent::__construct($config);
        // $this->set_session();
    }


    function index_get() {
        $this->response(array('status' => 0, 'msg' => 'Invalid URL', 'data' => array()), \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    function index_post() {
        $this->response(array('status' => 0, 'msg' => 'Invalid URL', 'data' => array()), \Restserver\Libraries\REST_Controller::HTTP_OK);
    }
   
}

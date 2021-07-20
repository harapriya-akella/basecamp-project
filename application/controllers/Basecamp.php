<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Basecamp extends BZ_Controller {

	function __construct() {
        parent::__construct();
        $this->load->library('basecamp_api');
    }

    function projects_get(){
        $resp = array('code' => 0, 'message' => ERROR_MSG, 'data' => []);

        $orgID = 4929569;
        $url = "https://3.basecampapi.com/".$orgID."/projects.json";
        $result = $this->basecamp_api->get($url);
        // var_dump("expression", $result);
        $resp['code'] = 1;
        $resp['message'] = 'List of projects fetched';
        $resp['data'] = $result;

        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    function todolists_get(){
        $resp = array('code' => 0, 'message' => ERROR_MSG, 'data' => []);
        $orgID = 4929569;
        $projectID = 19932836;
        $todosetID = 3274471489;
        // https://3.basecampapi.com/4929569/buckets/19932836/todosets/3274471489/todolists.json
        $url = "https://3.basecampapi.com/".$orgID."/buckets/".$projectID."/todosets/".$todosetID."/todolists.json";
        $result = $this->basecamp_api->get($url);
        // var_dump("expression", $result);
        $resp['code'] = 1;
        $resp['message'] = 'List of todo list fetched';
        $resp['data'] = $result;

        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    function todo_get(){
        $resp = array('code' => 0, 'message' => ERROR_MSG, 'data' => []);
        $orgID = 4929569;
        $projectID = 19932836;
        $todolistID = 3284386453;
        // https://3.basecampapi.com/4929569/buckets/19932836/todosets/3274471489/todolists.json
        $url = "https://3.basecampapi.com/".$orgID."/buckets/".$projectID."/todolists/".$todolistID."/todos.json";
        
        $result = $this->basecamp_api->get($url);
        // var_dump("expression", $result);
        $resp['code'] = 1;
        $resp['message'] = 'List of todos';
        $resp['data'] = $result;

        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    function todocreate_get(){
        $resp = array('code' => 0, 'message' => ERROR_MSG, 'data' => []);
        $orgID = 4929569;
        $projectID = 19932836;
        $todolistID = 3284386453;
        // https://3.basecampapi.com/4929569/buckets/19932836/todosets/3274471489/todolists.json
        $url = "https://3.basecampapi.com/".$orgID."/buckets/".$projectID."/todolists/".$todolistID."/todos.json";

        // {"content":"Program it","description":"<div><em>Try that new language!</em></div>","due_on":"2020-12-11"}
        
        $result = $this->basecamp_api->post($url, array(
            'content' => "TODO-2", 
            "description" => "<div><em>Try that new language!</em></div>", 
            "due_on" => "2020-12-12"
        ));
        $resp['code'] = 1;
        $resp['message'] = 'Todo created';
        $resp['data'] = $result;

        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    function todotrash_get(){
        $resp = array('code' => 0, 'message' => ERROR_MSG, 'data' => []);
        $orgID = 4929569;
        $projectID = 19932836;
        $todoID = 3286942625;

        $url = "https://3.basecampapi.com/".$orgID."/buckets/".$projectID."/recordings/".$todoID."/status/trashed.json";
        
        $result = $this->basecamp_api->put($url);
        
        $resp['code'] = 1;
        $resp['message'] = 'Todo trashed';
        $resp['data'] = $result;

        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

}
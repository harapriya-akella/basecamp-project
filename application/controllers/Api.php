<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require getcwd(). '/vendor/autoload.php';
// var_dump(php_sapi_name());
// if (php_sapi_name() != 'cli') {
//     throw new Exception('This application must be run on the command line.');
// }
class Api extends BZ_Controller {

	function __construct() {
        parent::__construct();
        $this->load->library('custom');
        $this->load->library('basecamp_api');
        // $this->load->library('spreadsheet');
        $this->load->library('emailer');
        // $this->service = $service;
    }
    // google function 
    function getClient(){
        $client = new Google_Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        // $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
        $client->setAuthConfig(getcwd().'/credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
            // $client->setAccessToken($client->authenticate());
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
            // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

            // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            // var_dump("tokenPath", json_encode($client->getAccessToken()));
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    function check_attachment($id) {
        $url = "https://secure.concordnow.com/api/rest/1/agreement/".$id."/attachment";
        $result = $this->custom->get($url);
        $c = 0;
        if (isset($result->attachments) && count($result->attachments)) {
            $c = count($result->attachments);            
        }else {
            $c = 0;
        }
        return $c;
    }

    function get_agreement_details($uid) {
        $url = "https://secure.concordnow.com/api/rest/1/agreement/".$uid;
        $result = $this->custom->get($url);
        if (is_null($result)) {
            $this->get_agreement_details($uid);
        }else {
            return $result;            
        }
    }

    function check_agreement_in_local_db($uuid) {
        $this->db->select('*');
        $this->db->where("agreement_uuid", $uuid);
        $pt = $this->db->get('attachment')->result();
        return $pt;
    }

    function find_members($uid) {
        // $uid = "UZLrRD";
        // $uid = "BMgB19";
        $url = "https://secure.concordnow.com/api/rest/1/agreement/".$uid."/member";
        $result = $this->custom->get($url);
        $d = [];
        $f = "";
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $r) {
                if(isset($r->user)){
                    array_push($d, $r->user->name);                    
                }
            }
            $f = implode(", ", $d);
        }
        return $f;
    } 

    function agreements_email_get() {
        $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);

        $data['return_path'] = "no-reply@projectheena.com";
        $data['from'] = "Go Quest";
        $data['email'] = "harapriya.akella@gmail.com";
        $data['name'] = "harapriya";
        $data['subject'] = "test-subject"; 
        $data['html'] = "TEST";
        //die;
        $result =  $this->emailer->send_mail($data);
        $resp['code'] = 1;
        $resp['message'] = '';
        $resp['data'] = $result;
        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    function get_agreements() {
        // $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);
        $orgID = 394441;
        $status = 'SIGNING';
        $url = "https://secure.concordnow.com/api/rest/1/agreement/inbox";
        $result = $this->custom->get($url, array('organization' => $orgID));
        return $result;
        // $resp['code'] = 1;
        // $resp['message'] = '';
        // $resp['data'] = $result;
        // $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    function data_from_excel_get() {
        $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);

        // ***google function call***

        // Get the API client and construct the service object.
        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);

        // Prints the names and majors of students in a sample spreadsheet:
        // https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
        // $spreadsheetId = '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms'; SAMPLE
        //CONCORD SHEET SHARED BY CLIENT
        $spreadsheetId = '1MSsp6MsMI1x3KhZoiMb5r0m8fHEN-W7Nnwi4xZtUs2Y';

        $range = 'Sheet1!A2:B'; // Sheet1-> sheet_name
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();
        $data_array = [];

        if (empty($values)) {
            $resp['code'] = 1;
            $resp['message'] = 'No data found.';
            $resp['data'] = $data_array;
            die();
        } else {
            // print "Name, Gender:\n";
            foreach ($values as $row) {
                // Print columns A and E, which correspond to indices 0 and 4.
                // printf("%s, %s\n", $row[0], $row[1]);
                array_push($data_array, array('name' => isset($row[0]) ? $row[0] : "", 'assets' => isset($row[1]) ? $row[1] : ""));
            }
            $resp['code'] = 1;
            $resp['message'] = 'Date from '.$spreadsheetId;
            $resp['data'] = $data_array;
        }
        // ***google function call end***
        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    function agreement_fields($uid) {
        $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);
        $url="https://secure.concordnow.com/api/rest/1/agreement/".$uid."/summary/fields";
        $result = $this->custom->get($url);
        $fields = "";
        $fields_array = [];
        if (isset($result)){
            if (is_array($result->fields) && count($result->fields) > 0){
                foreach ($result->fields as $f) {
                    $field = $f->value."(".$f->name.")";
                    array_push($fields_array, $field);
                }
                $fields = implode(", " , $fields_array);
            }else {
                $fields = "";
            }
        }else {
            $fields = "";
        }
        return $fields;        
    }

    function agreement_fields_get() {
        $uid = "";
        $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);
        $url="https://secure.concordnow.com/api/rest/1/agreement/".$uid."/summary/fields";
        $result = $this->custom->get($url);
        // var_dump($result);        
        $fields = "";
        $fields_array = [];
        if (isset($result)){
            if (is_array($result) && count($result) > 0){
                foreach ($result as $f) {
                    $field = $f->value."(".$f->name.")";
                    array_push($fields_array, $field);
                }
                $fields = implode(", " , $fields_array);
            }else {
                $fields = "";
            }
        }else {
            $fields = "";
        }
        // var_dump($fields);        
    }

    function agreement_summary($uid) {
        // $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);
        $url="https://secure.concordnow.com/api/rest/1/agreement/".$uid."/summary";
        $result = $this->custom->get($url);
        if (is_null($result)) {
            $this->agreement_summary($uid);
        }else {
            return $result;                    
        }
    }

    function links($uid) {
        // $url="https://secure.concordnow.com/api/rest/1/agreement/J0ydMj/summary";
        // $url="https://secure.concordnow.com/api/rest/1/agreement/".$uid."/summary";
        $url = "https://secure.concordnow.com/api/rest/1/agreement/".$uid."/attachment";
        $result = $this->custom->get($url);
        $d = "";
        $data = [];
        // if (count($result->links) > 0) {
        //     foreach ($result->links as $v) {
        //         array_push($data, "https://secure.concordnow.com/#/agreement/".$v->target->uid);
        //         array_push($data, "https://secure.concordnow.com/#/agreement/".$v->owner->uid);
        //     }
        //     $d = implode(", ", $data);
        // }
        if (isset($result->attachments) && count($result->attachments) > 0) {
            foreach ($result->attachments as $att) {
                array_push($data, "https://secure.concordnow.com/#/agreement/".$att->uid);
            }
            $d = implode(", ", $data);
        }else {
            $d = "";
        }
        return $d;
    } 

    function check_sale_word_in_title($a) {
        if (preg_match('/\bSALE\b/', $a)) {
            $r = true;
        }else {
            $r = false;
        }
        return $r;
    } 

    function create_task_in_basecamp($orgID, $projectID, $todolistID, $data_make_task) {
        $url = "https://3.basecampapi.com/".$orgID."/buckets/".$projectID."/todolists/".$todolistID."/todos.json";
        $result = $this->basecamp_api->post($url, $data_make_task);
        // var_dump("expression", $result->id);
        if (is_null($result)) {
            $this->create_task_in_basecamp($orgID, $projectID, $todolistID, $data_make_task);
        }else {
            // var_dump("expression", $result->id);

            return $result->id;            
        }
    }

    function update_a_basecamp_task($orgID, $projectID, $todoID, $data_make_task) {
        $url = "https://3.basecampapi.com/".$orgID."/buckets/".$projectID."/todos/".$todoID."/.json";
        $result = $this->basecamp_api->put($url, $data_make_task);
        return true;
    } 
    // validations API
    function agreements_get(){
        $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);

        $date = mktime(0, 0, 0, date("m")  , date("d")+7, date("Y"));
        $due_date  = date("d-m-Y", $date);

        $orgID = 394441;
        $status = 'SIGNING';
        $url = "https://secure.concordnow.com/api/rest/1/user/me/organizations/".$orgID."/agreements";
        $result = $this->custom->get($url, array('statuses'=> $status));
        $items = $result->items;
        if (count($items) > 0) {
            foreach ($items as $i) {
                $missing_data = "";
                $ij = $this->check_attachment($i->uuid);
                $description = 'SIGNING STATUS AGREEMENT- '.$i->uuid;
                $content = $i->title;
                $uid = $i->uuid;
                $aggrement_details = $this->get_agreement_details($uid);
                $assignee_ids = 30573991;
                // assignee_ids 30573991 archana
                // assignee_ids 30461189 santosh
                // assignee_ids 33828458 hara
                $notify = true;
                $completion_subscriber_ids = 30573991;
                $summary = $this->agreement_summary($i->uuid);
                // var_dump("here", $i->uuid, json_encode($summary));
                
                $is_duration = isset($summary->lifecycle->calculatedEnd);

                $check_third_party = count($summary->signedWithLabels);
                $check_description = $summary->description;
                $check_title = $aggrement_details->metadata->title;
                $check_tags = count($aggrement_details->metadata->tags);

                $check_aggreement = $this->check_agreement_in_local_db($i->uuid);
                if (count($check_aggreement) > 0) { // check if agreement exists in local db
                    if ($check_aggreement[0]->is_email_sent == 0) { // check if email is sent
                        if ($ij == 0 ){ // check if flag of attachments is 0
                            // send email
                            $dataemail['return_path'] = "no-reply@projectheena.com";
                            $dataemail['from'] = "Go Quest";
                            $dataemail['email'] = "archana.darji@goquestmedia.com";
                            $dataemail['name'] = "Archana Darji";
                            $dataemail['subject'] = $content; 
                            $dataemail['html'] = "NO ATTACHMENT ATTACHED TO ".$description;
                            $result =  $this->emailer->send_mail($dataemail);
                            if ($result) {
                                $is_sent = 1;
                            }else {
                                $is_sent = 0;
                            }
                            // $check_aggreement[0]->id update this ID
                            $this->db->set('is_email_sent', $is_sent);
                            $this->db->where('id', $check_aggreement[0]->id);
                            $this->db->update('attachment');
                        }
                    }
                }
                if (count($check_aggreement) <= 0) { // agreements not present in local db
                    if ($ij > 0) {
                        $flag = 1;
                        $is_sent = 1;
                    }else {
                        $flag = 0;
                        // send email
                        $dataemail['return_path'] = "no-reply@projectheena.com";
                        $dataemail['from'] = "Go Quest";
                        $dataemail['email'] = "archana.darji@goquestmedia.com";
                        $dataemail['name'] = "Archana Darji";
                        $dataemail['subject'] = $content; 
                        $dataemail['html'] = "NEW AGREEMENT ".$description;
                        $result =  $this->emailer->send_mail($dataemail);
                        if ($result) {
                            $is_sent = 1;
                        }else {
                            $is_sent = 0;
                        }
                    }
                    $data['is_attachment']  = $flag;
                    $data['agreement_uuid']  = $i->uuid;
                    $data['is_email_sent']  = $is_sent;
                    $data['is_task_made']  = 0;
                    $data['created_on']  = date('1');
                    $data['updated_on']  = date('1');
                    $data_upload = $this->db->insert('attachment',$data);
                    // var_dump("data_upload", $data_upload);
                }
                if ($check_third_party <= 0) { // check third party
                    $missing_data .= "<p>Third Party</p>";
                } 
                if ($check_description == "" || !isset($check_description)) { // description
                    $missing_data .= "<p>Description</p>";
                } 
                if ($this->check_sale_word_in_title($check_title)) { // check title
                    $missing_data .= "<p>Title</p>";
                } 
                if (!$is_duration) { // duration life cycle
                    $missing_data .= "<p>Duration Lifecycle</p>";
                } 
                if ($check_tags <= 0) { // tags
                    $missing_data .= "<p>Tags</p>";
                }
                if (isset($missing_data) || $missing_data != "") {
                    // todo list ID 3464818492
                    // https://3.basecamp.com/4521377/buckets/19924163/todolists/3464818492
                    // https://3.basecamp.com/4521377/buckets/19924163/todosets/3273139779
                    $orgID = 4521377;
                    $projectID = 19924163;
                    $todolistID = 3464818492;

                    $is_attached = ($ij == 0) ? 'Available' : 'Missing';
                    $data_make_task = array(
                        "content" => $content, 
                        "description" => "<div>
                        <p>Contract Title: ".$content."</p>
                        <p>Contract Link: https://secure.concordnow.com/#/agreement/".$uid."</p>
                        <p>Missing Fields: ".$missing_data."</p>
                        <p>Attachment status: ".$is_attached."</p>
                        </div>", 
                        "due_on" => $due_date,
                        "assignee_ids" => $assignee_ids,
                        "notify" => $notify,
                        "completion_subscriber_ids" => $completion_subscriber_ids 
                    );
                    $check_aggreement = $this->check_agreement_in_local_db($i->uuid);
                    if (count($check_aggreement) > 0) {
                            // update local
                        if ($check_aggreement[0]->is_task_made == 0) {
                            $create_task = $this->create_task_in_basecamp($orgID, $projectID, $todolistID, $data_make_task);
                            $this->db->set('is_task_made', 1);
                            $this->db->set('task_id', $create_task);
                            $this->db->where('agreement_uuid', $i->uuid);
                            $this->db->update('attachment');
                        }else {
                            // update the task
                            $todoID = $check_aggreement[0]->task_id;
                            $update_task = $this->update_a_basecamp_task($orgID, $projectID, $todoID, $data_make_task);
                        }
                    }else {
                            // create
                        if ($ij > 0) {
                            $flag = 1;
                        }else {
                            $flag = 0;
                        }
                        $data['is_attachment']  = $flag;
                        $data['agreement_uuid']  = $i->uuid;
                        $data['is_email_sent']  = 0;
                        $data['is_task_made']  = 1;
                        $data['created_on']  = date('1');
                        $data['updated_on']  = date('1');
                        $data_upload = $this->db->insert('attachment',$data);
                    }
                }
            } 
            $resp['code'] = 1;
            $resp['message'] = 'Task done';
            $resp['data'] = [];
        }else {
            $resp['code'] = 1;
            $resp['message'] = 'No signed agreements found';
            $resp['data'] = [];
        }

        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }
    // update excel API
    function update_excel_get() {
        $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);

        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);

        //CONCORD SHEET SHARED BY CLIENT
        $spreadsheetId = '1MSsp6MsMI1x3KhZoiMb5r0m8fHEN-W7Nnwi4xZtUs2Y';
         // *****create new sheet*****
        $newdate = date("M jS");

        $R = $this->getSheetID($spreadsheetId);
        
        // **************************
        $range = $newdate.'!A3:O';  // TODO: Update placeholder value.

        $uidArray = [];

        $get_contracts = $this->get_agreements();
        $i = 1;
        
        if (isset($get_contracts) && count($get_contracts) > 0) {
            foreach ($get_contracts as $c) {
                $summary = $this->agreement_summary($c->uid);
                $v = isset($summary->signedWithLabels) && count($summary->signedWithLabels) > 0 ? implode(", ", $summary->signedWithLabels) : "";
                $m = $this->find_members($c->uid);
                $aggrement_count = $this->check_attachment($c->uid);
                $links = $this->links($c->uid);
                $fields = $this->agreement_fields($c->uid);
                if ($c->metadata->status == 'TEMPLATE') {
                    $doc = "TEMPLATE";
                }else if ($c->metadata->status == 'VALIDATION') {
                    $doc = "DRAFT";
                }else if ($c->metadata->status == 'NEGOTIATION') {
                    $doc = "REVIEW";
                }else if ($c->metadata->status == 'SIGNING') {
                    $doc = "SIGNING";
                }else if ($c->metadata->status == 'CONTRACT') {
                    $doc = "SIGNED";
                }else {
                    $doc = "";                    
                }
                array_push($uidArray, array(
                    $i++, // serial number
                    $c->uid, // document id
                    "https://secure.concordnow.com/#/agreement/".$c->uid, // document url
                    $c->metadata->title, // document title
                    date("d-m-Y h:i A", ($c->metadata->createdAt)/1000), // document created on 
                    $doc,
                    $v, // third party name
                    isset($c->metadata->description)?$c->metadata->description:"", // description 
                    (count($c->metadata->tags) > 0 ? implode(", ", $c->metadata->tags): ""), // tags
                    $links,// link to another document
                    isset($summary->lifecycle->calculatedEnd) ? date("d-m-Y", $summary->lifecycle->calculatedEnd/1000) : "",// duration 
                    $m,// shared between
                    $summary->lifecycle->signatureDateUnknown ? "" : date("d-m-Y", $summary->lifecycle->signatureDate/1000),// document signed on 
                    ($aggrement_count > 0) ? "YES" : "NO",// has attachments
                    $fields
                ));
            }
            $requestBody = new Google_Service_Sheets_ValueRange(array('values' => $uidArray));

            $params = ['valueInputOption' => 'RAW'];

            // $response = $service->spreadsheets_values->append($spreadsheetId, $range, $requestBody, $params, $majorDimension="ROWS"); 
            $response = $service->spreadsheets_values->update($spreadsheetId, $range, $requestBody, $params); 

            //***google append data end***
            //***update time on google sheet***
            $range_for_date = $newdate.'!A1';  // TODO: Update placeholder value.
            $uidArray_for_date = [];
            array_push($uidArray_for_date, array("Last Updated On- ".date("d/m/Y h:i A")));
            $requestBody_for_date = new Google_Service_Sheets_ValueRange(array('values' => $uidArray_for_date));
            $params_for_date = ['valueInputOption' => 'RAW'];
            $response_for_date = $service->spreadsheets_values->update($spreadsheetId, $range_for_date, $requestBody_for_date, $params_for_date);
            //***end update time on google sheet***
            $resp['code'] = 1;
            $resp['message'] = 'Date updated in '.$spreadsheetId;
            $resp['data'] = $response;
        }else {
            $resp['code'] = 1;
            $resp['message'] = 'No agreements found';
            $resp['data'] = [];
        }
        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }
    // move signed contracts to gqm archival folder
    function move_to_folder_get() {
        $agreementIDs = [];
        $get_contracts = $this->get_agreements();
        foreach ($get_contracts as $i) {
            if ($i->metadata->status == 'CONTRACT') {
                array_push($agreementIDs, $i->uid);
            }
        }
        // var_dump($agreementIDs);
        // $orgID = 394441;

        // $url = "https://secure.concordnow.com/api/rest/1/organization/".$orgID."/folders";
        // $result = $this->custom->get($url);
        // foreach ($result->children as $k) {
        //     var_dump(json_encode($k->name));
        // }
    }
    // automatic check-ins
    function automatic_check_ins_get() {
        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);
        $spreadsheetId  = "1k03nNWFREZwnKIpspUevSyzfYnYq1-ohQRDUFArMlhM";
        $sheetId= "864851280";
        $date = "22-02-2021";
        $arr = array("Names", date("d-M", strtotime("22-02-2021")));
        for ($i=0; $i < 38; $i++) { 
            $date = date("d-M", strtotime("-7 days",strtotime($date)));
            array_push($arr, $date);
        }
        // *******
          $range_for_header = 'Sheet2!A1';  // TODO: Update placeholder value.
          $uidArray_for_header = [];
          array_push($uidArray_for_header, $arr);
          $requestBody_for_header = new Google_Service_Sheets_ValueRange(array('values' => $uidArray_for_header));
          $params_for_header = ['valueInputOption' => 'RAW'];
          $response_for_header = $service->spreadsheets_values->update($spreadsheetId, $range_for_header, $requestBody_for_header, $params_for_header);
          // *******************************
          $backgroundChangeRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
            array(
                "requests" => array(
                    "repeatCell" => array(
                        "range" => array( 
                            "sheetId"=> $sheetId,
                            "startRowIndex"=> 0,
                            "endRowIndex"=> 1
                        ),

                        "cell" => array(
                            "userEnteredFormat" => array(
                                "backgroundColor"=> array(
                                    "red"=> 75,
                                    "green"=> 75,
                                    "blue"=> 75
                                ),
                                "horizontalAlignment" => "CENTER",
                                "textFormat" => array(
                                    "fontSize"=> 10,
                                    "bold"=> true
                                )

                            )
                        ),
                        "fields" => "userEnteredFormat(textFormat,horizontalAlignment, backgroundColor)"
                    )
                )
            ));
          $backgroundChangeResponse = $service->spreadsheets->batchUpdate($spreadsheetId, $backgroundChangeRequest);
          // **********************
          $freezeRowRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
            array(
                "requests" => array(
                   "updateSheetProperties" => array(
                    "properties" => array(
                        "sheetId" => $sheetId,
                        "gridProperties" => array(
                            "frozenRowCount"  =>1,
                            "frozenColumnCount"  =>1
                        )
                    ),
                    "fields" => "gridProperties.frozenRowCount, gridProperties.frozenColumnCount"
                )
               )
            )
        );
          $freezeRowResponse = $service->spreadsheets->batchUpdate($spreadsheetId, $freezeRowRequest);
          // **********************
          $orgID = 4521377;
          $projectID = 17194635;
          $questionID = 2689193844;
          $arr_name = [];
          $url= "https://3.basecampapi.com/".$orgID."/buckets/".$projectID."/questions/".$questionID."/answers.json";
          $result = $this->basecamp_api->get($url);
            // $d = date("l",strtotime($result[0]->updated_at));
          foreach ($result as $r) {
            array_push($arr_name, $r->creator->name);
        }
    }
    //after signed
    function signed_contract_get() {
        $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);
        $todaydate = date("d-m-Y");
        $uidArray = [];

        $get_contracts = $this->get_agreements();
        $i = 1;

        if (isset($get_contracts) && count($get_contracts) > 0) {
            foreach ($get_contracts as $c) {
                $summary = $this->agreement_summary($c->uid);
                $signed_on = $summary->lifecycle->signatureDateUnknown ? "" : date("d-m-Y", $summary->lifecycle->signatureDate/1000);
                if ($c->metadata->status == 'CONTRACT' && $signed_on == $todaydate) {
                    array_push($uidArray, array(
                        "document_id"=>$c->uid, // document id
                        "url"=>"https://secure.concordnow.com/#/agreement/".$c->uid, // document url
                        "title"=>$c->metadata->title, // document title
                        "created_on"=>date("d-m-Y h:i A", ($c->metadata->createdAt)/1000), // document created on 
                        "status"=>"SIGNED",
                        "signed_on"=>$signed_on,// document signed on 
                    ));
                }
            }

            // ************************************
            $heading = "<p>Hi,</p><p>Please check the following data</p>";
            $description = "<table width='100%' border='1' cellpadding='8' cellspacing='0' bordercolor='#CCCCCC'><tr align='CENTER'><th>Contract Title</th><th>Contract Link</th><th>Contract Status</th><th>Signed On</th></tr>";
            $body=""; 
            $enddescription = "</table><p>Thank you.</p>";
            foreach ($uidArray as $key) {
                $body .= "<tr align='CENTER'><td>".$key['title']."</td><td>".$key['url']."</td><td>".$key['status']."</td><td>".$key['signed_on']."</td></tr>";
            }
            if (count($uidArray) > 0) {
                $email_body = $heading.$description.$body.$enddescription;
            }else {
                $email_body = "<p>Hi,</p><p>No contracts were signed today.</p><p>Thank you.</p>";
            }
            $dataemail['return_path'] = "no-reply@projectheena.com";
            $dataemail['from'] = "GoQuest Media";
            $dataemail['email'] = "harapriya.akella@gmail.com,santosh.kumar@goquestmedia.com";
            $dataemail['name'] = "Harapriya Akella";
            $dataemail['subject'] = "Contracts Signed today- ".$todaydate; 
            $dataemail['html'] = $email_body;
            $result =  $this->emailer->send_mail($dataemail);
            // ************************************

            $resp['code'] = 1;
            $resp['message'] = 'Got '.count($uidArray).' Signed contracts.'.$result ? "Email sent." : "Email not sent.";
            $resp['data'] = $uidArray;
        }else {
            $resp['code'] = 1;
            $resp['message'] = 'No agreements found';
            $resp['data'] = [];
        }
        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    function send_test_mail_get() {
        $dataemail['return_path'] = "no-reply@goquestmedia.com";
        $dataemail['from'] = "GoQuest Media";
        $dataemail['email'] = "harapriya.akella@gmail.com";
        $dataemail['name'] = "Harapriya Akella";
        $dataemail['subject'] = "Testing SMTP"; 
        $dataemail['html'] = "<p>Hi User</p>";
        $result =  $this->emailer->send_mail($dataemail);
        var_dump(json_encode($result));
    }

    function check_monday_get() {
        $dateToday = date("d-M");
        $resp = array('code' => 0, 'message' => ERROR_MSG, 'data' => []);

        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);
        $sheetId= "1899468968";
        $spreadsheetId = "1k03nNWFREZwnKIpspUevSyzfYnYq1-ohQRDUFArMlhM";

        $sheetInfo = $service->spreadsheets->get($spreadsheetId);
        $sheet_info = $sheetInfo['sheets'];

        $id = array_column($sheet_info, 'properties');

        function myArrayContainsWord(array $myArray, $word) {
            foreach ($myArray as $element) {
                if ($element->title == $word) {
                        // var_dump(json_encode($element));
                        return $element->gridProperties->columnCount; // found
                    }
                }
            return false; // not found
        }
        if (myArrayContainsWord($id, "Sheet2")) { // not found
            $columnCount = myArrayContainsWord($id, "Sheet2");
        }
        $numberToLetter = function(int $columnCount)
        {
            if ($columnCount <= 0) return null;

            $temp; $letter = '';
            while ($columnCount > 0) {
                $temp = ($columnCount - 1) % 26;
                $letter = chr($temp + 65) . $letter;
                $columnCount = ($columnCount - $temp - 1) / 26;
            }
            return $letter;
        };
        $existingColumnName = $numberToLetter($columnCount);

        $rangesForAllData = [
            "Sheet2!A2:".$existingColumnName
        ];
        $paramsForAllData = array(
            'ranges' => $rangesForAllData
        );
        $resultForAllData = $service->spreadsheets_values->batchGet($spreadsheetId, $paramsForAllData);
        $allValues = $resultForAllData->getValueRanges()[0]->values;

        $rangesForLatestDate = [
            "Sheet2!B1",
        ];
        $paramsForLatestDate = array(
            'ranges' => $rangesForLatestDate
        );
        $resultForLatestDate = $service->spreadsheets_values->batchGet($spreadsheetId, $paramsForLatestDate);
        $allValues = $resultForAllData->getValueRanges()[0]->values;
        $latestDate = $resultForLatestDate->getValueRanges()[0]->values;
        $dateToCheck = date("d-M", strtotime("+7 days",strtotime($latestDate[0][0])));
        // var_dump($dateToCheck, $dateToday);
        if ($dateToCheck == $dateToday) {
            $changeTo = $numberToLetter($columnCount+1);

            $rangesForHeader = [
                "Sheet2!1:1"
            ];
            $paramsForHeader = array(
                'ranges' => $rangesForHeader
            );
            $resultForHeader = $service->spreadsheets_values->batchGet($spreadsheetId, $paramsForHeader);
            // printf("%d ranges retrieved.", count($result->getValueRanges()));
            $headerValues = $resultForHeader->getValueRanges()[0]->values;
            array_splice( $headerValues[0], 1, 0, $dateToCheck ); // splice in at position 3
            // ----------
            $range_for_header = 'Sheet2!A1';  // TODO: Update placeholder value.
            $uidArray_for_header = [];
            array_push($uidArray_for_header, $headerValues[0]);
            $requestBody_for_header = new Google_Service_Sheets_ValueRange(array('values' => $uidArray_for_header));
            $params_for_header = ['valueInputOption' => 'RAW'];
            $response_for_header = $service->spreadsheets_values->update($spreadsheetId, $range_for_header, $requestBody_for_header, $params_for_header);
            // ----------
            // *********************
            $orgID = 4521377;
            $projectID = 17194635;
            $questionID = 2689193844;

            $getaquestion = "https://3.basecampapi.com/".$orgID."/buckets/".$projectID."/questions/".$questionID.".json";
            $getaquestionResult = $this->basecamp_api->get($getaquestion);
            $total_answers = $getaquestionResult->answers_count;

            $total_pages = intval(3+round(($total_answers-95)/100));
            // geared pagination; 1= 15; 2= 30; 3 = 50; 4 = 100 and so on;
            $names_arr  = array();
            $name_date_arr = array();
            for ($i=0; $i < 9; $i++) { 
                $url= "https://3.basecampapi.com/".$orgID."/buckets/".$projectID."/questions/".$questionID."/answers.json?page=".$i;
                $result = $this->basecamp_api->get($url);
                foreach ($result as $r) {
                    if (date("d-M", strtotime($r->created_at)) == $dateToCheck) {
                        array_push($names_arr, $r->creator->name);
                    }
                }
            }
            $dataToAdd = array();
            // *********************
            foreach ($allValues as $a) {
                if (in_array($a[0], $names_arr)) {
                    array_splice( $a, 1, 0, "Yes" );
                }else {
                    array_splice( $a, 1, 0, "No" );
                }
                array_push($dataToAdd, $a);
            }

            $existingColumnNameNew = $numberToLetter($columnCount+1);
            // ***************************************************
            $rangeToAdd = 'Sheet2!A2:'.$existingColumnNameNew;
            $requestBody = new Google_Service_Sheets_ValueRange(array('values' => $dataToAdd));
            $params = ['valueInputOption' => 'RAW'];
            $response = $service->spreadsheets_values->update($spreadsheetId, $rangeToAdd, $requestBody, $params); 
            // ***************************************************

            $resp['code'] = 1;
            $resp['message'] = "No of entries- ".count($allValues);
            $resp['data'] = $allValues;
        }else {
            $resp['code'] = 1;
            $resp['message'] = "Monday data up to date.";
            $resp['data'] = [];
        }
        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    } 

    function getSheetID($spreadsheetId) {
        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);

        function myArrayContainsWord(array $myArray, $word) {
            foreach ($myArray as $element) {
                if ($element->title == $word) {
                        return $element->sheetId; // found
                    }
                }
            return false; // not found
        }
        $sheetInfo = $service->spreadsheets->get($spreadsheetId);
        $sheet_info = $sheetInfo['sheets'];

        $date = date("M jS");

        $id = array_column($sheet_info, 'properties');

        if (!myArrayContainsWord($id, $date)) {
            $body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
                'requests' => array(
                    'addSheet' => array(
                        'properties' => array(
                            'title' => $date
                        )
                    )
                )
            ));
            $getAllSheets = $service->spreadsheets->batchUpdate($spreadsheetId,$body);
            // ****
            $sheetID = $getAllSheets["replies"][0]["addSheet"]["properties"]["sheetId"];
        } else {
            $sheetID = myArrayContainsWord($id, $date);
        }
        // return $sheetID;
        return $this->sheetDecoration($spreadsheetId, $sheetID);
    }

    function sheetDecoration($spreadsheetId, $sheetId) {
        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);
        $date = date("M jS");
        // *******
          $range_for_header = $date.'!A2';  // TODO: Update placeholder value.
          $uidArray_for_header = [];
          array_push($uidArray_for_header, array("#", "CONTRACT ID",  "DOCUMENT URL",    "DOCUMENT TITLE",  "DOCUMENT CREATED ON", "STAGE",   "THIRD PARTY NAME",    "DESCRIPTION", "TAGS",    "LINK TO ANOTHER DOCUMENT",    "DURATION",    "SHARED BETWEEN",  "DOCUMENT SIGNED ON",  "HAS ATTACHMENTS FIELDS"));
          $requestBody_for_header = new Google_Service_Sheets_ValueRange(array('values' => $uidArray_for_header));
          $params_for_header = ['valueInputOption' => 'RAW'];
          $response_for_header = $service->spreadsheets_values->update($spreadsheetId, $range_for_header, $requestBody_for_header, $params_for_header);
            // *******
          $moveSheetRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
            array(
                "requests" => array(
                 "updateSheetProperties" => array(
                    "properties" => array(
                        "sheetId" => $sheetId,
                        'index' => 0
                    ),
                    "fields" => "index"
                )
             )
            )
        );
          $moveSheetResponse = $service->spreadsheets->batchUpdate($spreadsheetId, $moveSheetRequest);

          $backgroundChangeRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
            array(
                "requests" => array(
                    "repeatCell" => array(
                        "range" => array( 
                            "sheetId"=> $sheetId,
                            "startRowIndex"=> 1,
                            "endRowIndex"=> 2
                        ),

                        "cell" => array(
                            "userEnteredFormat" => array(
                                "backgroundColor"=> array(
                                    "red"=> 75,
                                    "green"=> 75,
                                    "blue"=> 75
                                ),
                                "horizontalAlignment" => "CENTER",
                                "textFormat" => array(
                                    "fontSize"=> 10,
                                    "bold"=> true
                                )

                            )
                        ),
                        "fields" => "userEnteredFormat(textFormat,horizontalAlignment, backgroundColor)"
                    )
                )
            ));
          $backgroundChangeResponse = $service->spreadsheets->batchUpdate($spreadsheetId, $backgroundChangeRequest);

          $cellSizeRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
            array(
                "requests" => array(
                    "updateDimensionProperties"=> array(
                        "range"=> array(
                          "sheetId"=> $sheetId,
                          "dimension"=> "COLUMNS",
                          "startIndex"=> 0,
                          "endIndex"=> 15
                      ),
                        "properties"=>array(
                          "pixelSize"=> 200
                      ),
                        "fields"=> "pixelSize"
                    ),
                )
            )
        );
          $cellSizeResponse = $service->spreadsheets->batchUpdate($spreadsheetId, $cellSizeRequest);

          $freezeRowRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
            array(
                "requests" => array(
                   "updateSheetProperties" => array(
                    "properties" => array(
                        "sheetId" => $sheetId,
                        "gridProperties" => array(
                            "frozenRowCount"  =>2
                        )
                    ),
                    "fields" => "gridProperties.frozenRowCount"
                )
               )
            )
        );
          $freezeRowResponse = $service->spreadsheets->batchUpdate($spreadsheetId, $freezeRowRequest);

          return true;
      }

      function get_all_todo_get() {
        $url = "https://3.basecampapi.com/4521377/buckets/19924163/todosets/3273139779/todolists.json";
        $result = $this->basecamp_api->get($url);
        var_dump(json_encode($result));
    }

    function new_token_get() {
        $resp = array('code' => 0, 'data' => [],'message' => ERROR_MSG);
        $this->db->select("*");
        $token = $this->db->get("token")->row();
        // get_without_header
        $url = "https://launchpad.37signals.com/authorization/token?type=refresh&refresh_token=".$token->refresh_token."&client_id=758756b743b0ff7af2d8f2e4f464ba7f0208cc4e&redirect_uri=https://secure.concordnow.com/&client_secret=8b93b1406785ce9b21dceaa45750eb385b50fc44";
        $result = $this->basecamp_api->post_without_header($url);
        $access_token = $result->access_token;


        $this->db->set('access_token', $access_token);
        $this->db->where('id', 1);
        $this->db->update('token');

        $url = "https://3.basecampapi.com/4521377/buckets/19924163/todosets/3273139779/todolists.json";
        $result = $this->basecamp_api->get($url);

        $resp['code'] = 1;
        $resp['message'] = $access_token;
        $resp['data'] = json_encode($result);
        
        $this->response($resp, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }
}

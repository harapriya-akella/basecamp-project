<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Basecamp_api {
    public function post($url, $params = array()) {

        $authKey = "";
        $countParams = count($params);
        // $params = http_build_query($params,'&');
        $params = json_encode($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $countParams);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->get_token());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data);
    }

    public function get($url, $params = array()) {
        $authKey = "";
        $params = http_build_query($params, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->get_token());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data);
    }

    public function put($url, $params = array()) {
        // var_dump("expression", $url);

        $authKey = "";
        $params = http_build_query($params, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->get_token());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);

        curl_close($ch);
        return json_decode($data);
    }

    public function get_token() {
        $CI =& get_instance();
        $CI->db->select('*');
        $pt = $CI->db->get('token')->row();
        $token  = $pt->access_token;
        $auth_header = array(
            'Authorization: Bearer '.$token,
            'Accept: application/json',
            'User-Agent: Concord_New (https://secure.concordnow.com)',
            'Content-Type: application/json',
        );
        return $auth_header;
    }

    public function post_without_header ($url, $params = array()) {
        $authKey = "";
        $countParams = count($params);
        $params = json_encode($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $countParams);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data);
    }
}
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Custom {

    public function post($url, $params = array()) {
        $authKey = "";
        $countParams = count($params);
        // $params = http_build_query($params,'&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $countParams);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-API-KEY: WU8bc8NbQNmnJHZEZpGE/w',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data);
    }

    public function get($url, $params = array()) {
        $authKey = "WU8bc8NbQNmnJHZEZpGE/w";
        $params = http_build_query($params, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-API-KEY: '.$authKey,
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data);
    }

}

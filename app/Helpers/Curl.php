<?php 

if (!function_exists('postCurl')) {
    function postCurl($url = '', $params = null, $token = null)
    {
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);

        curl_close($ch);
        
        $data = json_decode($output);
        
        return $data;
    }
}
<?php
class Yhat {
    function Yhat($username, $apikey, $env) {
        $this->username = $username;
        $this->apikey = $apikey;

        $env = preg_replace('/^http:\/\//', '', $env);
        $env = preg_replace('/\/$/', '', $env);
        $this->env = $env;

        $url = "http://$env/verify?username=$username&apikey=$apikey";
        $options = array(
            "http" => array(
                "header" => "Content-type: application/json",
                "method" => "POST"
            )
        );
        $context = stream_context_create($options);

        try {        
            $result = file_get_contents($url, false, $context);
        } catch (Exception $e) {
            throw new Exception("Could not connect to host: $env");
        }

        try {
            $result = json_decode($result,true);
        } catch (Exception $e) {
            throw new Exception("Invalid response from host: $env");
        }        

        if($result["success"] != "true"){
            throw new Exception("Invalid username/apikey!");
        }
    }

    function predict($modelname, $data) {
        $env = $this->env;
        $username = $this->username;
        $apikey = $this->apikey;

        try {
            $data = json_encode($data);
        } catch (Exception $e) {
            throw new Exception("Could not convert data to JSON");
        }

        $auth = base64_encode("$username:$apikey");
        $url = "http://$env/$username/models/$modelname/";
        $options = array(
            "http" => array(
                "header" => "Content-Type: application/json\r\n".
                    "Authorization: Basic $auth\r\n",
                "method" => "POST",
                "content" => $data
            )
        );

        $context = stream_context_create($options);
        try {
            $result = file_get_contents($url, false, $context);
        } catch (Exception $e) {
            throw new Exception("Could not connect to endpoint: $url");
        }
        try {
            return json_decode($result,true);
        } catch (Exception $e) {
            throw new Exception("Invalid response from endpoint: $result");
        }
    }
}

?>

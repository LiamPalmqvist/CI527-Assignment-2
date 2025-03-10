<?php
class MyAPI {
    
    public function handleRequest() {
        
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "POST":
                // Read in the request body as a string
                // and parse it to get the object associated with it
                parse_str(file_get_contents('php://input'), $data);
                $data = (object)$data;
    
                // REGEX denoting alphanumeric or "_" with length between 4 and 16 (inclusive)
                $exp = "/^[a-zA-Z0-9_]{4,16}+$/m";
    
                // Match the source & target to the regular expression
                $valid = boolval(preg_match($exp, $data->target) && preg_match($exp, $data->source));
                
                // depending on the validity, respond with a HTTP code
                http_response_code($valid ? 200 : 400);
    
                // Send a header with content type back
                header("Content-type: application/json; charset=UTF-8");
                
                // This is what sends the JSON back
                echo json_encode($valid ? '' : '');
    
                // exit to stop anything more from being sent
                exit();
            case "GET":
                echo "GET METHOD DONE";
                // Read all information requested and return in JSON
                break;
            default:
                break;
        }

    }
}
$api = new MyAPI();
$api->handleRequest();
?>
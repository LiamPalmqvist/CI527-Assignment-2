<?php
class MyAPI {
    
    private $logins;
    
    function __construct() {
        $this->logins = json_decode(file_get_contents("passwords"), false);
    }

    public function createTable() {
        
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
            return 500;
        }
        echo "Connected successfully";

        // otherwise, create table and commit
        $query = "CREATE TABLE IF NOT EXISTS users( 
            id INT(11) NOT NULL AUTO_INCREMENT, 
            username VARCHAR(16) NOT NULL, 
            PRIMARY KEY (id)
        );";
        $conn->query($query);
        $conn->commit();
        
        $query = "CREATE TABLE IF NOT EXISTS messages( 
            id INT(11) NOT NULL AUTO_INCREMENT, 
            message VARCHAR(255) NOT NULL, 
            source INT(11) NOT NULL, 
            target INT(11) NOT NULL, 
            PRIMARY KEY (id),
            FOREIGN KEY (source) REFERENCES users(id),
            FOREIGN KEY (target) REFERENCES users(id)
        );";
        $conn->query($query);
        
        $conn->commit();

        return 200;
    }

    public function insertToTable() {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
            return 500;
        }
        // echo "Connected successfully";

        // Add users to the database
        $conn->query("INSERT INTO users (username) VALUES ('LP')");
        $conn->query("INSERT INTO users (username) VALUES ('MP')");
        
        // Database rows begin from 1
        $conn->query("INSERT INTO messages (message, source, target) VALUES('Hello, World!', 1, 2)");

        $info = [200, $conn->insert_id];
        $conn->close();
        return $info;
    }

    public function queryTable() {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
            return 500;
        }
        // echo "Connected successfully";

        // Fetch all messages from connected database table
        $row = $conn->query("SELECT * FROM messages")->fetch_assoc();
        $from = $conn->query("SELECT username FROM users WHERE " . $row['source'] . " = id LIMIT 1")->fetch_object()->username;
        $to = $conn->query("SELECT username FROM users WHERE " . $row['target'] . " = id LIMIT 1")->fetch_object()->username;


        $conn->close();
        return 200;
    }

    public function getUserMessages($userID) {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
            return 500;
        }
        // echo "Connected successfully";

        // fetch all messages from user with id $userID
        $messages = $conn->query("SELECT * FROM messages WHERE source = " . $userID)->fetch_all();
    

        for ($i=0; $i < count($messages); $i++) { 
            echo $messages[$i][1];
        }

        $conn->close();

        return 200;
    }

    public function dropTable() {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);
        
        // if connection fails, kill
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
            return 500;
        }
        // echo "Connected successfully";

        // otherwise, drop table and commit
        $conn->query("DROP TABLE messages");
        $conn->query("DROP TABLE users");
        $conn->commit();

        $conn->close();
        return 200;
    }

    public function handleRequest() {
        
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "POST":

                // Read in the request body as a string
                // and parse it to get the object associated with it
                parse_str(file_get_contents('php://input'), $data);
                $data = (object)$data;
                
                // Check if all required information exists
                if (!property_exists($data, "source") || !property_exists($data, "target") || !property_exists($data, "message")) {
                    http_response_code(400);
                } else if (strlen((string)$data->source) <= 0 || strlen((string)$data->target) <= 0 || strlen((string)$data->message) <= 0) {
                    http_response_code(400);
                }
                // REGEX denoting alphanumeric or "_" with length between 4 and 16 (inclusive)
                $exp = "/^[a-zA-Z0-9_]{4,16}+$/m";
    
                // Match the source & target to the regular expression
                $valid = boolval(preg_match($exp, $data->target) && preg_match($exp, $data->source));
                if (!$valid) {
                    http_response_code(400);
                } else {
                    $httpCode = $this->insertToTable();
    
                    // depending on the validity, respond with a HTTP code
                    http_response_code($httpCode[0]);
        
                    // Send a header with content type back
                    header("Content-type: application/json; charset=UTF-8");
                    
                    // This is what sends the JSON back
                    echo $httpCode[0] == 200 ? '{"id":'.json_encode($httpCode[1], JSON_PRETTY_PRINT).'}' : '';    
                }
                // exit to stop anything more from being sent
                exit();
            case "GET":
                // Check for information in body
                parse_str(file_get_contents('php://input'), $data);
                $data = (object)$data;

                // use regex to validate the information and check

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
//$api->dropTable();
//$api->createTable();
//$api->insertToTable();
//$api->queryTable();
//$api->getUserMessages(1);
?>
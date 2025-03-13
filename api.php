<?php
class MyAPI {
    
    private $logins;
    
    function __construct() {
        $this->logins = json_decode(file_get_contents('passwords'), false);
    }

    public function createTable() {
        
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
            return 500;
        }
        echo 'Connected successfully';
        
        $query = 'CREATE TABLE IF NOT EXISTS messages( 
            id      INT(11)         NOT NULL    AUTO_INCREMENT, 
            message VARCHAR(255)    NOT NULL, 
            date    DATETIME        NOT NULL,
            source  VARCHAR(11)         NOT NULL, 
            target  VARCHAR(11)     NOT NULL, 
            PRIMARY KEY(id)
        );';
        $conn->query($query);
        
        $conn->commit();

        return 200;
    }

    public function insertToTable($source, $target, $message) {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
            return 500;
        }
        // echo 'Connected successfully';

        // Database rows begin from 1
        $conn->query("INSERT INTO messages (message, date, source, target) VALUES('$message', '" . date('Y-m-d H:i:s', time()) . "', '$source', '$target')");

        $info = [200, $conn->insert_id];
        $conn->close();
        return $info;
    }

    public function queryTable() {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
            return 500;
        }
        // echo 'Connected successfully';

        // Fetch all messages from connected database table
        $row = $conn->query('SELECT * FROM messages')->fetch_assoc();
        $from = $conn->query('SELECT username FROM users WHERE ' . $row['source'] . ' = id LIMIT 1')->fetch_object()->username;
        $to = $conn->query('SELECT username FROM users WHERE ' . $row['target'] . ' = id LIMIT 1')->fetch_object()->username;


        $conn->close();
        return 200;
    }

    public function getMessagesFromUser($username) {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
            return [500, ""];
        }
        // echo 'Connected successfully';

        // Fetch messages from users with associated ids
        $messages = $conn->query("SELECT * FROM messages WHERE source = '$username'")->fetch_all();
        if ($messages == null) {
            $conn->close();
            return [204, ""];
        }

        $data = [200, $messages];

        $conn->close();

        return $data;
    }

    public function getMessagesToUser($username) {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
            return [500, ""];
        }
        // echo 'Connected successfully';

        // Fetch messages from users with associated ids
        $messages = $conn->query("SELECT * FROM messages WHERE target = '$username'")->fetch_all();
        if ($messages == null) {
            $conn->close();
            return [204, ""];
        }

        $data = [200, $messages];

        $conn->close();

        return $data;
    }

    public function getMessagesFromUserToUser($fromUsername, $toUsername) {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);

        // if connection fails, kill
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
            return [500, ""];
        }
        // echo 'Connected successfully';

        // Fetch messages from users with associated ids
        $messages = $conn->query("SELECT * FROM messages WHERE source = '$fromUsername' AND target = '$toUsername'")->fetch_all();

        if ($messages == null) {
            $conn->close();
            return [204, ""];
        }
        $data = [200, $messages];

        $conn->close();

        return $data;
    }

    public function dropTable() {
        // Connect using server name, username and password
        $conn = new mysqli($this->logins->servername, $this->logins->username, $this->logins->password, $this->logins->database);
        
        // if connection fails, kill
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
            return 500;
        }
        // echo 'Connected successfully';

        // otherwise, drop table and commit
        $conn->query('DROP TABLE messages');
        $conn->query('DROP TABLE users');
        $conn->commit();

        $conn->close();
        return 200;
    }

    public function handleRequest() {
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':

                // Read in the request body as a string
                // and parse it to get the object associated with it
                parse_str(file_get_contents('php://input'), $data);
                $data = (object)$data;
                
                // Check if all required information exists
                if (!property_exists($data, 'source') || !property_exists($data, 'target') || !property_exists($data, 'message')) {
                    http_response_code(400);
                } else if (strlen((string)$data->source) <= 0 || strlen((string)$data->target) <= 0 || strlen((string)$data->message) <= 0) {
                    http_response_code(400);
                }
                // REGEX denoting alphanumeric or '_' with length between 4 and 16 (inclusive)
                $exp = '/^[a-zA-Z0-9_]{4,16}+$/m';
    
                // Match the source & target to the regular expression
                $valid = boolval(preg_match($exp, $data->target) && preg_match($exp, $data->source));
                if (!$valid) {
                    http_response_code(400);
                } else {
                    $httpCode = $this->insertToTable($data->source, $data->target, $data->message);
    
                    // depending on the validity, respond with a HTTP code
                    http_response_code($httpCode[0]);
        
                    // Send a header with content type back
                    header('Content-type: application/json; charset=UTF-8');
                    
                    // This is what sends the JSON back
                    echo $httpCode[0] == 200 ? '{"id":'.json_encode($httpCode[1], JSON_PRETTY_PRINT).'}' : '';    
                }
                // exit to stop anything more from being sent
                exit();
            case 'GET':
                // Check for information in body
                $data = (object)$_GET;

                // regex for valid values
                $exp = '/^[a-zA-Z0-9_]{4,16}+$/m';

                // validate the data is present and filled
                $source = property_exists($data, 'source') ? (!empty($data->source) ? true : false) : false;
                $target = property_exists($data, 'target') ? (!empty($data->target) ? true : false) : false;
                

                // Check which properties exist
                if (!$source && !$target) {
                    http_response_code(400);
                    return;
                } else if ($source && $target) {
                    if (!preg_match($exp, $data->source) || !preg_match($exp, $data->target)) {
                        http_response_code(400);
                        return;
                    }
                    // Get sent from source to target
                    $results = $this->getMessagesFromUserToUser($data->source, $data->target);
                } else if ($source && !$target) {
                    if (!preg_match($exp, $data->source)) {
                        http_response_code(400);
                        return;
                    }
                    // Get sent from source
                    $results = $this->getMessagesFromUser($data->source);
                } else if (!$source & $target) {
                    if (!preg_match($exp, $data->target)) {
                        http_response_code(400);
                        return;
                    }
                    // Get sent to target
                    $results = $this->getMessagesToUser($data->target);
                } else {
                    http_response_code(400);
                }
                
                if ($results[0] != 200) {
                    http_response_code($results[0]);
                    return;
                }

                if (count($results[1]) == 0) {
                    http_response_code(204);
                    return;
                }

                $messages = '{"messages": [';

                // Fixes issue with trailing comma on last entry
                for ($i=0; $i < count($results[1]); $i++) { 
                    $messages = $messages . '{"id":' .json_encode($results[1][$i][0], JSON_PRETTY_PRINT) . ','
                    . '"sent":' .json_encode($results[1][$i][2], JSON_PRETTY_PRINT) . ','
                    . '"source":' .json_encode($results[1][$i][1], JSON_PRETTY_PRINT) . ','
                    . '"target":' .json_encode($results[1][$i][3], JSON_PRETTY_PRINT) . ','
                    . '"message":' .json_encode($results[1][$i][4], JSON_PRETTY_PRINT) . '}';
                    if ($i != count($results[1])-1) {
                        $messages = $messages . ',';
                    }
                }

                echo $messages . ']}';
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
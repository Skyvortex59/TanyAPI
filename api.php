<?php
require_once('./db/db.php');

class Api {
    private $db1;
    private $db2;
    protected $response;
    private $nodeJsPort; // Nouvelle variable pour stocker le port de l'application Node.js

    public function __construct() {
        $this->db1 = new DbConnect();
        $this->db2 = new DbConnect();
        $this->nodeJsPort = null;
        $this->response = array();
    }

    protected function logRequest($requestData) {
        $logDirectory = 'log';
        if (!is_dir($logDirectory)) {
            mkdir($logDirectory);
        }

        $logFile = $logDirectory . '/request_log.txt';
        $logData = date('Y-m-d H:i:s') . ': ' . json_encode($requestData) . PHP_EOL;
        file_put_contents($logFile, $logData, FILE_APPEND);
    }
    
    public function processRequest() {
        header("Content-Type:application/json");

        $requestData = array_merge($_GET, $_POST);

        $this->logRequest($requestData);

        if (isset($requestData['data']) && $requestData['data'] != "") {
            $password = $requestData['data'];

            $this->handleData($password);
        } elseif (isset($requestData['chapter']) && $requestData['chapter'] != "") {
            $filename = $requestData["chapter"];

            $this->handleChapter($filename);
        } elseif (isset($requestData['oeuvre']) && $requestData['oeuvre'] != "") {
            $oeuvrename = $requestData["oeuvre"];

            $this->handleOeuvre($oeuvrename);
        } elseif (isset($requestData['dlsite']) && $requestData['dlsite'] != "") {
            $dlCode = $requestData["dlsite"];

            $this->handleDLsite($dlCode);
        } elseif (isset($requestData['dlsite_nodeJS']) && $requestData['dlsite_nodeJS'] != "") {
            $dlCode = $requestData["dlsite_nodeJS"];

            $this->handleDLsite($dlCode, true);
        } elseif (isset($requestData['port']) && $requestData['port'] != "") {
            $this->handleNodeResponse($requestData['port']);
        } else {
            $this->response['code'] = false;
            $this->response['message'] = "Invalid Request";
            $this->response['body'] = $requestData;
            $this->response['request_methode'] = $_SERVER['REQUEST_METHOD'];
        }

        $json_response = json_encode($this->response);
        echo $json_response;
    }

    protected function handleData($password) {
        $check = $this->db1->getDb('api_rest')->prepare("SELECT password FROM pass WHERE password=:password");
        $check->execute(array(':password' => $password));
        $data = $check->fetch(PDO::FETCH_ASSOC);
        $row = $check->rowCount();

        if ($row == 1) {
            $this->response['code'] = true;
        } else {
            $this->response['code'] = false;
            $this->response['message'] = "No Record Found";
        }
    }

    protected function handleChapter($filename) {
        $check = $this->db2->getDb("tanya")->prepare("SELECT chapterName FROM sftpchapter WHERE chapterName=:filename");
        $check->execute(array(':filename' => $filename));
        $data = $check->fetch(PDO::FETCH_ASSOC);
        $row = $check->rowCount();

        if ($row == 1) {
            $this->response['code'] = true;
        } else {
            $this->response['code'] = false;
            $this->response['message'] = "No Record Found";
        }
    }

    protected function handleOeuvre($oeuvrename) {
        $check = $this->db2->getDb("tanya")->prepare("SELECT oeuvreName FROM sftpoeuvre WHERE oeuvreName=:oeuvrename");
        $check->execute(array(':oeuvrename' => $oeuvrename));
        $data = $check->fetch(PDO::FETCH_ASSOC);
        $row = $check->rowCount();


        switch ($row) {
            case 1:
                $this->response['code'] = true;
                break;
            case 0:
                break;
            default:
                $this->response['code'] = false;
                $this->response['message'] = "No Record Found";
                break;
        }
    }

    protected function handleDLsite($dlCode, $after = false) {
        switch ($after) {
            case false:
                $nodeJSUrl = "http://localhost:3000"; // Remplacez 3000 par le port utilisé par votre serveur Node.js
                $url = $nodeJSUrl . "/endpoint"; // Remplacez "/endpoint" par le chemin de l'API sur votre serveur Node.js

                $data = array(
                    'code' => $dlCode
                );

                $options = array(
                    'http' => array(
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'content' => http_build_query($data)
                    )
                );

                $context  = stream_context_create($options);
                $result = file_get_contents($url, false, $context);
                $rsp = true;
                break;
            case true:
                break;
        }

        // Traitement de la réponse du serveur Node.js si nécessaire

        // $this->response["code"] = true;
        switch ($rsp) {
            case true:
                $this->response['code'] = true;
                break;
            default:
                $this->response['code'] = false;
                $this->response['message'] = "No Record Found";
                break;
        }
    }

    protected function handleNodeResponse($port) {
        // Enregistrez le port de l'application Node.js
        $this->nodeJsPort = $port;

        // Vous pouvez effectuer d'autres actions en fonction de la réponse de l'application Node.js ici

        // Répondez à l'application Node.js pour confirmer la réception de la valeur
        $this->response['code'] = true;
        $this->response['message']= 'Port received successfully';

        
    }
}


class DataApi extends Api {
    public function __construct() {
        parent::__construct();
    }

    public function processRequest() {
        parent::handleData($_REQUEST['data']);
        $json_response = json_encode($this->response);
        echo $json_response;
    }
}

class ChapterApi extends Api {
    public function __construct() {
        parent::__construct();
    }

    public function processRequest() {
        parent::handleChapter($_REQUEST['chapter']);
        $json_response = json_encode($this->response);
        echo $json_response;
    }
}

class OeuvreApi extends Api {
    public function __construct() {
        parent::__construct();
    }

    public function processRequest() {
        parent::handleOeuvre($_REQUEST['oeuvre']);
        $json_response = json_encode($this->response);
        echo $json_response;
    }
}

class PortApi extends Api {
    public function __construct() {
        parent::__construct();
    }

    public function processRequest() {
        parent::handleNodeResponse($_REQUEST['port']);
        
        $json_response = json_encode($this->response);
        echo $json_response;
        echo json_encode($_REQUEST);
        echo json_encode($_SERVER['REQUEST_METHOD']);
    }
}

class DLSiteApi extends Api {
    public function __construct() {
        parent::__construct();
    }

    public function processRequest() {
        parent::handleDLsite($_REQUEST['port']);

        $json_response = json_encode($this->response);
        echo $json_response;
    }
}

class DLSiteNodeJSApi extends Api {
    public function __construct() {
        parent::__construct();
    }

    public function processRequest() {
        // Votre logique de traitement pour la requête "dlsite_nodeJS"
        // Par exemple, récupérer le code DLSite et effectuer des opérations spécifiques
        // ...

        $json_response = json_encode($this->response);
        echo $json_response;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['data'])) {
        $api = new DataApi();
    } elseif (isset($_GET['chapter'])) {
        $api = new ChapterApi();
    } elseif (isset($_GET['oeuvre'])) {
        $api = new OeuvreApi();
    } elseif (isset($_GET['port']) && $_GET['port'] != "") {
        $api = new PortApi();
    } elseif (isset($_GET['dlsite']) && $_GET['dlsite'] != "") {
        $api = new DLSiteApi();
    } elseif (isset($_GET['dlsite_nodeJS']) && $_GET['dlsite_nodeJS'] != "") {
        $api = new DLSiteNodeJSApi();
    } else {
        $api = new Api();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['data'])) {
        $api = new DataApi();
    } elseif (isset($_POST['chapter'])) {
        $api = new ChapterApi();
    } elseif (isset($_POST['oeuvre'])) {
        $api = new OeuvreApi();
    } elseif (isset($_POST['port']) && $_POST['port'] != "") {
        $api = new PortApi();
    } elseif (isset($_POST['dlsite']) && $_POST['dlsite'] != "") {
        $api = new DLSiteApi();
    } elseif (isset($_POST['dlsite_nodeJS']) && $_POST['dlsite_nodeJS'] != "") {
        $api = new DLSiteNodeJSApi();
    } else {
        $api = new Api();
    }
}

$api->processRequest();
?>

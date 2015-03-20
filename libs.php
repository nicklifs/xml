<?php

/**
 * Class Connection
 *
 * This class contains methods for working
 * with mysql database use dbo interface
 */
    class Connection {
        /**
         * A private variable
         *
         * @var string stores the name of host
         */
        private $hostname;
        /**
         * @var string stores the name of the database server
         */
        private $database;
        /**
         * @var string stores the name of user
         */
        private $user;
        /**
         * @var string stores the password of user
         */
        private $password;
        /**
         * @var string stores the current connection to server
         */
        public $conn;

        /**
         * @param string $hostname
         * @param string $database
         * @param string $user
         * @param string $password
         */
        function __construct($hostname = 'p15021.mysql.ihc.ru', $database = 'p15021_db', $user = 'p15021_db', $password = 'qaz12345') {
            $this->hostname = $hostname;
            $this->database = $database;
            $this->user = $user;
            $this->password = $password;
        }

        /**
         * Execute query and return result
         *
         * @param $query stores query
         * @return result query
         */
        public function Execute($query){
            if (!$this->conn) return;
            return $this->conn->query($query);
        }

        /**
         * Get last insert ID
         *
         * @return last inserted ID
         */
        public function getLastInsertID()
        {
            if (!$this->conn) return;
            return $this->conn->lastinsertId();
        }

        /**
         * Create connection with db
         */
        public function Connect( ){
            if ($this->conn) return;
            try {
                $this->conn = new PDO("mysql:host=$this->hostname;dbname=$this->database", $this->user , $this->password);
            } catch(PDOException $e) {
                echo 'ERROR: ' . $e->getMessage();
            }
        }

        /**
         * Close connection with db
         */
        public function Disconnect( ){
            if ($this->conn) $this->conn = null;
        }
    }

/**
 * Class Upload
 *
 * This class contains methods for upload files,
 * parsing and saving in database
 */
    class Upload
    {
        /**
         * @param $conn stores the current connection to server
         * @param $filename stored the name of file which needs upload to server
         */
        function __construct($conn, $filename) {

            $dom = new domDocument("1.0", "utf-8"); // Create XML-document version 1.0 with encoding utf-8
            $dom->load("php://input"); // Load the XML-document from a file in a DOM object
            $root = $dom->documentElement; // Get the root element

            $rootName = '\''.$root->localName.'\'';
            $conn->Execute("INSERT INTO `nodes` VALUES (null, $rootName,null,1,null)");
            $idRoot = $conn->getLastInsertID();

            $fileName = '\''. $filename .'\'';
            $conn->Execute("INSERT INTO `files` VALUES (null, $fileName, $idRoot)");

            $this->find($root, $idRoot, $conn);
        }

        /**
         * @param $par stored DOMNode
         * @param $parID stored ID DOMNode in database
         * @param $conn stores the current connection to server
         */
        public function find($par, $parID, $conn)
        {
            $childs = $par->childNodes;
            for ($i = 0; $i < $childs->length; $i++) {
                $elem = $childs->item($i);

                if ($elem->nodeType != 1 ) continue;

                $name =  '\'' . $elem->localName . '\'' ;

                if ($elem->childNodes->length > 1)   { $value = "''";  $hasChilds = 1; }
                else {  $value = '\'' . $elem->nodeValue . '\'';    $hasChilds = 0; }

                $conn->Execute("INSERT INTO `nodes` VALUES (null, $name, $value,$hasChilds, $parID)");
                $idPar = $conn->getLastInsertID();

                if ($elem->hasChildNodes())     $this->find($elem,$idPar, $conn);

                $attr = $elem->attributes;
                $n = $attr->length;

                for ($j = 0; $j < $n; $j++) {
                    $name =  '\'' . $attr->item($j)->nodeName . '\'' ;
                    $value = '\'' . $attr->item($j)->nodeValue . '\'';

                    $conn->Execute("INSERT INTO `attrs` VALUES (null, $name, $value, $idPar)");
                }
            }
        }
    }

/**
 * Class View
 *
 * This class contains methods for view last 5 uploading files
 * and display content of root selected file
 */
    class View
    {
        /**
         * @var stores id root element
         */
        private $idRoot;

        /**
         * @param $conn stores the current connection to server
         * @param $id stores id of selected file
         * @return list last files
         */
        public function getListFiles($conn, $id)
        {
            $res = $conn->Execute("SELECT * FROM files ORDER BY id DESC LIMIT 5");
            $prov = 0;
            $response = "";
            while ($row = $res->fetch(PDO::FETCH_NUM)){
                if ($row[0] == $id or (!$id and !$prov) ) { $class = 'class="active"'; $this->idRoot = $row[2]; $prov = 1; $id = $row[0];  }
                else $class = "";
                $response .= "<li $class style='margin: 10px;'>";
                $response .=  "<a style='padding:5px;' href='/view.php?id=$row[0]'>#$row[0] - $row[1]</a> <img src='images/loader.gif' alt='loader'/>";
                $response .=  '</li>';
            }
            return $response;
        }

        /**
         * @param $conn stores the current connection to server
         * @return content of root selected file
         */
        public function getListNodes($conn)
        {
            $response = "";
            if ($this->idRoot) {
                $response .= '<li class="styless"><span class="h-children">Корень</span></li>';
                $res1 = $conn->Execute("SELECT * FROM nodes WHERE id_par=$this->idRoot");
                while ($row = $res1->fetch(PDO::FETCH_NUM)){
                    $response .= "<li>";
                    $response .= "<a href='/node.php?id=$row[0]'>$row[1]</a> <img src='images/loader.gif' alt='loader'/>";
                    $response .= '</li>';
                }
            }
            return $response;
        }
    }

/**
 * Class Nodes
 *
 * This class contains methods for view content
 * (attrs, childNodes, value) of selected node
 */
    class Nodes
    {
        /**
         * @param $conn stores the current connection to server
         * @param $id stores id of selected node
         * @return list attrs
         */
        public function getListAttrs($conn, $id)
        {
            $res = $conn->Execute("SELECT * FROM attrs WHERE id_node = $id");

            $prov = false;
            $response = "";
            while ($row = $res->fetch(PDO::FETCH_NUM)){
                if (!$prov) {
                    $response .= '<ul class="styless"><li><span class="h-attribute">Атрибуты</span></li>';
                    $prov = true;
                }
                $response .= "<li class='value'>$row[1] = $row[2] </li>";
            }
            $response .= '</ul>';
            return $response;
        }

        /**
         * @param $conn stores the current connection to server
         * @param $id stores id of selected node
         * @return list childNodes and value
         */
        public function getChildsAndValue($conn, $id)
        {
            $res = $conn->Execute("SELECT * FROM nodes WHERE id = $id");
            $row = $res->fetch(PDO::FETCH_NUM);
            $hasChilds = $row[3];

            $response = "";
            if ($hasChilds) {
                $res = $conn->Execute("SELECT * FROM nodes WHERE id_par = $id");
                $response .= '<ul><li class="styless"><span class="h-children">Дети</span></li>';
                while ($row = $res->fetch(PDO::FETCH_NUM)){
                    $response .= "<li>";
                    $response .= "<a href='/node.php?id=$row[0]'>$row[1]</a> <img src='images/loader.gif' alt='loader'/>";
                    $response .= '</li>';
                }
            }
            else {
                $res = $conn->Execute("SELECT * FROM nodes WHERE id = $id");
                $row = $res->fetch(PDO::FETCH_NUM);
                if ($row[2]) {
                    $response .= "<ul class='styless'><span class='h-value'>Значение</span>";
                    $response .= "<li class='value'>$row[2]</li>";
                }
            }
            $response .= '</ul>';

            return $response;
        }
    }
?>

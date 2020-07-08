<?php
function getParam($name, $default = '')
{
    if (isset($_REQUEST[$name])) {
        return $_REQUEST[$name];
    } else {
        return $default;
    }
}

function checkPassword($username, $password, $database){
    $error = $database->getError(); //$error is null if there was no problem connecting
    if (!$error){
        $checkVal = $database->selectRecordWhere('password', 'users', 'username', $username);
        if ($password == md5($checkVal)){
            return true;
        }
    }
    echo $database->getError();
    return false;
}


//configure database info. Normally store this in a config file
//define("DB_HOST", "localhost");
//define("DB_USER", "root");
//define("DB_PASS", "");
//define("DB_NAME", "book_library");

define("DB_HOST", "localhost");
define("DB_USER", "barringt_librarian");
define("DB_PASS", "someSuperStrongPw");
define("DB_NAME", "barringt_book_library");

class Database{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbName = DB_NAME;
    private $dbHandler; // stores the PDO object if connection was successful
    public $error; //stores error message if there was one
    private $statement; //stores a statement prepared by the query method

    public function __construct(){
        $connString = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName;
        // Set options
        $options = [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        // Create a new PDO instance
        try{
            $this->dbHandler = new PDO($connString, $this->user, $this->pass, $options);
            $this->statement = $this->dbHandler->prepare('');
        }
        catch(PDOException $e){
            $this->error = $e->getMessage();
        }
    }

    public function getError(){
        return $this->error;
    }
    public function setQuery($query){
        $this->statement = $this->dbHandler->prepare($query);
    }

    // $param: the placeholder value that we will be using in our SQL statement example: name
    // $value: the actual value that we want to bind to the placeholder, example: “John Smith”
    // $type: the datatype of the parameter, example: string.
    public function bind($param, $value, $type = null){
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        //statement is a PDO statement object
        $this->statement->bindValue($param, $value, $type);
    }

    public function execute(){
        try{
            return $this->statement->execute();
        }
        catch(PDOException $e){
            $this->error = $e->getMessage();
        }

    }

    public function getRecords($fetchStyle=PDO::FETCH_NUM){
        $this->execute();
        return $this->statement->fetchAll($fetchStyle);
    }

    public function getRecord($fetchStyle=PDO::FETCH_NUM){
        $this->execute();
        return $this->statement->fetch($fetchStyle);
    }

    public function selectAll($table, $fetchStyle=PDO::FETCH_NUM){
        $this->setQuery("select * from $table;");
        $this->execute();
        return $this->statement->fetchAll($fetchStyle);
    }

    public function insert($cols, $values, $table){
        $colString = implode(',', $cols); //create string of column names

        //steps to create string of variable names for use with the prepared statement
        $valueVars = [];
        $len = sizeof($values);
        for($i = 0; $i < $len; $i++){
            $valueVars[$i] = ':' . $i;
        }
        $valueVarsString = implode(',', $valueVars);
        $q = "insert into $table ($colString) values($valueVarsString)";
        $this->setQuery($q);
        //loop and bind valueVars to values
        for ($i = 0; $i < $len; $i++){
            $this->bind($valueVars[$i], $values[$i]);
        }

        return $this->execute();
    }

    public function update(){
        //todo:implement db class update method
    }

    public function delete(){
        //todo:implement db class delete method
    }

    //returns single record as a string
    public function selectRecordWhere($col, $table, $whereCol, $clause){
        //using proper prepared statement for security
        $this->setQuery("select :col from :table where :whereCol = :clause");
        $this->bind(':col', $col);
        $this->bind(':table', $table);
        $this->bind(':whereCol', $whereCol);
        $this->bind(':clause', $clause);

        //todo: move try catch to execute method
        try{
            $this->setQuery("select $col from $table where $whereCol = '$clause'");
            return $this->getRecord(PDO::FETCH_NUM)[0];
        }
        catch(PDOException $e){
            $this->error = $e->getMessage();
            return $this->error;
        }
    }

}
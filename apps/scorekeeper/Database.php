<?php

//for mySQL
//define('DB_HOST', 'localhost');
//define('DB_USER', 'root');
//define('DB_PASS', 'password');
//define('DB_NAME', 'sakila');
//define('PORT', 82);

//for dev environment mariaDB in xamp
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spadesscorekeeper');
define('PORT', 3307);

//for production environment
//define('DB_HOST', 'localhost');
//define('DB_USER', 'barringt_librarian');
//define('DB_PASS', 'someSuperStrongPw');
//define('DB_NAME', 'barringt_book_library');


//source: https://culttt.com/2012/10/01/roll-your-own-pdo-php-class/
//abstracts all the commonly used database queries, handles error checking
class Database{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbName = DB_NAME;
    private $port = PORT;
    private $dbHandler; // stores the PDO object if connection was successful
    private $error; //stores the PDOException object if there was an exception at some point
    private $statement; //stores a statement prepared by the query method

    public function __construct(){
        $connString ="mysql:host=$this->host;dbname=$this->dbName;port=$this->port";
        $options = [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        try{
            $this->dbHandler = new PDO($connString, $this->user, $this->pass, $options);
            $this->statement = $this->dbHandler->prepare('');
        }
        catch(PDOException $e){
            $this->error = $e;
        }
    }

    public function getError(){
        return $this->error;
    }

    public function getErrorMessage(){
        if ($this->error){
            return $this->error->getMessage();
        }
        return '';
    }

    public function setQuery($query){
        try{
            $this->error = null;
            $this->statement = $this->dbHandler->prepare($query);
            return true;
        }
        catch (PDOException $e){
            $this->error = $e;
            return false;
        }

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
            $this->error = null; //dump any previous error
            return $this->statement->execute();
        }
        catch(PDOException $e){
            //$this->error = $e->getMessage();
            $this->error = $e;
            return false;
        }

    }



    //////////////////////////
    // higher level methods //
    //////////////////////////
    // todo: add query to getRecord, getRecords, and getColumn
    public function getRecords($fetchStyle=PDO::FETCH_NUM){
        $this->execute(); //execute handles possible exception and stores the error
        return $this->statement->fetchAll($fetchStyle);
    }

    public function getRecord($fetchStyle=PDO::FETCH_NUM){
        $this->execute();
        return $this->statement->fetch($fetchStyle);
    }

    //returns 1d array
    public function getColumn(){
        $this->execute();
        //return $this->statement->fetch($fetchStyle);
        $result = $this->statement->fetchAll(PDO::FETCH_NUM);

        $singleDArray = [];
        foreach ($result as $item) {
            array_push($singleDArray, $item[0]);
        }

        return $singleDArray;
    }

    //returns 2d array
    public function selectAll($table, $fetchStyle=PDO::FETCH_NUM){
        $this->setQuery("select * from $table;");
        $this->execute();
        return $this->statement->fetchAll($fetchStyle);
    }

    //returns single record as a string
    public function selectColWhere($col, $table, $whereCol, $clause){
        //using proper prepared statement for security
        $this->setQuery("select :col from :dbTable where :whereCol = :clause");
        $this->bind(':col', $col);
        $this->bind(':dbTable', $table);
        $this->bind(':whereCol', $whereCol);
        $this->bind(':clause', $clause);

        $this->setQuery("select $col from $table where $whereCol = '$clause'");
        return $this->getColumn()[0];
    }

    public function selectRecordWhere($table, $whereCol, $clause){
        //using proper prepared statement for security
        $this->setQuery("select * from :dbTable where :whereCol = :clause");
        $this->bind(':dbTable', $table);
        $this->bind(':whereCol', $whereCol);
        $this->bind(':clause', $clause);

        $this->setQuery("select * from $table where $whereCol = '$clause'");
        return $this->getRecord();
    }

    public function getColumnNames($table){
        $this->setQuery("SELECT column_name FROM information_schema.columns 
                                WHERE  table_name = :dbTable AND table_schema = :dbName");
        $this->bind(':dbTable', $table);
        $this->bind('dbName', $this->dbName);
        return $this->getColumn();
    }

    private function createValueVars($values){
        $valueVars = [];
        $len = sizeof($values);
        for($i = 0; $i < $len; $i++){
            $valueVars[$i] = ':' . $i;
        }
        return $valueVars;
    }

    public function insert($cols, $values, $table){
        $colString = implode(',', $cols); //create string of column names

        //create string of variable names for use with the prepared statement
        $valueVars = $this->createValueVars($values);
        $valueVarsString = implode(',', $valueVars);
        $q = "insert into $table ($colString) values($valueVarsString)";
        $this->setQuery($q);
        //loop and bind valueVars to values
        $len = sizeof($values);
        for ($i = 0; $i < $len; $i++){
            $this->bind($valueVars[$i], $values[$i]);
        }

        return $this->execute();
    }

    public function updateById($table, $id, $idColName, $cols, $values){
        // ex: UPDATE books SET books_name='2010 Odyssey Two'  WHERE books_id = 110;
        //cols must be an array of column names that match the db
        //data must be a list of values equal in length to cols, and in the same order

        $colValVars = $this->createValueVars($values);
        $setString = '';
        $len = sizeof($cols);
        for ($i = 0; $i < $len; $i++){
            $setString .= $cols[$i].'='.$colValVars[$i];
            if ($i < $len-1){
                $setString .= ', ';
            }
        }
        $this->setQuery("UPDATE $table SET $setString WHERE $idColName = $id");
        for ($i=0;$i<$len;$i++){
            $this->bind($colValVars[$i], $values[$i]);
        }

        return $this->execute();
    }

    public function delete($table, $id, $idColName){
        //todo:implement db class delete method
        $this->setQuery("DELETE FROM $table WHERE $idColName = $id");
        return $this->execute();
    }


}

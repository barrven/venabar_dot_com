<?php
//source: https://culttt.com/2012/10/01/roll-your-own-pdo-php-class/
//abstracts all the commonly used database queries, handles error checking
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


    public function getRecord(){
        $this->execute();
        //return $this->statement->fetch($fetchStyle);
        $result = $this->statement->fetchAll(PDO::FETCH_NUM);

        $singleDArray = [];
        foreach ($result as $item) {
            array_push($singleDArray, $item[0]);
        }

        return $singleDArray;
    }

    public function selectAll($table, $fetchStyle=PDO::FETCH_NUM){
        $this->setQuery("select * from $table;");
        $this->execute();
        return $this->statement->fetchAll($fetchStyle);
    }

    //returns single record as a string
    public function selectRecordWhere($col, $table, $whereCol, $clause){
        //using proper prepared statement for security
        $this->setQuery("select :col from :table where :whereCol = :clause");
        $this->bind(':col', $col);
        $this->bind(':table', $table);
        $this->bind(':whereCol', $whereCol);
        $this->bind(':clause', $clause);

        $this->setQuery("select $col from $table where $whereCol = '$clause'");
        return $this->getRecord(PDO::FETCH_NUM)[0];
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


}

//todo: finish table class draw function
//todo: store bootstrap classes in table parameters
class Table{
    private $numRows;
    private $numCols;
    private $colTitles;
    private $data;

    public function __construct($colTitles=[], $data=[[]]){
        $this->numRows = sizeof($data);
        $this->numCols = sizeof($colTitles);
        $this->colTitles = $colTitles;
        $this->data = $data;

        $this->draw();
    }

    public function draw(){
        echo "<table class='table table-striped'>".PHP_EOL;
        echo "<thead class='thead-dark'>".PHP_EOL;
        echo "<tr>".PHP_EOL;
        foreach ($this->colTitles as $colTitle){
            echo "<th scope='col'>$colTitle</th>".PHP_EOL;
        }
        echo "</tr>".PHP_EOL;
        echo "</thead>".PHP_EOL;

    }


}
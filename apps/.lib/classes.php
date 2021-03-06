<?php
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
    public function selectRecordWhere($col, $table, $whereCol, $clause){
        //using proper prepared statement for security
        $this->setQuery("select :col from :dbTable where :whereCol = :clause");
        $this->bind(':col', $col);
        $this->bind(':dbTable', $table);
        $this->bind(':whereCol', $whereCol);
        $this->bind(':clause', $clause);

        $this->setQuery("select $col from $table where $whereCol = '$clause'");
        return $this->getColumn()[0];
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

    public function delete(){
        //todo:implement db class delete method
    }


}


class Table{
    //basic properties
    protected $id;
    public $numRows;
    public $numCols;
    protected $colTitles;
    public $data;
    //style properties
    public $tableClass;
    public $theadClass;
    //pagination properties
    protected $pagination;
    protected $recordsPerPage;
    public  $numPages; //  roundUp(sizeOf($data) / $recordsPerPage)
    public $currPageNum; // zero indexed because it makes rowStart and rowEnd easier to calculate
    //data sources and controls
    public $dataSource;

    public function __construct($colTitles=[], $data=[[]], $id=''){
    //public function __construct(Database $dataSource, $query='', $colTitles=[], $id=''){
        $this->data = $data;
        $this->colTitles = $colTitles;
        $this->numRows = sizeof($this->data);
        if (isset($this->data[0]))
            $this->numCols = sizeof($this->data[0]);
        $this->id = $id;
        //set styles default
        $this->tableClass = 'table table-striped';
        $this->theadClass = 'thead-dark';

    }

    public function setDataSource(Database $dataSource, $query){
        //don't set as dataSource if not a valid object or source has an error
        if (get_class($dataSource) != 'Database') return false;
        if ($dataSource->getError()) return false; //if error is not null, return
        $this->dataSource = $dataSource;
        $this->dataSource->setQuery($query);
        $this->data = $this->dataSource->getRecords();

        $this->numRows = sizeof($this->data);
        if (isset($this->data[0])){
            $this->numCols = sizeof($this->data[0]);
        }

    }

    public function enablePagination($recPerPage=10, $currPage=0){
        $this->pagination = true;
        $this->recordsPerPage = $recPerPage;
        //ceil rounds up to nearest int but returns a float. intval converts to integer
        $this->numPages = intval(ceil($this->numRows/$recPerPage)); //  roundUp(sizeOf($data) / $recordsPerPage)
        //don't allow pages that are out of bounds
        if ($currPage < $this->numPages && $currPage >= 0){
            $this->currPageNum = $currPage;
        }
    }

    public function draw(){
        //set rowStart and rowEnd to different values based on whether pagination is enabled or not
        if ($this->pagination){
            $rowStart = $this->currPageNum * $this->recordsPerPage;
            //check if this is the last page -> avoid index out of bounds in the case where numRecords is not
            //divisible by recordsPerPage
            if ($this->currPageNum == $this->numPages-1){ //numPages-1 because currPage is zero-indexed
                $rowEnd = $this->numRows;
            }
            else{
                $rowEnd = ($rowStart + $this->recordsPerPage);
            }
        }
        else{
            $rowStart = 0;
            $rowEnd = $this->numRows;
        }

        echo "<table class='$this->tableClass' id='$this->id'>".PHP_EOL;
        echo "<thead class='$this->theadClass'>".PHP_EOL;
        echo "<tr>".PHP_EOL;
        //add the column titles
        foreach ($this->colTitles as $title){
            echo "<th scope='col'>$title</th>".PHP_EOL;
        }
        echo "</tr>".PHP_EOL;
        echo "</thead>".PHP_EOL;
        echo "<tbody class='bg-light'>".PHP_EOL;
        //populate the table
        //rowStart and rowEnd are set based on whether pagination is enabled
        for ($i = $rowStart; $i < $rowEnd; $i++){ // changed this from foreach so that addRow will work
            echo "<tr>".PHP_EOL;
            for ($j = 0; $j < $this->numCols; $j++){ // changed this from foreach so that addColumn will work
                $temp = $this->data[$i][$j];
                echo "<td>$temp</td>".PHP_EOL;
            }
            echo "</tr>".PHP_EOL;
        }
        echo "</tbody>".PHP_EOL;
        echo "</table>".PHP_EOL;

        if ($this->pagination){
            $this->drawPageButtons();
        }
    }

    protected function drawPageButtons(){

        echo '<nav aria-label="Table page navigation">'.PHP_EOL;
        echo '<ul class="pagination justify-content-center">'.PHP_EOL;
        /* format for each page button:
        <li class="page-item">
            <a class="page-link" href="?p=1">1</a>
        </li>
         */
        //loop from 1 to <= num pages
        //links for each button ar numbered (not zero indexed)
        //must -1 before sending value to enablePagination
        for ($i = 1; $i <= $this->numPages; $i++){
            //mark the active page button
            if ($i-1 == $this->currPageNum){
                echo '<li class="page-item active">'.PHP_EOL;
                echo "<a class='page-link' href='?p=$i'>$i<span class=\"sr-only\">(current)</span></a>".PHP_EOL;
            }
            else{
                echo '<li class="page-item">'.PHP_EOL;
                echo "<a class='page-link' href='?p=$i'>$i</a>".PHP_EOL;
            }

            echo '</li>'.PHP_EOL;
        }

        echo '</ul>'.PHP_EOL;
        echo '</nav>'.PHP_EOL;

    }

    //todo: practice attaching ids to buttons (e.g. for update)
    public function addColumn($newColData =[], $position=''){
        //specify the column (default is far right)
        if ($position == ''){
            $position = $this->numCols;
        }
        //increment numCols
        $this->numCols++;
        //add entry to every column in data
        $ncdPointer = 0;
        //loop through lines
        for ($i = 0; $i < $this->numRows; $i++){
            //check that newColData[pointer] exists. if not, substitute empty string
            if (isset($newColData[$ncdPointer]))
                $temp = $newColData[$ncdPointer++];
            else
                $temp = '';

            array_splice($this->data[$i], $position, 0, $temp);
        }

    }

    //todo: complete Table addRow method
    public function addRow(){
        //increment numRows
        //add array of data to data
    }


}

//todo: implement form class to use as container for multiple controls

class Form{
    
}

//todo: decide what other kinds of controls would be useful, then make a generic control
// class with DropDownForm as a subclass. ideas: radio buttons, check boxes, text input, buttons
abstract class Control{
    public $value, $valueList, $dataSource;

    public function __construct(){

    }
    public function setDataSource(Database $db, $query){

    }

    public function draw(){

    }

}



class DropdownForm{
    protected $selectList; // list of values the user can choose from
    public $selected; //value chosen by user
    protected $dataSource; // supplies list of values
    protected $title; //name of control

    public function __construct($selected='', $title='Select item:', $selectList=[]){
        $this->selectList = $selectList;
        $this->selected = $selected;
        $this->title = $title;
    }

    public function setDataSource(Database $dataSource, $query){
        //don't set as dataSource if not a valid object or source has an error
        if (get_class($dataSource) != 'Database') return false;
        if ($dataSource->getError()) return false; //if error is not null, return
        $this->dataSource = $dataSource;
        $this->dataSource->setQuery($query);
        $this->selectList = $this->dataSource->getColumn();
    }

    public function draw(){ //don't draw the dropdown if data source is not valid
        if (!$this->dataSource){
            echo "<p class='text-danger text-center'>Could not connect to database</p>";
            return;
        }

        echo '<form method="post" action="" style="max-width: 500px; margin: auto">'.PHP_EOL;
        echo '<div class="input-group mb-3">'.PHP_EOL;
        echo '<div class="input-group-prepend">'.PHP_EOL;
        echo "<label class='input-group-text' for='data-table'>$this->title</label>".PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '<select class="custom-select" id="" name="data-table">'.PHP_EOL;
        echo "<option selected>$this->selected</option>".PHP_EOL;
        foreach ($this->selectList as $item){
            echo "<option value='$item'>$item</option>".PHP_EOL;
        }
        echo '</select>'.PHP_EOL;
        echo '<div class="input-group-append">'.PHP_EOL;
        echo '<button class="btn btn-success" type="submit">Submit</button>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
        echo '</form>'.PHP_EOL;
    }
}
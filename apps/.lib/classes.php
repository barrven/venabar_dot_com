<?php
//source: https://culttt.com/2012/10/01/roll-your-own-pdo-php-class/
//abstracts all the commonly used database queries, handles error checking
class Database{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbName = DB_NAME;
    private $dbHandler; // stores the PDO object if connection was successful
    private $error; //stores the PDOException object if there was an exception at some point
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
    // todo: all functions below should have some sort of error returning
    public function getRecords($fetchStyle=PDO::FETCH_NUM){
        $this->execute(); //execute handles possible exception and stores the error
        return $this->statement->fetchAll($fetchStyle);
    }

    //returns 1d array
    public function getRecord(){
        $this->execute();
        //return $this->statement->fetch($fetchStyle);
        $result = $this->statement->fetchAll(PDO::FETCH_NUM);

        //todo: change this later to use array_implode() to reduce the second dimension to string
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
        return $this->getRecord()[0];
    }

    public function getColumnNames($table){
        $this->setQuery("SELECT column_name FROM information_schema.columns 
                                WHERE  table_name = :dbTable AND table_schema = :dbName");
        $this->bind(':dbTable', $table);
        $this->bind('dbName', $this->dbName);
        return $this->getRecord();
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

//todo: finish table class draw function with pagination
//todo: store bootstrap classes in table parameters
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
    //todo: change table to include a database connection (data source)
    //dataSource
    public $dataSource;
    //controls
    protected $control;

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

    public function setDataSource(Database $dataSource){
        //don't set as dataSource if not a valid object or source has an error
        if (get_class($dataSource) != 'Database') return false;
        if ($dataSource->getError()) return false; //if error is not null, return
        $this->dataSource = $dataSource;
    }

    public function populateData($query=''){
        if (!$this->dataSource) return;

        if ($query){
            $this->dataSource->setQuery($query);
            $this->data = $this->dataSource->getRecords();
        }
        else{
            $this->data = $this->dataSource->selectAll($this->control->selected);
        }

        $this->numRows = sizeof($this->data);
        if (isset($this->data[0])){
            $this->numCols = sizeof($this->data[0]);
        }

    }

    //todo: decide what other kinds of controls would be useful, then make a generic control
    // class with DropDownForm as a subclass
    public function setControl(DropdownForm $dropDown, $query){
        $dropDown->setDataSource($this->dataSource, $query);
        $this->control = $dropDown;
    }

    public function drawControl(){
        $this->control->draw();
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
        //todo: figure out how to differentiate multiple tables with different pages using table id

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

    //todo: complete addColumn Table method
    public function addColumn($newColData =[]){
        //specify the column (default is far right)
        //increment numCols
        //add entry to every column in data
        //check that newColData has same length as numLines? -> or if not just fill in blank
        //loop through lines, run

    }

    //todo: complete Table addRow method
    public function addRow(){
        //increment numRows
        //add array of data to data
    }


}

class DropdownForm{
    protected $selectList;
    public $selected;
    protected $dataSource;
    protected $title;

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
        $this->selectList = $this->dataSource->getRecord();
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
        echo '<select class="custom-select" id="data-table" name="data-table">'.PHP_EOL;
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
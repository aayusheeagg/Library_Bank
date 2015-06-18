<?php
include_once('class.tlbConfig.php');
class sql extends tlbConfig{
	private $link;
	public $query;
	public function __construct(){
		parent::__construct();
		$this->link = mysqli_connect($this->host, $this->mysql_user, $this->mysql_passwd, $this->mysql_dbName)
							or die(mysqli_error());
	}

	public function process(){
		$result = mysqli_query($this->link, $this->query) or die(mysqli_error($this->link));
		return $result;
	}

	public function getData($column, $table, $field = 1, $value = 1){
		$this->query = "SELECT $column FROM $table WHERE $field = '$value'";
		$result = $this->process();
		$row = mysqli_fetch_assoc($result);
		return $row[$column];
	}
	
	public function countData($column, $table, $field = 1, $value = 1){
		$this->query = "SELECT $column FROM $table WHERE $field = '$value'";
		$result = $this->process();
		return mysqli_num_rows($result);
	}
	
	public function countStudents($column, $table, $field = 1, $value = 1,$cod){
		$this->query = "SELECT $column FROM $table WHERE $field = '$value' AND code='$cod'";
		$result = $this->process();
		return mysqli_num_rows($result);
	}
	
	public function getDatas($columns, $table, $field = 1, $value = 1){
		$column = '';
		foreach($columns as $col)
			$column = $column.$col.',';
		$column = rtrim($column,',');
		$this->query = "SELECT $column FROM $table WHERE $field = '$value'";
		$result = $this->process();
		$row = mysqli_fetch_assoc($result);
		return $row;
	}
	
	public function escape($string){
		return mysqli_real_escape_string($this->link, $string);
	}
	
	public function close(){
		mysqli_close($this->link);
	}
}
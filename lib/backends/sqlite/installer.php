<?php
require_once 'backend.php';
class Table
{
	var $name;
	var $columns = array();
	var $indexes = array();
	var $types = array(
		'integer' => array('int', null, 0),
		'string' => array('varchar', 255, ''),
		'text' => array('text', null, null),
		'timestamp' => array('timestamp', null, null)
	);

	function Table($name) {
		$this->name = $name;
	}
	function column($name, $type, $length = null) {
		$_type = $this->types[$type];
		if ($length) {
			$column = "'$name' $_type[0]($length) NOT NULL default ''";
		} else {
			$column = "'$name' $_type[0]";
			if ($_type[1]) {
				$column .= "($_type[1])";
			}
			$column .= " NOT NULL";
			if ($_type[2]) {
				$column .= " default '$_type[2]'";
			}
		}
		$this->columns[] = $column;
	}
	function add_index($name) {
		$this->indexes[] = "CREATE INDEX '{$this->name}_${name}_index' ON '" . get_table_name($this->name) . " ('$name')";
	}
	function to_sql() {
		array_unshift($this->columns, "'id' INTEGER PRIMARY KEY");
		$sql = "CREATE TABLE '" . get_table_name($this->name) . "' (\n";
		$sql .= implode(",\n", $this->columns);
		$sql .= "\n)";
		return array($sql)+$this->indexes;
	}
}
function db_info_form() {
	field('dbfile', 'DB file', 'data/sqlite.db', 'text', 'Database로 사용할 파일을 입력합니다. 특별한 경우가 아니면 그대로 사용해도 됩니다');
}
function is_supported() {
	return function_exists('sqlite_open');
}
function init_db() {
	$conn = get_conn();
	include('db/schema.php');
}
?>

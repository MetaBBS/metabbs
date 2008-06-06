<?php
function get_column_name($column) { return $column->name; }
function column_to_string($column) { return $column->to_string(); }
class Column {
	function Column($name) {
		$this->name = $name;
	}
}
class IntegerColumn extends Column {
	var $default = 0;
	function to_spec() {
		return "`$this->name` integer(10) NOT NULL DEFAULT '$this->default'";
	}
}
class ShortColumn extends IntegerColumn {
	function to_spec() {
		return "`$this->name` tinyint NOT NULL DEFAULT '$this->default'";
	}
}
class UShortColumn extends IntegerColumn {
	function to_spec() {
		return "`$this->name` tinyint UNSIGNED NOT NULL DEFAULT '$this->default'";
	}
}
class StringColumn extends Column {
	var $default = '';
	function to_spec($length) {
		return "`$this->name` varchar($length) NOT NULL DEFAULT '$this->default'";
	}
}
class TextColumn extends Column {
	function to_spec() {
		return "`$this->name` text NOT NULL";
	}
}
class TimestampColumn extends Column {
	function to_spec() {
		return "`$this->name` integer(10) NOT NULL";
	}
}
class BooleanColumn extends Column {
	function to_spec() {
		return "`$this->name` bool NOT NULL";
	}
}

function &get_conn() {
	static $conn;
	global $config;
	if (!isset($conn)) {
		$conn = new MySQLAdapter;
		$conn->connect($config->get('host'), $config->get('user'), $config->get('password'));
		$conn->selectdb($config->get('dbname'));
		if ($config->get('force_utf8') == '1') {
			$conn->enable_utf8();
		}
	}
	return $conn;
}

class MySQLAdapter
{
	var $conn;
	var $utf8 = false;

	function connect($host, $user, $password) {
		$this->conn = mysql_connect($host, $user, $password) or trigger_error("mysql - Can't connect database",E_USER_ERROR);
		register_shutdown_function(array(&$this, 'disconnect'));
	}
	function disconnect() {
		mysql_close($this->conn);
	}
	function selectdb($dbname) {
		mysql_select_db($dbname, $this->conn) or trigger_error("Can't select database", E_USER_ERROR);
	}
	function enable_utf8() {
		$this->execute('set names utf8');
		$this->utf8 = true;
	}

	function execute($query) {
		$result = mysql_query($query, $this->conn);
		if (!$result) {
			trigger_error(mysql_error($this->conn), E_USER_ERROR);
		}
	}
	function query($query, $data = null) {
		if (!$query) return;
		if ($data) $query = $this->_q($query, $data);
		$result = mysql_query($query, $this->conn);
		if (!$result) {
			echo '<br />Error query: ' . htmlspecialchars($query);
			trigger_error(mysql_error($this->conn), E_USER_ERROR);
		}
		return $result;
	}
	function _q($query, $data) {
		$tokens = preg_split('/([?!])/', $query, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach ($tokens as $i => $token) {
			if ($token == '?')
				$tokens[$i] = "'".mysql_real_escape_string(array_shift($data), $this->conn)."'";
			else if ($token == '!')
				$tokens[$i] = array_shift($data);
		}
		return implode('', $tokens);
	}
	function fetchall($query, $model = 'Model', $data = null, $assoc = false) {
		$results = array();
		$result = $this->query($query, $data);
		while ($data = mysql_fetch_assoc($result)) {
			if ($assoc)
				$results[$data['id']] = new $model($data);
			else
				$results[] = new $model($data);
		}
		return $results;
	}
	function fetchrow($query, $model = 'Model', $data = null) {
		return new $model(mysql_fetch_assoc($this->query($query, $data)));
	}
	function fetchone($query, $data = null) {
		list($result) = mysql_fetch_row($this->query($query, $data));
		return $result;
	}
	function insertid() {
		return mysql_insert_id($this->conn);
	}
	function add_table($t) {
		$sql = $t->to_sql();
		if ($this->utf8)
			$sql .= 'CHARACTER SET utf8 COLLATE utf8_general_ci';
		$this->query($sql);
	}
	function rename_table($ot, $t) {
		$this->query("RENAME TABLE ".get_table_name($ot)." TO ".get_table_name($t));
	}
	function drop_table($t) {
		$this->query("DROP TABLE ".get_table_name($t));
	}
	function add_field($t, $name, $type, $length = null) {
		$table = new Table($t);
		$this->query("ALTER TABLE $table->table ADD " . $table->_column($name, $type, $length));
	}
	function drop_field($t, $name) {
		$this->query("ALTER TABLE ".get_table_name($t)." DROP COLUMN $name");
	}
	function add_index($t, $name) {
		$this->query("ALTER TABLE ".get_table_name($t)." ADD INDEX ${t}_$name ($name)");
	}
	function get_columns($table) {
		$result = $this->query("SHOW COLUMNS FROM $table");
		$fields = array();
		while (list($name) = mysql_fetch_row($result)) {
			if ($name != 'id') $fields[] = $name;
		}
		return $fields;
	}
	function get_server_version() {
		list($major, $minor) = explode('.', mysql_get_server_info($this->conn), 3);
		return array($major, $minor);
	}
}

?>
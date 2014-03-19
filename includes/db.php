<?php

class DB
{

	private $conn;
	private $query;


	public function __construct()
	{
		$this->conn = mysql_connect('localhost', 'root', '');
		mysql_select_db('business', $this->conn);
		mysql_query('set names utf8', $this->conn);
	}


	public function query($sql)
	{
		$this->query = mysql_query($sql, $this->conn);

		return $this;
	}


	public function row()
	{
		return mysql_fetch_object($this->query);
	}


	public function count()
	{
		$res = $this->row();

		return (int)$res->c;
	}


	public function rows()
	{
		$arr = array();

		while ($r = mysql_fetch_object($this->query))
		{
			$arr[] = $r;
		}

		return $arr;
	}


	public function quote($str)
	{
		return mysql_real_escape_string($str);
	}


	public function insert_id()
	{
		return mysql_insert_id($this->conn);
	}


    public function affect()
    {
        return mysql_affected_rows($this->conn);
    }


	public function insert($table, $data)
	{
		$fields = array();
		$values = array();

		foreach ($data as $k => $v)
		{
			$fields[] = $k;
			$values[] = $v;
		}

		$this->query('INSERT INTO `' . $table . '` (' . implode(',', $fields) . ') VALUES ("' . implode('", "', $values) . '")');
	}


	public function update($table, $data, $where)
	{
		$set = array();

		foreach ($data as $k => $v)
		{
			$set[] = '`' . $k . '` = "' . $v . '"';
		}

		$this->query('UPDATE `' . $table . '` SET ' . implode(',', $set) . ' WHERE ' . $where);
	}


	public function delete($table, $where = '')
	{
		if ($where !== '')
		{
			$this->query('DELETE FROM ' . $table . ' WHERE ' . $where);
		}
		else
		{
			$this->query('TRUNCATE TABLE ' . $table);
		}		
	}

}

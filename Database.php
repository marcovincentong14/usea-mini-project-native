<?php

class Database	{
	
	private $pdo = '';
	private $host = 'localhost';
	private $db = 'usea_db';
	private $user = 'root';
	private $password = '';
	
	
	public static function getInstance()	{
		return new static();
	}
	
	
	public function __construct()	{
		$this->pdo = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db, $this->user, $this->password);
	}
	
	public function getRows($table)	{
		return $this->pdo->query("select * from $table")->fetchAll();
	}
	public function getRowsByQuery($query, $params = [])	{
		$statement = $this->pdo->prepare($query);
		$statement->execute($params);
		
		return $statement->fetchAll();
	}
	
	public function insert($table, $columns, $values)	{
		$statement = $this->pdo->prepare("insert into $table (" . implode(', ', $columns) . ') values (' . implode(', ', array_fill(0, count($values), '?')) . ')');
		$statement->execute($values);
		
		return $this->pdo->lastInsertId();
	}
	
	public function clear($table)	{
		$this->pdo->prepare("delete from $table")->execute();
	}
	
}
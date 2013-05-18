<?php

class DB
{
	private $_adapter,$_dbName,$_username,$_password,$_host;
        
	private $_dbh = null;
	
	/**
	 * check arguments
	 * @param type $adapter
	 * @param type $dbName
	 * @param type $username
	 * @param type $password
	 * @param type $host
	 * @return boolean
	 */
        public function setDB($adapter, $dbName, $username, $password, $host = "localhost")
	{
		$this -> _adapter = $adapter;
		$this -> _username = $username;
		$this -> _password = $password;
		$this -> _dbName = $dbName;
		$this -> _host = $host;
                
                if ($this -> _adapter == "mysql")
                {
			try
			{
				$this -> _dbh = new PDO("mysql:host={$host};dbname={$dbName}", "{$username}", "{$password}");
				$this -> _dbh ->setAttribute(PDO :: ATTR_ERRMODE, PDO :: ERRMODE_WARNING);
				
				return TRUE;
			}
			catch(PDOException $e)
			{
				return FALSE;
			}
                }
                else
                {
			return FALSE;
                }
	}
	
	/**
	 * check arguments
	 * @param type $query
	 * @return boolean
	 */
	public function execute($query)
	{
		if ($this -> _dbh != null)
		{
			try
			{
				$count = $this -> _dbh -> exec($query);
				return $count;
			}
			catch(PDOException $e)
			{
				echo $e -> getMessage();
				return FALSE;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * check arguments
	 * @param type $query
	 * @return boolean
	 */
	public function query($query)
	{
		if ($this -> _dbh != null)
		{
			try
			{
				$exec = $this -> _dbh -> query($query);
				$result = $exec -> fetchAll(PDO :: FETCH_OBJ);
				return $result;
			}
			catch(PDOException $e)
			{
				echo $e -> getMessage();
				return FALSE;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * check arguments
	 * @param type $query
	 * @param type $args
	 * @return boolean
	 */
	public function prepare($query, $args)
	{
		if ($this -> _dbh != null)
		{
			try
			{
				$sth = $this -> _dbh -> prepare($query);

				for ($i = 0; $i < count($args); $i++)
				{
					$sth -> bindParam($i+1, $args[$i]);
				}

				$result = $sth -> execute();
				
				$type = substr($query, 0, 3);
				if ($type == "INS" || $type == "DEL" || $type == "UPD")
					return $result;
				else if ($type == "SEL")
				{
					$result = $sth -> fetchAll(PDO :: FETCH_ASSOC);
					return $result;
				}
			}
			catch(PDOException $e)
			{
				echo $e -> getMessage();
				return FALSE;
			}
		}
		
		return FALSE;
	}
}

?>
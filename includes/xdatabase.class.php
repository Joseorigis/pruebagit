<?php
/**
* Database class
*
* @author    Sven Wagener <sven_dot_wagener_at_rheinschmiede_dot_de>
* @copyright Sven Wagener
* @include 	 Funktion:_include_
*/

// ------------------------------------

class database{
	var $database_types="";
	
	var $db_connect="";
	var $db_close="";
	var $db_select_db="";
	var $db_query="";
	var $db_error="";
	var $db_error_nr="";
	var $db_fetch_array="";
	var $db_num_rows="";
	
	var $host;
	var $database;
	var $user;
	var $password;
	var $port;
	var $database_type;
	var $dsn;
  var $tsqldb1;
	
	var $sql;
	
	var $con; // variable for connection id
	var $con_string; // variable for connection string
	var $query_id; // variable for query id
	
	var $errors; // variable for error messages
	var $error_count=0; // variable for counting errors
	var $error_nr;
	var $error;
	
	var $debug=false; // debug mode off
	
	/**
	* Constructor of class - Initializes class and connects to the database
	* @param string $database_type the name of the database (ifx=Informix,msql=MiniSQL,mssql=MS SQL,mysql=MySQL,pg=Postgres SQL,sybase=Sybase)
	* @param string $host the host of the database
	* @param string $database the name of the database
	* @param string $user the name of the user for the database
	* @param string $password the passord of the user for the database
	* @desc Constructor of class - Initializes class and connects to the database.
	*
	*  You can use this shortcuts for the database type:
	*
	* 		ifx -> INFORMIX
	* 		msql -> MiniSQL
	* 		mssql -> Microsoft SQL Server
	* 		mysql -> MySQL
	*		odbc -> ODBC
	* 		pg -> Postgres SQL
	*		sybase -> Sybase
	*/
	function database($database_type,$host,$database,$user,$password,$port=false,$dsn=false){
		$database_type=strtolower($database_type);
		$this->host=$host;
		$this->database=$database;
		$this->user=$user;
		$this->password=$password;
		$this->port=$port;
		$this->dsn=$dsn;
    $this->tsqldb1='USE DATABASE '. $this->database;
		
		$this->database_types=array("ifx","msql","mssql","mysql","sqlsrv","odbc","pg","sybase");
		
		// Setting database type and connect to database
		if(in_array($database_type,$this->database_types)){
			$this->database_type=$database_type;
			
			$this->db_connect=$this->database_type."_connect";
			$this->db_close=$this->database_type."_close";
      
      if($database_type!="sqlsrv")
      {
			  $this->db_select_db=$this->database_type."_select_db";
      }
			
			if($database_type=="odbc"){
				$this->db_query=$this->database_type."_exec";
				$this->db_fetch_array=$this->database_type."_fetch_row";
				$this->db_error=$this->database_type."_errormsg";
				
			}else{
				$this->db_query=$this->database_type."_query";
				$this->db_fetch_array=$this->database_type."_fetch_array";
				$this->db_error=$this->database_type."_error";
				$this->db_error_nr=$this->database_type."_errno";
			}
			
			$this->db_num_rows=$this->database_type."_num_rows";
			
			return $this->connect();
		}else{
			$this->halt("Database type not supported");
			return false;
		}
	}
	
	/**
	* This function connects the database
	* @return boolean $is_connected Returns true if connection was successful otherwise false
	* @desc This function connects to the database which is set in the constructor
	*/
	function connect(){
		// Selecting connection function and connecting
		
		if($this->con==""){
			// INFORMIX
			if($this->database_type=="ifx"){
				$this->con=call_user_func($this->db_connect,$this->database."@".$this->host,$this->user,$this->password);
			}else if($this->database_type=="mysql"){
				// With port
				if(!$this->port){
					$this->con=call_user_func($this->db_connect,$this->host.":".$this->port,$this->user,$this->password);
				}
				// Without port
				else{
					$this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
				}
				// mSQL
			}else if($this->database_type=="msql"){
				$this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
				// MS SQL Server
			}else if($this->database_type=="mssql"){
				
				//$this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
				//$this->con=mssql_connect($this->host,$this->user,$this->password);
				global $databaseerrorid;
				try {
					//$this->con=mssql_connect($this->host,$this->user,$this->password);
					$this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
					if (!$this->con){ throw new Exception('ConnectionFailed'); }
				} catch (Exception $e) {
					//echo 'Caught exception:',  $e->getMessage(), "\n";
					$databaseerrorid = 1;
					//die("<meta http-equiv='REFRESH' content='0;url=exception.php?e=0'>");
				}
				
				// SQLSRV
			}else if($this->database_type=="sqlsrv"){
      global $databaseerrorid;
        try {
              $connectionInfo = array("UID" => $this->user, "PWD" => $this->password, "Database" => $this->database, "MultipleActiveResultSets" => true);
              //$serverName = "tcp:va8n7ywa28.database.windows.net,1433";
              //sqlsrv_configure('WarningsReturnAsErrors', 0);
              //$this->con=sqlsrv_connect($serverName, $connectionInfo);
            
              //$stmt = sqlsrv_query($this->con, $tsql);
              //if ($stmt === false)
              //{
                //$databaseerrorid = 1;
              //}
              //sqlsrv_free_stmt($stmt);
              //sqlsrv_close($conn);
              $this->con=call_user_func($this->db_connect,$this->host,$connectionInfo);

					    if (!$this->con){ throw new Exception('ConnectionFailed'); }
				    } catch (Exception $e) {
					    $databaseerrorid = 1;
              sqlsrv_close($conn);
				    }
        // ODBC
      }else if($this->database_type=="odbc"){
				$this->con=call_user_func($this->db_connect,$this->dsn,$this->user,$this->password);
				// Postgres SQL
			}else if($this->database_type=="pg"){
				// With port
				if(!$this->port){
					$this->con=call_user_func($this->db_connect,"host=".$this->host." port=".$this->port." dbname=".$this->database." user=".$this->user." password=".$this->password);
				}
				// Without port
				else{
					$this->con=call_user_func($this->db_connect,"host=".$this->host." dbname=".$this->database." user=".$this->user." password=".$this->password);
				}
				// Sybase
			}else if($this->database_type=="sybase"){
				$this->con=call_user_func($this->db_connect,$this->host,$this->user,$this->password);
			}
			
			if(!$this->con){
				$this->halt("Wrong connection data! Can't establish connection to host.");
				return false;
			}else{
				if($this->database_type!="odbc"){
          if($this->database_type!=="sqlsrv"){
					  if(!call_user_func($this->db_select_db,$this->database,$this->con)){
						  $this->halt("Wrong database data! Can't select database.");
						  return false;
					  }else{
						  return true;
					  }
          }
          else
          {
					  if(!call_user_func($this->db_query,$this->con,$this->tsqldb1)){
						  $this->halt("Wrong database data! Can't select database.");
						  return false;
					  }else{
						  return true;
					  }
          }
				}
			}
		}else{
			$this->halt("Already connected to database.");
			return false;
		}
	}
	
	/**
	* This function disconnects from the database
	* @desc This function disconnects from the database
	*/
	function disconnect(){
		if(@call_user_func($this->db_close,$this->con)){
			return true;
		}else{
			$this->halt("Not connected yet");
			return false;
		}
	}
	
	/**
	* This function starts the sql query
	* @param string $sql_statement the sql statement
	* @return boolean $successfull returns false on errors otherwise true
	* @desc This function disconnects from the database
	*/
	function query($sql_statement){
		$this->sql=$sql_statement;
		if($this->debug){
			printf("<!-- SQL statement: %s<br />//-->\r\n",$this->sql);			
		}
		if($this->database_type=="odbc"){
			// ODBC
			if(!$this->query_id=call_user_func($this->db_query,$this->con,$this->sql)){
				$this->halt("No database connection exists or invalid query");
			}else{
				if (!$this->query_id) {
					$this->halt("Invalid SQL Query");
					return false;
				}else{
					return true;
				}
			}
      // SQLSRV
		}else if($this->database_type=="sqlsrv"){
      if(!$this->query_id=call_user_func($this->db_query,$this->con,$this->sql, array(), array("Scrollable"=>"buffered"))){
				  $this->halt("No database connection exists or invalid query");
      } 
    }else{
			// All other databases
			if(!$this->query_id=call_user_func($this->db_query,$this->sql,$this->con)){
				$this->halt("No database connection exists or invalid query");
				
			}else{
				if (!$this->query_id) {
					
					$this->halt("Invalid SQL Query");
					return false;
				}else{
					return true;
				}
			}
		}
	}
	
	/**
	* This function returns the last error
	* @return string $error the error as string
	* @desc This function returns the last error
	*/
	function get_error(){
		return call_user_func($this->db_error,$this->con);
	}
	
	/**
	* This function returns the last error id
	* @return int $error the error as integer
	* @desc This function returns the last error id
	*/
	function get_error_nr(){
		return call_user_func($this->db_error_nr,$this->con);
	}
	
	/**
	* This function returns a row of the resultset
	* @return array $row the row as array or false if there is no more row
	* @desc This function returns a row of the resultset
	*/
	function get_row(){
		if($this->database_type=="odbc"){
			// ODBC database
			if($row=call_user_func($this->db_fetch_array,$this->query_id)){
				
				for ($i=1; $i<=odbc_num_fields($this->query_id); $i++) {
					$fieldname=odbc_field_name($this->query_id,$i);
					$row_array[$fieldname]=odbc_result($this->query_id,$i);
				}
				return $row_array;
			}else{
				return false;
			}
		}else{
			// All other databases
			$row=call_user_func($this->db_fetch_array,$this->query_id);
			return $row;
		}
	}
	
	/**
	* This function returns number of rows in the resultset
	* @return int $row_count the nuber of rows in the resultset
	* @desc This function returns number of rows in the resultset
	*/
	function count_rows(){
		$row_count=call_user_func($this->db_num_rows,$this->query_id);
		if($row_count>=0){
			return $row_count;
		}else{
			$this->halt("Can't count rows before query was made");
			return false;
		}
	}
	/**
	* This function returns all tables of the database in an array
	* @return array $tables all tables of the database in an array
	* @desc This function returns all tables of the database in an array
	*/
	function get_tables(){
		if($this->database_type=="odbc"){
			// ODBC databases
			$tablelist=odbc_tables($this->con);
			
			for($i=0;odbc_fetch_row($tablelist);$i++) {
				$tables[$i]=odbc_result($tablelist,3);
			}
			return $tables;
		}else{
			// All other databases
			$tables = "";
			$sql="SHOW TABLES";
			$this->query_id($sql);
			for($i=0;$data=$this->get_row();$i++){
				$tables[$i]=$data['Tables_in_'.$this->database];
			}
			return $tables;
		}
	}
	
	/**
	* Prints out a error message
	* @param string $message all occurred errors as array
	* @desc Returns all occurred errors
	*/
	function halt($message){
		if($this->debug){
			$this->error_nr=$this->get_error_nr();
			$this->error=$this->get_error();
					
			printf("Error: %s<br />\n", $message);
			
			if($this->error_nr!="" && $this->error!=""){
				printf("Database Error: %s (%s)<br />\n",$this->error_nr,$this->error);
			}
			die ("Session halted.");
		}
	}
	
	/**
	* Switches to debug mode
	* @param boolean $switch
	* @desc Switches to debug mode
	*/
	function debug_mode($debug=true){
		$this->debug=$debug;
	}
	
	
}

?>
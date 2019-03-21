<?php

	// DATABASE CONNECTION CLOSE
		// Disconnecting to database
		$dbconnection->disconnect();
		$dbsecurity->disconnect();

    // Liberar las instancias de los objetos
       // $dbconnection = null;
		//$dbsecurity = null;

		if (isset($dbtransactions)) {
			$dbtransactions->disconnect();
         // Liberar la instancia de objeto
         //   $dbtransactions = null;
		}


?>
<?php


	// DATABASE CONNECTION
		// DATABASE MAIN
			// Connecting to database CRM MAIN
			$dbconnection = new database($configuration['db1type'],
										$configuration['db1host'], 
										$configuration['db1name'],
										$configuration['db1username'],
										$configuration['db1password']);

		// DATABASE SECURITY
			// Connecting to database to SECURITY & LOGIN
			$dbsecurity = new database($configuration['db0type'],
								$configuration['db0host'], 
								$configuration['db0name'],
								$configuration['db0username'],
								$configuration['db0password']);

//		// DATABASE TRANSACTIONS
//			// Connecting to database to TRANSACTIONS & POINTS
//			$dbtransactions = new database($configuration['db2type'],
//								$configuration['db2host'], 
//								$configuration['db2name'],
//								$configuration['db2username'],
//								$configuration['db2password']);

		// DATABASE ALTERNATES

			// Switching to debug mode
			//$dbconnection->debug_mode();

?>
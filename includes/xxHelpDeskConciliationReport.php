<?php
    set_time_limit(0);
// ----------------------------------------------------------------------------------------------------
// HELP DESK CONCILIATION
// ----------------------------------------------------------------------------------------------------

		// Verificamos si ya están vinculadas las librerías necesarias...
		if (!isset($appcontainer)) {
			
			// Iniciamos el controlador de SESSIONs de PHP
				session_start();
			
			// WARNINGS & ERRORS
				ini_set('error_reporting', E_ALL&~E_NOTICE);
				error_reporting(E_ALL);
				ini_set('display_errors', '1');
		
			// SCRIPT
				// Obtengo el nombre del script en ejecución
				$script = __FILE__;
				$camino = get_included_files();
				$scriptactual = $camino[count($camino)-1];
			
		
			// INCLUDES & REQUIRES 
				include_once('../includes/configuration.php');	// Archivo de configuración
				include_once('../includes/functions.php');	// Librería de funciones
				include_once('../includes/database.class.php');	// Class para el manejo de base de datos
				include_once('../includes/databaseconnection.php');	// Conexión a base de datos
		
				include_once('../includes/databaseconnectiontransactions.php');	// Conexión a base de datos

				require('../includes/Classes/PHPExcel.php');	// Class Create Document Excel

		} 
		$itemownerid = 0;
		if (isset($_GET['i'])) {
			$itemownerid = $_GET['i'];
			if ($itemownerid == '') { $itemownerid = 0; }
		}
		$itemownername = '';
		if (isset($_GET['o'])) {
			$itemownername = setOnlyLetters($_GET['o']);
			if ($itemownername == '') { $itemownername = ''; }
		}
		$period = date('Y').date('m');
		if (isset($_GET['p'])) {
			$period = $_GET['p'];
			if ($period == '') { $period = date('Y').date('m'); }
		}
        $typeReport = 0;
		if (isset($_GET['t'])) {
			$typeReport = $_GET['t'];
			if ($typeReport == '') { $typeReport = 0; }
		}


        $fecha = date('Ymd');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if($typeReport == 1){
            header('Content-Disposition: attachment; filename="'.$itemownername.'_conciliacion_'.$period.'_'.$fecha.'.xls"');
        }
        else{
            header('Content-Disposition: attachment; filename="'.$itemownername.'_conciliacion_'.$period.'_'.$fecha.'_sustentos.xls"');
        }
        header('Cache-Control: max-age=0');

        $excel = new PHPExcel();

        $excel->getproperties()->setCreator('OrveeCRM')->setLAstModifiedBy('OrveeCRM')->setTitle('Conciliacion_'.$period);

        if($itemownerid == 6){

            // TRANSACTIONS DATABASE
                $connections = 0;
				$query = @"SELECT Distinct ConnectionId,LTRIM(REPLACE(REPLACE(REPLACE(REPLACE(ConnectionName,'/','-'),'FARMACIAS',''),'[',''),']','')) ConnectionName FROM SettlementResults WHERE ItemOwnerId=".$itemownerid." AND SettlementDate='".$period."' ORDER BY ConnectionId;";
				$dbtransactions->query($query);
                $connections = $dbtransactions->count_rows();

                $numsheet = 0;
                if($connections > 0){

                    while($my_crow=$dbtransactions->get_row()){
                        
                        $items = 0;
				        $query = @"SELECT SettlementDate AS Period,ConnectionId,ConnectionName,ItemSKU AS Item,ItemName,Units,UnitsBonus FROM SettlementResults WHERE ItemOwnerId=".$itemownerid." AND SettlementDate='".$period."' AND ConnectionId = " . $my_crow['ConnectionId'] . " ORDER BY ConnectionId,ItemSKU;";
				        $dbconnection->query($query);
           
				        $items = $dbconnection->count_rows();
				        if ($items > 0) {
                    
                            
                            if ($numsheet == 0){
                                $excel->setActiveSheetIndex($numsheet);
                            }
                            else{
                                $excel->createSheet();
                                $excel->setActiveSheetIndex($numsheet);
                            }

                            $sheet = $excel->getActiveSheet();
                            $sheet->setTitle(str_replace('FARMACIA','',$my_crow['ConnectionName']));

                            $sheet->setCellValue('A1','Period');
                            $sheet->setCellValue('B1','ConnectionId');
                            $sheet->setCellValue('C1','ConnectionName');
                            $sheet->setCellValue('D1','Item');
                            $sheet->setCellValue('E1','ItemName');
                            $sheet->setCellValue('F1','Units');
                            $sheet->setCellValue('G1','UnitsBonus');

                            $sheet->getstyle('A1:G1')->getFont()->setBold(TRUE);
                            $sheet->getstyle('A1:G1')->getFont()->setSize(12);

                            $i = 0;
                            while($my_row=$dbconnection->get_row())
                            {
                                $sheet->setCellValue('A'.($i+2), $my_row['Period']);
                                $sheet->setCellValue('B'.($i+2), $my_row['ConnectionId']);
                                $sheet->setCellValue('C'.($i+2), $my_row['ConnectionName']);
                                $sheet->setCellValue('D'.($i+2), $my_row['Item']);
                                $sheet->setCellValue('E'.($i+2), $my_row['ItemName']);
                                $sheet->setCellValue('F'.($i+2), $my_row['Units']);
                                $sheet->setCellValue('G'.($i+2), $my_row['UnitsBonus']);

                                $i = $i + 1;
                            }
                            foreach(range('A','G')as $column){
                                $sheet->getColumnDimension($column)->setAutoSize(TRUE);
                            }
                            $numsheet = $numsheet + 1;
                        }
                    }// [while($my_crow=$dbtransactions->get_row()){]
                    
                }//[if($connections > 0){]
        }
        else{

                if($typeReport == 1)
                {
                        $items = 0;
                        $query = '';
                       /*
                         $query = @"SELECT SettlementDate AS Period,ConnectionId,ConnectionName,ItemSKU AS Item,ItemName,Units,UnitsBonus,UnitsDiscount,ItemDiscount,ItemPrice FROM SettlementResults WHERE ItemOwnerId=".$itemownerid." AND SettlementDate='".$period."' ORDER BY ConnectionId,ItemSKU;";  
                       */
                       if($itemownerid == 5){
                          $query = @"SELECT SettlementDate AS Period,ConnectionId,ConnectionName,ItemSKU AS Item,ItemName,Units,UnitsBonus,UnitsDiscount,ItemDiscount,ItemPrice FROM SettlementResults WHERE ItemOwnerId in (".$itemownerid.") AND SettlementDate='".$period."'
                                     UNION ALL
                                     SELECT SettlementDate AS Period,ConnectionId,ConnectionName +' MORADA' AS ConnectionName,ItemSKU AS Item,ItemName,Units,UnitsBonus,UnitsDiscount,ItemDiscount,ItemPrice FROM SettlementResults WHERE ItemOwnerId in (4) AND SettlementDate='".$period."' AND (ItemDiscount>0.2058)ORDER BY ConnectionId,ConnectionName,ItemSKU;";  
                        }
                        else{
                          $query = @"SELECT SettlementDate AS Period,ConnectionId,ConnectionName,ItemSKU AS Item,ItemName,Units,UnitsBonus,UnitsDiscount,ItemDiscount,ItemPrice FROM SettlementResults WHERE ItemOwnerId=".$itemownerid." AND SettlementDate='".$period."' ORDER BY ConnectionId,ItemSKU;";    
                        }
				        
				        $dbtransactions->query($query);
           
				        $items = $dbtransactions->count_rows();
				        if ($items > 0) {
                    
                            $excel->setActiveSheetIndex(0);

                            $sheet = $excel->getActiveSheet();
                            $sheet->setTitle($itemownername);

                            $sheet->setCellValue('A1','Period');
                            $sheet->setCellValue('B1','ConnectionId');
                            $sheet->setCellValue('C1','ConnectionName');
                            $sheet->setCellValue('D1','Item');
                            $sheet->setCellValue('E1','ItemName');
                            $sheet->setCellValue('F1','Units');
                            $sheet->setCellValue('G1','UnitsBonus');

                            if($itemownerid == 5){
                                $sheet->setCellValue('H1','UnitsDiscount');
                                $sheet->setCellValue('I1','ItemDiscount');
                                $sheet->setCellValue('J1','ItemPrice');
                            }

                            if($itemownerid == 8 || $itemownerid == 9){
                                $sheet->setCellValue('H1','UnitsDiscount');
                                $sheet->setCellValue('I1','ItemDiscount');
                            }

                            $i = 0;
                            while($my_row=$dbtransactions->get_row())
                            {
                                $sheet->setCellValue('A'.($i+2), $my_row['Period']);
                                $sheet->setCellValue('B'.($i+2), $my_row['ConnectionId']);
                                $sheet->setCellValue('C'.($i+2), $my_row['ConnectionName']);
                                $sheet->setCellValue('D'.($i+2), $my_row['Item']);
                                $sheet->setCellValue('E'.($i+2), $my_row['ItemName']);
                                $sheet->setCellValue('F'.($i+2), $my_row['Units']);
                                $sheet->setCellValue('G'.($i+2), $my_row['UnitsBonus']);

                                if($itemownerid == 5){
                                    $sheet->setCellValue('H'.($i+2), $my_row['UnitsDiscount']);
                                    $sheet->setCellValue('I'.($i+2), $my_row['ItemDiscount']);
                                    $sheet->setCellValue('J'.($i+2), $my_row['ItemPrice']);
                                }

                                if($itemownerid == 8 || $itemownerid == 9){
                                    $sheet->setCellValue('H'.($i+2), $my_row['UnitsDiscount']);
                                    $sheet->setCellValue('I'.($i+2), $my_row['ItemDiscount']);
                                }

                                $i = $i + 1;
                            }
                            if($itemownerid == 5){
                                $sheet->getstyle('A1:J1')->getFont()->setBold(TRUE);
                                $sheet->getstyle('A1:J1')->getFont()->setSize(12);
                                foreach(range('A','J')as $column){
                                    $sheet->getColumnDimension($column)->setAutoSize(TRUE);
                                }
                            }
                            if($itemownerid == 8 || $itemownerid == 9){
                                $sheet->getstyle('A1:I1')->getFont()->setBold(TRUE);
                                $sheet->getstyle('A1:I1')->getFont()->setSize(12);
                                foreach(range('A','I')as $column){
                                    $sheet->getColumnDimension($column)->setAutoSize(TRUE);
                                }
                            }
                            else{
                                $sheet->getstyle('A1:G1')->getFont()->setBold(TRUE);
                                $sheet->getstyle('A1:G1')->getFont()->setSize(12);
                                foreach(range('A','G')as $column){
                                    $sheet->getColumnDimension($column)->setAutoSize(TRUE);
                                }
                            }
                        }
                }

                if($typeReport == 2)//sustentos lilly
                {
                        $items = 0;
                        $query = '';
                        $query = @" SELECT
										CONVERT(varchar(6), SI.SaleTransactionDateLocal, 112) AS Period,
	                                    S.ConnectionId,
	                                    Connections.ConnectionName AS [Cadena],
	                                    S.SaleInvoiceNumber AS Ticket,
	                                    CONVERT(VARCHAR,S.ConnectionId)+ CONVERT(VARCHAR,SI.TransactionId) AS [Transacción],
	                                    SI.SaleTransactionDateLocal AS [Fecha],
	                                    S.SaleTransactionStore AS [Sucursal],
	                                    CardsAffiliation.CardNumber AS [Tarjeta],
	                                    SI.Item AS [SKU],
	                                    I.ItemName AS Producto
	                                    ,ISNULL(SUM(SI.Quantity),0) AS [Cantidad]
	                                    ,ISNULL(SI.Discount,0) AS [Descuento]
	                                    ,ISNULL(SUM(SI.Quantity),0)-ISNULL(SUM(SI.Bonus),0) AS [Compras]
	                                    ,ISNULL(SUM(SI.Bonus),0) AS [Bonificaciones]
	                                    ,'OK' AS [Status]
									FROM dbo.SaleTransactionsItems SI WITH(NOLOCK)
									INNER JOIN dbo.SaleTransactions S WITH(NOLOCK)
									ON SI.SaleTransactionId = S.SaleTransactionId
                                    INNER JOIN CardsAffiliation WITH(NOLOCK)
                                    ON CardsAffiliation.CardAffiliationid = SI.CardAffiliationid
									INNER JOIN dbo.Connections WITH(NOLOCK)
									ON Connections.ConnectionId = S.ConnectionId
									LEFT JOIN dbo.ItemsList I WITH(NOLOCK)
									ON SI.Item = I.ItemSKU
									WHERE (CONVERT(varchar(6), SI.SaleTransactionDateLocal, 112) = '".$period."')
									AND (SI.TransactionTypeId NOT IN ('4'))
									AND (S.ConnectionId IN (
											SELECT DISTINCT St.ConnectionId
											FROM dbo.SettlementResults St WITH(NOLOCK)
											WHERE St.ItemOwnerId IN (".$itemownerid.")
											AND (CONVERT(varchar(6), St.SettlementDate, 112) = '".$period."')
											AND (St.ConnectionId <> 10)
									))
									AND (SI.Item IN (
											SELECT
													ItemSKU 
											FROM dbo.ItemsList WITH(NOLOCK)
											WHERE ItemGroupId IN (
													SELECT
															ItemGroupId
													FROM RulesBonus WITH(NOLOCK)
													WHERE RulePublishStatus='ACTIVE'
													AND RuleSubType='ordinary'
													AND (ItemOwnerId IN (".$itemownerid."))
											)
											UNION ALL 
											SELECT ItemSKU FROM dbo.ItemsList WITH(NOLOCK) WHERE ItemSKU IN ('7501082242720','7501082242409','7501082243406','7501082243420') AND (ItemOwnerId IN (".$itemownerid."))
									))
									AND (SI.CardAffiliationId IN (
										SELECT CardAffiliationId
										FROM dbo.CardsAffiliation WITH(NOLOCK)
										INNER JOIN dbo.CardsIssued WITH(NOLOCK)
										ON CardsAffiliation.CardNumber = CardsIssued.CardNumber
										WHERE (CardsIssued.ItemOwnerId IN (".$itemownerid."))
									))
									AND (SI.CardAffiliationId NOT IN (
										SELECT CardAffiliationId 
										FROM dbo.CardsAffiliationTestingList WITH(NOLOCK)
									))
                                    AND (SI.ItemId <> 9999)
									AND S.SaleTransactionEmployee <> 'Origis'
                                    AND NOT (S.ConnectionId=7  AND SI.item in ('7501082243406','7501082243420'))
                                    AND NOT (S.ConnectionId=41 AND SI.item in ('7501082243406','7501082243420'))
                                    AND NOT (SI.Item IN ('7501082242720','7501082242409','7501082243406','7501082243420') AND SI.Bonus=1)
                                    AND (SI.SaleTransactionItemId NOT IN(45495599,45495603,45330557,45590649))
									GROUP BY
										SI.TransactionId,
										CardsAffiliation.CardNumber,
										SI.SaleTransactionDateLocal,
										S.ConnectionId,
										S.SaleTransactionStore,
										Connections.ConnectionName,
										SI.Item,
										I.ItemName,
										SI.Discount,
										S.SaleInvoiceNumber
                                    UNION ALL--Farmacias del ahorro begin
                                     SELECT
                                         CONVERT(VARCHAR(6), GETDATE()-DAY(GETDATE()), 112) AS Period
                                         ,SettlementSourceId AS ConnectionId
                                         ,'FARMACIAS DEL AHORRO' AS Cadena
                                        ,NUMERO_TICKET AS Ticket
                                        ,TransaccionId AS [Transacción]
                                        ,FECHA_VENTA AS Fecha
                                        ,SUCURSAL AS Sucursal
                                        ,TARJETA AS Tarjeta
                                        ,ARTICULO AS SKU
                                        ,CASE
                                              WHEN ARTICULO = '7501082212136' THEN 'CIALIS 20 MG TAB 4'
                                              WHEN ARTICULO = '7501082212143' THEN 'CIALIS 20 MG TAB 1'
                                              WHEN ARTICULO = '7501082242058' THEN 'CIALIS 20 MG TAB 8'
                                        END AS Producto
                                        ,CASE
                                              WHEN ARTICULO = '7501082212136' THEN (CANTIDAD * 2) + Bonus
                                              WHEN ARTICULO = '7501082212143' THEN (CANTIDAD * 2) + Bonus
                                              WHEN ARTICULO = '7501082242058' THEN (CANTIDAD * 2) + Bonus
                                            ELSE CANTIDAD
                                         END AS Cantidad
                                        ,0 As Descuento
                                        ,CASE
                                              WHEN ARTICULO = '7501082212136' THEN (cantidad_obsequios * 2)
                                              WHEN ARTICULO = '7501082212143' THEN (cantidad_obsequios * 2)
                                              WHEN ARTICULO = '7501082242058' THEN (cantidad_obsequios * 2)
                                            ELSE CANTIDAD
                                         END AS Compras
                                        ,CASE
                                              WHEN ARTICULO = '7501082212136' THEN cantidad_obsequios
                                              WHEN ARTICULO = '7501082212143' THEN cantidad_obsequios
                                              WHEN ARTICULO = '7501082242058' THEN cantidad_obsequios
                                            ELSE 0
                                         END AS Bonificaciones
                                        ,'OK' AS Status--TransaccionStatus AS Status
                                        FROM Settlement10 AS FAHORRO WITH(NOLOCK)
                                        WHERE convert(varchar(6), fecha_operacion, 112) = '".$period."'
                                        and FAHORRO.itembrand  IN ('lillycialis')
                                        AND FAHORRO.transaccionstatus   in ('FailBonusUnknown','FailBonusZero','OKbonus')
                                    UNION ALL
                                     SELECT
                                         CONVERT(VARCHAR(6), GETDATE()-DAY(GETDATE()), 112) AS Period
                                         ,SettlementSourceId AS ConnectionId
                                         ,'FARMACIAS DEL AHORRO' AS Cadena
                                        ,NUMERO_TICKET AS Ticket
                                        ,TransaccionId AS [Transacción]
                                        ,FECHA_VENTA AS Fecha
                                        ,SUCURSAL AS Sucursal
                                        ,TARJETA AS Tarjeta
                                        ,ARTICULO AS SKU
                                        ,CASE
	                                          WHEN ARTICULO = '7501082243406' THEN 'TRULICITY 0.75MG/0.5ML'
                                              WHEN ARTICULO = '7501082243420' THEN 'TRULICITY 1.5MG/0.5ML'
                                        END AS Producto
                                        ,CANTIDAD AS Cantidad
                                        ,0 As Descuento
                                        ,cantidad_obsequios AS Compras
                                        ,0 AS Bonificaciones
                                        ,'OK' AS Status--TransaccionStatus AS Status
                                    FROM Settlement10 AS FAHORRO WITH(NOLOCK)
									LEFT JOIN ItemsList on
									RIGHT('0000000000000' + Ltrim(Rtrim(RTRIM(LTRIM(FAHORRO.articulo)))),13) = ItemsList.ItemSKU
                                    WHERE convert(varchar(6), fecha_operacion, 112) = '".$period."'
                                    and FAHORRO.itembrand IN ('lillytrulicity')
									AND FAHORRO.transaccionstatus IN ('FailDiscountQuantity','OKdiscount')
                                    ORDER BY convert(int,S.ConnectionId);"; //Fahorro end 
				        
				        $dbtransactions->query($query);
           
				        $items = $dbtransactions->count_rows();
				        if ($items > 0) {
                    
                            $excel->setActiveSheetIndex(0);

                            $sheet = $excel->getActiveSheet();
                            $sheet->setTitle($itemownername);

                            $sheet->setCellValue('A1','Period');
	                        $sheet->setCellValue('B1','ConnectionId');
	                        $sheet->setCellValue('C1','Cadena');
	                        $sheet->setCellValue('D1','Ticket');
	                        $sheet->setCellValue('E1','Transacción');
	                        $sheet->setCellValue('F1','Fecha');
	                        $sheet->setCellValue('G1','Sucursal');
	                        $sheet->setCellValue('H1','Tarjeta');
	                        $sheet->setCellValue('I1','SKU');
	                        $sheet->setCellValue('J1','Producto');
	                        $sheet->setCellValue('K1','Cantidad');
	                        $sheet->setCellValue('L1','Descuento');
	                        $sheet->setCellValue('M1','Compras');
	                        $sheet->setCellValue('N1','Bonificaciones');
	                        $sheet->setCellValue('O1','Status');

                            $i = 0;
                            while($my_row=$dbtransactions->get_row())
                            {
                                $sheet->setCellValue('A'.($i+2), $my_row['Period']);
	                            $sheet->setCellValue('b'.($i+2), $my_row['ConnectionId']);
	                            $sheet->setCellValue('C'.($i+2), $my_row['Cadena']);
	                            $sheet->setCellValue('D'.($i+2), $my_row['Ticket']);
	                            $sheet->setCellValue('E'.($i+2), $my_row['Transacción']);
	                            $sheet->setCellValue('F'.($i+2), $my_row['Fecha']);
	                            $sheet->setCellValue('G'.($i+2), $my_row['Sucursal']);
	                            $sheet->setCellValue('H'.($i+2), $my_row['Tarjeta']);
	                            $sheet->setCellValue('I'.($i+2), $my_row['SKU']);
	                            $sheet->setCellValue('J'.($i+2), $my_row['Producto']);
	                            $sheet->setCellValue('K'.($i+2), $my_row['Cantidad']);
	                            $sheet->setCellValue('L'.($i+2), $my_row['Descuento']);
	                            $sheet->setCellValue('M'.($i+2), $my_row['Compras']);
	                            $sheet->setCellValue('N'.($i+2), $my_row['Bonificaciones']);
	                            $sheet->setCellValue('O'.($i+2), $my_row['Status']);

                                $i = $i + 1;
                            } // [while($my_row=$dbtransactions->get_row())]

                            $sheet->getstyle('A1:O1')->getFont()->setBold(TRUE);
                            $sheet->getstyle('A1:O1')->getFont()->setSize(12);
                            foreach(range('A','O')as $column){
                                $sheet->getColumnDimension($column)->setAutoSize(TRUE);
                            }
                        }// [if ($items > 0) {]
                }//[if($typeReport == 2)]
        }// [if($itemownerid == 6){]

$objwriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$objwriter->save('php://output');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
    </head>
    <body>
        
    </body>
</html>

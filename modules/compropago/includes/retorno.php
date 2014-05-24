<?php
include_once('../../../config/config.inc.php');
include_once('compropago.php');

     
        if (isset($_REQUEST['topic']) && isset($_REQUEST['id'])) {

		$id = $_REQUEST['id'];
		$client_id = Db::getInstance()->getRow("SELECT value FROM "._DB_PREFIX_."configuration WHERE name = 'compropago_CLIENT_ID'");
		$client_secret = Db::getInstance()->getRow("SELECT value FROM "._DB_PREFIX_."configuration WHERE name = 'compropago_CLIENT_SECRET'");
		

		$mp = new MP ($client_id['value'], $client_secret['value']);
		$mp->sandbox_mode($sandbox['value'] == "active" ? true:false);
		$dados = $mp->get_payment_info ($id);
		$dados = $dados['response'];
		
		$order_id = $dados['collection']['external_reference'];
		$order_status = $dados["collection"]["status"];
	
	
		switch ($order_status) {
			case 'approved':
				$nomestatus = "compropago_STATUS_1";
				break;
			case 'pending':
				$nomestatus = "compropago_STATUS_0";
				break;    
			case 'in_process':
				$nomestatus = "compropago_STATUS_0";   
				break;    
			case 'reject':
				$nomestatus = "compropago_STATUS_2"; 
				break;    
			case 'refunded':
				$nomestatus = "compropago_STATUS_2";
				break;    
			case 'cancelled':
				$nomestatus = "compropago_STATUS_2";     
				break;    
			case 'in_metiation':
				$nomestatus = "compropago_STATUS_0";
				break;   
		}
		
		// Get Id StatusDb::getInstance()->getRow("SELECT value FROM "._DB_PREFIX_."configuration WHERE name = 'compropago_CLIENT_ID'");
		$result = Db::getInstance()->getRow("SELECT value FROM "._DB_PREFIX_."configuration WHERE name = '".$nomestatus."'");
		$state = $result['value'];
		
		
		// Update order
		Db::getInstance()->Execute("INSERT INTO "._DB_PREFIX_."order_history (`id_employee`, `id_order`, `id_order_state`, `date_add`) VALUES ('0', '".$order_id."', '". $state . "', NOW())");
		
		
		// Send email
		$extraVars = array();
		$history = new OrderHistory();
		$history->id_order = intval($order_id);
		$history->changeIdOrderState(intval($state),intval($order_id));
		$history->addWithemail(true,$extraVars);
		
        
	}  
        
        


?>
    
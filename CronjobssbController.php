<?php
//die('Wait...');
error_reporting(0);

ini_set('max_execution_time', 1500);

App::uses('Folder', 'Utility');
App::uses('File', 'Utility'); 

class CronjobssbController extends AppController
{
    
    var $name = "Cronjobssb";
    
    var $components = array('Session','Upload','Common','Auth');
    
    var $helpers = array('Html','Form','Common','Session');
    
    public function beforeFilter()
    {
            parent::beforeFilter();
            $this->layout = false;
            /*$this->Auth->Allow(array('saveOpenOrder', 'callOpenOrders' , 'updateMergeOrder', 'assign_services', 'getBarcode', 'setAgainAssignedServiceToAllOrder','removePickList','assignRegisteredBarcode','fetchOpenOrders','order_location_update'));
			 //die('maintenance going on!');*/
    }
      
      /* 
       * 
       * Params, Get client time
       *  
       */ 
      public function getClientTime()
      {
		  $this->layout = '';
		  $this->autoRender = false;
		  
		  date_default_timezone_set('Europe/Jersey');
		  
		  $serviceProvider = $this->request->data['serviceProvider'];
		  if( $serviceProvider == "Jersey Post" )
		  {
			  if( time() > strtotime('3 pm') )
			  {
				  echo '0';
				  exit;
			  }
			  else
			  {
				 
				  echo '1';
				  exit;
			  }
		  }
		  else
		  {			  
			  if( time() > strtotime('1 pm') )
			  {
				  echo '0';
				  exit;
			  }
			  else
			  {
				 
				  echo '1';
				  exit;
			  }
		  }
	  }
        
      public function getCurrencyrate()
		{
			$this->layout = '';
			$this->autoRender = false;
			$this->loadModel( 'CurrencyExchange' );
  
			$curs=array('GBP','EUR');
			$url='http://www.floatrates.com/daily/eur.xml';
			$xml=file_get_contents($url);
			$obj=json_decode(json_encode(simplexml_load_string($xml)),true);
			$data['rate'] 		= 	$obj['item'][1]['exchangeRate'];
			$data['currency'] 	= 	$obj['item'][1]['targetCurrency'];
		   
			$this->CurrencyExchange->save($data);
		}
		
			 
			
			public function getTruncate()
			{
				
				$this->layout = '';
				$this->autoRender = false;
				
				/*$this->loadModel('AssignService');
				$this->loadModel('MergeUpdate');
				$this->loadModel('OpenOrder');
				$this->loadModel('ScanOrder');
				$this->loadModel('ServiceCounter');
				$this->loadModel('SortingoperatortimeCalculations');
				$this->loadModel('UnprepareOrder');
				
				$this->AssignService->query( 'TRUNCATE assign_services' );
				$this->MergeUpdate->query( 'TRUNCATE merge_updates' );
				$this->OpenOrder->query( 'TRUNCATE open_orders' );
				$this->ScanOrder->query( 'TRUNCATE scan_orders' );
				$this->ServiceCounter->query( 'TRUNCATE service_counters' );
				$this->SortingoperatortimeCalculations->query( 'TRUNCATE sortingoperatortime_calculations' );
				$this->UnprepareOrder->query( 'TRUNCATE unprepare_orders' );*/
				
				exit;
			}
            
            /*
			 * 
			 * 
			 * Params, Update inventory by SKU
			 * 
			  
			public function reserveInventoryForUnknown_live( $orderItems = null , $type = null , $NumOrderId = null , $check = null )
			{
				
				$this->loadModel( 'Product' );
				
				$orderSku = array();
				$ik = 0;foreach( $orderItems as $index => $value )
				{
					
					$sku = explode( '-' , $value->SKU);
					if( count( $sku ) == 2 ) //For single
					{
						$orderSku[$ik][0] = $value->SKU;
						$orderSku[$ik][1] = $value->Quantity;
						$ik++;
					}
					else if( count( $sku ) == 3 ) // For Bundle (Single)
					{
						$orderSku[$ik][0] = 'S-'.$sku[1];
						$orderSku[$ik][1] = $value->Quantity * ($sku[2]);
						$ik++;
					}
					else if( count( $sku ) > 3 ) // For Bundle (Bundle)
					{
						
						$inc = 1;$ij = 0;while( $ij < count($sku)-2 )
						{
							
							$orderSku[$ik][0] = 'S-'.$sku[$inc];
							$orderSku[$ik][1] = $value->Quantity;
						
						$inc++;
						$ij++;
						$ik++;	
						}
					}					
				}
				//pr($orderSku);
				//Update in queue inventory
				foreach( $orderSku as $orderSkuIndex => $orderSkuValue )
				{
					
					$paramInner = array(
						 'conditions' => array(
							 'Product.product_sku' => $orderSkuValue[0]
						 ),
						 'fields' => array(
							 'Product.current_stock_level as CurrentStock',
							 'Product.id',
							 'Product.product_sku'
						 )  
					   );
					   $openOrderRow = json_decode(json_encode($this->Product->find('first' , $paramInner)),0);
					   //pr($openOrderRow);
					   
					   $orderSkuStock = $orderSkuValue[1];
					   //inventory manipulation					   
					   if( $type == 2 )
							$calculateStock = $openOrderRow->Product->CurrentStock + $orderSkuStock;
					   else 
							$calculateStock = $openOrderRow->Product->CurrentStock - $orderSkuStock;		
							
					   //echo "Update Stock : " . $calculateStock;
					   //echo "<br>"; 
					   
					   //Update inventory now very easy
					   $id = $openOrderRow->Product->id;  
					   $data['Product']['id'] = $openOrderRow->Product->id;
					   $data['Product']['current_stock_level'] = $calculateStock;
					   
					  // pr($data['Product']);
					   
					   //$this->Product->saveAll( $data , false);
					   //echo "UPDATE products set products.current_stock_level = {$calculateStock} where id = {$id}"; exit;
					   
					   if( isset( $id ) && $id > 0 )
					   $this->Product->query( "UPDATE products set products.current_stock_level = {$calculateStock} where id = {$id}" );
					   
					   $paramInner = array(
						 'conditions' => array(
							 'Product.product_sku' => $orderSkuValue[0]
						 ),
						 'fields' => array(
							 'Product.current_stock_level as CurrentStock',
							 'Product.id',
							 'Product.product_sku'
						 )  
					   ); 
					   
					   $openOrderRow = json_decode(json_encode($this->Product->find('first' , $paramInner)),0);
					   //pr($openOrderRow);  
					  
					   //echo "<br>*************vvvvvvvvvvvvvvvvvvv******************<br>";
					
				}
				
			}*/
			
		
		public function rs( $orderItems = null , $type = null , $NumOrderId = null , $check = null )
		{
			
			$this->loadModel( 'Product' );
			$this->loadModel( 'OversellRecord' );						
			
			$orderSku = array();
			$ik = 0;foreach( $orderItems as $index => $value )
			{
				
				$sku = explode( '-' , $value->SKU);
				if( count( $sku ) == 2 ) //For single
				{
					$orderSku[$ik][0] = $value->SKU;
					$orderSku[$ik][1] = $value->Quantity;
					$ik++;
				}
				else if( count( $sku ) == 3 ) // For Bundle (Single)
				{
					$orderSku[$ik][0] = 'S-'.$sku[1];
					$orderSku[$ik][1] = $value->Quantity * ($sku[2]);
					$ik++;
				}
				else if( count( $sku ) > 3 ) // For Bundle (Bundle)
				{
					
					$inc = 1;$ij = 0;while( $ij < count($sku)-2 )
					{
						
						$orderSku[$ik][0] = 'S-'.$sku[$inc];
						$orderSku[$ik][1] = $value->Quantity;
					
					$inc++;
					$ij++;
					$ik++;	
					}
				}					
			}
			//pr($orderSku);
			//Update in queue inventory
			foreach( $orderSku as $orderSkuIndex => $orderSkuValue )
			{
				
				$paramInner = array(
					 'conditions' => array(
						 'Product.product_sku' => $orderSkuValue[0]
					 ),
					 'fields' => array(
						 'Product.current_stock_level as CurrentStock',
						 'Product.id',
						 'Product.product_sku',
						 'ProductDesc.barcode'
					 )  
				   );
				   $openOrderRow = json_decode(json_encode($this->Product->find('first' , $paramInner)),0);
				   //pr($openOrderRow);
				   
				   //Update inventory now very easy
				   $id = $openOrderRow->Product->id;  
				   //$data['Product']['id'] = $openOrderRow->Product->id;
				   //$data['Product']['current_stock_level'] = $calculateStock;
				   
				   $orderSkuStock = $orderSkuValue[1];
				   
				   //inventory manipulation					   
				   if( $type == 2 )
				   {
				   
						$ckSku = $orderSkuValue[0];
						if( $check == 1 )
						{
							
							$param = array(
						
								'conditions' => array(
								
									'OversellRecord.sku' => $ckSku,
									'OversellRecord.num_order_id' => $NumOrderId
								
								)
							
							);
							$count = $this->OversellRecord->find( 'count' , $param );
							
							if( $count == 0 )
								$calculateStock = $openOrderRow->Product->CurrentStock + $orderSkuStock;	
						}
						else
						{
							//echo $openOrderRow->Product->CurrentStock .' + ' . $orderSkuStock;
							//exit;
							
							$calculateStock = $openOrderRow->Product->CurrentStock + $orderSkuStock;	
						}
						
						if( $id > 0  )	
					    {
						   if( $calculateStock <= 0 )
						   {
							   $this->Product->query( "UPDATE products set products.current_stock_level = 0 where id = {$id}" );
						   }
						   else
						   {
								$this->Product->query( "UPDATE products set products.current_stock_level = {$calculateStock} where id = {$id}" );
						   }
						}
						
				   }	
				   else
				   {  
					  
					   if( $openOrderRow->Product->CurrentStock <= 0 )
					   {
						  $rest = 0 - $orderSkuStock;
					   }
					   else if( ($openOrderRow->Product->CurrentStock < $orderSkuStock) && ($openOrderRow->Product->CurrentStock <= 0) )
					   {
						  $rest = 0 - $orderSkuStock;
					   }  
					   else if( ($openOrderRow->Product->CurrentStock < $orderSkuStock) && ($openOrderRow->Product->CurrentStock > 0) )
					   {
						  $rest = $openOrderRow->Product->CurrentStock - $orderSkuStock;
					   }
					   else if( $openOrderRow->Product->CurrentStock > $orderSkuStock )
					   {
						  $rest = $openOrderRow->Product->CurrentStock - $orderSkuStock;
					   }	  
					   
					   //Over sell check up
					   if( $openOrderRow->Product->CurrentStock <= 0 ) 
					   {
							//store record future reference  //$NumOrderId							
							$data['OversellRecord']['OversellRecord']['num_order_id']		= $NumOrderId;
							$data['OversellRecord']['OversellRecord']['sku']				= $orderSkuValue[0];
							$data['OversellRecord']['OversellRecord']['original_qty']		= $orderSkuValue[1];
							$data['OversellRecord']['OversellRecord']['marker']			= 3;							
							$this->OversellRecord->saveAll( $data['OversellRecord'] );
					   }
						
					   if( $id > 0  )	
					   {
						   if( $rest <= 0 )
						   {
							   $this->Product->query( "UPDATE products set products.current_stock_level = 0 where id = {$id}" );
						   }
						   else
						   {
								$this->Product->query( "UPDATE products set products.current_stock_level = {$rest} where id = {$id}" );
						   }
						}
						
				   }

				   /*if( $openOrderRow->Product->CurrentStock <= 0 )
				   {
						$this->Product->query( "UPDATE products set products.current_stock_level = 0 where id = {$id}" );
				   }
				   else
				   {
						$this->Product->query( "UPDATE products set products.current_stock_level = {$calculateStock} where id = {$id}" );
				   }*/
				
				}
				
			}
		
		
			
		/*
		 * 
		 * 
		 * Params, Update inventory by SKU
		 * 
		 */
		public function reserveInventoryForUnknown( $orderItems = null , $type = null , $NumOrderId = null , $check = null )
		{
			
			$this->loadModel( 'Product' );
			$this->loadModel( 'OversellRecord' );						
			
			$orderSku = array();
			$ik = 0;foreach( $orderItems as $index => $value )
			{
				
				$sku = explode( '-' , $value->SKU);
				if( count( $sku ) == 2 ) //For single
				{
					$orderSku[$ik][0] = $value->SKU;
					$orderSku[$ik][1] = $value->Quantity;
					$orderSku[$ik][2] = $value->RowId;
					$ik++;
				}
				else if( count( $sku ) == 3 ) // For Bundle (Single)
				{
					$orderSku[$ik][0] = 'S-'.$sku[1];
					$orderSku[$ik][1] = $value->Quantity * ($sku[2]);
					$orderSku[$ik][2] = $value->RowId;
					$ik++;
				}
				else if( count( $sku ) > 3 ) // For Bundle (Bundle)
				{
					
					$inc = 1;$ij = 0;while( $ij < count($sku)-2 )
					{
						
						$orderSku[$ik][0] = 'S-'.$sku[$inc];
						$orderSku[$ik][1] = $value->Quantity;
						$orderSku[$ik][2] = $value->RowId;
					$inc++;
					$ij++;
					$ik++;	
					}
				}					
			}
			//pr($orderSku);
			//Update in queue inventory
			foreach( $orderSku as $orderSkuIndex => $orderSkuValue )
			{
				$channel_sku = $orderSkuValue[2];
				
				$paramInner = array(
					 'conditions' => array(
						 'Product.product_sku' => $orderSkuValue[0]
					 ),
					 'fields' => array(
						 'Product.current_stock_level as CurrentStock',
						 'Product.id',
						 'Product.product_sku',
						 'ProductDesc.barcode'
					 )  
				   );
				   $openOrderRow = json_decode(json_encode($this->Product->find('first' , $paramInner)),0);
				   //pr($openOrderRow);
				   
				   $extSku = $orderSkuValue[0];
				   
				   //Update inventory now very easy
				   $id = @$openOrderRow->Product->id;  
				   //$data['Product']['id'] = $openOrderRow->Product->id;
				   //$data['Product']['current_stock_level'] = $calculateStock;
				   
				   $orderSkuStock = $orderSkuValue[1];
				   
				   //inventory manipulation					   
				   if( $type == 2 )
				   {
				   
						$ckSku = $orderSkuValue[0];
						if( $check == 1 )
						{
							
							$param = array(
						
								'conditions' => array(
								
									'OversellRecord.sku' => $ckSku,
									'OversellRecord.num_order_id' => $NumOrderId
								
								)
							
							);
							$count = $this->OversellRecord->find( 'count' , $param );
							
							if( $count == 0 )
								$calculateStock = $openOrderRow->Product->CurrentStock + $orderSkuStock;	
						}
						else
						{
							$calculateStock = $openOrderRow->Product->CurrentStock + $orderSkuStock;	
						}
						
						if( $id > 0  )	
					    {
							$extStock = $openOrderRow->Product->CurrentStock;
						   if( $calculateStock <= 0 )
						   {
							   $this->Product->query( "UPDATE products set products.current_stock_level = 0 where id = {$id}" );
						   }
						   else
						   {
								$this->Product->query( "UPDATE products set products.current_stock_level = {$calculateStock} where id = {$id}" );
						   }
						   $this->writeLogInventory( $ckSku , $extStock .'<>'. $orderSkuStock .' ( Reserve but UP Inventory ) '); 
						   
						   // store current stock level with current order quantity
						   $actionType = 'Reserve Up';
						   $this->storeInventoryRecord( $openOrderRow , $orderSkuStock , $NumOrderId , $actionType,'' , $channel_sku );
						   
						}
						
				   }	
				   else if( $type == 1 )
				   {  
					   
					   $extStock = @$openOrderRow->Product->CurrentStock;
					   if( $openOrderRow->Product->CurrentStock <= 0 )
					   {
						  $rest = 0 - $orderSkuStock;
					   }
					   else if( ($openOrderRow->Product->CurrentStock < $orderSkuStock) && ($openOrderRow->Product->CurrentStock <= 0) )
					   {
						  $rest = 0 - $orderSkuStock;
					   }  
					   else if( ($openOrderRow->Product->CurrentStock < $orderSkuStock) && ($openOrderRow->Product->CurrentStock > 0) )
					   {
						  $rest = $openOrderRow->Product->CurrentStock - $orderSkuStock;
					   }
					   else if( $openOrderRow->Product->CurrentStock > $orderSkuStock )
					   {
						  $rest = $openOrderRow->Product->CurrentStock - $orderSkuStock;
					   }	  
					   
					   //Over sell check up
					   if( $openOrderRow->Product->CurrentStock <= 0 ) 
					   {
							//store record future reference  //$NumOrderId							
							$data['OversellRecord']['OversellRecord']['num_order_id']		= $NumOrderId;
							$data['OversellRecord']['OversellRecord']['sku']				= $orderSkuValue[0];
							$data['OversellRecord']['OversellRecord']['original_qty']		= $orderSkuValue[1];
							$data['OversellRecord']['OversellRecord']['marker']			= 3;							
							$this->OversellRecord->saveAll( $data['OversellRecord'] );
					   }
						
					   if( $id > 0  )	
					   {
						   
						   $this->loadModel( 'InventoryRecord' );
						   
						   $unp_order = array('234534');
						   
						   if(in_array($NumOrderId,$unp_order)){
							   $paramInventory = array(						   
									'conditions' => array(								
										'InventoryRecord.split_order_id' => $NumOrderId,
										'InventoryRecord.sku' => $orderSkuValue[0]
										
									)
							   
							   );
						   }else{
							   $paramInventory = array(
									'conditions' => array(
										'InventoryRecord.split_order_id' => $NumOrderId,
										'InventoryRecord.sku' => $orderSkuValue[0],
										'InventoryRecord.channel_sku' =>  $channel_sku
									)
							   
							   );
						   }
						   
						   //'InventoryRecord.sku' => $orderSkuValue[0]
						   //$orderSkuValue[0]
						   $countRecords = $this->InventoryRecord->find( 'count' , $paramInventory );
						   if( $countRecords == 0 )
						   {
							   
							   if( $rest <= 0 )
							   {
								   $this->Product->query( "UPDATE products set products.current_stock_level = 0 where id = {$id}" );
							   }
							   else
							   {
									$this->Product->query( "UPDATE products set products.current_stock_level = {$rest} where id = {$id}" );
							   }
							   
							   $this->writeLogInventory( $extSku , $extStock .'<>'. $orderSkuStock .' ( Reserve Inventory ) '); 
							   
							   // store current stock level with current order quantity
							   $actionType = 'Reserve Inventory';
							   $this->storeInventoryRecord( $openOrderRow , $orderSkuStock , $NumOrderId , $actionType, '' , $channel_sku);
							   
								   
							   }
						   
						}
						
				   }

				   /*if( $openOrderRow->Product->CurrentStock <= 0 )
				   {
						$this->Product->query( "UPDATE products set products.current_stock_level = 0 where id = {$id}" );
				   }
				   else
				   {
						$this->Product->query( "UPDATE products set products.current_stock_level = {$calculateStock} where id = {$id}" );
				   }*/
				
				}
				
			}
			
			
            public function unprepareOrder( $result = null , $sourceName = null ,  $orderId = null )
            {
				
				//pr($result);
				
				/*------Added on 19 AUG 2021----*/
				$ord_flag = WWW_ROOT .'logs-ord/'.$result->NumOrderId.".flag";	
				if(file_exists($ord_flag)){
  					file_put_contents(WWW_ROOT .'logs-ord/unprepareOrder_'.date('dmY').'.log',$result->NumOrderId."\n", FILE_APPEND|LOCK_EX);
					$flagVar = 2;
					return $flagVar;
					exit;
				}
				/*-------End----*/
					
				$flagVar = 3;
				$this->layout = 'index';
				$this->autoRender = false;
				$this->loadModel('OpenOrder');
				$this->loadModel('AssignService');
				$this->loadModel('Customer');
				$this->loadModel('OrderItem');
				$this->loadModel('Product');
				$this->loadModel('UnprepareOrder');				

				$data['order_id']		= $result->OrderId;
				$data['num_order_id']	= $result->NumOrderId;
				$data['general_info']	= serialize($result->GeneralInfo);
				$data['shipping_info']	= serialize($result->ShippingInfo);
				$data['customer_info']	= serialize($result->CustomerInfo);
				$data['totals_info']	= serialize($result->TotalsInfo);
				$data['folder_name']	= serialize($result->FolderName);
				$data['items']			= serialize($result->Items);
				$data['date']			= date( 'Y-m-d H:i:s' );
				$data['unprepare_check']= 1;
				$data['source_name']= $sourceName;
				
				//Extra information will save my according to manage sorting station section
				$country = $data['destination'] = $result->CustomerInfo->Address->Country;
				$orderitems	=	unserialize($data['items']);
				
				// Get order is exist here
				$checkorder = $this->UnprepareOrder->find('first', array(
							  'conditions'=>array(
								'UnprepareOrder.num_order_id' => $result->NumOrderId) , 
							  'fields' => array( 
								'UnprepareOrder.num_order_id', 
								'UnprepareOrder.destination',
								'UnprepareOrder.id'  
							)
						)
					);
						
				if( count($checkorder) > 0 )
				{
					 
					$setData['UnprepareOrder']['id'] = $checkorder['UnprepareOrder']['id'];
					$setData['UnprepareOrder']['destination'] = $country;
					$setData['UnprepareOrder']['customer_info'] = serialize($result->CustomerInfo);
					if( $country !== "UNKNOWN" )
					{
						//$this->UnprepareOrder->saveAll( $setData );
						
						$this->reserveInventoryForUnknown( $result->Items , 4 , $result->NumOrderId, $checkorder['UnprepareOrder']['unprepare_check'] );
						$this->UnprepareOrder->delete( $checkorder['UnprepareOrder']['id'] );
						 
 						$flagVar = 1;
						return $flagVar;
					}
					else
					{
						$flagVar = 2;
						return $flagVar;
					}
				}
				else
				{
					$this->UnprepareOrder->create();
					$this->UnprepareOrder->save($data);
					$flagVar = 2;
					
					//Update product quantity / inventory at run time when order is splitted
					if( $flagVar == 2 )
					{
						$this->reserveInventoryForUnknown( $result->Items , 1 , $result->NumOrderId , $data['unprepare_check'] );
					}
					return $flagVar;
				}				
				
			}    
			       
			public function saveOpenOrder_old()
			{
					
				$this->layout = 'index';
				$this->autoRender = false;
				$this->loadModel('OpenOrder');
				$this->loadModel('AssignService');
				$this->loadModel('Customer');
				$this->loadModel('OrderItem');
				$this->loadModel('Product');
				$this->loadModel('UnprepareOrder');
				
				//Sync start
				App::import( 'Controller' , 'MyExceptions' );
				$exception = new MyExceptionsController();
				$exception->syncCalling();
				
				exit;
				App::import('Vendor', 'linnwork/api/Auth');
				App::import('Vendor', 'linnwork/api/Factory');
				App::import('Vendor', 'linnwork/api/Orders');
			
				$username = Configure::read('linnwork_api_username');
				$password = Configure::read('linnwork_api_password');
				
				$multi = AuthMethods::Multilogin($username, $password);
				
				$auth = AuthMethods::Authorize($username, $password, $multi[0]->Id);	

				$token = $auth->Token;	
				$server = $auth->Server;
			  
			    $openorder	=	OrdersMethods::GetOpenOrders('2500','1','','','00000000-0000-0000-0000-000000000000','',$token, $server);
				
				foreach($openorder->Data as $orderids)
				{
					$orders[]	=	$orderids->OrderId;
				}
				
				/*echo "HI";
				pr($orders);
				echo "hmm"; exit;*/
				
				$results	=	OrdersMethods::GetOrders($orders,'00000000-0000-0000-0000-000000000000',true,true,$token, $server);
				
				$itt = 1;
				$countryArray = Configure::read('customCountry');
				foreach($results as $result)
				{
					/*if( ($result->GeneralInfo->Status == 1 || $result->GeneralInfo->Status == 4 ) && $result->GeneralInfo->HoldOrCancel == ''  )
					{*/
					$data['order_id']		= $result->OrderId;
					$data['num_order_id']	= $result->NumOrderId;
					$data['general_info']	= serialize($result->GeneralInfo);
					$data['shipping_info']	= serialize($result->ShippingInfo);
					$data['customer_info']	= serialize($result->CustomerInfo);
					$data['totals_info']	= serialize($result->TotalsInfo);
					$data['folder_name']	= serialize($result->FolderName);
					$data['items']			= serialize($result->Items);
					$data['linn_fetch_orders'] = $result->GeneralInfo->Status;
					$data['sub_source'] = $result->GeneralInfo->SubSource;
					
					//Extra information will save my according to manage sorting station section
					$country = $data['destination'] = $result->CustomerInfo->Address->Country;
					$orderitems	=	unserialize($data['items']);
					
					$flagVar = 3;
					
					//echo "<br>";
					//echo $itt . '==' . $data['num_order_id']	= $result->NumOrderId;
					//echo "<br>";
					
					$itt++;
					
					//Check Unpreparee step by step
					$getAllUnprepardId = $this->UnprepareOrder->find('first', array('conditions'=>array('UnprepareOrder.order_id' => $result->OrderId) , 'fields' => array( 'UnprepareOrder.order_id', 'UnprepareOrder.num_order_id' , 'UnprepareOrder.id' , 'UnprepareOrder.linn_fetch_orders' , 'UnprepareOrder.destination' )));
					
					if( count($getAllUnprepardId) > 0 )
					{
						
						if( $country !== $getAllUnprepardId['UnprepareOrder']['destination'] )
						{
							$flagVar = $this->unprepareOrder($result);
						}
						else
						{
							$flagVar = $this->unprepareOrder($result);
						}
						
					}
					else
					{
						
						//If it is UNKNOWN or not
						if( $country == "UNKNOWN" )
						{
							$flagVar = $this->unprepareOrder($result);
						}
						else
						{
							$flagVar = 3;
						}
						
					}
						
					if( $flagVar != 2 )
					{	
						
						//Check OpenOrder
						$this->OpenOrder->create();
						$checkorder 	=	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $result->OrderId) , 'fields' => array( 'OpenOrder.order_id', 'OpenOrder.num_order_id' , 'OpenOrder.id' , 'OpenOrder.linn_fetch_orders' )));
						
						if(count($checkorder) > 0)
						{
							
							//Clean Orders
							$this->cleanOrders();
							
							//CHECK IF ORDER EXISTS OR NOT
							// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
							$linnStatus = $result->GeneralInfo->Status;
							$dataUpdate['OpenOrder']['id'] = $checkorder['OpenOrder']['id'];
							$dataUpdate['OpenOrder']['linn_fetch_orders'] = $result->GeneralInfo->Status;							
                            //$dataUpdate['OpenOrder']['linn_fetch_orders'] = serialize($result->CustomerInfo);
							$this->OpenOrder->saveAll( $dataUpdate ); 
							
							//Now update into Merge Section
							$this->loadModel( 'MergeUpdate' );
							
							//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
							$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $result->GeneralInfo->Status), array('MergeUpdate.order_id' => $result->NumOrderId) );
						}
						else //if( $flagVar != 2 )
						{
							
							$this->OpenOrder->save($data);
							
							//Clean Orders
							$this->cleanOrders();
							
							$getCurrencyText = $result->TotalsInfo->Currency;
							
							if( $getCurrencyText == "EUR" )
							{
								$baseRate = '1';
							}
							else
							{
								$baseRate = '1.38';
							}
							
							/***************** split the order item ******************/
							$orderItemValueTotal = 0;foreach( $orderitems as $orderitem )
							{
								$orderItemValueTotal = $orderItemValueTotal + $orderitem->Cost;
							}
							
							//Get special postal service name as discussed by shashi at run time when did launch
							$serviceNameNow = unserialize($data['shipping_info']);
							$servicePostal = $serviceNameNow->PostalServiceName;
							if( $servicePostal == "Standard_Jpost" )
							{	
								
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}
								
								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								foreach( $orderitems as $orderitem )
								{
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;
										
									}
																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$combinePrice;
								$splititem['product_order_id_identify']		=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$result->NumOrderId;
								$splititem['sku']			=	$combineSkuVisit;								
								$splititem['barcode']		=	$combineBarcode;
								
								//pr($splititem);
								
								$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else if( ($orderItemValueTotal * $baseRate) > 100 )
							{	
								
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}

								
								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								foreach( $orderitems as $orderitem )
								{
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;
										
									}
																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$combinePrice;
								$splititem['product_order_id_identify']		=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$result->NumOrderId;
								$splititem['sku']			=	$combineSkuVisit;								
								$splititem['barcode']		=	$combineBarcode;
								
								//pr($splititem);
								
								$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else
							{
								$bundleIdentity = 0;foreach( $orderitems as $orderitem )
								{
									//echo $orderitem->SKU . '==' . $orderitem->PricePerUnit;
									//echo "<br>";
									
									$splitskus	=	explode('-', $orderitem->SKU);
									$count	=	count($splitskus);
									
									if( count( $orderitems ) > 1 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
									
									//Find Country
									if( in_array( $country , $countryArray ) )
									{			
										
										//It means, Inside EU country shipping for bundle with sameSKU
										if($splitskus['0'] == 'B')
										{
											
											for( $i = 1; $i <= count($splitskus)-2 ; $i++ )
											{																						
												if( $count == 3 )
												{													
													$value = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
													
													//echo $value = $orderitem->CostIncTax * $baseRate;
													//echo "<br>";
													
													//For Euro												
													if( $value <= 54.20 || $value > 100 )
													{													
														$numId = '';
														if( $bundleIdentity > 0 )
														{
															$bundleIdentity = $bundleIdentity + 1;												
															$numId = $result->NumOrderId .'-'. $bundleIdentity;
															$splititem['product_order_id_identify']		=	$numId;
															$splititem['order_split']		=	$orderGroup;
															//$splititem['order_split']		=	"split";
														}
														else 
														{
															if( count($orderitems) == 1 )
															{
																$numId = $result->NumOrderId;	
															}
															else
															{
																$bundleIdentity = $bundleIdentity + 1;	
																$numId = $result->NumOrderId .'-'. $bundleIdentity;	
																$splititem['product_order_id_identify']		=	$numId;
																$splititem['order_split']		=	$orderGroup;
																//$splititem['order_split']		=	"split";
															}
														}
														
														$splititem['order_split']		=	$orderGroup;
														$splititem['pack_order_quantity']		=	$splitskus[$count-1];
														$splititem['product_sku_identifier']		= "single";			
														$splititem['price']		=	( $orderitem->Quantity * $splitskus[2] ) * (($orderitem->Quantity * $orderitem->PricePerUnit) / ( $orderitem->Quantity * $splitskus[2] ));   //$splitskus[2] * ($orderitem->PricePerUnit / $splitskus[2]);
														
														$splititem['quantity']			=	( $orderitem->Quantity * $splitskus[2] ); //$orderitem->Quantity;
														$splititem['product_type']		=	"bundle";
														$splititem['order_id']		=	$result->NumOrderId;
														
														if( count($orderitems) == 1 )
															$splititem['sku']			=	( $orderitem->Quantity * $splitskus[2] ) .'X'. 'S-'.$splitskus[$i];
														else
															$splititem['sku']			=	'S-'.$splitskus[$i];	
															
														$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => 'S-'.$splitskus[$i] )));
														$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
														
														//pr($splititem);
														
														$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
														$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
														
														
														/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																					array('Product.product_sku' => 'S-'.$splitskus[$i]));*/
																					
														//pr($productDetail); 
																					
														$this->OrderItem->create();
														$this->OrderItem->save( $splititem );													
													}
													else
													{
														
														if( $value <= 100 && $value > 54.20 )	
														{
															$total = 0;
															$perUnitPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
															$orderQuantity = $orderitem->Quantity * $splitskus[$count-1];
															
															$itemPrice = $perUnitPrice / $orderQuantity;
															
															$inc = 0;														
															$checkOuter = 0;
															$isLeader = false;
															
															if( ( $orderQuantity > 1 ) )
															{														
																//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
																$e = 0;while( $e <= ($orderQuantity-1) )
																{	
																	
																	//$total = $total + ( $baseRate * $itemPrice );
																	
																	if( ( $total + ( $baseRate * $itemPrice ) ) <= 54.20 )
																	{
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;
																		//echo "<br>";
																		$inc++;
																		$checkOuter++;
																		$isLeader = true;
																		
																		if( $e == ($orderQuantity-1) )
																		{
																			//echo "Now Split" . $total;
																			//echo "<br>*********<br>";
																			
																			//Splitting the order accordign the rule
																			//Store previous then initialized																	
																			$bundleIdentity++;																	
																			//Store and split the same SKU bundle order
																			$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																			$splititem['product_sku_identifier']		= "single";			
																			$splititem['price']		=	$total / $baseRate;
																			$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																			
																			$splititem['order_split']		=	$orderGroup;
																			$splititem['quantity']			=	$inc;
																			$splititem['product_type']		=	"bundle";
																			$splititem['order_id']		=	$result->NumOrderId;
																			$splititem['sku']			=	'S-'.$splitskus[$i];
																			$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																			$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																			
																			//pr($splititem);
																			
																			$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																			$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																			
																			/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																										array('Product.product_sku' => $splititem['sku']));*/
																			$this->OrderItem->create();
																			$this->OrderItem->save( $splititem );
																			
																			$total = 0;
																			$inc = 1;
																			$total = $total + ( $baseRate * $itemPrice );
																			//echo $total;																	
																			//echo "<br>";
																		}
																	}
																	else
																	{
																		
																		if( $isLeader == false )
																		{
																			//Increase Counter
																			$checkOuter++;
																			$total = $total + ( $baseRate * $itemPrice );
																			
																			if( $e == ($orderQuantity-1) )
																			{																			
																				$inc = 1;
																				//echo "Now Split " . $total;
																				//echo "<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				$splititem['order_split']		=	$orderGroup;
																				//$splititem['order_split']		=	"split";
																				$splititem['quantity']			=	$checkOuter;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem); 
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 0;
																				
																			}
																			
																		}
																		else
																		{
																			
																			if( $e == ($orderQuantity-1) )
																			{
																				
																				//For Previous calculate and store it split order into database
																				//echo "Now Split------" . $total;
																				//echo "<br>*********<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																				//Now store last index calculation if reaches at end point then 
																				//need to be remind , there is last one we have to also store into database
																				//echo "Now Split" . $total;
																				//echo "<br>*********<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																			}
																			else
																			{
																				
																				//echo "Now Split " . $total;
																				//echo "<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																			}
																					
																		}
																		
																	}
																	
																$e++;	
																}
															}
															
														}
														else
														{
															//echo "Exceed Limit to split."; exit;
														}												
													}
												}
												else
												{	
													
													//Get Count Sku for bundle with multiple
													$getLastIndex = $splitskus[count($splitskus)-1];
													
													//Handle Multiple Sku with type bundle												
													$value = $orderitem->Quantity * ($orderitem->PricePerUnit / $getLastIndex) * $baseRate;
													
													//echo $value = $orderitem->CostIncTax * $baseRate;
													//echo "<br>";
													
													$anotherValue = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
													
													//For Euro												
													if( $anotherValue <= 54.20 || $anotherValue > 100 )
													{	
														if( (count($splitskus)-2) == $i )
														{
															$totalQuantity = $orderitem->Quantity;
															//echo "<br>";
															$combinedSkuForMulti .= ',' . $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
															
															if( $bundleIdentity > 0 )
															{
																if( count($orderitems) > 1 )
																{
																	$bundleIdentity = $bundleIdentity + 1;												
																	$numId = $result->NumOrderId .'-'. $bundleIdentity;
																}
																else
																{										
																	$numId = $result->NumOrderId;
																}															
															}
															else
															{
																if( count($orderitems) > 1 )
																{
																	$bundleIdentity = $bundleIdentity + 1;												
																	$numId = $result->NumOrderId .'-'. $bundleIdentity;
																}
																else
																{											
																	$numId = $result->NumOrderId;
																}
															}
															
															$splititem['product_order_id_identify']		=	$numId;
															
															$splititem['order_split']		=	"Group B";
															$splititem['pack_order_quantity']		=	$splitskus[$count-1];
															$splititem['product_sku_identifier']		= "multiple";			
															$splititem['price']		=	$orderitem->Quantity * $orderitem->PricePerUnit;
															
															$splititem['quantity']			=	$orderitem->Quantity * $getLastIndex; //$totalQuantity;
															$splititem['product_type']		=	"bundle";
															$splititem['order_id']		=	$result->NumOrderId;
															$splititem['sku']			=	$combinedSkuForMulti;
															$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => 'S-'.$splitskus[$i] )));
															$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
															
															$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
															$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
															
															/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																						array('Product.product_sku' => $splititem['sku']));*/
															$this->OrderItem->create();
															$this->OrderItem->save( $splititem );
															$combinedSkuForMulti = '';
														}
														else
														{
															$totalQuantity = $orderitem->Quantity * $getLastIndex; //$orderitem->Quantity;
															//echo "xxxxx<br>";
															
															//if( $i == 1 )
															
															if( $combinedSkuForMulti == '' )
																$combinedSkuForMulti = $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
															else	
																$combinedSkuForMulti .= ',' . $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
														}
														
													}
													else
													{
														
														if( $anotherValue <= 100 && $anotherValue > 54.20 )	
														{
															$total = 0;
															
															//total price
															$perUnitPrice = ( $orderitem->Quantity * $orderitem->PricePerUnit );
																		
															//total quantity														
															$orderQuantity = $orderitem->Quantity * $getLastIndex;
															
															//unit price
															$itemPrice = $perUnitPrice / $orderQuantity;
															
															$inc = 0;														
															$checkOuter = 0;
															$isLeader = false;
															$total = 0;
															
															if( ( $orderQuantity > 0 ) )
															{														
																
																//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
																$inc = 0;$out = 0;while( $out <= $orderitem->Quantity-1 )
																{
																	
																	//Store
																	//echo " Bundle Multiple SKUxx " . $total;
																	//echo "<br>";
																			
																	//Splitting the order accordign the rule
																	//Store previous then initialized																	
																	$bundleIdentity++;			
																	$inc++;;														
																	//Store and split the same SKU bundle order
																	$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																	$splititem['product_sku_identifier']		= "single";			
																	$splititem['price']		=	$itemPrice;
																	$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																	
																	//$splititem['order_split']		=	"split";
																	$splititem['order_split']		=	"Group B";
																	$splititem['quantity']			=	$inc;
																	$splititem['product_type']		=	"bundle";
																	$splititem['order_id']		=	$result->NumOrderId;
																	$splititem['sku']			=	'S-'.$splitskus[$i];
																	$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																	$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																	
																	//pr($splititem);
																	
																	$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																	$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																	
																	/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																								array('Product.product_sku' => $splititem['sku']));*/
																	$this->OrderItem->create();
																	$this->OrderItem->save( $splititem );
																	
																	$inc = 0;
																$out++;	
																}															
															}
														}
													}
												}										
											}
										}
										else
										{
											
											// Single SKU order splitting
											$value = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
												
											//echo $value = $orderitem->CostIncTax * $baseRate;
											//echo "<br>";
											
											//For Euro												
											if( $value <= 54.20 || $value > 100 )
											{
												
												$numId = '';
												if( $bundleIdentity > 0 )
												{
													$bundleIdentity = $bundleIdentity + 1;												
													$numId = $result->NumOrderId .'-'. $bundleIdentity;
													$splititem['product_order_id_identify']		=	$numId;
													//$splititem['order_split']		=	"split";
												}
												else
												{
													if( count($orderitems) == 1 )
													{
														$numId = $result->NumOrderId;	
													}
													else
													{
														$bundleIdentity = $bundleIdentity + 1;	
														$numId = $result->NumOrderId .'-'. $bundleIdentity;	
														$splititem['product_order_id_identify']		=	$numId;
														//$splititem['order_split']		=	"split";
													}
												}
												
												$splititem['order_split']		=	$orderGroup;
												$splititem['pack_order_quantity']		=	0;
												$splititem['product_sku_identifier']		= "single";			
												$splititem['price']		=	$orderitem->Quantity * $orderitem->PricePerUnit;
												
												$splititem['quantity']			=	$orderitem->Quantity;
												$splititem['product_type']		=	"single";
												$splititem['order_id']		=	$result->NumOrderId;
												
												if( count( $orderitems ) == 1 )
													$splititem['sku']			=	$orderitem->Quantity .'X'. $orderitem->SKU;
												else
													$splititem['sku']			=	$orderitem->SKU;
													
												$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
												$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
												
												//pr($splititem);
												$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
												$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
												
												/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																			array('Product.product_sku' => $orderitem->SKU));*/
												$this->OrderItem->create();
												$this->OrderItem->save( $splititem );
																									
											}
											else
											{
												
												if( $value <= 100 && $value > 54.20 )	
												{
													
													$total = 0;
													$perUnitPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
													
													$orderQuantity = $orderitem->Quantity;
													
													$itemPrice = $perUnitPrice / $orderQuantity;
													
													$inc = 0;
													$checkOuter = 0;
													$isLeader = false;
													
													if( ( $orderQuantity > 1 ) )
													{		
																										
														//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
														$e = 0;while( $e <= ($orderQuantity-1) )
														{	
															
															//$total = $total + ( $baseRate * $itemPrice );
															
															if( ( $total + ( $baseRate * $itemPrice ) ) <= 54.20 )
															{
																$total = $total + ( $baseRate * $itemPrice );
																//echo $total;
																//echo "<br>";
																$inc++;
																$checkOuter++;
																$isLeader = true;
																
																if( $e == ($orderQuantity-1) )
																{
																	//echo "Now Split" . $total;
																	//echo "<br>*********<br>";
																	
																	//Splitting the order accordign the rule
																	//Store previous then initialized																	
																	$bundleIdentity++;																	
																	//Store and split the same SKU bundle order
																	$splititem['pack_order_quantity']		=	0;
																	$splititem['product_sku_identifier']		= "single";			
																	$splititem['price']		=	$total / $baseRate;
																	$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																	//$splititem['order_split']		=	"split";
																	
																	$splititem['order_split']		=	$orderGroup;
																	$splititem['quantity']			=	$inc;
																	$splititem['product_type']		=	"single";
																	$splititem['order_id']		=	$result->NumOrderId;
																	$splititem['sku']			=	$orderitem->SKU;
																	$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																	$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																	
																	//pr($splititem);
																	
																	$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																	$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																	
																	/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																								array('Product.product_sku' => $splititem['sku']));*/
																	$this->OrderItem->create();
																	$this->OrderItem->save( $splititem );
																	
																	$total = 0;
																	$inc = 1;
																	$total = $total + ( $baseRate * $itemPrice );
																	//echo $total;																	
																	//echo "<br>";
																}
															}
															else
															{
																
																if( $isLeader == false )
																{
																	//Increase Counter
																	$checkOuter++;
																	$total = $total + ( $baseRate * $itemPrice );
																	
																	if( $e == ($orderQuantity-1) )
																	{																			
																		$inc = 1;
																		//echo "Now Split " . $total;
																		//echo "<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$checkOuter;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem); 
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 0;
																		
																	}
																	
																}
																else
																{
																	
																	if( $e == ($orderQuantity-1) )
																	{
																		
																		//For Previous calculate and store it split order into database
																		//echo "Now Split------" . $total;
																		//echo "<br>*********<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		//$splititem['order_split']		=	"split";
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																		//Now store last index calculation if reaches at end point then 
																		//need to be remind , there is last one we have to also store into database
																		//echo "Now Split" . $total;
																		//echo "<br>*********<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																	}
																	else
																	{
																		
																		//echo "Now Split " . $total;
																		//echo "<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																	}
																			
																}
																
															}
															
														$e++;	
														}
													}
													else
													{
														
														//If order item count is 1 then would be store directly
														$splititem['pack_order_quantity']		=	0;
														$splititem['product_sku_identifier']		= "single";			
														$splititem['price']		=	$orderitem->PricePerUnit;
														
														$splititem['order_split']		=	$orderGroup;
														$splititem['quantity']			=	$orderitem->Quantity;
														$splititem['product_type']		=	"single";
														$splititem['order_id']		=	$result->NumOrderId;
														$splititem['sku']			=	$orderitem->SKU;
														$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
														$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
														
														$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
														$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
														
														/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																					array('Product.product_sku' => $splititem['sku']));*/
														$this->OrderItem->create();
														$this->OrderItem->save( $splititem );
														
													}
													
												}
												else
												{
													//echo "Exceed Limit to split."; exit;
												}												
											}
												
										}
										
									}
									else
									{
										
										$getCurrencyText = $result->TotalsInfo->Currency;
										$getCountryText = $result->CustomerInfo->Address->Country;
										
										if( $getCurrencyText == "EUR" )
										{
											$baseRate = '1';
										}
										else
										{
											$baseRate = '1.38';
										}
										
										/***************** split the order item ******************/
										$orderItemValueTotal = 0;foreach( $orderitems as $orderitem )
										{
											$orderItemValueTotal = $orderItemValueTotal + $orderitem->Cost;
										}
										
										//if( ($orderItemValueTotal * $baseRate) > 250 || ($orderItemValueTotal * $baseRate) <= 54.20 )
										
										if( ($orderItemValueTotal * $baseRate) >= 0 )
										{	
											if( count( $orderitems ) > 1 )
											{
												$orderGroup = "Group B";
											}
											else
											{
												if( count(explode('-',$orderitems[0]->SKU)) > 3 )
												{
													$orderGroup = "Group B";
												}
												else
												{
													$orderGroup = "Group A";
												}
											}
											
											//Store direct into storage
											$combineSkuVisit = '';
											$combinePrice = 0;
											$combineQuantity = 0;
											$combineBarcode = '';
											foreach( $orderitems as $orderitem )
											{
												if( count( explode( '-', $orderitem->SKU ) ) == 2 )
												{
													if( $combineSkuVisit == '' )
													{
														$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
														$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
														$combineQuantity = $combineQuantity + $orderitem->Quantity;
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
														$combineBarcode = $productDetail['ProductDesc']['barcode'];
													}
													else
													{
														$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
														$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
														$combineQuantity = $combineQuantity + $orderitem->Quantity;
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
														$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
													}
													
												}
												else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
												{
													$splitskus = explode( '-' , $orderitem->SKU);										
													if( $combineSkuVisit == '' )
													{
														$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
														$combineQuantity = $combineQuantity + $splitskus[2];
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
														$combineBarcode = $productDetail['ProductDesc']['barcode'];
													}
													else
													{
														$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
														$combineQuantity = $combineQuantity + $splitskus[2];
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
														$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
													}
													
												}
												else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
												{
													// For Bundle with muti type Sku
													$splitskus = explode( '-', $orderitem->SKU );
													
													$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
													$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
													
													$in = 1; while( $in <= count( $splitskus )-2 ):											
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
														if( $combineSkuVisit == '' )
														{
															//$quantity = $quantity + $orderitem->Quantity;
															$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
															$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
														}
														else
														{
															//$quantity = $quantity + $orderitem->Quantity;
															$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
															$combineQuantity = $combineQuantity + $orderitem->Quantity;
														}
													$in++;
													endwhile;
													
												}
																					
											}
											
											//Saving
											// For Bundle with same type Sku				
											//Store and split the same SKU bundle order
											$splititem['pack_order_quantity']		=	0;
											$splititem['product_sku_identifier']		= "single";			
											$splititem['price']		=	$combinePrice;
											$splititem['product_order_id_identify']		=	$result->NumOrderId;
											
											$splititem['order_split']		=	$orderGroup;
											$splititem['quantity']			=	$combineQuantity;
											$splititem['product_type']		=	"bundle";
											$splititem['order_id']		=	$result->NumOrderId;
											$splititem['sku']			=	$combineSkuVisit;								
											$splititem['barcode']		=	$combineBarcode;
											
											//pr($splititem);
											
											$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
											$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
											
											/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																		array('Product.product_sku' => $splititem['sku']));*/
											$this->OrderItem->create();
											$this->OrderItem->save( $splititem );
											//echo "saved";
											break;
										}										
										
									}
									
								}
								
							}	
							
							//************** Merge Order after splitting with different scenarios **************
							$this->mergeSplitOrdersByOrderId_AccordingRules( $result->NumOrderId , $flagVar );
                                                        
							//$this->loadModel( 'MergeOrder' );							
							//pr( $this->MergeOrder->find('all') ); exit;
							
							// code for save customer detail
							$customerInfo['Customer']['email']		=	$result->CustomerInfo->Address->EmailAddress;
							$customerInfo['Customer']['address1']	=	$result->CustomerInfo->Address->Address1;
							$customerInfo['Customer']['address2']	=	$result->CustomerInfo->Address->Address2;
							$customerInfo['Customer']['address3']	=	$result->CustomerInfo->Address->Address3;
							$customerInfo['Customer']['town']		=	$result->CustomerInfo->Address->Town;
							$customerInfo['Customer']['region']		=	$result->CustomerInfo->Address->Region;
							$customerInfo['Customer']['postcode']	=	$result->CustomerInfo->Address->PostCode;
							$customerInfo['Customer']['country']	=	$result->CustomerInfo->Address->Country;
							$customerInfo['Customer']['name']		=	$result->CustomerInfo->Address->FullName;
							$customerInfo['Customer']['company']	=	$result->CustomerInfo->Address->Company;
							$customerInfo['Customer']['phone']		=	$result->CustomerInfo->Address->PhoneNumber;
							$customerInfo['Customer']['source']		=	$result->GeneralInfo->Source;
							$customerInfo['Customer']['subsource']	=	$result->GeneralInfo->SubSource;
						
							$customerdetails	=	$this->Customer->find('first', array('conditions' => array('Customer.email' => $customerInfo['Customer']['email'])));
							
							if( count($customerdetails) > 0 )
							{
								//$customerdetails['Customer']['count'] = $customerdetails['Customer']['count'] + '1';
								$this->Customer->updateAll(array('Customer.order_id' => $result->NumOrderId),
								array('Customer.email' => $customerdetails['Customer']['email']));
							}
							else
							{
								$customerInfo['Customer']['order_id']		=	$result->NumOrderId;
								$this->Customer->create();
								$this->Customer->saveAll( $customerInfo );
							}
							
						}	
					}
					else
					{
						//Do something you required
					}					
				}
				
				/* update tyose order has 0 quantity */
				//$this->updateMergeOrder();
				
				/* call the function for assign the postal servises */
				//$this->assign_services();
				$this->getBarcode();	
				//$this->setAgainAssignedServiceToAllOrder(); // Euraco Group	
				
				//pr($orders); 
				
				//Delete cancel UNKNOWN orders
				$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
							'conditions' => array(
								'UnprepareOrder.order_id NOT IN' => $orders
								),
							  'fields' => array( 
								'UnprepareOrder.order_id',
								'UnprepareOrder.id',  
								'UnprepareOrder.items',  
							)
						)
					);
				
				foreach( $getAllUnprepardId  as $getAllId )
				{
					$itemsReserve = unserialize($getAllId['UnprepareOrder']['items']);
					$this->reserveInventoryForUnknown( $itemsReserve , 2 );
					$this->UnprepareOrder->delete( $getAllId['UnprepareOrder']['id'] );
				}
				
				//Sync start
				App::import( 'Controller' , 'MyExceptions' );
				$exception = new MyExceptionsController();
				$exception->syncComp();
							
				App::import('Controller', 'Products');
				$productModel = new ProductsController();
				$productModel->prepareVirtualStock();
				
		}
		
		/*
		 * 
		 * Cleaning orders
		 * 
		 */ 
		public function cleanOrders()
		{
			
			$this->layout = '';
			$this->autoRender = false;
			
			$this->loadModel( 'OpenOrder' );			
			$this->OpenOrder->query( "DELETE from open_orders where num_order_id = ''" );

			$this->loadModel( 'MergeUpdate' );
			$this->MergeUpdate->query( "DELETE from merge_updates where order_id = ''" );
			
		}
		
		public function updateMergeOrder()
		{
			
			$this->layout = '';
			$this->autoRender = false;
			
			/* get all orders where quantity would be zero */				
			$this->loadModel( 'MergeUpdate' );
			$getMergeUpdateOrders = json_decode(json_encode($this->MergeUpdate->find('all',array(
					'conditions' => array( 'MergeUpdate.quantity' => 0 ),
					'fields' => array(
						'MergeUpdate.id',
						'MergeUpdate.sku',
						'MergeUpdate.quantity',
						'MergeUpdate.order_id',
						'MergeUpdate.product_order_id_identify'
					)
				)
			)),0);
			
			$this->loadModel( 'OpenOrder' );
			if( count($getMergeUpdateOrders) > 0 )
			{
				
				foreach( $getMergeUpdateOrders as $index => $value )
				{

					$sku = explode( 'XS-' , $value->MergeUpdate->sku );
					$getSku = $sku[1];
					$getOpenOrderQuantity = json_decode(json_encode($this->OpenOrder->find( 'first', array(
							'conditions' => array(
								'OpenOrder.num_order_id' => $value->MergeUpdate->order_id
							),
							'fields' => array(
								'OpenOrder.items'
							)
						) 
					)),0);
					$getUnserializeItem = unserialize($getOpenOrderQuantity->OpenOrder->items);
					
					$data['MergeUpdate']['id'] = $value->MergeUpdate->id;
					$data['MergeUpdate']['quantity'] = $getUnserializeItem[0]->Quantity;
					$data['MergeUpdate']['sku'] = $getUnserializeItem[0]->Quantity.'X'.'S-'.$getSku;
					$this->MergeUpdate->saveAll( $data );
					
					$this->loadModel( 'ScanOrder' );
					
					$getScanOrder = json_decode(json_encode($this->ScanOrder->find( 'first', array(
							'conditions' => array(
								'ScanOrder.split_order_id' => $value->MergeUpdate->product_order_id_identify
							),
							'fields' => array(
								'ScanOrder.id',
								'ScanOrder.sku',
								'ScanOrder.quantity',
								'ScanOrder.barcode'
							)
						) 
					)),0);
					
					$setData['ScanOrder']['id'] = $getScanOrder->ScanOrder->id;
					$setData['ScanOrder']['quantity'] = $getUnserializeItem[0]->Quantity;					
					$this->ScanOrder->saveAll( $setData );
					
				}
				
			}
			
			$this->redirect( Router::url( $this->referer(), true ) );
			
		}
		
		public function writeLogInventory( $sku = null , $inverntory = null ) 
			{
				
				$this->layout = '';
				$this->autoRender = false;
				
				//Cake Log
				App::uses('CakeLog', 'Log');
				CakeLog::config('default', array(
					'engine' => 'File'
				));
				
				//create and setup the logs
				CakeLog::write('Euraco Group_[Open_Order_'.date( 'd-m-Y A' ).']', $sku .'__'. $inverntory);
				
			}
		/*
		 * 
		 * 
		 * Params, Update inventory by SKU
		 * 
		 */
		public function updateInventory( $orderId = null , $flagVar = null , $actionType = null, $channel_sku = NULL )
		{
			
			$this->loadModel( 'Product' );
			$this->loadModel( 'MergeUpdate' );
			$this->loadModel( 'InventoryRecord' );
			
			$storeOverSellHostory = array();
			
			// On Fly unbind Model First
			$this->Product->unbindModel( array( 'belongsTo' => array( 'ProductLocation' ) ) );
			
			//get all rows from merge update because we need to get deduct inventory accordign to SKU
			$param = array(
				'conditions' => array(
					'MergeUpdate.order_id' => $orderId
				),
				'fields' => array(
					'MergeUpdate.id as MainPointer',
					'MergeUpdate.sku',
					'MergeUpdate.order_id',
					'MergeUpdate.product_order_id_identify'
				)
			);
			
			$rest = '';
			$mergeRows = json_decode(json_encode($this->MergeUpdate->find( 'all', $param )),0);
			$chsku = explode('__',$channel_sku);
			foreach( $mergeRows as $mergeRowsIndex => $mergeRowsValue )
			{
			   // echo "<br>*************vvvvvvvvvvvvvvvvvvv******************<br>";
				
				$skuSplit = explode( ',' , $mergeRowsValue->MergeUpdate->sku );
				
				$j = 0;while( $j <= count( $skuSplit )-1 )
				{
				   $splitOrderId = explode( 'XS-' , $skuSplit[$j] );
				   
				   //pr($splitOrderId);
				   //Load Model
				   $paramInner = array(
					 'conditions' => array(
						 'Product.product_sku' => 'S-'.$splitOrderId[1]
					 ),
					 'fields' => array(
						 'Product.current_stock_level as CurrentStock',
						 'Product.id',
						 'Product.product_sku',
						 'ProductDesc.barcode'
					 )  
				   );
				   $openOrderRow = json_decode(json_encode($this->Product->find('first' , $paramInner)),0);
				   //pr($openOrderRow);
				   
				   $orderSkuStock = $splitOrderId[0];
				  
				   //inventory manipulation
				   $calculateStock = $openOrderRow->Product->CurrentStock - $orderSkuStock;
				   $extStock = $openOrderRow->Product->CurrentStock;
				   
				   //Update inventory now very easy 
				   $id = $openOrderRow->Product->id;
				   $data['Product']['id'] = $openOrderRow->Product->id;
					
				   //Over sell check up
					$orderSkuStock = $splitOrderId[0];
				   $skuAdd = 'S-'.$splitOrderId[1];
				   
				   //inventory manipulation
				   $calculateStock = $openOrderRow->Product->CurrentStock - $orderSkuStock;
				   
				   if( $openOrderRow->Product->CurrentStock <= 0 )
				   {
					  $rest = 0 - $orderSkuStock;
				   }
				   else if( ($openOrderRow->Product->CurrentStock < $orderSkuStock) && ($openOrderRow->Product->CurrentStock <= 0) )
				   {
					  $rest = 0 - $orderSkuStock;
				   }  
				   else if( ($openOrderRow->Product->CurrentStock < $orderSkuStock) && ($openOrderRow->Product->CurrentStock > 0) )
				   {
					  $rest = $openOrderRow->Product->CurrentStock - $orderSkuStock;
				   }
				   else if( $openOrderRow->Product->CurrentStock > $orderSkuStock )
				   {
					  $rest = $openOrderRow->Product->CurrentStock - $orderSkuStock;
				   }	  
				   
				   //Store record od inventory
				   if( isset( $id ) && $id > 0 )
				   {
 					    //23-08-2021
					  /* if(count($chsku) == 1){
					   	$channelsku = $channel_sku;
					   }else{
					  	 $channelsku = $chsku[$j];
					   }
					   $actionType = 'Update Inventory';
					 
						$this->storeInventoryRecord( $openOrderRow, $orderSkuStock, $mergeRowsValue->MergeUpdate->order_id, $actionType ,'', $channelsku,$mergeRowsValue->MergeUpdate->product_order_id_identify);*/
					  
				   }
				   
				   //unprepare order
					if( $flagVar == 1)
					{						
						//marked for reference to know is it coming from unprepare order or not
						$mergeUpdateForOverSell['MergeUpdate']['id'] = $mergeRowsValue->MergeUpdate->MainPointer;
						$mergeUpdateForOverSell['MergeUpdate']['source_coming'] = 5;
												
						//update marked with history
						$this->MergeUpdate->saveAll( $mergeUpdateForOverSell );
					}
					
					//date marked
					$mergeUpdateForOverSell['MergeUpdate']['id'] = $mergeRowsValue->MergeUpdate->MainPointer;
					$mergeUpdateForOverSell['MergeUpdate']['order_date'] = date( 'Y-m-d H:i:s' );						
					//update marked with history
					$this->MergeUpdate->saveAll( $mergeUpdateForOverSell );
										
				   //Over sell check up
				   if( $rest <= 0 )
				   {
						$storeOverSellHostory[] = $this->overSellCheckUp_StoreMarked( $rest , $skuAdd , $mergeRowsValue->MergeUpdate->MainPointer);
				   }
				 
				   if( isset( $id ) && $id > 0 )
				   {
					   
					   if( $calculateStock <= 0 )
					   {
						   $this->Product->query( "UPDATE products set products.current_stock_level = 0 where id = {$id}" );
					   }
					   else
					   {
							$this->Product->query( "UPDATE products set products.current_stock_level = {$calculateStock} where id = {$id}" );
							
							if(count($chsku) == 1){
								$channelsku = $channel_sku;
							}else{
								$channelsku = $chsku[$j];
							}
							$actionType = 'Update Inventory';
							// store current stock level with current order quantity
							$this->storeInventoryRecord( $openOrderRow, $orderSkuStock, $mergeRowsValue->MergeUpdate->order_id, $actionType ,'', $channelsku,$mergeRowsValue->MergeUpdate->product_order_id_identify);
						
					   }
					   
					   $this->writeLogInventory( $skuAdd , $extStock .'<>'. $orderSkuStock  .' ( Update Inventory ) '); 
					   
				   }
				$rest = '';
				   
				$j++;    
				}
				
				//Store over sell historyand update merge update
				if( count( $storeOverSellHostory ) > 0 )
				{
					
					$mergeUpdateForOverSell['MergeUpdate']['id'] = $mergeRowsValue->MergeUpdate->MainPointer;
					$mergeUpdateForOverSell['MergeUpdate']['out_sell_marker'] = 3;
					$mergeUpdateForOverSell['MergeUpdate']['sku_decode'] = serialize($storeOverSellHostory);
					//$mergeUpdateForOverSell['MergeUpdate']['sku_decode'] = base64_encode(serialize($storeOverSellHostory));
					
					//update marked with history
					//$this->MergeUpdate->saveAll( $mergeUpdateForOverSell );
					
					unset($storeOverSellHostory);
					$storeOverSellHostory = '';
					
				}				
			}			
		}
		
		/*
		 * 
		 * Params, Overs sell checkup
		 * 
		 * 
		 */
	   public function overSellCheckUp_StoreMarked( $rest = null , $sku = null , $mergeId = null )
	   {
		   
		   $this->loadModel( 'MergeUpdate' );
		   $mergeOverSellMarker = array();
		   
		   //Now store the marker to point the order is over sell to future manipulation (3)
		   $mergeOverSellMarker['id'] = $mergeId;
		   $mergeOverSellMarker['sku_decode'] = $rest .'x'. $sku;
		   
		   $resultDecode = json_encode($mergeOverSellMarker);
		   
		   unset($mergeOverSellMarker);
		   $mergeOverSellMarker = '';
		   
		return $resultDecode; 				   
		   
	   }
			   
		/*
		 * 
		 * Params, Merge splti order according rules
		 * 
		 */ 
		public function mergeSplitOrdersByOrderId_AccordingRules( $rulesOrderId = null , $flagVar = null )
		{
			
			//Load Models
			$this->loadModel( 'OpenOrder' );
			$this->loadModel( 'OrderItem' );
			$this->loadModel( 'MergeOrder' );
			
			//Params
			$params = array(
				'conditions' => array(
					array(
						'OrderItem.order_id' => $rulesOrderId
					)
				),
				'order' => array(
					array(
						'OrderItem.price DESC' 
					)
				)
			);
			
			//Main calculation for split orders
			$mergeItems = json_decode(json_encode($this->OrderItem->find( 'all' , $params )),0);
			
			if( count( $mergeItems ) > 1 ){
			
				//default variables
				$total = 0;
				$bundle = '';
				$bundleType = '';
				$quantity = 0;
				$skuDefined = '';
				$channel_sku_t = '';
				$globalBarcode = '';
				$rowArray = array();
				$checkLeader = false;
				 
				//loop for calculation to add or count
				$bundleIdentity = 0; 
				$e = 0;
				foreach( $mergeItems as $mergeItemsIndex => $mergeItemsValue ){
					
					$channel_sku_t .= $mergeItemsValue->OrderItem->channel_sku.'__';	
										
					if( (($total + $mergeItemsValue->OrderItem->price) * 1.38) <= 150.0 ){
					
						$total  = $total + $mergeItemsValue->OrderItem->price ;
						//echo "<br>";
						
						//To check it has manipulate first or not
						$checkLeader = true;
						
						if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "multiple" )
						{
							
							$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
							
							if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
							{
								$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
							}	
							else
							{
								$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
							}							
						}
						else if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
						{	
							
							$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
							if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
							{
								$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
							}	
							else
							{
								$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
							}
						}
						else if( $mergeItemsValue->OrderItem->product_type == "single" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
						{
							
							$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
							if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
							{
								$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
							}	
							else
							{
								$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
							}
							
						}
						else{}							
						
						$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $mergeItemsValue->OrderItem->sku )));
						
						if( $globalBarcode == '' )
						{							
							$globalBarcode 	= $productDetail['ProductDesc']['barcode'];
						}
						else
						{
							$globalBarcode .= ','.$productDetail['ProductDesc']['barcode'];
						}
												
						//Last Index
						if( $e == (count( $mergeItems ) - 1) ){
							
							//Merge and Store an order with specific rules
							$splitskus = explode( '-', $mergeItemsValue->OrderItem->sku );
							
							//Store and split the same SKU bundle order
							$splititem['pack_order_quantity']		=	0;
							$splititem['product_sku_identifier']		= "single";			
							$splititem['price']		=	$total;
							
							if( count( $mergeItems ) > 0 )
							{
								$bundleIdentity++;
								$splititem['product_order_id_identify']		=	$rulesOrderId .'-'. $bundleIdentity;
							}
							else
							{
								$splititem['product_order_id_identify']		=	$rulesOrderId;
								$bundleIdentity = '';
							}
							
							$splititem['order_split']		=	$mergeItemsValue->OrderItem->order_split;
							$splititem['quantity']			=	$quantity;
							$splititem['product_type']		=	"bundle";
							$splititem['order_id']		=	$rulesOrderId;
							$splititem['sku']			=	$skuDefined;							
							$splititem['barcode']		=	$globalBarcode;
							$splititem['channel_sku']	=	$mergeItemsValue->OrderItem->channel_sku;
							//pr($splititem);
							
							$this->MergeOrder->create();
							$this->MergeOrder->save( $splititem );	
																				
							//echo "ok Ji Last if index ji";
							
							//re-initialize
							$quantity = 0;
							$total = 0;
							$skuDefined = '';
							$globalBarcode = '';
						}
						else{}
						
					}else{
						
						//Check first if it is coming from above condition or not
						if( $checkLeader == false ){
						
							if( $e == count( $mergeItems )-1 ){
								
								//Get relative values now
								if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "multiple" )
								{
									
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}							
								}
								else if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
								{	
									
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}
								}
								else if( $mergeItemsValue->OrderItem->product_type == "single" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
								{
								
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}
								}
								else{}							
								
								$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $mergeItemsValue->OrderItem->sku )));
								
								if( $globalBarcode == '' )
								{							
									$globalBarcode 	= $productDetail['ProductDesc']['barcode'];
								}
								else
								{
									$globalBarcode .= ','.$productDetail['ProductDesc']['barcode'];
								}
								
								//Merge and Store an order with specific rules
								$splitskus = explode( '-', $mergeItemsValue->OrderItem->sku );
								
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$mergeItemsValue->OrderItem->price;
								
								if( count( $mergeItems ) > 0 )
								{
									$bundleIdentity++;
									$splititem['product_order_id_identify']		=	$rulesOrderId .'-'. $bundleIdentity;
								}
								else
								{
									$splititem['product_order_id_identify']		=	$rulesOrderId;
									$bundleIdentity = '';
								}
								
								$splititem['order_split']		=	$mergeItemsValue->OrderItem->order_split;
								$splititem['quantity']			=	$quantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$rulesOrderId;
								$splititem['sku']			=	$skuDefined;							
								$splititem['barcode']		=	$globalBarcode;
								$splititem['channel_sku']	=	$mergeItemsValue->OrderItem->channel_sku;
								//pr($splititem);
								
								$this->MergeOrder->create();
								$this->MergeOrder->save( $splititem );	
								
								//re-initialize
								$quantity = 0;
								$total = 0;
								$skuDefined = '';
								$globalBarcode = '';
																					
								//echo "Store First Time In Else but at last index";
							
							}else{
								
								//Get relative values now
								if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "multiple" )
								{
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}							
								}
								else if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
								{	
									
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}
								}
								else if( $mergeItemsValue->OrderItem->product_type == "single" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
								{
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}
								}
								else{}							
								
								$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $mergeItemsValue->OrderItem->sku )));
								
								if( $globalBarcode == '' )
								{							
									$globalBarcode 	= $productDetail['ProductDesc']['barcode'];
								}
								else
								{
									$globalBarcode .= ','.$productDetail['ProductDesc']['barcode'];
								}
								
								//Merge and Store an order with specific rules
								$splitskus = explode( '-', $mergeItemsValue->OrderItem->sku );
								
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$mergeItemsValue->OrderItem->price;
								
								if( count( $mergeItems ) > 0 )
								{
									$bundleIdentity++;
									$splititem['product_order_id_identify']		=	$rulesOrderId .'-'. $bundleIdentity;
								}
								else
								{
									$splititem['product_order_id_identify']		=	$rulesOrderId;
									$bundleIdentity = '';
								}
								
								$splititem['order_split']		=	$mergeItemsValue->OrderItem->order_split;
								$splititem['quantity']			=	$quantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$rulesOrderId;
								$splititem['sku']			=	$skuDefined;							
								$splititem['barcode']		=	$globalBarcode;
								$splititem['channel_sku']	=	$mergeItemsValue->OrderItem->channel_sku;
								//pr($splititem);
								
								$this->MergeOrder->create();
								$this->MergeOrder->save( $splititem );	
								
								//re-initialize
								$quantity = 0;
								$total = 0;
								$skuDefined = '';
								$globalBarcode = '';
																					
								//echo "Store First Time In Else";
									
							}
							
						}else{
							
							if( $e == count( $mergeItems )-1 ){
								
								//It comes from above condition after that we would store accordingly
								if( $bundleIdentity == 0 )
								{
									$bundleIdentity++;
								}
								else
								{
									$bundleIdentity++;
								}
								
								//Merge and Store an order with specific rules
								$splitskus = explode( '-', $mergeItemsValue->OrderItem->sku );
								
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$total;
								$splititem['product_order_id_identify']		=	$rulesOrderId .'-'. $bundleIdentity;
								
								$splititem['order_split']		=	$mergeItemsValue->OrderItem->order_split;
								$splititem['quantity']			=	$quantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$rulesOrderId;
								$splititem['sku']			=	$skuDefined;							
								$splititem['barcode']		=	$globalBarcode;
								$splititem['channel_sku']	=	$mergeItemsValue->OrderItem->channel_sku;
								//pr($splititem);
								
								$this->MergeOrder->create();
								$this->MergeOrder->save( $splititem );	
																					
								//echo "ok Ji Last if index";
								
								//re-initialize
								$quantity = 0;
								$total = 0;
								$skuDefined = '';
								$globalBarcode = '';
								
								//$total  = $total + $mergeItemsValue->OrderItem->price;							
								//$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
								
								//Get relative values now
								if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "multiple" )
								{
									
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}							
								}
								else if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
								{	
									
									
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}
									
								}
								else if( $mergeItemsValue->OrderItem->product_type == "single" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
								{
									
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}
								}
								else{}							
								
								$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $mergeItemsValue->OrderItem->sku )));
								
								if( $globalBarcode == '' )
								{							
									$globalBarcode 	= $productDetail['ProductDesc']['barcode'];
								}
								else
								{
									$globalBarcode .= ','.$productDetail['ProductDesc']['barcode'];
								}
								
								//Inc here
								$bundleIdentity++;
								
								//Merge and Store an order with specific rules
								$splitskus = explode( '-', $mergeItemsValue->OrderItem->sku );
								
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$mergeItemsValue->OrderItem->price;
								$splititem['product_order_id_identify']		=	$rulesOrderId .'-'. $bundleIdentity;
								
								$splititem['order_split']		=	$mergeItemsValue->OrderItem->order_split;
								$splititem['quantity']			=	$quantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$rulesOrderId;
								$splititem['sku']			=	$skuDefined;							
								$splititem['barcode']		=	$globalBarcode;								
								$splititem['channel_sku']	=	$mergeItemsValue->OrderItem->channel_sku;
								//pr($splititem);
								
								$this->MergeOrder->create();
								$this->MergeOrder->save( $splititem );	
																					
								//echo "Store both";
								//$total  = $total + $mergeItemsValue->OrderItem->price;							
								//$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;	
							
							}else{
								
								//It comes from above condition after that we would store accordingly
								if( $bundleIdentity == 0 )
								{
									$bundleIdentity++;
								}
								else
								{
									$bundleIdentity++;
								}
								//Merge and Store an order with specific rules
								$splitskus = explode( '-', $mergeItemsValue->OrderItem->sku );
								
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$total;
								$splititem['product_order_id_identify']		=	$rulesOrderId .'-'. $bundleIdentity;
								
								$splititem['order_split']		=	$mergeItemsValue->OrderItem->order_split;
								$splititem['quantity']			=	$quantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$rulesOrderId;
								$splititem['sku']			=	$skuDefined;							
								$splititem['barcode']		=	$globalBarcode;								
								$splititem['channel_sku']	=	$mergeItemsValue->OrderItem->channel_sku;	
								//pr($splititem);
								
								$this->MergeOrder->create();
								$this->MergeOrder->save( $splititem );	
																					
								//echo "ok Ji Last if index";
								
								//re-initialize
								$quantity = 0;
								$total = 0;
								$skuDefined = '';
								$globalBarcode = '';
								
								$total  = $total + $mergeItemsValue->OrderItem->price;							
								//$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
								
								//Get relative values now
								if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "multiple" )
								{
									
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}
																
								}
								else if( $mergeItemsValue->OrderItem->product_type == "bundle" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
								{	
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}
								}
								else if( $mergeItemsValue->OrderItem->product_type == "single" && $mergeItemsValue->OrderItem->product_sku_identifier == "single" )
								{
									
									$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
									if( strpos($mergeItemsValue->OrderItem->sku, 'XS-') )
									{
										$skuDefined .= ',' . $mergeItemsValue->OrderItem->sku;	
									}	
									else
									{
										$skuDefined .= ',' . ( $mergeItemsValue->OrderItem->quantity ) .'X'.$mergeItemsValue->OrderItem->sku;
									}
									
								}
								else{}							
								
								$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $mergeItemsValue->OrderItem->sku )));
								
								if( $globalBarcode == '' )
								{							
									$globalBarcode 	= $productDetail['ProductDesc']['barcode'];
								}
								else
								{
									$globalBarcode .= ','.$productDetail['ProductDesc']['barcode'];
								}
								
								//$total  = $total + $mergeItemsValue->OrderItem->price;							
								//$quantity = $quantity + $mergeItemsValue->OrderItem->quantity;
							
							}
						
						}
						
					}
				
				$e++;
				}
			
			}else{
				
				$orderitems = $mergeItems;
				$this->loadModel( 'Product' );				
				
				foreach( $orderitems as $orderitem ){
				
					// For Single Type Sku
						//If order item count is 1 then would be store directly
						$splititem['pack_order_quantity']		=	$orderitem->OrderItem->pack_order_quantity;
						$splititem['product_sku_identifier']		= $orderitem->OrderItem->product_sku_identifier;;			
						$splititem['price']		=	$orderitem->OrderItem->price;
						
						$splititem['order_split']		=	$orderitem->OrderItem->order_split;
						$splititem['quantity']			=	$orderitem->OrderItem->quantity;
						$splititem['product_type']		=	$orderitem->OrderItem->product_type;
						$splititem['product_order_id_identify']		=	$orderitem->OrderItem->product_order_id_identify;
						$splititem['order_id']		=	$orderitem->OrderItem->order_id;
						$splititem['sku']			=	$orderitem->OrderItem->sku;						
						$splititem['barcode']		=	'';						
						$splititem['channel_sku']	=	$orderitem->OrderItem->channel_sku;	
						//pr($splititem);
						
						$this->MergeOrder->create();
						$this->MergeOrder->save( $splititem );	
						
					//echo "Single  defined";
					
				}
			
			}
			
			//Params
			$params = array(
				'conditions' => array(
					array(
						'MergeOrder.order_id' => $rulesOrderId
					)
				)
			);
			
			//Main calculation for split orders
			$mergeItems = json_decode(json_encode($this->MergeOrder->find( 'all' , $params )),0);
			
			if( count( $mergeItems ) == 1 )
			{
				$updateOrderId['updateOrder']['MergeOrder']['id'] = $mergeItems[0]->MergeOrder->id;
				$updateOrderId['updateOrder']['MergeOrder']['product_order_id_identify'] = $mergeItems[0]->MergeOrder->order_id;				
				$this->MergeOrder->saveAll( $updateOrderId['updateOrder'] );
				$channel_sku = $mergeItems[0]->MergeOrder->channel_sku;
			}
			
			/*
			 * 
			 * Params, just check if order has above 54.20 value should be merge with each other
			 * 
			 */ 
			
			$channel_sku_t = rtrim(@$channel_sku_t,'__');
			if(empty($channel_sku_t)){
				 $channel_sku_final = $channel_sku;
			}else{
				 $channel_sku_final = $channel_sku_t;
			}	
			
			$this->updateAfterMerge( $rulesOrderId ,  $flagVar, $channel_sku_final ); 
			 
		}
		
		/*
		 * 
		 * Params, If done success than update with new scenario
		 * 
		 */ 
		public function updateAfterMerge( $rulesOrderId = NULL , $flagVar = NULL, $channel_sku = NULL )
		{
			//Load Models
			$this->loadModel( 'MergeOrder' );
			$this->loadModel( 'MergeUpdate' );
			$this->loadModel( 'OpenOrder' );
			
			//$rulesOrderId = 100182;
			
			//Params
			$params = array(
				'conditions' => array(
					array(
						'MergeOrder.order_id' => $rulesOrderId
					)
				),
				'fields' => array('SUM(MergeOrder.price) as TotalPrice'),
			);
			
			//Main calculation for split orders
			$price = json_decode(json_encode($this->MergeOrder->find( 'all' , $params )),0);
			$totalPrice = $price[0][0]->TotalPrice; 
			
			//Get exact postal service name ( Standard_Jpost / Standard etc etc *)
			//Params
			$paramsNew = array(
				'conditions' => array(
					array(
						'OpenOrder.num_order_id' => $rulesOrderId
					)
				),
				'fields' => array(
					'OpenOrder.num_order_id',
					'OpenOrder.open_order_date',
					'OpenOrder.shipping_info as Shipper',
					'OpenOrder.customer_info as Customer',
					'OpenOrder.general_info as Ginfo',
				)
			);
			
			//Main calculation for split orders
			$getPostalNameById = json_decode(json_encode($this->OpenOrder->find( 'first' , $paramsNew )),0);
			$shipperInfo = unserialize($getPostalNameById->OpenOrder->Shipper); 
			$serviceName = $shipperInfo->PostalServiceName;
			
			$customerInfo = unserialize($getPostalNameById->OpenOrder->Customer);
			$country = $customerInfo->Address->Country;
			
			$gInfo = unserialize($getPostalNameById->OpenOrder->Ginfo);
			
			
			//Get sub-orders from MergeOrder
			if( ($serviceName == "Express" || $serviceName == "Tracked") && ( $country == "United Kingdom" ) )
			{
				$this->mergeByPostal( $rulesOrderId );
			}
			else
			{
				//Merge Update
				$this->overrideMergeUpdateByPostal( $rulesOrderId );
			}
		      
			//Update product quantity / inventory at run time when order is splitted
			if( $flagVar == 3 )
			{
				$this->updateInventory( $rulesOrderId , $flagVar , 'Update Inventory',$channel_sku);
			}
            else if( $flagVar == 1 )
			{
				//$this->updateInventory( $rulesOrderId , $flagVar , 'Update Inventory' );
			}
			
            $mergetorders 	=	$this->MergeUpdate->find('all', array('conditions' => array( 'MergeUpdate.order_id' => $rulesOrderId ) , 'fields' => array( 'MergeUpdate.id' )));
			
			//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
			foreach( $mergetorders as $mergetorder )
			{				
				$mergeId = $mergetorder['MergeUpdate']['id'];
				$newMergeData['MergeUpdate']['id'] = $mergeId;
				$newMergeData['MergeUpdate']['linn_fetch_orders'] = $gInfo->Status;
				$newMergeData['MergeUpdate']['order_date'] = date( 'Y-m-d H:i:s' );	
				$newMergeData['MergeUpdate']['purchase_date'] = date('Y-m-d H:i:s', strtotime($getPostalNameById->OpenOrder->open_order_date));	 
				$newMergeData['MergeUpdate']['merge_order_date'] = date( 'Y-m-d H:i:s' );						
				$this->MergeUpdate->saveAll( $newMergeData );
			}
			        
			/*
			 * 
			 * Params, 
			 * Updates and merge new barcode in list
			 * 
			 */ 			
			$this->mergeAllBarcodes( $rulesOrderId );	
			
			/*
			 * 
			 * Params, Store and update Correct Packaging type and name , id or weight + cost
			 * 
			 */ 
			$this->store_updatePackagingVariant( $rulesOrderId );
			
		}
		
		public function mergeByPostal( $rulesOrderId = null )
		{
			
			$this->loadModel( 'MergeOrder' );
			$this->loadModel( 'OpenOrder' );
			$this->loadModel( 'MergeUpdate' );			
			
			//Params
			$paramsNew = array(
				'conditions' => array(
					array(
						'MergeOrder.order_id' => $rulesOrderId
					)
				)
			);
			
			//Main calculation for split orders
			$totalRows = json_decode(json_encode($this->MergeOrder->find( 'all' , $paramsNew )),0);
			
			//Arrangements for under 54.20 and above 54.20
			$group = '';
			$quantity = 0;
			$conCatSku = '';
			$price = 0;
			$productType = '';
			$productDefined = '';
			$barcode = '';
			$data = array();
			
			if( $totalRows > 0 )
			{
				$outerBoundary = 0;$suv = 0;foreach( $totalRows as $totalRowsIndex => $totalRowsValue )
				{
					
					//Move only
					if( count($totalRows) == 1 )	
					{
						//echo "Under 54.20";
						//echo "<br>";
						
						$outerBoundary++;
						
						//If order item count is 1 then would be store directly
						$splititem['MergeUpdate']['pack_order_quantity']		=	$totalRowsValue->MergeOrder->pack_order_quantity;
						$splititem['MergeUpdate']['product_sku_identifier']		= $totalRowsValue->MergeOrder->product_sku_identifier;;			
						$splititem['MergeUpdate']['price']		=	$totalRowsValue->MergeOrder->price;
						
						$splititem['MergeUpdate']['order_split']		=	$totalRowsValue->MergeOrder->order_split;
						$splititem['MergeUpdate']['quantity']			=	$totalRowsValue->MergeOrder->quantity;
						$splititem['MergeUpdate']['product_type']		=	$totalRowsValue->MergeOrder->product_type;
						$splititem['MergeUpdate']['product_order_id_identify']		=	$totalRowsValue->MergeOrder->order_id .'-'. $outerBoundary;//$totalRowsValue->MergeOrder->product_order_id_identify;
						$splititem['MergeUpdate']['order_id']		=	$totalRowsValue->MergeOrder->order_id;
						if( strpos($totalRowsValue->MergeOrder->sku, 'XS-'))
						{
							$splititem['MergeUpdate']['sku']			=	trim($totalRowsValue->MergeOrder->sku,",");	
						}
						else
						{
							$splititem['MergeUpdate']['sku']			=	$totalRowsValue->MergeOrder->quantity .'X'. trim($totalRowsValue->MergeOrder->sku,",");						
						}
						$splititem['MergeUpdate']['barcode']		=	'';
						
						//pr($splititem);
						//$this->order_location_merge($splititem);
						$this->MergeUpdate->create();
						$this->MergeUpdate->save( $splititem );	
						unset( $splititem );
						$splititem = '';
						
					}
					else
					{
						$quantity = $quantity + $totalRowsValue->MergeOrder->quantity;
						$price = $price + $totalRowsValue->MergeOrder->price;
						$productType = 	$totalRowsValue->MergeOrder->product_type;
						$productDefined = 	$totalRowsValue->MergeOrder->product_sku_identifier;
						$barcode = '';
						$group = $totalRowsValue->MergeOrder->order_split;	
						
						if( $conCatSku == "" )							
						{
							$conCatSku = trim($totalRowsValue->MergeOrder->sku , ",");
						}
						else
						{
							$conCatSku .= ',' . trim($totalRowsValue->MergeOrder->sku , ",");
						}
						
					}
					
				}
				
				//Store accordingly
				if( $price > 0 )
				{
					
					$splititem = '';
					
					$outerBoundary++;
					
					//If order item count is 1 then would be store directly
					$splititem['MergeUpdate']['pack_order_quantity']		=	0;
					$splititem['MergeUpdate']['product_sku_identifier']		= $productDefined;			
					$splititem['MergeUpdate']['price']		=	$price;
					
					$splititem['MergeUpdate']['order_split']		=	$group;
					$splititem['MergeUpdate']['quantity']			=	$quantity;
					$splititem['MergeUpdate']['product_type']		=	$productType;
					
					$splititem['MergeUpdate']['product_order_id_identify']		=	$totalRowsValue->MergeOrder->order_id .'-'. $outerBoundary;
					$splititem['MergeUpdate']['order_id']		=	$totalRowsValue->MergeOrder->order_id;
					//$splititem['MergeUpdate']['sku']			=	trim($conCatSku,",");						
					
					if( strpos(trim($conCatSku,","), 'XS-'))
					{
						$splititem['MergeUpdate']['sku']			=	trim($conCatSku , ",");	
					}
					else
					{
						$splititem['MergeUpdate']['sku']			=	$quantity .'X'. trim($conCatSku , ",");						
					}
						
					$splititem['MergeUpdate']['barcode']		=	'';
					
					//pr($splititem);
					//$this->order_location_merge($splititem);
					$this->MergeUpdate->create();
					$this->MergeUpdate->save( $splititem );	
					
				}
			}
			
			$this->order_location_merge($rulesOrderId);
			
		}
		
		/*
		 * 
		 * Merge Order according rules
		 * 
		 */ 
		public function overrideMergeUpdateByPostal( $rulesOrderId = null )
		{
			
			//$rulesOrderId = 106575;
			
			$this->loadModel( 'MergeOrder' );
			$this->loadModel( 'OpenOrder' );
			$this->loadModel( 'MergeUpdate' );			
			
			//$rulesOrderId = 104858; 
			
			//Params
			$paramsNew = array(
				'conditions' => array(
					array(
						'MergeOrder.order_id' => $rulesOrderId
					)
				)
			);
			
			//Main calculation for split orders
			$totalRows = json_decode(json_encode($this->MergeOrder->find( 'all' , $paramsNew )),0);
			
			//Arrangements for under 54.20 and above 54.20
			$group = '';
			$quantity = 0;
			$conCatSku = '';
			$price = 0;
			$productType = '';
			$productDefined = '';
			$barcode = '';
			$data = array();
			
			if( $totalRows > 0 )
			{
				$outerBoundary = 0;$suv = 0;foreach( $totalRows as $totalRowsIndex => $totalRowsValue )
				{
					
					$setParam = array(
						'conditions' => array(
							'OpenOrder.num_order_id' => $rulesOrderId
						),
						'fields' => array(
							'OpenOrder.totals_info'
						)
					);
					//Get currency
					$getOpenOrdeCurrencyText = $this->OpenOrder->find( 'first' , $setParam );
					
					$totalInfo = unserialize( $getOpenOrdeCurrencyText['OpenOrder']['totals_info'] );
					$currencyText = $totalInfo->Currency;
					$baseRatePrice = 1;
					if( $currencyText == "EUR" )
					{
						$baseRatePrice = 1;
					}
					else
					{
						$baseRatePrice = 1.38;
					}
					
					//Move only
					if( ($totalRowsValue->MergeOrder->price ) == 0 )	
					{
						//echo "Under 54.20";
						//echo "<br>";
						
						$outerBoundary++;
						
						//If order item count is 1 then would be store directly
						$splititem['MergeUpdate']['pack_order_quantity']		=	$totalRowsValue->MergeOrder->pack_order_quantity;
						$splititem['MergeUpdate']['product_sku_identifier']		= $totalRowsValue->MergeOrder->product_sku_identifier;;			
						$splititem['MergeUpdate']['price']		=	$totalRowsValue->MergeOrder->price;
						
						$splititem['MergeUpdate']['order_split']		=	$totalRowsValue->MergeOrder->order_split;
						$splititem['MergeUpdate']['quantity']			=	$totalRowsValue->MergeOrder->quantity;
						$splititem['MergeUpdate']['product_type']		=	$totalRowsValue->MergeOrder->product_type;
						$splititem['MergeUpdate']['product_order_id_identify']		=	$totalRowsValue->MergeOrder->order_id .'-'. $outerBoundary;//$totalRowsValue->MergeOrder->product_order_id_identify;
						$splititem['MergeUpdate']['order_id']		=	$totalRowsValue->MergeOrder->order_id;
						if( strpos($totalRowsValue->MergeOrder->sku, 'XS-'))
						{
							$splititem['MergeUpdate']['sku']			=	trim($totalRowsValue->MergeOrder->sku,",");	
						}
						else
						{
							$splititem['MergeUpdate']['sku']			=	$totalRowsValue->MergeOrder->quantity .'X'. trim($totalRowsValue->MergeOrder->sku,",");						
						}
						$splititem['MergeUpdate']['barcode']		=	'';
						
						//pr($splititem);
						//$this->order_location_merge($splititem);
						$this->MergeUpdate->create();
						$this->MergeUpdate->save( $splititem );	
						unset( $splititem );
						$splititem = '';
						
					}
					else if( ($totalRowsValue->MergeOrder->price * $baseRatePrice) == 54.20 )	
					{
						//echo "Under 54.20";
						//echo "<br>";
						
						$outerBoundary++;
						
						//If order item count is 1 then would be store directly
						$splititem['MergeUpdate']['pack_order_quantity']		=	$totalRowsValue->MergeOrder->pack_order_quantity;
						$splititem['MergeUpdate']['product_sku_identifier']		= $totalRowsValue->MergeOrder->product_sku_identifier;;			
						$splititem['MergeUpdate']['price']		=	$totalRowsValue->MergeOrder->price;
						
						$splititem['MergeUpdate']['order_split']		=	$totalRowsValue->MergeOrder->order_split;
						$splititem['MergeUpdate']['quantity']			=	$totalRowsValue->MergeOrder->quantity;
						$splititem['MergeUpdate']['product_type']		=	$totalRowsValue->MergeOrder->product_type;
						$splititem['MergeUpdate']['product_order_id_identify']		=	$totalRowsValue->MergeOrder->order_id .'-'. $outerBoundary;//$totalRowsValue->MergeOrder->product_order_id_identify;
						$splititem['MergeUpdate']['order_id']		=	$totalRowsValue->MergeOrder->order_id;
						if( strpos($totalRowsValue->MergeOrder->sku, 'XS-'))
						{
							$splititem['MergeUpdate']['sku']			=	trim($totalRowsValue->MergeOrder->sku,",");	
						}
						else
						{
							$splititem['MergeUpdate']['sku']			=	$totalRowsValue->MergeOrder->quantity .'X'. trim($totalRowsValue->MergeOrder->sku,",");						
						}
						$splititem['MergeUpdate']['barcode']		=	'';
						
						//pr($splititem);
						//$this->order_location_merge($splititem);
						$this->MergeUpdate->create();
						$this->MergeUpdate->save( $splititem );	
						unset( $splititem );
						$splititem = '';
						
					}
					else if( (($totalRowsValue->MergeOrder->price * $baseRatePrice) + ($price * $baseRatePrice)) < 54.20 )	
					{
						
						//$quantity = $quantity + $totalRowsValue->MergeOrder->quantity;
						
						$idSplit = explode( ',', $totalRowsValue->MergeOrder->sku );
						$ik = 0;while( $ik <= count($idSplit)-1 )
						{
							
							$idInnerSplit = explode( 'XS-', $idSplit[$ik] );
							$innerQuantity = $idInnerSplit[0];
							$quantity = $quantity + $innerQuantity;
						$ik++;	
						}
						
						$price = $price + $totalRowsValue->MergeOrder->price;
						$productType = 	$totalRowsValue->MergeOrder->product_type;
						$productDefined = 	$totalRowsValue->MergeOrder->product_sku_identifier;
						$barcode = '';
						$group = $totalRowsValue->MergeOrder->order_split;	
						
						if( $conCatSku == "" )							
						{
							$conCatSku = trim($totalRowsValue->MergeOrder->sku , ",");
						}
						else
						{
							$conCatSku .= ',' . trim($totalRowsValue->MergeOrder->sku , ",");
						}
						
					}
					else if( (($totalRowsValue->MergeOrder->price * $baseRatePrice) + ($price * $baseRatePrice)) > 54.20 )	
					{	
						
						if( $price > 0 )
						{
								
							if( ($price * $baseRatePrice) < 54.20 )
							{
								
								$splititem = '';
						
								$outerBoundary++;
								
								//If order item count is 1 then would be store directly
								$splititem['MergeUpdate']['pack_order_quantity']		=	0;
								$splititem['MergeUpdate']['product_sku_identifier']		= $productDefined;			
								$splititem['MergeUpdate']['price']		=	$price;
								
								$splititem['MergeUpdate']['order_split']		=	$group;
								$splititem['MergeUpdate']['quantity']			=	$quantity;
								$splititem['MergeUpdate']['product_type']		=	$productType;
								
								$splititem['MergeUpdate']['product_order_id_identify']		=	$totalRowsValue->MergeOrder->order_id .'-'. $outerBoundary;
								$splititem['MergeUpdate']['order_id']		=	$totalRowsValue->MergeOrder->order_id;
								//$splititem['MergeUpdate']['sku']			=	trim($conCatSku,",");						
								
								if( strpos(trim($conCatSku,","), 'XS-'))
								{
									$splititem['MergeUpdate']['sku']			=	trim($conCatSku , ",");	
								}
								else
								{
									$splititem['MergeUpdate']['sku']			=	$quantity .'X'. trim($conCatSku , ",");						
								}
									
								$splititem['MergeUpdate']['barcode']		=	'';
								
								//pr($splititem);
								//$this->order_location_merge($splititem);
								$this->MergeUpdate->create();
								$this->MergeUpdate->save( $splititem );
								
								$price = 0;
								$conCatSku = '';
								$quantity = 0;
								
							}
							
							$idSplit = explode( ',', $totalRowsValue->MergeOrder->sku );
							$ik = 0;while( $ik <= count($idSplit)-1 )
							{
								
								$idInnerSplit = explode( 'XS-', $idSplit[$ik] );
								$innerQuantity = $idInnerSplit[0];
								$quantity = $quantity + $innerQuantity;
							$ik++;	
							}
							
							$price = $price + $totalRowsValue->MergeOrder->price;
							$productType = 	$totalRowsValue->MergeOrder->product_type;
							$productDefined = 	$totalRowsValue->MergeOrder->product_sku_identifier;
							$barcode = '';
							$group = $totalRowsValue->MergeOrder->order_split;	
							
							if( $conCatSku == "" )							
							{
								$conCatSku = trim($totalRowsValue->MergeOrder->sku , ",");
							}
							else
							{
								$conCatSku .= ',' . trim($totalRowsValue->MergeOrder->sku , ",");
							}
							
						}
						else
						{
							
							$idSplit = explode( ',', $totalRowsValue->MergeOrder->sku );
							$ik = 0;while( $ik <= count($idSplit)-1 )
							{
								
								/*$idInnerSplit = explode( 'XS-', $idSplit[$ik] );
								$innerQuantity = $idInnerSplit[0];
								$quantity = $quantity + $innerQuantity;*/
								
								$idInnerSplit = explode( 'XS-', $idSplit[$ik] );
								if( count( $idInnerSplit ) > 1 )
								{
									$innerQuantity = $idInnerSplit[0];
									$quantity = $quantity + $innerQuantity;
								}
								else
								{
									$innerQuantity = $totalRowsValue->MergeOrder->quantity;
									$quantity = $quantity + $innerQuantity;
								}
								
							$ik++;	
							}
							
							$price = $price + $totalRowsValue->MergeOrder->price;
							$productType = 	$totalRowsValue->MergeOrder->product_type;
							$productDefined = 	$totalRowsValue->MergeOrder->product_sku_identifier;
							$barcode = '';
							$group = $totalRowsValue->MergeOrder->order_split;	
							
							if( $conCatSku == "" )							
							{
								$conCatSku = trim($totalRowsValue->MergeOrder->sku , ",");
							}
							else
							{
								$conCatSku .= ',' . trim($totalRowsValue->MergeOrder->sku , ",");
							}							
							
						}
					}					
				}
				
				//Store accordingly
				if( $price > 0 )
				{
					
					$splititem = '';
					
					$outerBoundary++;
					
					//If order item count is 1 then would be store directly
					$splititem['MergeUpdate']['pack_order_quantity']		=	0;
					$splititem['MergeUpdate']['product_sku_identifier']		= $productDefined;			
					$splititem['MergeUpdate']['price']		=	$price;
					
					$splititem['MergeUpdate']['order_split']		=	$group;
					$splititem['MergeUpdate']['quantity']			=	$quantity;
					$splititem['MergeUpdate']['product_type']		=	$productType;
					
					$splititem['MergeUpdate']['product_order_id_identify']		=	$totalRowsValue->MergeOrder->order_id .'-'. $outerBoundary;
					$splititem['MergeUpdate']['order_id']		=	$totalRowsValue->MergeOrder->order_id;
					//$splititem['MergeUpdate']['sku']			=	trim($conCatSku,",");						
					
					if( strpos(trim($conCatSku,","), 'XS-'))
					{
						$splititem['MergeUpdate']['sku']			=	trim($conCatSku , ",");	
					}
					else
					{
						$splititem['MergeUpdate']['sku']			=	$quantity .'X'. trim($conCatSku , ",");						
					}
						
					$splititem['MergeUpdate']['barcode']		=	'';
					
					//pr($splititem); exit;
					//$this->order_location_merge($splititem);
					$this->MergeUpdate->create();
					$this->MergeUpdate->save( $splititem );	
					
				}
			}
			$this->order_location_merge($rulesOrderId);
		}

	 
		
		public function store_updatePackagingVariant( $rulesOrderId = null )
		{
			//$rulesOrderId = 101184-1;
			$this->loadModel( 'MergeUpdate' );
			$this->loadModel( 'PackageEnvelope' );
			$this->loadModel( 'Packagetype' );
			$this->loadModel( 'Product' );
			$this->loadModel( 'ProductDesc' );
			
			$params = array(
				'conditions' => array(
					'MergeUpdate.order_id' => $rulesOrderId
				),
				'fields' => array(
					'MergeUpdate.order_id',
					'MergeUpdate.id',
					'MergeUpdate.packet_weight as weight',
					'MergeUpdate.packet_length as lenght',
					'MergeUpdate.packet_width as width',
					'MergeUpdate.packet_height as height',
					'MergeUpdate.order_split',
					'MergeUpdate.sku as SKU',
					'MergeUpdate.product_order_id_identify'
				)				
			); 
			$getMregeUpdateOrders = $this->MergeUpdate->find( 'all', $params );
			foreach( $getMregeUpdateOrders as $getMregeUpdateOrder)
			{
				//pr($getMregeUpdateOrder);
				$identifireID	=	$getMregeUpdateOrder['MergeUpdate']['product_order_id_identify'];
				
				$params = array(
					'conditions' => array(
						'MergeUpdate.product_order_id_identify' => $identifireID
					),
					'fields' => array(
						'MergeUpdate.order_id',
						'MergeUpdate.id',
						'MergeUpdate.packet_weight as weight',
						'MergeUpdate.packet_length as lenght',
						'MergeUpdate.packet_width as width',
						'MergeUpdate.packet_height as height',
						'MergeUpdate.order_split',
						'MergeUpdate.sku as SKU',
						'MergeUpdate.product_order_id_identify'
					)				
				);
				$getSpecificOrder = $this->MergeUpdate->find( 'all', $params );
				//pr($getSpecificOrder);
				$this->packagingManipulate( $identifireID , $getSpecificOrder );
			}
			
		}
			
		public function packagingManipulate( $identifireID = null,$getMregeUpdateOrders = null )	
		{
			
			$this->loadModel( 'PackageEnvelope' );
			$this->loadModel( 'Packagetype' );
			$this->loadModel( 'Product' );
			$this->loadModel( 'ProductDesc' );
			
			//Manipulate all conditions
			/*
			 * 1) Box Only (High Priority) 
			 * 2) Envelope / Box (Medium Priority) 
			 * 3) Any (Low Priority)
			 * 
			 */
			$mergeOrders = $getMregeUpdateOrders;	
					
			foreach( $mergeOrders as $mergeOrdersIndex => $mergeOrdersValue )
			{
				
				//Split all sku for getting class type
				$skuSplit = explode( ',' , $mergeOrdersValue['MergeUpdate']['SKU']);
				$variantArray = array();
				$k = 0;while( $k <= count($skuSplit)-1 )
				{
					//Getting proper class from every sku
					$pop = $skuSplit[$k];
					$properSku = explode( 'XS-' , $pop );
					
					$doneSku = 'S-'.$properSku[1]; 
					
					$param = array(
						'conditions' => array(
							'Product.product_sku' => $doneSku
						),
						'fields' => array(
							'ProductDesc.variant_envelope_id',
							'ProductDesc.variant_envelope_name'
						)
					);
					$variant = $this->ProductDesc->find( 'first' , $param );					
					$variantArray[] = $variant['ProductDesc']['variant_envelope_name'];	
					$variantArray[] = $variant['ProductDesc']['variant_envelope_id'];	
					
				$k++;	
				}
				$boxOnly = 0;
				$envelopeBox = 0;
				$any	 = 0;
				if( in_array( 'Box Only', $variantArray ) )
				{
					$boxOnly++;
				}
				else if( in_array( 'Envelope / Box', $variantArray ) )
				{
					$envelopeBox++;
				}
				else if( in_array( 'Any', $variantArray ) )
				{
					$any++;
				}
				else{}
				
				
				if($boxOnly > 0 )
				{
					$type = 'Box Only';
					$this->updateMergeUpdateByVerient( $type, $mergeOrdersValue , $identifireID );
				}
				else if( $boxOnly == 0 && $envelopeBox > 0 && $any == 0)
				{
					$type = 'Envelope / Box';
					$this->updateMergeUpdateByVerient( $type, $mergeOrdersValue , $identifireID );
				}
				else if($boxOnly == 0 && $envelopeBox == 0 && $any > 0)
				{
					$type = 'Any';
					$this->updateMergeUpdateByVerient( $type, $mergeOrdersValue , $identifireID );
				}
				else
				{
				}
			}
		}
		
		/*
		 * Use for update varient cost, weight, type in merge update
		 * 
		 * 
		 * */
		 public function updateMergeUpdateByVerient( $type = null, $mergeOrdersValue = null , $identifireID = null )
		 {
			 $this->loadModel( 'PackageEnvelope' );
			 $this->loadModel( 'Packagetype' );
			 $this->loadModel( 'MergeUpdate' );
			 
			 $id		=	$mergeOrdersValue['MergeUpdate']['id'];
			 $weight	=	$mergeOrdersValue['MergeUpdate']['weight'];
			 $length	=	$mergeOrdersValue['MergeUpdate']['lenght'];
			 $width		=	$mergeOrdersValue['MergeUpdate']['width'];
			 $height	=	$mergeOrdersValue['MergeUpdate']['height'];
			 
			 $param = array(
						'conditions' => array(
							'PackageEnvelope.envelope_length >= ' => $length,
							'PackageEnvelope.envelope_width >= ' => $width,
							'PackageEnvelope.envelope_height >= ' => $height,
							'Packagetype.package_type_name' => $type
						)
					);
			$getEnvelopes		=	$this->PackageEnvelope->find( 'all', $param );
			
			if( count($getEnvelopes) > 0 )
			   {
					$minCost    =  Set::sort($getEnvelopes, '{n}.PackageEnvelope.cost', 'ASC');
					$data['MergeUpdate']['id']      = $id;
					$data['MergeUpdate']['envelope_weight']  = $minCost[0]['PackageEnvelope']['envelope_weight'];
					$data['MergeUpdate']['envelope_cost']   = $minCost[0]['PackageEnvelope']['envelop_cost'];
					$data['MergeUpdate']['envelope_name']   = $minCost[0]['PackageEnvelope']['envelope_name'];
					$data['MergeUpdate']['envelope_id']   = $minCost[0]['PackageEnvelope']['id'];
					$data['MergeUpdate']['packaging_type']   = $type;
					
					$wgth = $minCost[0]['PackageEnvelope']['envelope_weight'];
					$cost = $minCost[0]['PackageEnvelope']['envelop_cost'];
					$name = $minCost[0]['PackageEnvelope']['envelope_name'];
					$pE_id = $minCost[0]['PackageEnvelope']['id'];
					$type = $type;
			   }
			   else
			   {
					$wgth = '0';
					$cost = '0';
					$name = 'Not Matched';
					$pE_id = '0';
					$type = $type;
			   }
			
			//$this->MergeUpdate->create();
			//$this->MergeUpdate->saveAll( $data );
			
			$strQuery = "Update merge_updates as MU set MU.envelope_weight = {$wgth}, 
						MU.envelope_cost = {$cost}, 
						MU.envelope_name = '{$name}', 
						MU.envelope_id = {$pE_id}, 
						MU.packaging_type = '{$type}' 
						where MU.id = {$id}";
					
			$this->MergeUpdate->query( $strQuery );
			
		 }
		 
		/*
		 * 
		 * Params, Merge Barcodes according new rules
		 * 
		 */ 
		public function mergeAllBarcodes( $rulesOrderId = null )
		{
			
			$this->layout = '';
			$this->autoRender = false;					
			$this->loadModel( 'MergeUpdate' );
			
			//Params
			$paramsNew = array(
				'conditions' => array(
					array(
						'MergeUpdate.order_id' => $rulesOrderId
					)
				)
			);
			
			//Main calculation for split orders
			$totalRows = json_decode(json_encode($this->MergeUpdate->find( 'all' , $paramsNew )),0);
			$outerArray = array();	
			$mergeBarcodes = '';foreach( $totalRows as $totalRowsIndex => $totalRowsValue )
			{	
			
				//Now Split
				$explodeArray = explode( ',', $totalRowsValue->MergeUpdate->sku );				
				$in = 0;$i = 0;while( $i <= count( $explodeArray )-1 )
				{				
					$inExplode = explode( '-' , $explodeArray[$i] );
					$outerArray[$in++] = 'S-'.$inExplode[1];					
				$i++;
				}
				
				//Now update in progress
				$this->loadModel( 'Product' );
				$stk = 0;while( $stk <= count( $outerArray )-1 )
				{
					//Merge Barcode according new rules
					$productDetail			=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $outerArray[$stk] )));
					
					if( $mergeBarcodes == '' )
						$mergeBarcodes = @$productDetail['ProductDesc']['barcode'];
					else
						$mergeBarcodes .= ',' . @$productDetail['ProductDesc']['barcode'];	
					
				$stk++;	
				}
				
				//Now update in progress haaaaahaaaa
				$rowId = $totalRowsValue->MergeUpdate->id;
				$this->request->data['Merge']['MergeUpdate']['id']		= $rowId;
				$this->request->data['Merge']['MergeUpdate']['barcode']	= $mergeBarcodes;
				$this->request->data['Merge']['MergeUpdate']['row_status']	= 1;
				$this->MergeUpdate->saveAll( $this->request->data['Merge'] );
				$mergeBarcodes = '';
				unset($outerArray); $outerArray = '';				
			}
			
			$this->saveSeparateSku( $rulesOrderId );
			//Truncate tables
			$this->loadModel( 'MergeOrder' );
			$this->loadModel( 'OrderItem' );
			$this->MergeOrder->query('TRUNCATE merge_orders;');
			$this->OrderItem->query('TRUNCATE order_items;');
			
			//Assiging postal service through LAFF algorithm
			$this->deliveryService( $rulesOrderId );
			
			//Now store ans seperate sku with quantity
			
			
		}
		
		public function ukAmazonFees()
		{
			$this->layout = '';
			$this->autoRender = false;
			$this->loadModel('AmazonFee');
			$this->loadModel('Location');
			App::import('Vendor', 'PHPExcel/IOFactory');
			$objPHPExcel = new PHPExcel();
			$objReader= PHPExcel_IOFactory::createReader('CSV');
			$objReader->setReadDataOnly(true);
			$objPHPExcel=$objReader->load('files/uk_amazon_fees.csv');
			$objWorksheet=$objPHPExcel->setActiveSheetIndex(0);
			$lastRow = $objPHPExcel->getActiveSheet()->getHighestRow();
			$colString	=	 $highestColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
			$colNumber = PHPExcel_Cell::columnIndexFromString($colString);
			
			for($i=2;$i<=$lastRow;$i++) 
			{
				$this->request->data['category']				=	$objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
				$this->request->data['referral_fee']			=	$objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
				$this->request->data['app_min_referral_fee']	=	$objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
				
				$country	=	$this->Location->find('first', array('conditions' => array('Location.county_name' => $objWorksheet->getCellByColumnAndRow(3,$i)->getValue())));
				$this->request->data['country'] 	= 	$country['Location']['id'];
				$this->request->data['platform']	=	$objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
				
				$this->AmazonFee->create();
				$this->AmazonFee->save($this->request->data);
			}
		}
		
		public function assign_services()
			  {
			   
			   $this->layout = '';
			   $this->autoRender = false;
			   
			   $this->loadModel('OpenOrder');
			   $this->loadModel('PostalServiceDesc');
			   $this->loadModel('Product');
			   $this->loadModel('CurrencyExchange');
			   $this->loadModel('AssignService');
			   
			   $allopenorders = $this->OpenOrder->find('all', array('conditions' => array('OpenOrder.status' => '0')));
			   
			   foreach($allopenorders as $allopenorder)
			   {
				
				$data['id']    =  $allopenorder['OpenOrder']['id'];
				$data['order_id']  =  $allopenorder['OpenOrder']['order_id'];
				$data['num_order_id'] =  $allopenorder['OpenOrder']['num_order_id'];
				$data['general_info'] =  unserialize($allopenorder['OpenOrder']['general_info']);
				$data['shipping_info'] =  unserialize($allopenorder['OpenOrder']['shipping_info']);
				$data['customer_info'] =  unserialize($allopenorder['OpenOrder']['customer_info']);
				$data['totals_info'] =  unserialize($allopenorder['OpenOrder']['totals_info']);
				$data['folder_name'] =  unserialize($allopenorder['OpenOrder']['folder_name']);
				$data['items']   =  unserialize($allopenorder['OpenOrder']['items']);
				
				
				$weight     = $data['shipping_info']->TotalWeight;
				
				$servicelevel   = $data['shipping_info']->PostalServiceName;
				
				//Standard_Jpost -> Standard to use Jersey Post
				if( $servicelevel == "Standard_Jpost" )
				{
					$servicelevel = "Standard";
				}
				
				$source     = $data['general_info']->Source; //($data['general_info']->Source == 'DIRECT') ? 'Jersey' : $data['general_info']->Source ;
				
				$weight = array();
				$qty = 1;
				
				foreach($data['items'] as $item)
				{
				 $qty = $item->Quantity;
				 $productDetail = $this->Product->find('first',array('conditions' => array('Product.product_sku' => $item->SKU)));
					 if(isset($productDetail['ProductDesc']))
					 {
						$weight[] = $productDetail['ProductDesc']['weight'] * $qty;
						$dimension  =  (($productDetail['ProductDesc']['length'] + $productDetail['ProductDesc']['width'] + $productDetail['ProductDesc']['height'] ) * $qty);
					 }
				}
				
				$weight = array_sum($weight);
				  
				$postalservices = $this->PostalServiceDesc->find('all', 
				array('conditions' => 
				 array(
				   'AND' => array('Location.county_name' => $data['customer_info']->Address->Country,'ServiceLevel.service_name' => $servicelevel,'PostalServiceDesc.warehouse' => $source,'PostalServiceDesc.max_weight >=' =>$weight)
				  )));
				$serdata['id'] = $allopenorder['OpenOrder']['id'];
				if(count($postalservices) != 0)
				{
				 $i = 0;
				 foreach($postalservices as $postalservice1)
				 {
				  $ccyprice[$i] = $postalservice1['PostalServiceDesc']['ccy_prices'];
				  $perItem[$i] = $postalservice1['PostalServiceDesc']['per_item'];
				  $perkilo[$i] = $postalservice1['PostalServiceDesc']['per_kilo'];
				  $postalid[$i] = $postalservice1['PostalServiceDesc']['id'];
				  $weightKilo[$i] = $weight;
				  $i++;
				 }
				 
				 $getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
				 $id = array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
				 unset($perItem);
				 unset($perkilo);
				 unset($weightKilo);
				}
				
				if(count($postalservices) != 0)
				{
				 $postalservicessel = $this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
				 foreach($postalservicessel as $postalservice)
				 { 
				  
				  $matrixdimension  =  ($postalservice['PostalServiceDesc']['max_length'] + $postalservice['PostalServiceDesc']['max_width'] + $postalservice['PostalServiceDesc']['max_height']) ."<br>";
				  
				  if( $dimension < $matrixdimension )
				  {
				   $serdata['service'] =  $postalservice['PostalServiceDesc']['service_name'];
				   $serdata['provider_ref_code'] =  $postalservice['PostalServiceDesc']['provider_ref_code'];       
				   $serdata['service_provider'] =  $postalservice['PostalServiceDesc']['courier'];
				   $serdata['manifest'] 	=  $postalservice['PostalServiceDesc']['manifest'];
					$serdata['cn22'] 		=  $postalservice['PostalServiceDesc']['cn_required'];
					$templateID 		=  $postalservice['PostalServiceDesc']['template_id'];            
				   $serdata['error_code'] =  ''; 
				         
				  }
				  else
				  {
				   $serdata['service'] =  "Over Dimension";
				   $serdata['provider_ref_code'] =  '';
				   $serdata['error_code'] =  'error';       
				   $serdata['service_provider'] =  '';
				   $templateID 		=  $postalservice['PostalServiceDesc']['template_id'];      
				  }
				 }
				}
				else
				{
				 $serdata['service'] =  "Over Weight Or Not in Matrices";
				 $serdata['provider_ref_code'] =  '';
				 $serdata['error_code'] =  'error';
				 $serdata['service_provider'] =  '';
				 $templateID 		=  $postalservice['PostalServiceDesc']['template_id'];             
				}
				
				$newdata['AssignService']['id'] = $serdata['id'];
				$newdata['AssignService']['open_order_id'] = $serdata['id'];
				$newdata['AssignService']['assigned_service'] = $serdata['service'];
				$newdata['AssignService']['provider_ref_code'] = $serdata['provider_ref_code'];
				$newdata['AssignService']['service_provider'] = $serdata['service_provider'];
				$newdata['AssignService']['error_code'] = $serdata['error_code'];
				$newdata['AssignService']['manifest'] =	 ($serdata['manifest'] == 'Yes') ? '1' : '0' ;
				$newdata['AssignService']['cn22'] =	($serdata['cn22'] == 'Yes') ? '1' : '0' ;
				
				$this->AssignService->create();
				$this->AssignService->save($newdata);
				/* Assign the template of open order */
				$this->OpenOrder->updateAll(array('OpenOrder.template_id' => $templateID),
							array('OpenOrder.num_order_id' => $allopenorder['OpenOrder']['num_order_id']));
			   }
			   
			  }
		
		
		/*public function assign_services()
		{
			
			$this->layout = '';
			$this->autoRender = false;
			
			$this->loadModel('OpenOrder');
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('Product');
			$this->loadModel('CurrencyExchange');
			$this->loadModel('AssignService');
			
			$allopenorders	=	$this->OpenOrder->find('all', array('conditions' => array('OpenOrder.status' => '0')));
			
			foreach($allopenorders as $allopenorder)
			{
				
				$data['id']				=	 $allopenorder['OpenOrder']['id'];
				$data['order_id']		=	 $allopenorder['OpenOrder']['order_id'];
				$data['num_order_id']	=	 $allopenorder['OpenOrder']['num_order_id'];
				$data['general_info']	=	 unserialize($allopenorder['OpenOrder']['general_info']);
				$data['shipping_info']	=	 unserialize($allopenorder['OpenOrder']['shipping_info']);
				$data['customer_info']	=	 unserialize($allopenorder['OpenOrder']['customer_info']);
				$data['totals_info']	=	 unserialize($allopenorder['OpenOrder']['totals_info']);
				$data['folder_name']	=	 unserialize($allopenorder['OpenOrder']['folder_name']);
				$data['items']			=	 unserialize($allopenorder['OpenOrder']['items']);
				
				
				$weight					=	$data['shipping_info']->TotalWeight;
				$servicelevel			=	$data['shipping_info']->PostalServiceName;
				$source					=	($data['general_info']->Source == 'DIRECT') ? 'Jersey' : $data['general_info']->Source ;
				
				$weight = array();
				$qty = 1;
				
				foreach($data['items'] as $item)
				{
					$qty	=	$item->Quantity;
					$productDetail	=	$this->Product->find('first',array('conditions' => array('Product.product_sku' => $item->SKU)));
					$weight[] = $productDetail['ProductDesc']['weight'] * $qty;
					$dimension		=	 (($productDetail['ProductDesc']['length'] + $productDetail['ProductDesc']['width'] + $productDetail['ProductDesc']['height'] ) * $qty);
				}
				
				$weight	=	array_sum($weight);
						
				$postalservices	=	$this->PostalServiceDesc->find('all', 
				array('conditions' => 
					array(
							'AND' => array('Location.county_name' => $data['customer_info']->Address->Country,'ServiceLevel.service_name' => $servicelevel,'PostalServiceDesc.warehouse' => $source,'PostalServiceDesc.max_weight >=' =>$weight)
						)));
				$serdata['id'] = $allopenorder['OpenOrder']['id'];
				if(count($postalservices) != 0)
				{
					$i = 0;
					foreach($postalservices as $postalservice1)
					{
						$ccyprice[$i]	=	$postalservice1['PostalServiceDesc']['ccy_prices'];
						$perItem[$i]	=	$postalservice1['PostalServiceDesc']['per_item'];
						$perkilo[$i]	=	$postalservice1['PostalServiceDesc']['per_kilo'];
						$postalid[$i]	=	$postalservice1['PostalServiceDesc']['id'];
						$weightKilo[$i]	=	$weight;
						$i++;
					}
					
					$getPerItem_PerKilo	=	$this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
					$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
					unset($perItem);
					unset($perkilo);
					unset($weightKilo);
				}
				
				
				
				if(count($postalservices) != 0)
				{
					$postalservicessel	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.id' => $id[0])	));
					foreach($postalservicessel as $postalservice)
					{	
						$matrixdimension 	=	 ($postalservice['PostalServiceDesc']['max_length'] + $postalservice['PostalServiceDesc']['max_width'] + $postalservice['PostalServiceDesc']['max_height']) ."<br>";
						
						if( $dimension < $matrixdimension )
						{
							$serdata['service'] =  $postalservice['PostalServiceDesc']['service_name'];
						}
						else
						{
							$serdata['service']	=	 "Over Dimension";
						}
					}
				}
				else
				{
					$serdata['service'] =  "Over Weight Or Not in Matrices";
				}
				
				$newdata['AssignService']['id'] =	$serdata['id'];
				$newdata['AssignService']['open_order_id'] =	$serdata['id'];
				$newdata['AssignService']['assigned_service'] =	$serdata['service'];

				$this->AssignService->create();
				$this->AssignService->save($newdata);
			}
			
		}*/
		
		public function getAdditionOfItem_PerKilo( $perItem = null , $perkilo = null , $weightkilo = null ,  $postalid = null, $ccyprice = null )
			{ 
				$this->loadModel('CurrencyExchange');
				$currency	=	$this->CurrencyExchange->find('all', array('order' => 'CurrencyExchange.date DESC'));
				$exchangeRate	=	$currency[0]['CurrencyExchange']['rate'];
				$resultantArrayAfterAddition = array();
				  $e = 0;while( $e <= count($perItem)-1 )
				  {
					  if($ccyprice[$e] == 'GBP')
					  {
						
						//(double)$resultantArrayAfterAddition[$postalid[$e]] = (double)$perItem[$e] + ((double)$perkilo[$e] * $weightkilo[$e]);  
						(double)$resultantArrayAfterAddition[$postalid[$e]] = (double)$perItem[$e]; 
					  }
					  else
					  {
						 
						 //(double)$resultantArrayAfterAddition[$postalid[$e]] = $exchangeRate * ((double)$perItem[$e] + ((double)$perkilo[$e] * $weightkilo[$e]));  
						 (double)$resultantArrayAfterAddition[$postalid[$e]] = $exchangeRate * (double)$perItem[$e];
					  }
				  $e++; 
				  }
				 return $resultantArrayAfterAddition; 
				 unset($resultantArrayAfterAddition);
			}
			
			public function getBarcodeOutside($spilt_order_id  = null)
			 { 
			
				  $this->layout = '';
				  $this->autoRender = false;
				  $this->loadModel( 'OpenOrder' );
				  $this->loadModel( 'AssignService' );
				  $this->loadModel( 'MergeUpdate' );
				
				  //$uploadUrl = $this->getUrlBase();
				  $imgPath = WWW_ROOT .'img/orders/barcode/';   
				   
				  
				 // $allSplitOrders	=	$this->MergeUpdate->find('all', array('conditions' => array('MergeUpdate.status' => '0')));
				  
				  if($spilt_order_id != ''){
				 	 $allSplitOrders	=	$this->MergeUpdate->find('all', array('conditions' => array('MergeUpdate.product_order_id_identify' => $spilt_order_id)));
				  }else{
				 	 $allSplitOrders	=	$this->MergeUpdate->find('all', array('conditions' => array('MergeUpdate.status' => '0')));
				  }
				  
				  
			 	require_once(APP . 'Vendor' . DS . 'barcodegen' . DS . 'class/BCGFontFile.php'); 
				require_once(APP . 'Vendor' . DS . 'barcodegen' . DS . 'class/BCGDrawing.php');
				require_once(APP . 'Vendor' . DS . 'barcodegen' . DS . 'class/BCGcode128.barcode.php');
				//$font = new BCGFontFile(APP .'Vendor/barcodegen/font/Arial.ttf', 13);
				$colorFront = new BCGColor(0, 0, 0);
				$colorBack = new BCGColor(255, 255, 255);
					
				  if( count($allSplitOrders) > 0 )	
				  {
					  foreach( $allSplitOrders as $allSplitOrder )
					  {					  
						  $id 			= 		$allSplitOrder['MergeUpdate']['id'];
						  $openorderid	=	 	$allSplitOrder['MergeUpdate']['product_order_id_identify'];
						  $barcodeimage	=	$openorderid.'.png';
						  
						  	$orderbarcode=$openorderid;
							$code128 = new BCGcode128();
							$code128->setScale(2);
							$code128->setThickness(20);
							$code128->setForegroundColor($colorFront);
							$code128->setBackgroundColor($colorBack);
							$code128->setLabel(false);
							$code128->parse($orderbarcode);
												
							//Drawing Part
							$imgOrder128=$orderbarcode.".png";
							$imgOrder128path=$imgPath.'/'.$orderbarcode.".png";
							$drawing128 = new BCGDrawing($imgOrder128path, $colorBack);
							$drawing128->setBarcode($code128);
							$drawing128->draw();
							$drawing128->finish(BCGDrawing::IMG_FORMAT_PNG);
						  
						  if( $allSplitOrder['MergeUpdate']['product_order_id_identify'] != "" )
						  {
							  //$content = file_get_contents($uploadUrl.$openorderid);
							  //file_put_contents($imgPath.$barcodeimage, $content);
							  
							  $data['MergeUpdate']['id'] 	=  $id;
							  $data['MergeUpdate']['order_barcode_image'] 	=  $barcodeimage;
							  $this->MergeUpdate->save($data);
						  }
					  }
				  }
				  
				  $this->redirect( Router::url( $this->referer(), true ) );
			  
			 }
			
			public function getBarcodeOutside200317()
			 { 
				  
				  $this->layout = '';
				  $this->autoRender = false;
				  $this->loadModel( 'OpenOrder' );
				  $this->loadModel( 'AssignService' );
				  $this->loadModel( 'MergeUpdate' );
				
				  $uploadUrl = $this->getUrlBase();
				  $imgPath = WWW_ROOT .'img/orders/barcode/';   
				  //$allSplitOrders	=	$this->MergeUpdate->find('all', array('conditions' => array('MergeUpdate.status' => '0')));
				  
				  $allSplitOrders	=	$this->MergeUpdate->find('all', array('conditions' => array('MergeUpdate.order_barcode_image' => '')));
				  if( count($allSplitOrders) > 0 )	
				  {
					  foreach( $allSplitOrders as $allSplitOrder )
					  {					  
						  $id 			= 		$allSplitOrder['MergeUpdate']['id'];
						  $openorderid	=	 	$allSplitOrder['MergeUpdate']['product_order_id_identify'];
						  $barcodeimage	=	$openorderid.'.png';
						  
						  if( $allSplitOrder['MergeUpdate']['product_order_id_identify'] != "" )
						  {
							  $content = file_get_contents($uploadUrl.$openorderid);
							  file_put_contents($imgPath.$barcodeimage, $content);
							  
							  $data['MergeUpdate']['id'] 	=  $id;
							  $data['MergeUpdate']['order_barcode_image'] 	=  $barcodeimage;
							  $this->MergeUpdate->save($data);
						  }
					  }
				  }
				  
				  $this->redirect( Router::url( $this->referer(), true ) );
			  
			 }
			
			public function getBarcode()
				 { 
					 
				  $this->layout = '';
				  $this->autoRender = false;
				  $this->loadModel( 'OpenOrder' );
				  $this->loadModel( 'AssignService' );
				  $this->loadModel( 'MergeUpdate' );
				
				  //$uploadUrl = $this->getUrlBase();
				  $imgPath = WWW_ROOT .'img/orders/barcode/';   
				  $allSplitOrders	=	$this->MergeUpdate->find('all', array('conditions' => array('MergeUpdate.status' => '0', 'MergeUpdate.order_id !=' => '', 'MergeUpdate.order_barcode_image' => '')));
					
					require_once(APP . 'Vendor' . DS . 'barcodegen' . DS . 'class/BCGFontFile.php'); 
					require_once(APP . 'Vendor' . DS . 'barcodegen' . DS . 'class/BCGDrawing.php');
					require_once(APP . 'Vendor' . DS . 'barcodegen' . DS . 'class/BCGcode128.barcode.php');
					//require_once(APP . 'Vendor' . DS . 'barcodegen' . DS . 'font/Arial.ttf');
					//echo APP . 'Vendor' . DS . 'barcodegen';
					//$font = new BCGFontFile(APP .'Vendor/barcodegen/font/Arial.ttf', 13);
					$colorFront = new BCGColor(0, 0, 0);
					$colorBack = new BCGColor(255, 255, 255);
					
					// Barcode Part
					
					
					  foreach( $allSplitOrders as $allSplitOrder )
					  {
						  
								  $id 			= 		$allSplitOrder['MergeUpdate']['id'];
								  $openorderid	=	 	$allSplitOrder['MergeUpdate']['product_order_id_identify'];
								  $barcodeimage	=	$openorderid.'.png';
								  
								
									$orderbarcode=$openorderid;
									$code128 = new BCGcode128();
									$code128->setScale(2);
									$code128->setThickness(20);
									$code128->setForegroundColor($colorFront);
									$code128->setBackgroundColor($colorBack);
									$code128->setLabel(false);
									$code128->parse($orderbarcode);
														
									//Drawing Part
									$imgOrder128=$orderbarcode.".png";
									$imgOrder128path=$imgPath.'/'.$orderbarcode.".png";
									$drawing128 = new BCGDrawing($imgOrder128path, $colorBack);
									$drawing128->setBarcode($code128);
									$drawing128->draw();
									$drawing128->finish(BCGDrawing::IMG_FORMAT_PNG);
								
								  
									$data['MergeUpdate']['id'] 	=  $id;
									$data['MergeUpdate']['order_barcode_image'] 	=  $barcodeimage;
								  $this->MergeUpdate->save($data);
					  }
				  
						 /*$allopenorders	=	$this->OpenOrder->find('all', array('conditions' => array('OpenOrder.status' => '0')));
						  foreach( $allopenorders as $allopenorder )
						  {
							  $id 			= 		$allopenorder['OpenOrder']['id'];
							  $openorderid	=	 	$allopenorder['OpenOrder']['num_order_id'];
							  $barcodeimage	=	$openorderid.'.png';
							  
							  $content = file_get_contents($uploadUrl.$openorderid);
							  file_put_contents($imgPath.$barcodeimage, $content);
							  
							  $assingdata['AssignService']['id'] 	=  $id;
							  $assingdata['AssignService']['assign_barcode'] 	=  $barcodeimage;
							  $this->AssignService->save($assingdata);
						  }*/
				 }
				
			 
			public function getUrlBase()
				{
					return 'http://www.davidscotttufts.com/code/barcode.php?codetype=Code128&size=40&text=';
				}
			
			 
			public function checkorder()
				{
					$this->layout = '';
					$this->autoRender = false;
					
					$this->loadModel('OpenOrder');
					$this->loadModel('Source');
					$orders = $this->OpenOrder->find('all');
					
					foreach($orders as $allopenorder)
						{
							$data['id']				=	 $allopenorder['OpenOrder']['id'];
							$data['order_id']		=	 $allopenorder['OpenOrder']['order_id'];
							$data['num_order_id']	=	 $allopenorder['OpenOrder']['num_order_id'];
							$data['general_info']	=	 unserialize($allopenorder['OpenOrder']['general_info']);
							$data['shipping_info']	=	 unserialize($allopenorder['OpenOrder']['shipping_info']);
							$data['customer_info']	=	 unserialize($allopenorder['OpenOrder']['customer_info']);
							$data['totals_info']	=	 unserialize($allopenorder['OpenOrder']['totals_info']);
							$data['folder_name']	=	 unserialize($allopenorder['OpenOrder']['folder_name']);
							$data['items']			=	 unserialize($allopenorder['OpenOrder']['items']);
							
							$this->Source->bindModel(array('hasMany' => array('SubSource')));
							$sourceresults 	=	$this->Source->find('all');
							foreach($sourceresults as $sourceresult)
							{
								if($data['general_info']->Source == $sourceresult['Source']['source_name'])
								{
									foreach($sourceresult['SubSource'] as $subsource)
									{
										if($data['general_info']->SubSource == $subsource['sub_source_name'])
										{
											echo $data['id']."<br>";
											echo $subsource['sub_source_name']."<br>";
										}
									}
								}
							}
						}
				 
				}
				public function printpackagingslip()
				{
				$this->layout = '';
				$this->autoRender = false;
				$order	=	$this->getOpenOrderById('100024');
				//pr($order);
				$serviceLevel		=	 	$order['shipping_info']->PostalServiceName;
				$assignedservice	=	 	$order['assigned_service'];
				$courier			=	 	$order['courier'];
				$manifest			=	 	($order['manifest'] == 1) ? '1' : '0';
				$cn22				=	 	($order['cn22'] == 1) ? '1' : '0';
				$barcode			=	 	$order['assign_barcode'];
				
				$subtotal			=		$order['totals_info']->Subtotal;
				$totlacharge		=		$order['totals_info']->TotalCharge;
				$ordernumber		=		$order['num_order_id'];
				$fullname			=		$order['customer_info']->Address->FullName;
				$address1			=		$order['customer_info']->Address->Address1;
				$address2			=		$order['customer_info']->Address->Address2;
				$address3			=		$order['customer_info']->Address->Address3;
				$town				=	 	$order['customer_info']->Address->Town;
				$resion				=	 	$order['customer_info']->Address->Region;
				$postcode			=	 	$order['customer_info']->Address->PostCode;
				$country			=	 	$order['customer_info']->Address->Country;
				$paymentmethod		=	 	$order['totals_info']->PaymentMethod;
				$recivedate			=	 	explode('T', $order['general_info']->ReceivedDate);
				$address			=		$address1.','.$address2.','.$address3;
				
				App::import('Vendor','tcpdf/tcpdf');
			    //$pdf = new tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  
				// add a page
				$resolution= array(100, 150);
				$pdf->AddPage('P', $resolution);
			    $date =	date("Y-m-d");

							 
				$pdf->SetCreator(PDF_CREATOR);
				$pdf->SetHeaderData('', '', 'Service', '');
				

				$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
				$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

				$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

				$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
				$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
				$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

				$pdf->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);
	
				$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


					if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
						require_once(dirname(__FILE__).'/lang/eng.php');
						$pdf->setLanguageArray($l);
					}

				$pdf->SetFont('times', '', 8);
				//$pdf->AddPage();
				$j=0;
				$imgPath = WWW_ROOT .'css/';
				$imageurl 	=	Router::url('/img/source', true);
				$html ='<style>
body{margin:0px;}
#label{padding:10px; width:394px; font-size:11px; color:#000000; font-family:"Helvetica Neue",Helvetica,Arial,sans-serif; }
#label .container{width:100%}
 .header,  .cn22,  .tablesection,  .footer{border-bottom:2px solid #000000; clear:both; padding:8px 4px;}
 .footer{line-height:1.2}
 .leftside,  .rightside,  .date,  .sign{ width:50%}
 .jpoststamp{border:1px solid #000000; padding:1px;width:150px; float:right}
 .stampnumber{width: 35%; border:1px solid #000000; padding:4px; background:#000000;  color:#ffffff; text-align:center; font-size:22px; line-height:1.2}
 .jerseyserial{ float: left;    padding: 0 5px;    width: 65%; line-height:1; text-transform:uppercase; font-size:12px;font-weight:bold}
 .vatnumber{ float: left;    padding: 2px 0 0 0; line-height:1.2; font-size:11px;font-weight:bold}
 h1{font-family:"Oswald"; font-size:16px; margin:0px}
 h3{font-size:12px;font-weight:bold; margin:0}
 h4{font-size:14px;font-weight:bold; margin:0 0 5px}
 .rightheading{text-align:center;}
 .fullwidth{clear:both}
 .fullwidth h2{text-align:center; font-size:14px; font-weight:bold; margin:0; padding:5px 0;}
 .fullwidth p{line-height:1.2; margin-top:5px}
 .producttype{margin-bottom:5px;}
 .producttype div{width:25%; display:inline-block;}
 .producttype span{border: 1px solid #000000;
    display: inline-block;
    height: 15px;
    line-height: 1.2;
    margin-right: 5px;
    text-align:center;
    width: 15px;}
	 table{border:none; width:100%}
 th{ font-weight:bold;font-size:12px; }
 th,  td{padding:2px;line-height:1.2;}
 td table td{padding:0px;}
 .norightborder{border-right:0px;}
 .noleftborder{border-left:0px;}
 .rightborder{border-right:1px solid #000000;}
 .leftborder{border-left:1px solid #000000;}
 .topborder{border-top:1px solid #000000;}
 .bottomborder{border-bottom:1px solid #000000;}
 .center{text-align:center}
 .right{text-align:right}
 .bold{font-weight:bold;font-size:12px; }
 .sign{ margin-top: -20px;}
 .barcode{padding:5px 0;}
 .tracking{padding:5px 0;font-size:12px}
 .address{font-size:12px; line-height:1.2}
 .barcode div{font-family:"3 of 9 Barcode"; font-size:30px; margin: -5px 0;}
 .otherinfo{width:60%;  margin-top:5px}
 .totalprice{width:40%;  margin-top:5px}
 .tablesection{padding:0px 0px 8px ; margin-top:-1px}

</style>';
				$html .= '<body>
<div id="label">
<div class="container">
<table class="header row">
<tr>
<td>Logo</td>
<td>
<div class="barcode center">&nbsp;<div class="right">4584327-0</div><span class="center">4584327-0</span></div>
</td>
</tr>
</table>


<table class="cn22 row">
<tr>
<td class="leftside address"><h4>Ship From:</h4>
<span class="bold">SPC Limited</span><br>
Longueville Road<br>
St Saviour Jersey JE2 7WF<br>
E: sales@vitapure.co.uk<br>
T: 0845 800 8888
</td>

<td class="rightside address"><h4>Ship To:</h4>
<span class="bold">Julia Bastek</span><br>
12/13 Duddingston Mills<br>
Edinburgh, Midlothian, EH8 7TU<br>
United Kingdom<br>
T: 07517063902<br>
</td>
</tr>
</table>
<div class="">

</div>
<table class="header row">
<tr>
<td class="leftside"><span class="bold">Order No.:</span> 149680

</td>
<td><span class="bold">Ship Via:</span> Jersey Post</td>
</tr>
<tr>
<td class="rightside">
<span class="bold">Payment Method:</span> Paypal
</td>
<td><span class="bold">Order Date:</span> 23/09/2015</td>
</tr>
</table>

<div class="">
<table class="tablesection row" border="1" cellpadding="5px" cellspacing="0">
<tr>
<th class="" width="15%">Item No.</th>
<th valign="top" width="40%">Description of contents</th>
<th valign="top" class="center" width="15%">Qty</th>
<th valign="top" class="center" width="15%">Price</th>
<th valign="top" class="center " width="15%">Amount</th>
</tr>
<tr>
<td valign="top" class="">0001</td>
<td valign="top">Playtex Drop Ins Pre Sterilized Soft Bottle Liners, 8-10 oz. 100 ea</td>
<td valign="top" class="center">2</td>
<td valign="top" class="right">£75.00</td>
<td valign="top" class="right ">£150.00</td>
</tr>
<tr>
<td valign="top" class="">0002</td>
<td valign="top">Now Foods, Calcium Hydroxyapatite 250mg 120 Capsules</td>
<td valign="top" class="center">1</td>
<td valign="top" class="right">£6.96</td>
<td valign="top" class="right ">£6.96</td>
</tr>

</table>
<table>
<tr>
<td class="otherinfo">
<div><span class="bold">Total Item Count:</span> 3</div>
<div><span class="bold">Payment Reference:</span> 4G073562WB731302E</div></td>
<td class="totalprice">
<table>
<tr>
<td class="leftside right">Sub Total</td>
<td class="rightside right">£156.96</td>
</tr>
<tr>
<td class="leftside right">Shipping</td>
<td class="rightside right">£9.25</td>
</tr>
<tr>
<td class="leftside right">Tax</td>
<td class="rightside right">£0.00</td>
</tr>
<tr>
<td class="leftside right">Discount</td>
<td class="rightside right">£0.00</td>
</tr>
<tr>
<td class="leftside right">Other Charges</td>
<td class="rightside right">£0.00</td>
</tr>
<tr>
<td class="leftside right bold">Order Total</td>
<td class="rightside right bold">£166.21</td>
</tr>
</table>
</td>
</tr>
</table>


</div>
<div class="footer row">
Thanks for shopping with us. It was a pleasure to serve you.
Get special 5% off on next purchase by using promo code: WELCOMEBACK
</div>

</div>
</div>

</body>';

			//$html .= '<style>'.file_get_contents($imgPath.'packing.css').'</style>';
			//echo $html;
				$pdf->writeHTML($html, true, false, true, false, '');
				$js = 'print(true);';
				$pdf->IncludeJS($js);
				$pdf->Output('Service_'.$date.'.pdf', 'D');
	}
	

	public function getOpenOrderById( $numOrderId = null )
	{
		$this->loadModel('OpenOrder');
		$order = $this->OpenOrder->find('first', array('conditions' => array('OpenOrder.num_order_id' => $numOrderId )));
		
		$data['id']					=	 $order['OpenOrder']['id'];
		$data['order_id']			=	 $order['OpenOrder']['order_id'];
		$data['destination']			=	 $order['OpenOrder']['destination'];
		$data['sub_source']			=	 $order['OpenOrder']['sub_source'];
		$data['num_order_id']		=	 $order['OpenOrder']['num_order_id'];
		$data['general_info']		=	 unserialize($order['OpenOrder']['general_info']);
		$data['shipping_info']		=	 unserialize($order['OpenOrder']['shipping_info']);
		$data['customer_info']		=	 unserialize($order['OpenOrder']['customer_info']);
		$data['totals_info']		=	 unserialize($order['OpenOrder']['totals_info']);
		$data['folder_name']		=	 unserialize($order['OpenOrder']['folder_name']);
		$data['items']				=	 unserialize($order['OpenOrder']['items']);
		$data['assigned_service']	=	 $order['AssignService']['assigned_service'];
		$data['assign_barcode']		=	 $order['AssignService']['assign_barcode'];
		$data['manifest']			=	 $order['AssignService']['manifest'];
		$data['cn22']				=	 $order['AssignService']['cn22'];
		$data['courier']			=	 $order['AssignService']['courier'];
		
		return $data;
	}
	
	/*public function shortingthtml()
	{
		$this->layout = 'index';
		
	}*/
	
	public function domprint()
		{
				$this->layout = '';
				$this->autoRender = false;
			
				//App::import('Vendor','dompdf_config.inc');
				require_once(APP . 'Vendor' . DS . 'dompdf' . DS . 'dompdf_config.inc.php'); 
				
				spl_autoload_register('DOMPDF_autoload'); 
				$dompdf = new DOMPDF();
				
				$this->layout = '';
				//$this->autoRender = false;
				
				$this->loadModel( 'PackagingSlip' );
				$order	=	$this->getOpenOrderById('100001');
				//pr($order);
				//exit;
				$serviceLevel		=	 	$order['shipping_info']->PostalServiceName;
				$assignedservice	=	 	$order['assigned_service'];
				$courier			=	 	$order['courier'];
				$manifest			=	 	($order['manifest'] == 1) ? '1' : '0';
				$cn22				=	 	($order['cn22'] == 1) ? '1' : '0';
				$barcode			=	 	$order['assign_barcode'];
				
				$subtotal			=		$order['totals_info']->Subtotal;
				$totlacharge		=		$order['totals_info']->TotalCharge;
				$ordernumber		=		$order['num_order_id'];
				$fullname			=		$order['customer_info']->Address->FullName;
				$address1			=		$order['customer_info']->Address->Address1;
				$address2			=		$order['customer_info']->Address->Address2;
				$address3			=		$order['customer_info']->Address->Address3;
				$town				=	 	$order['customer_info']->Address->Town;
				$resion				=	 	$order['customer_info']->Address->Region;
				$postcode			=	 	$order['customer_info']->Address->PostCode;
				$country			=	 	$order['customer_info']->Address->Country;
				$phone				=	 	$order['customer_info']->Address->PhoneNumber;
				$paymentmethod		=	 	$order['totals_info']->PaymentMethod;
				$postagecost		=	 	$order['totals_info']->PostageCost;
				$tax				=	 	$order['totals_info']->Tax;
				$items				=	 	$order['items'];
				$address			=		'';
				$address			.=		($address2 != '') ? $address2.'<br>' : '' ; 
				$address			.=		($address3 != '') ? $address3.'<br>' : '' ;
				$address			.=		(isset($town)) ? $town.'<br>' : '';
				$address			.=		(isset($resion)) ? $resion.'<br>' : '';
				$address			.=		(isset($postcode)) ? $postcode.'<br>' : '';
				$address			.=		(isset($country)) ? $country.'<br>' : '';
											
				$recivedate			=	 	explode('T', $order['general_info']->ReceivedDate);
				
				
				$gethtml 	=	$this->PackagingSlip->find('all');
				//pr($gethtml);
				$setRepArray = array();
				$setRepArray[] 					= $address1;
				$setRepArray[] 					= $address2;
				$setRepArray[] 					= $address3;
				$setRepArray[] 					= $town;
				$setRepArray[] 					= $resion;
				$setRepArray[] 					= $postcode;
				$setRepArray[] 					= $country;
				$setRepArray[] 					= $phone;
				$setRepArray[] 					= $ordernumber;
				$setRepArray[] 					= $courier;
				$setRepArray[] 					= $recivedate[0];
				$i = 1;
				$str = '';
				foreach($items as $item)
				{
					$str .= '<tr>
							<td valign="top" class="">'.$i.'</td>
							<td valign="top">'.substr($item->Title, 0, 10 ).'</td>
							<td valign="top" class="center">'.$item->Quantity.'</td>
							<td valign="top" class="right">'.$item->PricePerUnit.'</td>
							<td valign="top" class="right ">'.$item->Quantity * $item->PricePerUnit.'</td>
							</tr>';
					$i++;
				}
				$totalitem = $i - 1;
				$setRepArray[]	=	 $str;
				$setRepArray[]	=	 $totalitem;
				$setRepArray[]	=	 $subtotal;
				$Path 			= 	'/img/client/';
				$img			=	 '<img src='.$Path.'demo.png height="50" width="50">';
				//$img			=	 '<img src='.$Path.'demo.png alt="test alt attribute" width="100" height="100" border="0" />';
				$setRepArray[]	=	 $img;
				$setRepArray[]	=	 $postagecost;
				$setRepArray[]	=	 $tax;
				$totalamount	=	 (float)$subtotal + (float)$postagecost + (float)$tax;
				$setRepArray[]	=	 $totalamount;
				$setRepArray[]	=	 $address;
				
				
				$imgPath = WWW_ROOT .'css/';
				
							$html2 = '<body><div id="label">
							<div class="container">
							<table class="header row" style="margin-top:10px;">
							<tr>
							<td>_IMAGE_</td>
							<td>
							<div class="barcode center">&nbsp;<div class="right">4584327-0</div><span class="center">4584327-0</span></div>
							</td>
							</tr>
							</table>
							<table class="cn22 row">
							<tr>
							<td class="leftside address"><h4 style="font-size:12px; ">Ship From:</h4>
							<span class="bold">SPC Limited</span><br>
							Longueville Road<br>
							St Saviour Jersey JE2 7WF<br>
							E: sales@vitapure.co.uk<br>
							T: 0845 800 8888
							</td>

							<td class="rightside address" ><h4 style="font-size:12px;">Ship To:</h4>
							<span class="bold">_ADDRESS1_</span><br>
							_ADDRESS_
							T: _PHONE_<br>
							</td>
							</tr>
							</table>
							<div class="">

							</div>
							<table class="header row">
							<tr style="margin-top:-10px;">
							<td class="leftside"><span class="bold">Order No.:</span>_ORDERNUMBER_

							</td>
							<td><span class="bold">Ship Via:</span>_COURIER_</td>
							</tr>
							<tr>
							<td class="rightside">
							<span class="bold">Payment Method:</span> Paypal
							</td>
							<td><span class="bold">Order Date:</span>_RECIVEDATE_</td>
							</tr>
							</table>

							<div class="">
							<table class="tablesection row" border="1" cellpadding="5px" cellspacing="0">
							<tr>
							<th class="" width="20%">Item No.</th>
							<th valign="top" width="40%">Description of contents</th>
							<th valign="top" class="center" width="10%">Qty</th>
							<th valign="top" class="center" width="14%">Price</th>
							<th valign="top" class="center " width="16%">Amount</th>
							</tr>
							_ORDERSUMMARY_
							</table>
							<table>
							<tr>
							<td class="otherinfo">
							<div><span class="bold">Total Item Count: </span>_TOTALITEM_</div>
							<div><span class="bold">Payment Reference:</span> 4G073562WB731302E</div></td>
							<td class="totalprice">
							<table>
							<tr>
							<td class="leftside right">Sub Total</td>
							<td class="rightside right">£_SUBTOTAL_</td>
							</tr>
							<tr>
							<td class="leftside right">Shipping</td>
							<td class="rightside right">£_POSTAGECOST_</td>
							</tr>
							<tr>
							<td class="leftside right">Tax</td>
							<td class="rightside right">£_TAX_</td>
							</tr>
							<tr>
							<td class="leftside right bold">Order Total</td>
							<td class="rightside right bold">£_TOTALAMOUNT_</td>
							</tr>
							</table>
							</td>
							</tr>
							</table>


							</div>
							<div class="footer row">
							Thanks for shopping with us. It was a pleasure to serve you.
							Get special 5% off on next purchase by using promo code: WELCOMEBACK
							</div>

							</div>
							</div></body>';
				
				
				//$html2 	=	$gethtml[0]['PackagingSlip']['html'];
				//$html2 .= '<style>'.file_get_contents($imgPath.'packing.css').'</style>';
				$html 	= $this->setReplaceValue( $setRepArray, $html2 );
				
				echo $html;
				//$dompdf->load_html($html);
				//$dompdf->render();
				//$dompdf->stream("hello.pdf");
				
				
		}
		
		
		/*public function setAgainAssignedServiceToAllOrder()
		{
	   
		      
		   $this->loadModel( 'AssignService' );
		   $this->loadModel( 'OpenOrder' );
		   
		   // On Fly unbind Model First
		   $this->AssignService->unbindModel( array( 'belongsTo' => array( 'OpenOrder' ) ) );
		   
		   // Get data from this table and update into open order because for managing Manifesto file
		   $getAssignServiceData = $this->AssignService->find( 'all' );
		  
		   foreach( $getAssignServiceData as $assignIndex => $assignValue )
		   { 
			
			$this->request->data['OpenOrder']['id'] = $assignValue['AssignService']['open_order_id'];
			$this->request->data['OpenOrder']['service_name'] = $assignValue['AssignService']['assigned_service'];    
			$this->request->data['OpenOrder']['service_provider'] = $assignValue['AssignService']['service_provider'];
			$this->request->data['OpenOrder']['service_code'] = $assignValue['AssignService']['provider_ref_code'];    
			$this->OpenOrder->saveAll( $this->request->data['OpenOrder'] );    
			
		   }
					
		   // Update the service records , those will entertain which has service code.
		   $conditions = array(
			'OpenOrder.status' => '1',
			'OpenOrder.service_code !=' => '',
			//'OpenOrder.soreted !=' => 'sorted',
			array(
			 'AND' => array(
			  'OpenOrder.service_name !=' => 'Over Dimension',
			  'OpenOrder.service_name !=' => 'Over Weight Or Not in Matrices'
			 )
			)
		   );
	   
	   $params = array( 
		'conditions' => $conditions,
		'fields' => array( 'count( OpenOrder.service_code ) as ServiceCode_Count' , 'OpenOrder.service_name as ServiceName' , 'OpenOrder.service_provider as ServiceProvider' , 'OpenOrder.service_code as ServiceCode' ),
		'group'  => array( 'OpenOrder.service_code' )
	   );
	   
	   $receivedServiceCount = $this->OpenOrder->find( 'all' , $params );
	   $this->setUpdateServiceCounter( $receivedServiceCount );
   
  }*/
  
		/*
		 * Update 17/11/15 
		 * 
		 * */
	public function setAgainAssignedServiceToAllOrder()
                                {
                                                
                                                $this->layout = '';
												$this->autoRender = false;
                                             
                                                /*
                                                * 
                                                 * Params, Get All assigned services and setup 
                                                 * 
                                                 */                                          
                                                $this->loadModel( 'AssignService' );
                                                $this->loadModel( 'OpenOrder' );
                                                
                                                // On Fly unbind Model First
                                                $this->AssignService->unbindModel( array( 'belongsTo' => array( 'OpenOrder' ) ) );
                                                
                                                // Get data from this table and update into open order because for managing Manifesto file
                                                $getAssignServiceData = $this->AssignService->find( 'all' );
												
                                                foreach( $getAssignServiceData as $assignIndex => $assignValue )
                                                {              
                                                                
                                                                $this->request->data['OpenOrder']['OpenOrder']['id'] = $assignValue['AssignService']['open_order_id'];
                                                                $this->request->data['OpenOrder']['OpenOrder']['service_name'] = $assignValue['AssignService']['assigned_service'];                                                           
                                                                $this->request->data['OpenOrder']['OpenOrder']['service_provider'] = $assignValue['AssignService']['service_provider'];
                                                                $this->request->data['OpenOrder']['OpenOrder']['service_code'] = $assignValue['AssignService']['provider_ref_code'];                                                       
                                                                
                                                                // Update Manifest and cn22
                                                                $this->request->data['OpenOrder']['OpenOrder']['manifest'] = $assignValue['AssignService']['manifest'];                                                            
                                                                $this->request->data['OpenOrder']['OpenOrder']['cn22'] = $assignValue['AssignService']['cn22'];                                                     
                                                                
                                                                $this->OpenOrder->saveAll( $this->request->data['OpenOrder'] );                                                         
                                                                
                                                }
                                                                                                                                                                               
                                                // Update the service records , those will entertain which has service code.
                                                $conditions = array(
                                                                'OpenOrder.status' => '1',
                                                                'OpenOrder.service_code !=' => '',
                                                                //'OpenOrder.soreted !=' => 'sorted',
                                                                array(
                                                                                'AND' => array(
                                                                                                'OpenOrder.service_name !=' => 'Over Dimension',
                                                                                                'OpenOrder.service_name !=' => 'Over Weight Or Not in Matrices'
                                                                                )
                                                                )
                                                );
                                                
                                                $params = array( 
                                                                'conditions' => $conditions,
                                                                'fields'   => array( 'count( OpenOrder.service_code ) as ServiceCode_Count' , 'OpenOrder.service_name as ServiceName' , 'OpenOrder.service_provider as ServiceProvider' , 'OpenOrder.service_code as ServiceCode' , 'OpenOrder.manifest as Manifest' , 'OpenOrder.cn22 as CN22' , 'OpenOrder.destination as Country' ),
                                                                'group'                  => array( 'OpenOrder.service_code' , 'OpenOrder.destination'  )
                                                );
                                                
                                                $receivedServiceCount = $this->OpenOrder->find( 'all' , $params );                                                                                                                                                                                                                                                                                         
                                                //$this->setUpdateServiceCounter( $receivedServiceCount );
                                                
                                                
                                }
                                
                                /*
                                 * 
                                 * Params, Its sync button process to camm service counter
                                 * 
                                 */ 
                                public function callService()
                                {
									$this->layout = 'index';
									$this->call_service_counter();
									
									$this->redirect( array( 'controller' => 'cronjobs' , 'action' => 'shortingthtml' ) );
								}
                                
                                public function call_service_counter( $id = null)
                                {
									
									$this->layout = '';
									$this->autoRender = false;
									
									$this->loadModel('MergeUpdate');
									
									// Update the service records , those will entertain which has service code.
									$conditions = array('MergeUpdate.product_order_id_identify' => $id );
									
									$params = array( 
													'conditions' => $conditions,
													'fields'   => array( 'count( MergeUpdate.provider_ref_code ) as ServiceCode_Count' , 
																		 'MergeUpdate.service_name as ServiceName' , 
																		 'MergeUpdate.service_provider as ServiceProvider' , 
																		 'MergeUpdate.provider_ref_code as ServiceCode' , 
																		 'MergeUpdate.manifest as Manifest' , 
																		 'MergeUpdate.cn_required as CN22' , 
																		 'MergeUpdate.delevery_country as Country',
																		 'MergeUpdate.service_id' ),
													'group'    => array( 'MergeUpdate.provider_ref_code' , 'MergeUpdate.delevery_country'  )
									);
									
									$receivedServiceCount = $this->MergeUpdate->find( 'all' , $params );                                                                                                                                                                                                                                                                                                                                         														
									//pr($receivedServiceCount); exit;
									$this->setUpdateServiceCounter( $receivedServiceCount );
									
								}
                                
                                private function setUpdateServiceCounter( $serviceCounter = null )
                                {                                              
                                                
                                                /*
                                                * 
                                                 * Params, Need to input or update accordingly
                                                * 
                                                 */ 
                                                $this->loadModel( 'ServiceCounter' );
                                                
                                                // Truncate the table first
                                                //$this->ServiceCounter->query('Truncate service_counters');
                                                
                                                // Update or input the values
                                                foreach( $serviceCounter as $serviceCounterIndex => $serviceCounterValue )
                                                { 
													
														//Check if exists or not
														$params = array(
															'conditions' => array(
																'ServiceCounter.destination'		=> $serviceCounterValue['MergeUpdate']['Country'],
																//'ServiceCounter.service_name'		=> $serviceCounterValue['MergeUpdate']['ServiceName'],
																//'ServiceCounter.service_provider'	=> $serviceCounterValue['MergeUpdate']['ServiceProvider'],
																//'ServiceCounter.service_code'		=> $serviceCounterValue['MergeUpdate']['ServiceCode']
																'ServiceCounter.service_id'		    => $serviceCounterValue['MergeUpdate']['service_id']
															)
														);
														$checkService = json_decode(json_encode($this->ServiceCounter->find( 'first', $params )),0);														
														
														//pr($serviceCounterValue);
														//pr($checkService); exit;
														
														if( count($checkService) > 0 )
														{
															$this->request->data['ServiceCounter']['ServiceCounter']['id']  = $checkService->ServiceCounter->id;															
															$this->request->data['ServiceCounter']['ServiceCounter']['original_counter']  = $checkService->ServiceCounter->original_counter + $serviceCounterValue[0]['ServiceCode_Count'];															
															
															// Create Temp row pointer
															$this->ServiceCounter->create();
															$this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );	
														}
														else
														{
															$this->request->data['ServiceCounter']['ServiceCounter']['service_name'] = $serviceCounterValue['MergeUpdate']['ServiceName'];
															$this->request->data['ServiceCounter']['ServiceCounter']['service_code']  = $serviceCounterValue['MergeUpdate']['ServiceCode'];
															$this->request->data['ServiceCounter']['ServiceCounter']['service_provider']  = $serviceCounterValue['MergeUpdate']['ServiceProvider'];
															$this->request->data['ServiceCounter']['ServiceCounter']['service_id']  = $serviceCounterValue['MergeUpdate']['service_id'];
															$this->request->data['ServiceCounter']['ServiceCounter']['original_counter']  = $serviceCounterValue[0]['ServiceCode_Count'];
															$this->request->data['ServiceCounter']['ServiceCounter']['manifest']    = ( isset($serviceCounterValue['MergeUpdate']['Manifest']) && $serviceCounterValue['MergeUpdate']['Manifest'] != '') ? '1' : '0';
															$this->request->data['ServiceCounter']['ServiceCounter']['destination'] = ( isset( $serviceCounterValue['MergeUpdate']['Country'] ) && $serviceCounterValue['MergeUpdate']['Country'] != '' ) ? $serviceCounterValue['MergeUpdate']['Country'] : '';
															$this->request->data['ServiceCounter']['ServiceCounter']['bags']   = 1;
															$this->request->data['ServiceCounter']['ServiceCounter']['counter'] = 0;                
															
															// Create Temp row pointer
															$this->ServiceCounter->create();
															$this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );
														}
                                                }                                                                                       
                                }
                                
                                public function setUpdateServiceCounter_old( $serviceCounter = null )
                                {                                              
									
												//pr($serviceCounter); exit;
                                                
                                                /*
                                                * 
                                                 * Params, Need to input or update accordingly
                                                * 
                                                 */ 
                                                $this->loadModel( 'ServiceCounter' );
                                                
                                                // Truncate the table first
                                                $this->ServiceCounter->query('Truncate service_counters');
                                                
                                                // Update or input the values
                                                foreach( $serviceCounter as $serviceCounterIndex => $serviceCounterValue ): 
                                                                $this->request->data['ServiceCounter']['ServiceCounter']['service_name']                         = $serviceCounterValue['OpenOrder']['ServiceName'];
                                                                $this->request->data['ServiceCounter']['ServiceCounter']['service_code']                           = $serviceCounterValue['OpenOrder']['ServiceCode'];
                                                                $this->request->data['ServiceCounter']['ServiceCounter']['service_provider']    = $serviceCounterValue['OpenOrder']['ServiceProvider'];
                                                                $this->request->data['ServiceCounter']['ServiceCounter']['original_counter']     = $serviceCounterValue[0]['ServiceCode_Count'];
                                                                $this->request->data['ServiceCounter']['ServiceCounter']['manifest']                                    = ( isset($serviceCounterValue['OpenOrder']['Manifest']) && $serviceCounterValue['OpenOrder']['Manifest'] != '') ? '1' : '0';
                                                                $this->request->data['ServiceCounter']['ServiceCounter']['destination']                               = ( isset( $serviceCounterValue['OpenOrder']['Country'] ) && $serviceCounterValue['OpenOrder']['Country'] != '' ) ? $serviceCounterValue['OpenOrder']['Country'] : '';
                                                                $this->request->data['ServiceCounter']['ServiceCounter']['bags']                                                             = 1;
                                                                //$this->request->data['ServiceCounter']['ServiceCounter']['counter']                                  = 0;                
                                                                
                                                                // Create Temp row pointer
                                                                $this->ServiceCounter->create();
                                                                $this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );
                                                endforeach;                                                                                       
                                }
                                
                                public function sh()
                                {
									
									$this->layout = 'index';
									/*
									* 
									 * Params to set the data over view of sorting station
									*
									*/  
									
									// Load ServiceCounter
									$this->loadModel( 'ServiceCounter' );
									
									
									// Get Data
									$serviceCounterData = $this->ServiceCounter->find( 'all' , array( 'conditions' => array( 'ServiceCounter.original_counter >' => 0 ) , 'order' => 'ServiceCounter.original_counter DESC' ) );
									
									pr($serviceCounterData); exit;
									
								}
                                
                                public function shortingthtml()
                                {
                                                $this->layout = 'index';
                                                /*
                                                * 
                                                 * Params to set the data over view of sorting station
                                                *
                                                */  
                                                
                                                // Load ServiceCounter
                                                $this->loadModel( 'ServiceCounter' );
                                                
                                                
                                                // Get Data
                                                //$serviceCounterData = $this->ServiceCounter->find( 'all' , array( 'conditions' => array( 'ServiceCounter.original_counter >' => 0 ) , 'order' => 'ServiceCounter.original_counter DESC' , 'group' => array( 'ServiceCounter.service_code' ) ) );
                                                //pr($serviceCounterData); exit;
                                                
                                                $serviceCounterData = $this->ServiceCounter->find( 'all' , array( 'conditions' => array( 'ServiceCounter.original_counter >' => 0 ) , 'order' => 'ServiceCounter.original_counter DESC' ) );
                                                $leftService = array();
                                                $rightService = array();
                                                
                                                // Set left and right corner data for sorting station operator
                                                $iGetter = 1;$icount = 0;while( $icount <= count( $serviceCounterData )-1 ):
													/*if( ceil(count( $serviceCounterData ) / 2) >= $iGetter ):
														$leftService[] = $serviceCounterData[$icount];
													else:     													
														$rightService[] = $serviceCounterData[$icount];
													endif;*/
													
													if( $icount <= 26 )
													{
														$leftService[] = $serviceCounterData[$icount];
													}
													else
													{
														$rightService[] = $serviceCounterData[$icount];
													}
                                                $icount++; 
                                                $iGetter++;
                                                endwhile;
                                                
                                                // Get Service counter details which we have atleast 1 manifest then will active the button
                                                $getActivationForCutOffList = count( $this->ServiceCounter->find( 'all', array( 'conditions' => array( 'ServiceCounter.manifest' => 1 , 'ServiceCounter.order_ids !=' => '' ) ) ) );
                                                
                                                // Input and check operator data inTime, If already exists accordign today that will not input again but not today is ther that will input it.
                                                $this->loadModel( 'SortingoperatortimeCalculation' );
                                                $user_id = $this->Session->read('Auth.User.id');
                                                $paramOperator = array(
                                                                'conditions' => array(
                                                                                'SortingoperatortimeCalculation.user_id' => $user_id,
                                                                                'SortingoperatortimeCalculation.today_date' => date( 'Y-m-d' )
                                                                )
                                                );
                                                
                                                if( count( $this->SortingoperatortimeCalculation->find( 'first', $paramOperator ) ) == 0 ):                                              
                                                                $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['user_id'] = $user_id;
                                                                $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['in_time'] = date('Y-m-d G:i:s');
                                                                $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['today_date'] = date('Y-m-d');
                                                                $this->SortingoperatortimeCalculation->saveAll( $this->request->data['SortingoperatortimeCalculation'] );
                                                endif;    
                                                
                                                // Set data for view                                                                         
                                                $this->set( compact( 'leftService' , 'rightService' , 'getActivationForCutOffList' ) );                              
                                }
     
     
     
     /*
	 * 
	 * Params, CutOff creation for specific services which have own manifest value(1)
	 * Belgium Post / Jersey Post / Yodel / Fedex / DHL etc etc
	 * Service Name + Current Time
	 * 
	 */ 
	 
	public function manifestCreate()
	{
		
		$this->layout = 'index';
		
		$this->loadModel( 'PostalProvider' );
		
		$getPostalNames = $this->PostalProvider->find('all' , array( 'conditions' => array( 'PostalProvider.status' => 1 ) ));				
		$folderName = 'Service Manifest -'. date("d.m.Y");
		$manifestPath = WWW_ROOT .'img/cut_off/'.$folderName;
		
		App::uses('Folder', 'Utility');
	    App::uses('File', 'Utility');
		$dir = new Folder($manifestPath, true, 0755);
	    $files = $dir->find('.*\.csv');
	    $files = Set::sort($files, '{n}', 'DESC');
	    
	    //pr($files);
	    
	    $this->set('files', $files);
	    $this->set( 'folderName' , $folderName );		
		$this->set( 'getPostalNames' , $getPostalNames );
		
	}           
	/*
	* 
	 * Params, CutOff creation for specific services which have own manifest value(1)
	* 
	 */ 
   
   public function getrand()
   {
	   (float)$min=20.10;
	   (float)$max=21.99;

   return ($min + ($max - $min) * (mt_rand() / mt_getrandmax()));	    	
   }	 
   public function updateManifestDate($merge_id)
   {
	  $this->loadModel('MergeUpdate'); 
	   date_default_timezone_set('Europe/Jersey');
	  $firstName = ( $this->Session->read('Auth.User.first_name') != '' ) ? $this->Session->read('Auth.User.first_name') : '_';
	  $lastName = ( $this->Session->read('Auth.User.last_name') != '' ) ? $this->Session->read('Auth.User.last_name') : '_';
	  $manifest_username = $firstName.' '.$lastName;
	  $data['id'] = $merge_id;   
	  $data['manifest_date'] = date('Y-m-d H:i:s');   
	  $data['manifest_username'] 	= $manifest_username;   
	  $this->MergeUpdate->saveAll( $data);	    	
   }
   public function createCutOffList()
	{
		$this->layout = '';
		$this->autoRender = false;          
		
		// Get All manifest related services
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		
		/* start european country iso code*/
		$isoCode = Configure::read('customIsoCodes');
		  /* end european country iso code*/
		
		//Global Variable
		$glbalSortingCounter = 0;
		
		$serviceProvider = $this->request->data['serviceProvider'];
		date_default_timezone_set('Europe/Jersey');
		$time_in_12_hour_format  = date("g:i a", strtotime(date("H:i",$_SERVER['REQUEST_TIME'])));
			
		$folderName = 'Service Manifest -'. date("d.m.Y");
		$service = str_replace(' ', '', str_replace(':','_',$serviceProvider.'-'. date("d.m.Y") .'_'. $time_in_12_hour_format));
		
		//Changes for Royalmail
		// Get Data
		$manifest = json_decode(json_encode($this->ServiceCounter->find( 'all' , 
			array( 
				'conditions' => array( 
						//'ServiceCounter.manifest' => 1 , 
						'ServiceCounter.service_provider IN ' => array($serviceProvider,'Royalmail','GLS') , 
						//'ServiceCounter.service_provider' => $serviceProvider , ,
						'ServiceCounter.order_ids !=' => '' , 
						'ServiceCounter.counter >' => 0 , 

						'ServiceCounter.original_counter >' => 0, 
						//'ServiceCounter.locking_stage' => 0 
					)                                                                                                           
				)                                                                              
			)),0); 
		
		if( count($manifest) > 0 )
		{
			
			//We got number of sorted rows which had been done through operator and creating manifest 1 for same provider
			$inc = 1;$cnt = 2;$e = 0;foreach( $manifest as $manifestIndex => $manifestValue )
			{
				
				if( $e == 0 )
				{
					// Clean Stream (Input)
					//ob_clean();                                                         
					App::import('Vendor', 'PHPExcel/IOFactory');
					App::import('Vendor', 'PHPExcel');                          
					
					//Set and create Active Sheet for single workbook with singlle sheet
					$objPHPExcel = new PHPExcel();       
					$objPHPExcel->createSheet();
					
					//Column Create                              
					$objPHPExcel->setActiveSheetIndex(0);
					
					$objPHPExcel->getActiveSheet()->setCellValue('A1', 'LineNo');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Option');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('C1', 'LineIdentifier');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('D1', 'GroupageManifestNo');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Consignor');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('F1', 'ConsignorAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('G1', 'ConsignorAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('H1', 'ConsignorPostCode');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('I1', 'ConsigneeName');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('J1', 'ConsigneeAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('K1', 'ConsigneeAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('L1', 'ConsigneeGSTNumber');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('M1', 'ConsigneePostCode');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('N1', 'ConsigneeCountry');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('O1', 'NoOfUnits');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('P1', 'GrossMass');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Description');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('R1', 'Value');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('S1', 'ValueCurr');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('T1', 'ForwardingAgent');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('U1', 'ForwardingAgentAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('V1', 'ForwardingAgentAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('W1', 'ForwardingAgentCountry');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('X1', 'CommunityStatus');                                                                  
						
				}
				
				$orderIds = explode( ',' , $manifestValue->ServiceCounter->order_ids);
				
				//pr($orderIds);
				
				$this->loadModel( 'MergeUpdate' );
				$this->loadModel( 'Product' );
				$this->loadModel( 'ProductDesc' );
				$this->loadModel( 'OpenOrder' );
				$this->loadModel( 'Customer' );
				
				$combineSku = '';				
				$k = 0;while( $k <= count($orderIds)-1 )
				{
					
					$orderIdSpecified = $orderIds[$k];
					$this->updateManifestDate($orderIdSpecified);
					$params = array(
						'conditions' => array(
							'MergeUpdate.id' => $orderIdSpecified
						),
						'fields' => array(
							'MergeUpdate.id',
							'MergeUpdate.order_id',
							'MergeUpdate.product_order_id_identify',
							'MergeUpdate.quantity',
							'MergeUpdate.sku',
							'MergeUpdate.price',
							'MergeUpdate.packet_weight',
                            'MergeUpdate.envelope_weight'
						)
					);
					
					$mergeOrder = json_decode(json_encode($this->MergeUpdate->find(
						'all', $params
					)),0);
					
					$this->setManifestRecord( $mergeOrder[0]->MergeUpdate->id, $service );
					//pr($mergeOrder);
										
					$getSku = explode( ',' , $mergeOrder[0]->MergeUpdate->sku);
					
                    $packageWeight = $mergeOrder[0]->MergeUpdate->envelope_weight;                    
					$totalPriceValue = 0;
					$massWeight = 0;
					$totalUnits = 0;
					$combineTitle = '';
					$combineCategory = '';
                    $calculateWeight = 0;
                    
                    $getSpecifier = explode('-', $mergeOrder[0]->MergeUpdate->product_order_id_identify);
                   
                    $setSpaces = '';
                    $identifier = $getSpecifier[1];
                    $em = 0;while( $em < $identifier )
                    {
						$setSpaces .= $setSpaces.' ';						
					$em++;	
					}
					
					$j = 0;while( $j <= count($getSku)-1 )
					{
						$newSku = explode( 'XS-' , $getSku[$j] );
						
						//Get Title of product
						$setNewSku = 'S-'.$newSku[1];
						
						$this->loadModel( 'Product' );
						$this->loadModel( 'Category' );					
					
						$this->Product->bindModel(
							array(
							 'hasOne' => array(
							  'Category' => array(
							   'foreignKey' => false,
							   'conditions' => array('Category.id = Product.category_id'),
							   'fields' => array('Category.id,Category.category_name')
							  )
							 )
							)
						   );
					
						$productSku = $this->Product->find(
							'first' ,
							array(
								'conditions' => array(
									'Product.product_sku' => $setNewSku
								)
							)
						);
						
						if( $combineTitle == '' )
						{
							$combineTitle = $newSku[0] .'X' .substr($productSku['Product']['product_name'],0,25);	
							$totalUnits = $totalUnits + $newSku[0];     
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;		
							$combineCategory = $productSku['Category']['category_name'];				
						}
						else
						{
							$combineTitle .= ',' .  $newSku[0] . 'X' .substr($productSku['Product']['product_name'],0,25);	
							$totalUnits = $totalUnits + $newSku[0];
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;                                                        
							$combineCategory .= ',' . $productSku['Category']['category_name'];				
						}
						 
						if( $combineSku == '' )
							$combineSku = $setNewSku;	
						else
							$combineSku .= ',' . $setNewSku;	
						
					$j++;	
					}
					
					//package weight + order item weight
					$massWeight = $packageWeight + $massWeight; 
                                        
					//LineNo
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt, $inc );
					
					//Option
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt, 'N' );
					
					//LineIdentifier
					//$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, 'ECGL'.$mergeOrder[0]->MergeUpdate->product_order_id_identify );
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, $mergeOrder[0]->MergeUpdate->product_order_id_identify );
					
					//LineIdentifier
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt, '' );
					
					/*  Consignor */					
					//Consignor
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, 'ESL Limited' );
					
					//ConsignorAddress1
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$cnt, 'Unit 4 Airport Cargo Centre' );
					
					//ConsignorAddress2
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$cnt, 'L\'avenue De La Comune; Jersey' );
					
					//ConsignorPostCode
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$cnt, 'JE3 7BY' );
					
					/* Condignee */
					$paramsConsignee = array(
						'conditions' => array(
							'OpenOrder.num_order_id' => $mergeOrder[0]->MergeUpdate->order_id
						),
						'fields' => array(
							'OpenOrder.num_order_id',
							'OpenOrder.id',
							'OpenOrder.general_info',
							'OpenOrder.shipping_info',
							'OpenOrder.customer_info',
							'OpenOrder.totals_info'							
						)
					);
					
					$getConsigneeDetailFromLinnworksOrder = json_decode(json_encode($this->OpenOrder->find( 'first', $paramsConsignee )),0);					
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->general_info));
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->shipping_info));
					$congineeInfo = unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->customer_info);					
					//pr($congineeInfo);
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->totals_info));
					
					$totalInfo = unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->totals_info);
					//pr($totalInfo); exit;
					//$congineeInfo->Address->FullName;
					
					
					$postcountry =  $congineeInfo->Address->Country;
					$previousDate	=	date('Y-m-d h:i:s', strtotime('-10 days'));
					
						
					$exteraword = '';
					 if($identifier >= 2)
						{
							$getCustomerDetail =	$this->Customer->find('all', 
									array( 
									'conditions' => array( 
														'Customer.country' => $postcountry,
									'and'		=> array('Customer.date >' => '2016-01-01 00:00:00',
														'Customer.date <' => $previousDate
														)),
									 'order' => 'rand()',
									  'limit' => 1,
									)
								);
								
							$customerName		=	$getCustomerDetail[0]['Customer']['name'].' ';
							$customerAddress1	=	$getCustomerDetail[0]['Customer']['address1'].' ';
							$customerAddress2	=	$getCustomerDetail[0]['Customer']['address2'].' ';
						
						}
						else
						{
							$customerName 		= $congineeInfo->Address->FullName;
							$customerAddress1 	= $congineeInfo->Address->Address1 .' '. $congineeInfo->Address->Address2.' '. $congineeInfo->Address->Address3.' '. $congineeInfo->Address->Town.' '. $congineeInfo->Address->Region;
							$customerAddress2 	= $congineeInfo->Address->Address2;
						}
					
					
					//ConsigneeName
					//$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, str_replace(' ',$setSpaces,$congineeInfo->Address->FullName) );
					$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, str_replace(',',';', $customerName) );
					
					//ConsigneeAddress1
					//$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, str_replace(' ',$setSpaces,$congineeInfo->Address->Address1) );
					$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, str_replace(',',';', $customerAddress1) );
					
					//ConsigneeAddress2
					//$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, str_replace(' ',$setSpaces,$congineeInfo->Address->Address2) );
					$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, '' );
					
					//ConsigneeGSTNumber
					$objPHPExcel->getActiveSheet()->setCellValue('L'.$cnt, '' );
					
					$postcode = $congineeInfo->Address->PostCode;
					
					if(is_numeric($postcode) && $postcode[0] < 1){
						$postcode = "~".$postcode ;
					}
						 
						 //ConsigneePostCode
					$objPHPExcel->getActiveSheet()->setCellValue('M'.$cnt, str_replace(',',';', $postcode) );
					
					$setSpaces = '';
					
					//ConsigneeCountry
					$country = '';					
					foreach( $isoCode as $index => $value )
					{
						if( $index == $congineeInfo->Address->Country )
						{
							$country = $value;
						}
					}					
					$objPHPExcel->getActiveSheet()->setCellValue('N'.$cnt, $country );
					
					//NoOfUnits
					$objPHPExcel->getActiveSheet()->setCellValue('O'.$cnt, $totalUnits );
					
					//MassWeight
					$objPHPExcel->getActiveSheet()->setCellValue('P'.$cnt, $massWeight );
					
					//Description					
					$objPHPExcel->getActiveSheet()->setCellValue('Q'.$cnt, str_replace(',',';', $combineCategory ) );
					//$objPHPExcel->getActiveSheet()->setCellValue('Q'.$cnt, 'Printer cartridge & Electronics' );
					
					$currencyMatter = $totalInfo->Currency;
					
					$globalCurrencyConversion = 1;
					
					if( $currencyMatter == "EUR" )
					{
						$globalCurrencyConversion = 1;
					}
					else
					{
						$globalCurrencyConversion = 1.38;
					}
					
					//Value
					$totalValue = 0;
					$setPrice = $mergeOrder[0]->MergeUpdate->price;
					/*if( ( $setPrice * $globalCurrencyConversion) > 21.99 )
					{
						$totalValue = sprintf( '%.2f' , $this->getrand() );
					}
					else
					{
						$totalValue = sprintf( '%.2f' , $setPrice * $globalCurrencyConversion );
					}*/
					
					if( ( $setPrice * $globalCurrencyConversion) > 21.99 )
					{
						$totalValue = number_format($this->getrand(), 2, '.', ''); //sprintf( '%.2f' , $this->getrand() );
					}
					else
					{
						$totalValue = number_format(( $setPrice * $globalCurrencyConversion ), 2, '.', ''); //sprintf( '%.2f' , $setPrice * $globalCurrencyConversion );
					}
					
					$objPHPExcel->getActiveSheet()->setCellValue('R'.$cnt, $totalValue );
					
					//Currency
					$objPHPExcel->getActiveSheet()->setCellValue('S'.$cnt, 'EUR' );
					
					//ForwardingAgent
					$objPHPExcel->getActiveSheet()->setCellValue('T'.$cnt, 'ESL Limited' );
					
					//ForwardingAgentAddress1
					$objPHPExcel->getActiveSheet()->setCellValue('U'.$cnt, 'Unit 4 Airport Cargo Centre' );
					
					//ForwardingAgentAddress2
					$objPHPExcel->getActiveSheet()->setCellValue('V'.$cnt, "L'avenue De La Comune; JE3 7BY" );
					
					//ForwardingAgentCountry
					$objPHPExcel->getActiveSheet()->setCellValue('W'.$cnt, "JE" );
					
					//CommunityStatus
					$objPHPExcel->getActiveSheet()->setCellValue('X'.$cnt, "T2" );
					
					$combineSku = '';
					$totalUnits = 0;
					$massWeight = 0;
					$combineCategory = '';
					
				$inc++;	
				$cnt++;	
				$k++;	
				}
				
				$serviceData = json_decode(json_encode($this->ServiceCounter->find( 'first', array( 'conditions' => array( 'ServiceCounter.id' => $manifestValue->ServiceCounter->id ) ) )),0);							
				$originalCounter = $serviceData->ServiceCounter->original_counter - $serviceData->ServiceCounter->counter;
				
				//Update Now at specific id
				$this->request->data['ServiceCounter']['ServiceCounter']['id'] = $manifestValue->ServiceCounter->id;
				$this->request->data['ServiceCounter']['ServiceCounter']['original_counter'] = $originalCounter;
				$this->request->data['ServiceCounter']['ServiceCounter']['counter'] = 0;
				$this->request->data['ServiceCounter']['ServiceCounter']['order_ids'] = '';
				//$this->request->data['ServiceCounter']['ServiceCounter']['locking_stage'] = 1;
				$this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );
				
			$e++;	
			}
			
			//Set First Row  for Amazon FBa Sheet
			$objPHPExcel->setActiveSheetIndex(0);                                                                              
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:D1')->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));                   
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:D1')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet(0)->getStyle("A1:D1")->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet(0)
			->getStyle('A1:D1')
			->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'EBE5DB')
                                )
                            )
			);
			
			/*date_default_timezone_set('Europe/Jersey');
			$time_in_12_hour_format  = date("g:i a", strtotime(date("H:i",$_SERVER['REQUEST_TIME'])));
			
			$folderName = 'Service Manifest -'. date("d.m.Y");
			$service = str_replace(' ', '', str_replace(':','_',$serviceProvider.'-'. date("d.m.Y") .'_'. $time_in_12_hour_format));*/
							  
			// create new folder with date if exists will remain same or else create new one                                                                                                                                                                                                
			$dir = new Folder(WWW_ROOT .'img/cut_off/'.$folderName, true, 0755);
			
			$uploadUrl = WWW_ROOT .'img/cut_off/'. $folderName . '/' .$service.'.csv';                                          
			$uploadUrI = Router::url('/', true) . 'img/cut_off/'. $folderName . '/' .$service.'.csv';                                          
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');  
			$objWriter->save($uploadUrl);
			
                        //flush all blank rows
			$this->delServiceRows();

			//Changes for Royalmail
			App::Import('Controller', 'RoyalMail'); 
			$royal = new RoyalMailController;
			$royal->createManifest();
			
			App::Import('Controller', 'Jerseypost'); 
			$jp = new JerseypostController;
			$jp->createManifest();
			
			//Update Service Counter
			//$this->call_service_counter();			
			echo $uploadUrI; exit;
		}
		else
		{
			echo "blank"; exit;
		}
	}	 
  	 

                /*
			Params, delete those rows have blank or zero counter which will have flush from tables
		*/
		public function delServiceRows()
		{

			$this->layout = '';
			$this->autoRender = false;	

			$this->loadModel('ServiceCounter');		
			$this->ServiceCounter->deleteAll( array( 'ServiceCounter.counter' => 0, 'ServiceCounter.original_counter' => 0 ) );
			

		}

   public function createCutOffList_4_2_2016()
	{
		$this->layout = '';
		$this->autoRender = false;          
		
		// Get All manifest related services
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		
		/* start european country iso code*/
		$isoCode = Configure::read('customIsoCodes');
		  /* end european country iso code*/
		
		//Global Variable
		$glbalSortingCounter = 0;
		
		$serviceProvider = $this->request->data['serviceProvider'];
		
		// Get Data
		$manifest = json_decode(json_encode($this->ServiceCounter->find( 'all' , 
			array( 
				'conditions' => array( 
						//'ServiceCounter.manifest' => 1 , 
						'ServiceCounter.service_provider' => $serviceProvider , 
						'ServiceCounter.order_ids !=' => '' , 
						'ServiceCounter.counter >' => 0 , 
						'ServiceCounter.original_counter >' => 0, 
						//'ServiceCounter.locking_stage' => 0 
					)                                                                                                           
				)                                                                              
			)),0); 
		
		if( count($manifest) > 0 )
		{
			
			//We got number of sorted rows which had been done through operator and creating manifest 1 for same provider
			$inc = 1;$cnt = 2;$e = 0;foreach( $manifest as $manifestIndex => $manifestValue )
			{
				
				if( $e == 0 )
				{
					// Clean Stream (Input)
					//ob_clean();                                                         
					App::import('Vendor', 'PHPExcel/IOFactory');
					App::import('Vendor', 'PHPExcel');                          
					
					//Set and create Active Sheet for single workbook with singlle sheet
					$objPHPExcel = new PHPExcel();       
					$objPHPExcel->createSheet();
					
					//Column Create                              
					$objPHPExcel->setActiveSheetIndex(0);
					
					$objPHPExcel->getActiveSheet()->setCellValue('A1', 'LineNo');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Option');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('C1', 'LineIdentifier');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('D1', 'GroupageManifestNo');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Consignor');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('F1', 'ConsignorAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('G1', 'ConsignorAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('H1', 'ConsignorPostCode');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('I1', 'ConsigneeName');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('J1', 'ConsigneeAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('K1', 'ConsigneeAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('L1', 'ConsigneeGSTNumber');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('M1', 'ConsigneePostCode');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('N1', 'ConsigneeCountry');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('O1', 'NoOfUnits');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('P1', 'GrossMass');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Description');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('R1', 'Value');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('S1', 'ValueCurr');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('T1', 'ForwardingAgent');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('U1', 'ForwardingAgentAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('V1', 'ForwardingAgentAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('W1', 'ForwardingAgentCountry');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('X1', 'CommunityStatus');                                                                  
						
				}
				
				$orderIds = explode( ',' , $manifestValue->ServiceCounter->order_ids);
				
				//pr($orderIds);
				
				$this->loadModel( 'MergeUpdate' );
				$this->loadModel( 'Product' );
				$this->loadModel( 'ProductDesc' );
				$this->loadModel( 'OpenOrder' );
				$this->loadModel( 'Customer' );
				
				$combineSku = '';				
				$k = 0;while( $k <= count($orderIds)-1 )
				{
					
					$orderIdSpecified = $orderIds[$k];
					$this->updateManifestDate($orderIdSpecified);
					$params = array(
						'conditions' => array(
							'MergeUpdate.id' => $orderIdSpecified
						),
						'fields' => array(
							'MergeUpdate.id',
							'MergeUpdate.order_id',
							'MergeUpdate.product_order_id_identify',
							'MergeUpdate.quantity',
							'MergeUpdate.sku',
							'MergeUpdate.price',
							'MergeUpdate.packet_weight',
                            'MergeUpdate.envelope_weight'
						)
					);
					
					$mergeOrder = json_decode(json_encode($this->MergeUpdate->find(
						'all', $params
					)),0);
					
					//pr($mergeOrder);
										
					$getSku = explode( ',' , $mergeOrder[0]->MergeUpdate->sku);
					
                                        $packageWeight = $mergeOrder[0]->MergeUpdate->envelope_weight;
					$totalPriceValue = 0;
					$massWeight = 0;
					$totalUnits = 0;
					$combineTitle = '';
					$combineCategory = '';
                                        $calculateWeight = 0;
					$j = 0;while( $j <= count($getSku)-1 )
					{
						$newSku = explode( 'XS-' , $getSku[$j] );
						
						//Get Title of product
						$setNewSku = 'S-'.$newSku[1];
						
						$this->loadModel( 'Product' );
						$this->loadModel( 'Category' );					
					
						$this->Product->bindModel(
							array(
							 'hasOne' => array(
							  'Category' => array(
							   'foreignKey' => false,
							   'conditions' => array('Category.id = Product.category_id'),
							   'fields' => array('Category.id,Category.category_name')
							  )
							 )
							)
						   );
					
						$productSku = $this->Product->find(
							'first' ,
							array(
								'conditions' => array(
									'Product.product_sku' => $setNewSku
								)
							)
						);
						
						if( $combineTitle == '' )
						{
							$combineTitle = $newSku[0] .'X' .substr($productSku['Product']['product_name'],0,25);	
							$totalUnits = $totalUnits + $newSku[0];     
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;		
							$combineCategory = $productSku['Category']['category_name'];				
						}
						else
						{
							$combineTitle .= ',' .  $newSku[0] . 'X' .substr($productSku['Product']['product_name'],0,25);	
							$totalUnits = $totalUnits + $newSku[0];
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;                                                        
							$combineCategory .= ',' . $productSku['Category']['category_name'];				
						}
						 
						if( $combineSku == '' )
							$combineSku = $setNewSku;	
						else
							$combineSku .= ',' . $setNewSku;	
						
					$j++;	
					}
					
                                        //package weight + order item weight
                                        $massWeight = $packageWeight + $massWeight; 
                                        
					//LineNo
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt, $inc );
					
					//Option
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt, 'N' );
					
					//LineIdentifier
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, 'ECGL'.$mergeOrder[0]->MergeUpdate->product_order_id_identify );
					
					//LineIdentifier
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt, '' );
					
					/*  Consignor */					
					//Consignor
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, 'ESL Limited' );
					
					//ConsignorAddress1
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$cnt, 'Unit 4 Airport Cargo Centre' );
					
					//ConsignorAddress2
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$cnt, 'L\'avenue De La Comune, Jersey' );
					
					//ConsignorPostCode
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$cnt, 'JE3 7BY' );
					
					/* Condignee */
					$paramsConsignee = array(
						'conditions' => array(
							'OpenOrder.num_order_id' => $mergeOrder[0]->MergeUpdate->order_id
						),
						'fields' => array(
							'OpenOrder.num_order_id',
							'OpenOrder.id',
							'OpenOrder.general_info',
							'OpenOrder.shipping_info',
							'OpenOrder.customer_info',
							'OpenOrder.totals_info'							
						)
					);
					
					$getConsigneeDetailFromLinnworksOrder = json_decode(json_encode($this->OpenOrder->find( 'first', $paramsConsignee )),0);					
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->general_info));
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->shipping_info));
					$congineeInfo = unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->customer_info);					
					//pr($congineeInfo);
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->totals_info));
					
					//$congineeInfo->Address->FullName;
					
					$postcountry =  $congineeInfo->Address->Country;
					$previousDate	=	date('Y-m-d h:i:s', strtotime('-10 days'));
					
						
					$exteraword = '';
					 if($identifier >= 2)
						{
							$getCustomerDetail =	$this->Customer->find('all', 
									array( 
									'conditions' => array( 
														'Customer.country' => $postcountry,
									'and'		=> array('Customer.date >' => '2016-01-01 00:00:00',
														'Customer.date <' => $previousDate
														)),
									 'order' => 'rand()',
									  'limit' => 1,
									)
								);
								
							$customerName		=	$getCustomerDetail[0]['Customer']['name'].' ';
							$customerAddress1	=	$getCustomerDetail[0]['Customer']['address1'].' ';
							$customerAddress2	=	$getCustomerDetail[0]['Customer']['address2'].' ';
						
						}
						else
						{
							$customerName 		= $congineeInfo->Address->FullName;
							$customerAddress1 	= $congineeInfo->Address->Address1;
							$customerAddress2 	= $congineeInfo->Address->Address2;
						}
					
					
					
					//ConsigneeName
					//$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, $congineeInfo->Address->FullName );
					$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, $customerName );
					//ConsigneeAddress1
					//$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, $congineeInfo->Address->Address1 );
					$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, $customerAddress1 );
					
					//ConsigneeAddress2
					//$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, $congineeInfo->Address->Address2 );
					$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, '' );
					
					//ConsigneeGSTNumber
					$objPHPExcel->getActiveSheet()->setCellValue('L'.$cnt, '' );
					
					//ConsigneePostCode
					$objPHPExcel->getActiveSheet()->setCellValue('M'.$cnt, $congineeInfo->Address->PostCode );
					
					//ConsigneeCountry
					$country = '';					
					foreach( $isoCode as $index => $value )
					{
						if( $index == $congineeInfo->Address->Country )
						{
							$country = $value;
						}
					}					
					$objPHPExcel->getActiveSheet()->setCellValue('N'.$cnt, $country );
					
					//NoOfUnits
					$objPHPExcel->getActiveSheet()->setCellValue('O'.$cnt, $totalUnits );
					
					//MassWeight
					$objPHPExcel->getActiveSheet()->setCellValue('P'.$cnt, $massWeight );
					
					//Description					
					$objPHPExcel->getActiveSheet()->setCellValue('Q'.$cnt, $combineCategory );
					
					//Value
					$totalValue = 0;
					$setPrice = $mergeOrder[0]->MergeUpdate->price;
					if( ( $setPrice * 1.38) > 21.99 )
					{
						$totalValue = '21.99';
					}
					else
					{
						$totalValue = ($setPrice * 1.38);
					}
					$objPHPExcel->getActiveSheet()->setCellValue('R'.$cnt, $totalValue );
					
					//Currency
					$objPHPExcel->getActiveSheet()->setCellValue('S'.$cnt, 'EUR' );
					
					//ForwardingAgent
					$objPHPExcel->getActiveSheet()->setCellValue('T'.$cnt, 'ESL Limited' );
					
					//ForwardingAgentAddress1
					$objPHPExcel->getActiveSheet()->setCellValue('U'.$cnt, 'Unit 4 Airport Cargo Centre' );
					
					//ForwardingAgentAddress2
					$objPHPExcel->getActiveSheet()->setCellValue('V'.$cnt, "L'avenue De La Comune, JE3 7BY" );
					
					//ForwardingAgentCountry
					$objPHPExcel->getActiveSheet()->setCellValue('W'.$cnt, "JE" );
					
					//CommunityStatus
					$objPHPExcel->getActiveSheet()->setCellValue('X'.$cnt, "T2" );
					
					$combineSku = '';
					$totalUnits = 0;
					$massWeight = 0;
					$combineCategory = '';
					
				$inc++;	
				$cnt++;	
				$k++;	
				}
				
				$serviceData = json_decode(json_encode($this->ServiceCounter->find( 'first', array( 'conditions' => array( 'ServiceCounter.id' => $manifestValue->ServiceCounter->id ) ) )),0);							
				$originalCounter = $serviceData->ServiceCounter->original_counter - $serviceData->ServiceCounter->counter;
				
				//Update Now at specific id
				$this->request->data['ServiceCounter']['ServiceCounter']['id'] = $manifestValue->ServiceCounter->id;
				$this->request->data['ServiceCounter']['ServiceCounter']['original_counter'] = $originalCounter;
				$this->request->data['ServiceCounter']['ServiceCounter']['counter'] = 0;
				$this->request->data['ServiceCounter']['ServiceCounter']['order_ids'] = '';
				//$this->request->data['ServiceCounter']['ServiceCounter']['locking_stage'] = 1;
				$this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );
			$e++;	
			}
			
			//Set First Row  for Amazon FBa Sheet
			$objPHPExcel->setActiveSheetIndex(0);                                                                              
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:D1')->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));                   
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:D1')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet(0)->getStyle("A1:D1")->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet(0)
			->getStyle('A1:D1')
			->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'EBE5DB')
                                )
                            )
			);
			
			date_default_timezone_set('Europe/Jersey');
			//echo '[ '.date("g:i A", strtotime(date("H:i",$_SERVER['REQUEST_TIME']))) .' ]';
			//CutOff time store
			$time_in_12_hour_format  = date("g:i a", strtotime(date("H:i",$_SERVER['REQUEST_TIME'])));
			
			$folderName = 'Service Manifest -'. date("d.m.Y");
			$service = str_replace(' ', '', str_replace(':','_',$serviceProvider.'-'. date("d.m.Y") .'_'. $time_in_12_hour_format));
							  
			// create new folder with date if exists will remain same or else create new one                                                                                                                                                                                                
			$dir = new Folder(WWW_ROOT .'img/cut_off/'.$folderName, true, 0755);
			
			$uploadUrl = WWW_ROOT .'img/cut_off/'. $folderName . '/' .$service.'.csv';                                          
			$uploadUrI = Router::url('/', true) . 'img/cut_off/'. $folderName . '/' .$service.'.csv';                                          
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');  
			$objWriter->save($uploadUrl);
			
			//Update Service Counter
			//$this->call_service_counter();
			
			echo $uploadUrI; exit;
		}
		else
		{
			echo "blank"; exit;
		}
	}
	
	
	/*
	 * 
	 * Params, to fill the sorted item information int eShipper where we will go through Form Filler or Auto Fill option 
	 * 
	 */ 
	
	public function setInfoEshipper()
	{
		$this->layout = '';
		$this->autoRender = false;          
		
		// Get All manifest related services
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		
		$serviceProvider = 'Belgium Post'; //$this->request->data['serviceProvider'];
		
		//Information will get from those models where need to update itself and filler into eShipper
		$params = array(
			'conditions' => array(
				'ServiceCounter.service_provider' => $serviceProvider,
				'ServiceCounter.counter >' => 0,
				'ServiceCounter.original_counter >' => 0
			)			
		);
		$eShipperData = $this->ServiceCounter->find( 'all', $params );
//pr($eShipperData); exit;
		echo (json_encode(array('status' => '1', 'data' => $eShipperData)));
		exit;
	}
	public function setInfoEshipperDHL()
	{
		$this->layout = '';
		$this->autoRender = false;          
	
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		$scanCount = 0;$totalOrderWeight = 0;
		$serviceProvider = 'DHL';
		$params = array('conditions' => array('ServiceCounter.service_provider' => $serviceProvider,'ServiceCounter.counter >' => 0,'ServiceCounter.original_counter !=' => ''));
		$eShipperDatas = $this->ServiceCounter->find( 'all', $params );
		$dhl_orders = array();	
		foreach( $eShipperDatas  as $eShipperData)
		{	
			$scanCount			+=	$eShipperData['ServiceCounter']['counter'];
			$mergeupdateIds 	= 	explode(',', $eShipperData['ServiceCounter']['order_ids']);
		
			$productWeight 		= 0; 
			$envelopWeight 		= 0;
			$totalOrderWeight 	= 0;
			foreach( $mergeupdateIds as $mergeupdateId )
			{
				$params = array('conditions' => array('MergeUpdate.id' => $mergeupdateId),
								'fields' => array('packet_weight','envelope_weight','MergeUpdate.delevery_country','MergeUpdate.provider_ref_code','MergeUpdate.product_order_id_identify'));
				$orderDetail	= 	$this->MergeUpdate->find( 'first' , $params );
				$serviceCode 	= 	$orderDetail['MergeUpdate']['provider_ref_code'];
				$productWeight	=	$productWeight + $orderDetail['MergeUpdate']['packet_weight'];
				$envelopWeight	=	$envelopWeight + $orderDetail['MergeUpdate']['envelope_weight'];
				$dhl_orders[]   =	$orderDetail['MergeUpdate']['product_order_id_identify'];
			}
			$totalOrderWeight	+=	$productWeight + $envelopWeight;
			 
		}
		
		if(count($dhl_orders) > 0 ){
			App::import( 'Controller' , 'Manifests' );
			$Manifests = new ManifestsController();
			$Manifests->putDhlFiles($dhl_orders);
		}
		
		$msg['status'] = 1;
		$msg['totla_weight'] =  $totalOrderWeight;
		$msg['count'] = $scanCount;
		echo  json_encode($msg);
		exit;
	
		 
	}
	public function setInfoEshipperPostnl()
	{
		$this->layout = '';
		$this->autoRender = false;          
		
		// Get All manifest related services
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		
		$serviceProvider = 'Belgium Post'; //$this->request->data['serviceProvider'];
		//Information will get from those models where need to update itself and filler into eShipper
		$params = array(
			'conditions' => array(
				'ServiceCounter.service_provider' => $serviceProvider,
				'ServiceCounter.counter >' => 0,
				'ServiceCounter.original_counter !=' => ''
			)			
		);
		$eShipperData = $this->ServiceCounter->find( 'all', $params );
		echo (json_encode(array('status' => '1', 'data' => $eShipperData)));
		exit;
	}
	
	public function setInfoEdoc()
	{
		$this->layout = '';
		$this->autoRender = false;          
		// Get All manifest related services
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		$serviceProvider = $this->request->data['serviceProvider'];
		date_default_timezone_set('Europe/Jersey');
		$time_in_12_hour_format  = date("g:i a", strtotime(date("H:i",$_SERVER['REQUEST_TIME'])));
		
		$folderName = 'Service Manifest -'. date("d.m.Y");
		$service = str_replace(' ', '', str_replace(':','_',$serviceProvider.'-'. date("d.m.Y") .'_'. $time_in_12_hour_format));
		//Information will get from those models where need to update itself and filler into eShipper
		/*$params = array(
			'conditions' => array(
				'ServiceCounter.service_provider' => $serviceProvider,
				'ServiceCounter.counter >' => 0,
				'ServiceCounter.original_counter !=' => ''
			),
			'fields' => array(				
				'ServiceCounter.counter',
				'ServiceCounter.bags',
				'ServiceCounter.destination',
				'ServiceCounter.service_name',
				'ServiceCounter.service_provider',
				'ServiceCounter.service_code',
				'ServiceCounter.original_counter',
				'ServiceCounter.order_ids',
				'ServiceCounter.manifest'
			)*/	
			
			$params = array(
			'conditions' => array(
				'ServiceCounter.service_provider' => $serviceProvider,
				'ServiceCounter.counter >' => 0,
				'ServiceCounter.original_counter !=' => ''
			),
			'fields' => array(				
				'ServiceCounter.counter',
				'ServiceCounter.bags',
				'ServiceCounter.service_code',
				'ServiceCounter.order_ids'
			)
		);
		
		$eShipperData = $this->ServiceCounter->find( 'all', $params );	
		foreach( $eShipperData as $value )
		{
			 $ids	=	explode(',' , $value['ServiceCounter']['order_ids']);
			 foreach($ids as $id)
			 {
			 	$this->setManifestRecordJerseyPost( $id,  $service );
			 }
		}		
		echo (json_encode(array('status' => '1', 'data' => $eShipperData)));
		exit;
		
	}
	
	 public function setManifestRecordJerseyPost( $id = null, $manifestName )
			 {
					$this->loadModel( 'OpenOrder' );
					$this->loadModel( 'MergeUpdate' );
					$this->loadModel( 'ManifestEntrie' ); 
					$this->loadModel( 'ClientMagazine' );
					$getRecord	=	$this->MergeUpdate->find( 'first', array( 'conditions' => array( 'MergeUpdate.id' => $id ) ) );
					
					$checkRecord		=	$this->ManifestEntrie->find('all', array('conditions' => array( 'ManifestEntrie.split_order_id' => $getRecord['MergeUpdate']['product_order_id_identify'] ) ) );
					if(count($checkRecord) == 0)
					{
						$getSku = explode( ',' , $getRecord['MergeUpdate']['sku']);
						$j = 0;while( $j <= count($getSku)-1 )
						{
							$newSku = explode( 'XS-' , $getSku[$j] );
							$setNewSku = 'S-'.$newSku[1];
							$this->loadModel( 'Product' );
							$productSku = $this->Product->find(
								'first' ,
								array(
									'conditions' => array(
										'Product.product_sku' => $setNewSku
									)
								)
							);
							
							if( $combineTitle == '' )
							{
								$totalUnits = $totalUnits + $newSku[0];     
															$calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
								$massWeight = $massWeight + $calculateWeight;		
							}
							else
							{
								$totalUnits = $totalUnits + $newSku[0];
															$calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
								$massWeight = $massWeight + $calculateWeight;                                                        
							}
						$j++;	
						}
						
									
							$paramsConsignee = array(
						'conditions' => array(
							'OpenOrder.num_order_id' => $getRecord['MergeUpdate']['order_id']
						),
						'fields' => array(
							'OpenOrder.num_order_id',
							'OpenOrder.id',
							'OpenOrder.general_info',
							'OpenOrder.shipping_info',
							'OpenOrder.customer_info',
							'OpenOrder.totals_info'							
						)
					);
						
						$getConsigneeDetailFromLinnworksOrder = json_decode(json_encode($this->OpenOrder->find( 'first', $paramsConsignee )),0);					
						$customerDetail	=	unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->customer_info);
						$generalDetail	=	unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->general_info);
						
						$subsource		=	$generalDetail->SubSource;
						
						if( $mergeOrder['MergeUpdate']['magazine_status'] == '1' )
						{
							$getMagazineStatus	=	$this->ClientMagazine->find('first', array( 
																			'conditions' => array( 'enabled' => '1' ),
																			'fields' => array( 'SUM( ClientMagazine.mag_weight ) as Weight' )
																			)
																		);
																		
							$magazWeight	=	$getMagazineStatus[0]['Weight'];
						}
						else
						{
							$magazWeight = 0;
						}
						
						$getConsigneeDetailFromLinnworksOrder = json_decode(json_encode($this->OpenOrder->find( 'first', $paramsConsignee )),0);					
					
						$customerDetail	=	unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->customer_info);
						$generalInfo	=	unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->general_info);
						$subsource		=	$generalInfo->SubSource;
						$packageWeight 	= 	$getRecord['MergeUpdate']['envelope_weight'];
						$massWeight 	= 	$packageWeight + $massWeight; 
						
						$data['split_order_id']		=	$getRecord['MergeUpdate']['product_order_id_identify'];
						$data['reference_num']		=	$generalInfo->ReferenceNum;
						$data['sub_source']			=	$subsource;
						$data['quantity']			=	$getRecord['MergeUpdate']['quantity'];
						$data['recipent_name']		=	$customerDetail->Address->FullName;
						
						$data['sku']				=	$getRecord['MergeUpdate']['sku'];
						$data['service_provider']	=	$getRecord['MergeUpdate']['service_provider'];
						$data['service_name']		=	$getRecord['MergeUpdate']['service_name'];
						$data['provider_ref_code']	=	$getRecord['MergeUpdate']['provider_ref_code'];
						$data['packaging_type']		=	$getRecord['MergeUpdate']['packaging_type'];
						$data['weight']				=	$massWeight;
						$data['envelope_cost']		=	$getRecord['MergeUpdate']['envelope_cost'];
						$data['delevery_country']	=	$getRecord['MergeUpdate']['delevery_country'];
						$data['manifest_name']		=	$manifestName;
						$data['manifest_date']		=	date("Y-m-d h:i:s");
						
						$this->ManifestEntrie->saveAll( $data );
						
						/*$packageWeight = $getRecord['MergeUpdate']['envelope_weight'];
						$massWeight = $packageWeight + $massWeight + $magazWeight; 
						$packageWeight = $mergeOrder['MergeUpdate']['envelope_weight'];				
						
						$data['split_order_id']		=	$getRecord['MergeUpdate']['product_order_id_identify'];
						$data['reference_num']		=	$getConsigneeDetailFromLinnworksOrder->OpenOrder->ReferenceNum;
						$data['sub_source']			=	$subsource;
						$data['quantity']			=	$getRecord['MergeUpdate']['quantity'];
						$data['recipent_name']		=	$customerDetail->Address->RecipentName;
						
						$data['sku']				=	$getRecord['MergeUpdate']['sku'];
						$data['service_provider']	=	$getRecord['MergeUpdate']['service_provider'];
						$data['service_name']		=	$getRecord['MergeUpdate']['service_name'];
						$data['provider_ref_code']	=	$getRecord['MergeUpdate']['provider_ref_code'];
						$data['packaging_type']		=	$getRecord['MergeUpdate']['packaging_type'];
						$data['weight']				=	$massWeight;
						$data['envelope_cost']		=	$getRecord['MergeUpdate']['envelope_cost'];
						$data['delevery_country']	=	$getRecord['MergeUpdate']['delevery_country'];
						$data['manifest_name']		=	$manifestName;
						$data['manifest_date']		=	date("Y-m-d h:i:s");
						$this->ManifestEntrie->saveAll( $data );*/
					}
					
					
			 }
	
	public function setInfoSpainEdoc()
	{
		$this->layout = '';
		$this->autoRender = false;          
		// Get All manifest related services
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		
		$serviceProvider = $this->request->data['serviceProvider'];

		//Information will get from those models where need to update itself and filler into eShipper
		/*$params = array(
			'conditions' => array(
				'ServiceCounter.service_provider' => $serviceProvider,
				'ServiceCounter.counter >' => 0,
				'ServiceCounter.original_counter !=' => ''
			),
			'fields' => array(				
				'ServiceCounter.counter',
				'ServiceCounter.bags',
				'ServiceCounter.destination',
				'ServiceCounter.service_name',
				'ServiceCounter.service_provider',
				'ServiceCounter.service_code',
				'ServiceCounter.original_counter',
				'ServiceCounter.order_ids',
				'ServiceCounter.manifest'
			)*/	
			
			$params = array(
			'conditions' => array(
				'ServiceCounter.service_provider' => $serviceProvider,
				'ServiceCounter.counter >' => 0,
				'ServiceCounter.original_counter !=' => ''
			),
			'fields' => array(				
				'ServiceCounter.counter',
				'ServiceCounter.bags',
				'ServiceCounter.service_code',
				'ServiceCounter.order_ids'
			)
		);
		
		$eShipperData = $this->ServiceCounter->find( 'all', $params );	
		echo (json_encode(array('status' => '1', 'data' => $eShipperData)));
		exit;
		
	}
	
	/*
	 * 
	 * Params, Get exact weight with packaging weight
	 * 
	 */ 
	public function getExactWeight()
	{
		
		$this->loadModel( 'MergeUpdate' );
		
		//$orderIds = $this->request->data['orderId'];
         //pr( $this->request->data );       
                $orderIds = $this->request->data['orderId'];
		 
		$splitOrderId = explode( ',' , $orderIds );
                
                $params = array(                    
                    'conditions' => array(
                        'MergeUpdate.id' => $splitOrderId
                    ),
                    'fields' => array(                                                
                       'SUM( MergeUpdate.packet_weight ) as Weight',
                        'SUM( MergeUpdate.envelope_weight ) as EnvelopeWeight',
                    )
                );
                
                $sumDataById = $this->MergeUpdate->find( 'first' , $params );
               
				$weight = $sumDataById['0']['Weight']; 
                $envelopeWeight = $sumDataById['0']['EnvelopeWeight'];
                
                echo $weight .'=='. $envelopeWeight;
                exit;
	}
	
	public function getExactWeightPostNL()
	{
		
		$this->loadModel( 'MergeUpdate' );
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'PostalProvider' );
		$this->loadModel( 'Location' );
		$countryArray = Configure::read('customCountry');
		
	    //$orderIds = $this->request->data['orderId'];
	    $mergeUpdaeIDs	=	$this->ServiceCounter->find( 'all', array( 'conditions' => array( 'ServiceCounter.service_provider' => 'PostNL'), 'fields' => 'ServiceCounter.order_ids, ServiceCounter.destination, ServiceCounter.counter' ) );
	    $getPostalNames = $this->PostalProvider->find('all' , array( 'conditions' => array( 'PostalProvider.status' => 1,'PostalProvider.provider_name' => 'PostNL') ));				
	    foreach( $getPostalNames as $getPostalName )
		{
				$locationIDs		=	explode(',', $getPostalName['PostalProvider']['location_id']);
				foreach( $locationIDs as $locationID )
				{
					$this->Location->unbindModel( array( 'hasMany' => array( 'PostaServiceDesc' ) ) );
					$locationParam 	=	array( 'conditions'=>array('Location.id' => $locationID), 'fields' => array( 'Location.county_name' ) );
					$locations[]	=	$this->Location->find( 'first', $locationParam );
				}
		}
		/* for create rule country array */
		foreach( $locations as $country )
		{
			
			$locationArray[ $country['Location']['county_name'] ] = $country['Location']['county_name'];
		}
		
		
		$i = 0;
		foreach( $mergeUpdaeIDs as $mergeUpdaeID )
				{
					$mergeUpdatearray		=	explode(',', $mergeUpdaeID['ServiceCounter']['order_ids']);
					$counter		=	explode(',', $mergeUpdaeID['ServiceCounter']['counter']);
			foreach( $locations as $locationValue )
					{
						if( $mergeUpdaeID['ServiceCounter']['destination'] == $locationValue['Location']['county_name'] )
						{
							$params = array(                    
									'conditions' => array(
										'MergeUpdate.id' => $mergeUpdatearray,
										'MergeUpdate.delevery_country' => $mergeUpdaeID['ServiceCounter']['destination']
									),
									'fields' => array(                                                
									   'SUM( MergeUpdate.packet_weight ) as Weight',
										'SUM( MergeUpdate.envelope_weight ) as EnvelopeWeight',
										'MergeUpdate.delevery_country',
										'MergeUpdate.provider_ref_code'
									),
								);
						
							$sumDataById[$i] = $this->MergeUpdate->find( 'first' , $params );
							$sumDataById[$i]['counter'] = $counter;
						}
						else
						{
							$params = array(                    
											'conditions' => array(
												'MergeUpdate.id' => $mergeUpdatearray,
												'MergeUpdate.delevery_country' => $mergeUpdaeID['ServiceCounter']['destination']
											),
											'fields' => array(                                                
											   'SUM( MergeUpdate.packet_weight ) as Weight',
												'SUM( MergeUpdate.envelope_weight ) as EnvelopeWeight',
												'MergeUpdate.delevery_country',
												'MergeUpdate.provider_ref_code'
											),
										);
								
							$sumDataById[$i] = $this->MergeUpdate->find( 'first' , $params );
							$sumDataById[$i]['counter'] = $counter;
						}
					}
					$i++;
				}
				$totalWeight = 0;
				
				$outerLocation = $locationArray;
				$locateValue = array_keys($locationArray, "Rest Of EU");
				$locateValue = $locateValue[0];
				unset( $outerLocation[$locateValue] );
				
				$getdata = array();
				
				
				if( count($locationArray) > 0 )
				{
					$j = 0;
					$getCounter = 0;
					foreach( $locationArray as $countryName ) //Uk||DE|FR|REST
					{
						
						$totalWeight = 0;
						$encelopWeight = 0;
						$getCounter = 0;
						$weight =0;
						$gTotal = 0;
						
						foreach( $sumDataById as $value )
						{
							
							if( $countryName == $value['MergeUpdate']['delevery_country'] )
							{
								$getCounter 			= $getCounter + $value['counter'][0];
								$totalWeight 			= $value[0]['Weight'];
								$encelopWeight 			= $value[0]['EnvelopeWeight'];
								$weight 				= $value[0]['Weight'] + $value[0]['EnvelopeWeight'];
								$country 				= $value['MergeUpdate']['delevery_country'];
								$postal_provider_code[]	= $value['MergeUpdate']['provider_ref_code'].'###'.$weight.'###'.$value['counter'][0];
								
							}
							else
							{
								if( stristr( $countryName , "Rest Of EU" ) == true )
								{
									
									if( count( $outerLocation ) > 0 )	
									{
										if( !in_array( $value['MergeUpdate']['delevery_country'] , $outerLocation ) )
										{
											$getCounter 			= $getCounter + $value['counter'][0];
											$totalWeight 			= $value[0]['Weight'];
											$encelopWeight 			= $value[0]['EnvelopeWeight'];
											$weight 				= $value[0]['Weight'] + $value[0]['EnvelopeWeight'];
											$country 				= 'RestOfEU';

											$postal_provider_code[]	=	$value['MergeUpdate']['provider_ref_code'] .'###'.$weight.'###'.$value['counter'][0];
											$weight = '';
										}
										
									}
									else
									{
											$getCounter 			= $getCounter + $value['counter'][0];
											$totalWeight 			= $value[0]['Weight'];
											$encelopWeight 			= $value[0]['EnvelopeWeight'];
											$weight 				= $value[0]['Weight'] + $value[0]['EnvelopeWeight'];
											$country 				= 'RestOfEU';
											$postal_provider_code[]	=	$value['MergeUpdate']['provider_ref_code'].'###'.$weight.'###'.$value['counter'][0];
											$weight = '';
									}
									
								}
									
							}
							
						}
						
						if( isset( $country ) && isset($postal_provider_code) )
						{
							$getdata[$j]['totalWeight'] 	= $totalWeight + $encelopWeight;
							$getdata[$j]['counter'] 		= $getCounter;
							//$getdata[$j]['envelopWeight'] 	= $encelopWeight;
							$getdata[$j]['delcountry'] 		= $country;
							$getdata[$j]['postal_provider_code'] = $postal_provider_code;
							unset( $totalWeight );
							//unset( $encelopWeight );
							unset( $country );
							unset( $getCounter );
							unset( $postal_provider_code );
							$j++;
						}
						
					}
				
				}
				else
				{
					echo "0"; exit;
				}
				//pr($getdata);
				//exit;
				
				echo (json_encode(array('status' => '1', 'data' => $getdata)));
				exit;
	}
	
	
		     
   public function createCutOffList_old()
	{                              
					$this->layout = '';
					$this->autoRender = false;          
					
					// Get All manifest related services
					$this->loadModel( 'ServiceCounter' );
					
					//Global Variable
					$glbalSortingCounter = 0;
					
					// Get Data
					$relatedManifestData = json_decode(json_encode($this->ServiceCounter->find( 'all' , 
						array( 
							'conditions' => array( 
															'ServiceCounter.manifest' => 1 , 
															'ServiceCounter.order_ids !=' => '' , 
															'ServiceCounter.counter >' => 0 , 
															'ServiceCounter.original_counter >' => 0, 
															'ServiceCounter.locking_stage' => 0 
											),
							'fields' => array(
															'count(ServiceCounter.service_code) as ProviderCode',
															'ServiceCounter.service_code',
															'ServiceCounter.destination',
															'ServiceCounter.service_name',
															'ServiceCounter.service_provider'                                                                                                                                                           
											),
							'group'                  => array(
															'ServiceCounter.service_code' 
											)                                                                                                              
							)                                                                              
						)),0); 
					                                     
					/*
					* 
					 * Params, Get manifest rows from service
					* Params, Get related rows of order Id's
					* Params, To call Controller and Helper to managed into component related sheets
					* 
					 */
					$st = 0;foreach( $relatedManifestData as $sheetIndex => $sheetIndexArray ): 
									
																									
						// Get Data
						$relatedManifestData = json_decode(json_encode($this->ServiceCounter->find( 'all' , 
						array( 
							'conditions' => array( 
									'ServiceCounter.manifest' => 1 , 
									'ServiceCounter.order_ids !=' => '' , 
									'ServiceCounter.counter >' => 0 , 
									'ServiceCounter.original_counter >' => 0, 
									'ServiceCounter.locking_stage' => 0,
									'ServiceCounter.service_code' => $sheetIndexArray->ServiceCounter->service_code 
								)                                                                                              
							)                                                                              
						)),0);
		
						//pr($relatedManifestData); exit;
						$innerLoop = 0;foreach( $relatedManifestData as $relatedManifestDataIndex => $relatedManifestDataValue ):
										
							// Get and prepare data for extracting from DB
							$serilizedData = $this->getOrderProductsById( $relatedManifestDataValue->ServiceCounter->order_ids );
							
							// Clean Stream (Input)
							//ob_clean();                                                         
							App::import('Vendor', 'PHPExcel/IOFactory');
							App::import('Vendor', 'PHPExcel');                          
							
							//Set and create Active Sheet for single workbook with singlle sheet
							$objPHPExcel = new PHPExcel();       
							$objPHPExcel->createSheet();
							
							//Column Create                              
							$objPHPExcel->setActiveSheetIndex(0);
							$objPHPExcel->getActiveSheet()->setCellValue('A1', 'OrderItemNumber');                                                                  
							$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Name');
							$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Address');
							$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Postcode');
							$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Country');
							$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Item Count');
							$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Contents');
							$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Total Packet Value');
							$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Weight');
							$objPHPExcel->getActiveSheet()->setCellValue('J1', 'HS');
							$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Deposit');
							$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Invoice Number');
							$objPHPExcel->getActiveSheet()->setCellValue('M1', 'Bag barcode');
																															
							//Dynamic Service Name with country
							//$serviceFileName = $sheetIndexArray->ServiceCounter->service_name. ' ' . $sheetIndexArray->ServiceCounter->service_code .'-('. $sheetIndexArray->ServiceCounter->destination .')-'.$sheetIndexArray->ServiceCounter->service_provider;
							
							$serviceFileName = $sheetIndexArray->ServiceCounter->service_name. ' ' .$sheetIndexArray->ServiceCounter->service_provider;
							$serviceFileName = strtolower(str_replace(')','-',str_replace('(','-',str_replace('<','-',str_replace(' ','-',$serviceFileName)))));
							
							// Manage data accordign to Id's, which receive from DB
							// Manage Inner Sheets and columns ( Means, Every row could be multiple id's)                                                                                        
							$cnt = 2;foreach( $serilizedData as $serilizedDataIndex => $serilizedDataIndexValue ):
								
								//Set Counter
								$glbalSortingCounter++;                                               
								
								//Exctract all information (Unserialized)
								$general_info    = json_decode(json_encode(unserialize($serilizedDataIndexValue->OpenOrder->general_info)),0);                        
								$shipping_info = json_decode(json_encode(unserialize($serilizedDataIndexValue->OpenOrder->shipping_info)),0);                       
								$customer_info                = json_decode(json_encode(unserialize($serilizedDataIndexValue->OpenOrder->customer_info)),0);                    
								$totals_info        = json_decode(json_encode(unserialize($serilizedDataIndexValue->OpenOrder->totals_info)),0);                            
								$items                                  = json_decode(json_encode(unserialize($serilizedDataIndexValue->OpenOrder->items)),0);                       
								
								/*pr($general_info);
								pr($shipping_info);
								pr($customer_info);
								pr($totals_info);
								pr($items);
								exit;*/
								
								// Data Input into sheets
								
								//Order No.
								$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt,$serilizedDataIndexValue->OpenOrder->num_order_id);
								
								//Name
								$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt,$customer_info->Address->FullName);
								
								//Address
								$address = $customer_info->Address->Address1.' '.$customer_info->Address->Address2.' '.$customer_info->Address->Address3;
								$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt,$address);
								
								//Postcode
								$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt,$customer_info->Address->PostCode);
								
								//Country
								$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt,$customer_info->Address->Country);
								
								//Item Count
								$objPHPExcel->getActiveSheet()->setCellValue('F'.$cnt,count($items));
								
								//Contents with each item of order
								$productStr = '';								
								$itemLoop = 0;foreach( $items as $itemsIndex => $itemsValue ):
									
									//Set Up quantity levels with each product
									if( $itemLoop == 0 ):
										$productStr = $itemsValue->Quantity .'x'. $itemsValue->Title;
									else:
										$productStr .= ' , '.$itemsValue->Quantity .'x'. $itemsValue->Title;
									endif;
								$itemLoop++;
								endforeach;
								
								$objPHPExcel->getActiveSheet()->setCellValue('G'.$cnt,$productStr); // Exception case
								
								//Total Packet Value
								$objPHPExcel->getActiveSheet()->setCellValue('H'.$cnt,$totals_info->TotalCharge);
								
								//Total Order Weight
								$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt,$shipping_info->TotalWeight);
																												
								//HS
								$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt,'N/A');
								
								//Deposit
								$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt,'N/A');
								
								//Invoice Number
								$objPHPExcel->getActiveSheet()->setCellValue('L'.$cnt,$serilizedDataIndexValue->OpenOrder->num_order_id);
								
								//Bag Barcode
								$objPHPExcel->getActiveSheet()->setCellValue('M'.$cnt,'N/A');
								
								$serviceData = json_decode(json_encode($this->ServiceCounter->find( 'first', array( 'conditions' => array( 'ServiceCounter.id' => $relatedManifestDataValue->ServiceCounter->id ) ) )),0);
								$originalCounter = $serviceData->ServiceCounter->original_counter - $serviceData->ServiceCounter->counter;
								
								//Update Now at specific id
								$this->request->data['ServiceCounter']['ServiceCounter']['id'] = $relatedManifestDataValue->ServiceCounter->id;
								$this->request->data['ServiceCounter']['ServiceCounter']['original_counter'] = $originalCounter;
								$this->request->data['ServiceCounter']['ServiceCounter']['counter'] = 0;
								$this->request->data['ServiceCounter']['ServiceCounter']['order_ids'] = '';
								$this->request->data['ServiceCounter']['ServiceCounter']['locking_stage'] = 1;
								$this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );
											
							$cnt++;
							endforeach;
										
						$innerLoop++;
						endforeach;
						
						//Set First Row  for Amazon FBa Sheet
						$objPHPExcel->setActiveSheetIndex(0);                                                                              
						$objPHPExcel->getActiveSheet(0)->getStyle('A1:M1')->getAlignment()->applyFromArray(
						array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));                   
						$objPHPExcel->getActiveSheet(0)->getStyle('A1:M1')->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet(0)->getStyle("A1:M1")->getFont()->setBold(true);
						$objPHPExcel->getActiveSheet(0)
						->getStyle('A1:M1')
						->applyFromArray(
							array(
								'fill' => array(
												'type' => PHPExcel_Style_Fill::FILL_SOLID,
												'color' => array('rgb' => 'EBE5DB')
								)
							)
						);
										  
						// create new folder with date if exists will remain same or else create new one                                                                                                                                                                                                
						$dir = new Folder(WWW_ROOT .'img/cut_off/Service-Manifest-'.date("m.d.y"), true, 0755);
						
						$uploadUrl = WWW_ROOT .'img/cut_off/Service-Manifest-'.date("m.d.y").'/'.$serviceFileName.'.csv';                                          
						$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');  
						$objWriter->save($uploadUrl);
												
					endforeach;       
					
					// Get Session User
	   $user_id = $this->Session->read('Auth.User.id');
	   
	   // Call Query
	   $paramOperator = array(
			'conditions' => array(
							'SortingoperatortimeCalculation.user_id' => $user_id
			)
		);
	   
	   // Query Here
	   $this->loadModel('SortingoperatortimeCalculation');
	   $getOperator = $this->SortingoperatortimeCalculation->find( 'first', $paramOperator );
		
	   //Update first out time
	   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['id'] = $getOperator['SortingoperatortimeCalculation']['id'];
	   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['user_id'] = $user_id;
	   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['out_time'] = date('Y-m-d G:i:s');
	   $this->SortingoperatortimeCalculation->saveAll( $this->request->data['SortingoperatortimeCalculation'] ); 
	   
	   // Query Here
	   $getOperator = $this->SortingoperatortimeCalculation->find( 'first', $paramOperator ); 
	   
	   // Calculation
	   $logout = strtotime($getOperator['SortingoperatortimeCalculation']['out_time']);
	   $login  = strtotime($getOperator['SortingoperatortimeCalculation']['in_time']);
	   $diff   = $logout - $login;                                
	   $timeCalculate = round( $diff / 3600 ) ." hour ". round($diff/60)." minutes ".($diff%60)." seconds";
	   
	   // Yes, calculate the time but we need to check one more time is that today or not
	   $dayString = "2015-11-16 05:33:49";
	  $dayStringSub = substr($dayString, 0, 10);

	   $isToday = ( strtotime('now') >= strtotime($dayStringSub . " 00:00") 
													  && strtotime('now') <  strtotime($dayStringSub . " 23:59") );
	   
		// Today
	   if( $isToday == 1 ):
									
			// Update a row again and again
			//Update calculate time
		   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['id'] = $getOperator['SortingoperatortimeCalculation']['id'];
		  $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['user_id'] = $user_id;
		   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['total_time'] = $timeCalculate;
		   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['no_of_orders_sorted'] = $glbalSortingCounter;
		   $this->SortingoperatortimeCalculation->saveAll( $this->request->data['SortingoperatortimeCalculation'] ); 
		else:
		
			// Insert new row, If today is not
			//Insert calculate time                                      
		   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['user_id'] = $user_id;
		   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['total_time'] = $timeCalculate;
		   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['no_of_orders_sorted'] = $glbalSortingCounter;
		   $this->SortingoperatortimeCalculation->saveAll( $this->request->data['SortingoperatortimeCalculation'] ); 
									
	   endif;
	   
			// Get Data
			$serviceCounterData = $this->ServiceCounter->find( 'all' , array( 'order' => 'ServiceCounter.original_counter DESC' ) );
			
			$leftService = array();
			$rightService = array();
			
			// Set left and right corner data for sorting station operator
			$iGetter = 1;$icount = 0;while( $icount <= count( $serviceCounterData )-1 ):
							/*if( ceil(count( $serviceCounterData ) / 2) >= $iGetter ):
											$leftService[] = $serviceCounterData[$icount];
							else:
											$rightService[] = $serviceCounterData[$icount];
							endif;*/
							if( $icount <= 26 )
							{
								$leftService[] = $serviceCounterData[$icount];
							}
							else
							{
								$rightService[] = $serviceCounterData[$icount];
							}
							
			$icount++;
			$iGetter++;
			endwhile;
			
			// Get Service counter details which we have atleast 1 manifest then will active the button
			$getActivationForCutOffList = count( $this->ServiceCounter->find( 'all', array( 'conditions' => array( 'ServiceCounter.manifest' => 1 , 'ServiceCounter.order_ids !=' => '' ) ) ) );
			
			// Set data for view                                                                         
			$this->set( compact( 'leftService' , 'rightService' , 'getActivationForCutOffList' , 'getOperator' ) );                                
			$this->render( 'shortingthtml' );
		$this->redirect( array( 'controller' => 'cronjobs' , 'action' => 'shortingthtml' ) );
													
	}
	
                                /*
                                * 
                                 * Params, Here will set a demo to forcing the open popup
                                * 
                                 */ 
                                public function callManifest_Popup()
                                {
                                                $this->layout = '';
                                                $this->autoRender = false;          
                                                
                                                $uploadUrl          =             WWW_ROOT .'img/cut_off/service.csv';
                                                
                                                header('Content-Encoding: UTF-8');
                                    header('Content-type: text/csv; charset=UTF-8');
                                    header('Content-Disposition: attachment;filename="'.$uploadUrl.'"');
                                    header("Content-Type: application/octet-stream;");
                                    header('Cache-Control: max-age=0');
                                                readfile($uploadUrl); 
                                                exit;
                                }
                                
                   // Open popup for bags addition
                   public function addBag()
                   {
                                   
                                   $this->layout = "";
                                   $this->autoRander = false;
                                   
                                   //Get Data
                                   $exactLocation = $this->data['exactLocationClick'];
                                   
                                   //Load Model
                                   $this->loadModel( 'ServiceCounter' );
                                   $params = array(
                                                                'conditions' => array(
                                                                                'ServiceCounter.id' => $exactLocation 
                                                                ),
                                                                'fields' => array(
                                                                                'ServiceCounter.id',
                                                                                'ServiceCounter.bags'
                                                                )
                                   );                             
                                   $getParamsData = $this->ServiceCounter->find( 'all' , $params );                               
                                   $this->set( compact( 'getParamsData' ) );

                                   $this->render('bags');
                   }
                   
                   // Updation in service accordign to operator for bags
                   public function addBagByOperator()
                   {                              
                                   $this->layout = "";
                                   $this->autoRander = false;
                                   //Get Data
                                   $exactLocationId = $this->data['exactLocationId']; // Location
                                   $addRemoveBag = $this->data['addRemoveBag']; // Location
                                   if( $addRemoveBag == 'Remove' )
                                   {
										$exactLocation = $this->data['exactLocationClick'] - 1; // Value
								   }
								   else
								   {
									   $exactLocation = $this->data['exactLocationClick'] + 1; // Value
								   }
								   
                                   //load model
                                   $this->loadModel( 'ServiceCounter' );
                                   $this->request->data['ServiceCounter']['ServiceCounter']['id'] = $exactLocationId;
                                   $this->request->data['ServiceCounter']['ServiceCounter']['bags'] = $exactLocation;
                                   $this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );
                                   echo $exactLocation .'=='. $exactLocationId; exit;                             
                   }
                   
                   //Calculate the hours accordign operator login and logout time at sorting station
                   public function getCalculateTimeOfOperatorById()
                   {
                                   
                                   $this->layout = "index";
                                                                   
                                   //Load Model
                                   $this->loadModel( 'SortingoperatortimeCalculation' );
                                   
                                   // Get Session User
                                   $user_id = $this->Session->read('Auth.User.id');
                                   
                                   // Call Query
                                   $paramOperator = array(
                                                                'conditions' => array(
                                                                                'SortingoperatortimeCalculation.user_id' => $user_id
                                                                )
                                                );
                                   
                                   // Query Here
                                   $getOperator = $this->SortingoperatortimeCalculation->find( 'first', $paramOperator );
                                    
                                   //Update first out time
                                   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['id'] = $getOperator['SortingoperatortimeCalculation']['id'];
                                   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['user_id'] = $user_id;
                                   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['out_time'] = date('Y-m-d G:i:s');
                                   $this->SortingoperatortimeCalculation->saveAll( $this->request->data['SortingoperatortimeCalculation'] ); 
                                   
                                   // Query Here
                                   $getOperator = $this->SortingoperatortimeCalculation->find( 'first', $paramOperator ); 
                                   
                                   $logout = strtotime($getOperator['SortingoperatortimeCalculation']['out_time']);
                                   $login  = strtotime($getOperator['SortingoperatortimeCalculation']['in_time']);
                                   $diff   = $logout - $login;                                
                                   $timeCalculate = round( $diff / 3600 ) ." hour ". round($diff/60)." minutes ".($diff%60)." seconds";
                                   
                                   //Update calculate time
                                   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['id'] = $getOperator['SortingoperatortimeCalculation']['id'];
                                   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['user_id'] = $user_id;
                                   $this->request->data['SortingoperatortimeCalculation']['SortingoperatortimeCalculation']['total_time'] = $timeCalculate;
                                   $this->SortingoperatortimeCalculation->saveAll( $this->request->data['SortingoperatortimeCalculation'] ); 
                                   
                                   // Query Here
                                   $getOperator = $this->SortingoperatortimeCalculation->find( 'first', $paramOperator );
                                   
                                   //Data Send of Left and right panel also
                                   // Load ServiceCounter
                                                $this->loadModel( 'ServiceCounter' );
                                                
                                                // Get Data
                                                $serviceCounterData = $this->ServiceCounter->find( 'all' , array( 'order' => 'ServiceCounter.original_counter DESC' ) );
                                                
                                                $leftService = array();
                                                $rightService = array();
                                                
                                                // Set left and right corner data for sorting station operator
                                                $iGetter = 1;$icount = 0;while( $icount <= count( $serviceCounterData )-1 ):
                                                                /*if( ceil(count( $serviceCounterData ) / 2) >= $iGetter ):
                                                                                $leftService[] = $serviceCounterData[$icount];
                                                                else:
                                                                                $rightService[] = $serviceCounterData[$icount];
                                                                endif;*/
                                                                
                                                                if( $icount <= 26 )
																{
																	$leftService[] = $serviceCounterData[$icount];
																}
																else
																{
																	$rightService[] = $serviceCounterData[$icount];
																}
                                                $icount++;
                                                $iGetter++;
                                                endwhile;
                                                
                                                // Get Service counter details which we have atleast 1 manifest then will active the button
                                                $getActivationForCutOffList = count( $this->ServiceCounter->find( 'all', array( 'conditions' => array( 'ServiceCounter.manifest' => 1 , 'ServiceCounter.order_ids !=' => '' ) ) ) );
                                                
                                                // Set data for view                                                                         
                                                $this->set( compact( 'leftService' , 'rightService' , 'getActivationForCutOffList' , 'getOperator' ) );                                
                                                $this->render( 'shortingthtml' );
                                    $this->redirect( array( 'controller' => 'cronjobs' , 'action' => 'shortingthtml' ) );
                   }
                   
                   // Product information by  id
                   public function getOrderProductsById( $orderIds = null )
                  {
                                   /*
                                    * 
                                    * Params, Get all information through order Id and get information Id's
                                    * 
                                    */                          
                                   $this->loadModel( 'OpenOrder' );                                                            
                                   $OpenOrder   =             json_decode(json_encode($this->OpenOrder->find('all', 
                                                                                                                                array('conditions' => 
                                                                                                                                                array(
                                                                                                                                                                                'OpenOrder.id' => explode(',',$orderIds),
                                                                                                                                                                                'OpenOrder.sorted_scanned' => 1,
                                                                                                                                                                                'OpenOrder.status' => 1
                                                                                                                                                                ),
                                                                                                                                                'fields' => array(
                                                                                                                                                                                'OpenOrder.general_info',
                                                                                                                                                                                'OpenOrder.shipping_info',
                                                                                                                                                                                'OpenOrder.customer_info',
                                                                                                                                                                                'OpenOrder.totals_info',
                                                                                                                                                                                'OpenOrder.items',
                                                                                                                                                                                'OpenOrder.num_order_id'
                                                                                                                                                                )              
                                                                                                                                                )
                                                                                                                                )),0);                                                                                                                                      
                   return $OpenOrder;
                   }  
                   
                   public function checkBarcodeForSortingOperator()
     {
      $this->autoRender = false;
      $this->layout = '';
     
      $this->loadModel('OpenOrder');
     
      $express1 = array();
      $express2 = array();
      $standerd1 = array();
      $standerd2 = array();
      $tracked1 = array();
      $tracked2 = array();
      
      $barcode  =  $this->request->data['barcode'];
      
      if($barcode)
      {
       $results = $this->OpenOrder->find('all', array('conditions' => array('OpenOrder.num_order_id'=>$barcode, 'OpenOrder.status' => '1' , 'OpenOrder.sorted_scanned' => '0', 'OpenOrder.error_code' => '')));
      }
       
       // Update tables and create manifest according to services those alloted already with manifest feild
       if( count($results) > 0 )
       {
       $i = 0;
       foreach($results as $result)
       {
       
        $openOrderId = $result['OpenOrder']['id'];
        $serviceName = $result['OpenOrder']['service_name'];
        $serviceProvider = $result['OpenOrder']['service_provider'];
        $serviceCode = $result['OpenOrder']['service_code'];
        $country = $result['OpenOrder']['destination'];
        
        /*$itemdetails[$i]['Id']    = $result['OpenOrder']['id'];
        $itemdetails[$i]['OrderId']   = $result['OpenOrder']['order_id'];
        $itemdetails[$i]['NumOrderId']  = $result['OpenOrder']['num_order_id'];
        $itemdetails[$i]['GeneralInfo']  = unserialize($result['OpenOrder']['general_info']);
        $itemdetails[$i]['ShippingInfo']  = unserialize($result['OpenOrder']['shipping_info']);
        $itemdetails[$i]['CustomerInfo']  = unserialize($result['OpenOrder']['customer_info']);
        $itemdetails[$i]['TotalsInfo']  = unserialize($result['OpenOrder']['totals_info']);
        $itemdetails[$i]['FolderName']  = unserialize($result['OpenOrder']['folder_name']);
        $itemdetails[$i]['Items']   = unserialize($result['OpenOrder']['items']);*/
        
        // Update Service table and applied counter value
        $this->loadModel( 'ServiceCounter' );
        $getCounterValue = $this->ServiceCounter->find( 'first' , 
      array( 'conditions' => 
       array( 
         'ServiceCounter.service_name' => $serviceName,
         'ServiceCounter.service_provider' => $serviceProvider,
         'ServiceCounter.service_code' => $serviceCode,
         'ServiceCounter.destination' => $country        
       )
      ) );
      
      // Manage Counter Section
      $counter = 0;
      if( $getCounterValue['ServiceCounter']['counter'] > 0 ):
       $counter = $getCounterValue['ServiceCounter']['counter'];
       $counter = $counter + 1;
      else:
       $counter = $counter + 1;
      endif;
        
        // Manage Order Id's also
        $orderCommaSeperated = '';
        if( $getCounterValue['ServiceCounter']['order_ids'] != '' ):
       $orderCommaSeperated = $getCounterValue['ServiceCounter']['order_ids'];
       $orderCommaSeperated = $orderCommaSeperated.','.$openOrderId;
        else:
       $orderCommaSeperated = $openOrderId;
        endif;
        
         // Now, Updating Services ...... 
      $this->request->data['ServiceCounter']['ServiceCounter']['id'] = $getCounterValue['ServiceCounter']['id'];      
      $this->request->data['ServiceCounter']['ServiceCounter']['counter'] = $counter;
      $this->request->data['ServiceCounter']['ServiceCounter']['order_ids'] = $orderCommaSeperated;         
         $this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );
         
         // Now, Updating Open Order ...... 
      $this->request->data['OpenOrder']['OpenOrder']['id'] = $openOrderId;            
      $this->request->data['OpenOrder']['OpenOrder']['sorted_scanned'] = 1;         
         $this->OpenOrder->saveAll( $this->request->data['OpenOrder'] );
         
         // Manifest Creation
         //$this->createManifestAccordingToServiceIfYes();
         
        $i++;
       } 
       
        //Conversion done with services and return it.                
    return strtolower(str_replace(')','-',str_replace('(','-',str_replace('<','-',str_replace(' ','-',$serviceProvider.$serviceName.$serviceCode.$country)))));       
    }
    else
    {
     //Response Blank         
     echo "none"; exit;
    }
        
    /*if(isset($itemdetails))
    {
     $itemdetails = json_decode(json_encode($itemdetails), TRUE);
     $myArray = Set::sort($itemdetails, '{n}.GeneralInfo.ReceivedDate', 'ASC');
    
     foreach($myArray as $itemdetail)
     {
      if($itemdetail['ShippingInfo']['PostalServiceName'] == 'Express' && count($itemdetail['Items']) == 1)
      {
       $express1[] = $itemdetail;
      }
      if($itemdetail['ShippingInfo']['PostalServiceName'] == 'Standard' && count($itemdetail['Items']) == 1)
      {
       $standerd1[] = $itemdetail;
      }
      if($itemdetail['ShippingInfo']['PostalServiceName'] == 'Tracked' && count($itemdetail['Items']) == 1)
      {
        $tracked1[] = $itemdetail;
      }
      if($itemdetail['ShippingInfo']['PostalServiceName'] == 'Express' && count($itemdetail['Items']) > 1)
      {
       $express2[] = $itemdetail;
      }
      if($itemdetail['ShippingInfo']['PostalServiceName'] == 'Standard' && count($itemdetail['Items']) > 1)
      {
       $standerd2[] = $itemdetail;
      }
      if($itemdetail['ShippingInfo']['PostalServiceName'] == 'Tracked' && count($itemdetail['Items']) > 1)
      {
        $tracked2[] = $itemdetail;
      }
     }
    }
      $this->set(compact('express1','standerd1','tracked1','express2','standerd2','tracked2'));
      echo $this->render('scansearch');*/
      
      
     }
     
     /*
      * 
      * 
      * Function for RacK Label print and Add or Remove ( 27-11-2015 Morning )
      * 
      * 
      * 
      */
      /*
	    * 
	    * Params, manipulate racks, level, section then Bins
	    * 
	    */ 
	   public function openRack()
	   {
		   $this->layout = "index";
		   $this->loadModel( 'Rack' );
		   
		   $data = $this->Rack->find('first' , array( 'fields' => array( 'Rack.rack_name' ) , 'group' => array('Rack.rack_name') , 'order' => array( 'Rack.id DESC' ) , 'limit' => 1 ));		   		   
		   if( count($data) > 0 )
		   {
				$firstRack = json_decode(json_encode($data),0);			   		   
				
				$rackGroupData = json_decode(json_encode($this->Rack->find('all' , array( 'fields' => array( 'count( Rack.level_association ) as SectionCounter' ) , 'group' => array('Rack.level_association') , 'order' => array( 'Rack.id ASC' ) ))),0);
				
				$rackData = json_decode(json_encode($this->Rack->find('all' , array( 'fields' => array( 'count( Rack.level_association ) as sectionCounter' , 'Rack.rack_name' , 'Rack.level_association' , 'Rack.rack_level_section' , 'Rack.locking_stage_section') , 'conditions' => array('Rack.rack_name' => $firstRack->Rack->rack_name),'group' => array('Rack.level_association'), 'order' => array( 'Rack.level_association ASC' ) ))),0);			   
				
				$rackNameList = $this->Rack->find('list' , array( 'fields' => array( 'Rack.rack_name' ) , 'group' => array('Rack.rack_name') , 'order' => array( 'Rack.id ASC' ) ));
				
				$this->set(compact('rackData' , 'rackNameList' , 'rackGroupData') );
		   }
		   
	   }
	   
	   /*
	    * 
	    * Params, Get Rack detail according to RackN
	    * 
	    */ 
	   public function getRackdetail()
	   {
			$this->layout = "";
			$this->autoRander = false;	
			
			$rackName = $this->request->data['rackInputName'];
			
			//Load model
			$this->loadModel( 'Rack' );
			
			$rackData = json_decode(json_encode($this->Rack->find('all' , array( 'fields' => array( 'count( Rack.level_association ) as sectionCounter' , 'Rack.rack_name' , 'Rack.level_association' , 'Rack.rack_level_section' , 'Rack.locking_stage_section') , 'conditions' => array( 'Rack.rack_name' => $rackName ),'group' => array('Rack.level_association'), 'order' => array( 'Rack.level_association ASC' ) ))),0);				
			//$rackData = json_decode(json_encode($this->Rack->find('all' , array( 'fields' => array( 'count( Rack.level_association ) as sectionCounter' , 'Rack.rack_name' , 'Rack.level_association' , 'Rack.rack_level_section' , 'Rack.locking_stage_section') , 'conditions' => array('Rack.rack_name' => $firstRack->Rack->rack_name),'group' => array('Rack.level_association'), 'order' => array( 'Rack.level_association ASC' ) ))),0);			   
			
			$this->set(compact('rackData') );
			$this->render( 'add_rack' );			
	   }
	   
	   /*
	    * 
	    * Params, New rack will be entertain according to new one
	    * 
	    */ 
	   public function addRackBtnOnClick()
	   {
		   $this->layout = "";
		   $this->autoRander = false;
		   
		   $rackName = $this->request->data['rackInputName'];
		   $fn = explode( '_',$rackName  );
		   $floorName = $fn[0];
		   $rn = explode( '_',$rackName  );
		   $rackfloorName = $rn[1];
		   
		   //Load model
		   $this->loadModel( 'Rack' );
		   
		   //Saving new Rack but ensure it has existed in table or not
		   $data = $this->Rack->find('all' , array( 'conditions' => array( 'Rack.rack_name' => $rackName )));
		   
		   if( count( $data ) > 0 ):
				echo "1"; exit;
		   else:
				// Store new rack with default locations
				$rackLevel = 1;$incDouble = 2;$inc = 1;$ik = 0;while( $ik < 5 ):
					
					//Pair store with 1-2
					$this->request->data['NewRack']['Rack']['floor_name'] = $floorName;
					$this->request->data['NewRack']['Rack']['rack_floorName'] = $rackfloorName;
					$this->request->data['NewRack']['Rack']['rack_name'] = $rackName;
					$this->request->data['NewRack']['Rack']['level_association'] = $rackfloorName.'-'.'L'.$rackLevel;
					$this->request->data['NewRack']['Rack']['rack_level_section'] = $rackName.'-'.'L'.$rackLevel.'-'.'S'.$inc;
					$this->request->data['NewRack']['Rack']['rack_section'] = 'S'.$inc;										
					$this->request->data['NewRack']['Rack']['locking_stage_section'] = 0;
					
					//Create Label and Pdf
					//Racks Barcodes
				    $uploadUrl = $this->getUrlBase();
				    $imgPath = WWW_ROOT .'img/racks/barcodes/';   
				    
				    // Section 1
				    $content = file_get_contents($uploadUrl.$rackName.'-'.'L'.$rackLevel.'-'.'S'.$inc);
				    file_put_contents($imgPath.$rackName.'-'.'L'.$rackLevel.'-'.'S'.$inc.'.png', $content);
				    //$this->rackBarcodePrintAccordingToSection_level( $rackName.'-'.'L'.$rackLevel.'-'.'S'.$inc );
					$this->Rack->saveAll( $this->request->data['NewRack'] );
					
					//Pair store with 1-2
					$this->request->data['NewRack']['Rack']['floor_name'] = $floorName; // Floor
					$this->request->data['NewRack']['Rack']['rack_floorName'] = $rackfloorName; // Rack
					$this->request->data['NewRack']['Rack']['rack_name'] = $rackName;
					$this->request->data['NewRack']['Rack']['level_association'] = $rackfloorName.'-'.'L'.$rackLevel; // Level
					$this->request->data['NewRack']['Rack']['rack_level_section'] = $rackName.'-'.'L'.$rackLevel.'-'.'S'.$incDouble;
					$this->request->data['NewRack']['Rack']['rack_section'] = 'S'.$incDouble;										
					$this->request->data['NewRack']['Rack']['locking_stage_section'] = 0;
					
					//Section 2
				    $content = file_get_contents($uploadUrl.$rackName.'-'.'L'.$rackLevel.'-'.'S'.$incDouble);
				    file_put_contents($imgPath.$rackName.'-'.'L'.$rackLevel.'-'.'S'.$incDouble.'.png', $content);
				    //$this->rackBarcodePrintAccordingToSection_level( $rackName.'-'.'L'.$rackLevel.'-'.'S'.$incDouble );
					$this->Rack->saveAll( $this->request->data['NewRack'] );
					
					$inc = 1;$incDouble = 2;
					$rackLevel++;					
				$ik++;
				endwhile;
			   $rackData = json_decode(json_encode($this->Rack->find('all' , array( 'conditions' => array( 'Rack.rack_name' => $rackName ) ))),0);	
			   $this->set(compact('rackData') );
			   $this->render( 'add_rack' );	
		   endif;		   	   
	   }
	   
	   /*
	    * 
	    * Params, Add cordinates for rack label printing with barcode through third-Party
	    * 
	    */ 
	   public function addRackCordinates()
	   {
		   $this->layout = "";
		   $this->autoRander = false;
		   $rackSectionCordinate = $this->request->data;
		   $rn = explode('-',$rackSectionCordinate['rack_level_section']);	  
		   $rackName = $rn[0];
		   $rack_level_section = $rackSectionCordinate['rack_level_section'];
		   
		   //Store cordinates of specific Rack-Level-Section
		   $this->loadModel( 'Rack' );
		   $rackData = $this->Rack->find('all' , array( 'conditions' => array( 'Rack.rack_level_section' => $rack_level_section ) ));		   		   
		   $level = explode('-',$rackSectionCordinate['rack_level_section']);
		   
		   //Racks Barcodes
		   $uploadUrl = $this->getUrlBase();
		   $imgPath = WWW_ROOT .'img/racks/barcodes/';   
		   
		   // Section 1
		   $content = file_get_contents($uploadUrl.$rack_level_section);
		   file_put_contents($imgPath.$rack_level_section.'.png', $content);
		   //$this->rackBarcodePrintAccordingToSection_level( $rack_level_section );
		   
		   //Section 2
		   $content = file_get_contents($uploadUrl.$level[0].'-'.$level[1].'-S2');
		   file_put_contents($imgPath.$level[0].'-'.$level[1].'-S2'.'.png', $content);
		   //$this->rackBarcodePrintAccordingToSection_level( $level[0].'-'.$level[1].'-S2' );
		   
		   // Get continous data now
		   $rackExistingData = $this->Rack->find('all' , array( 'conditions' => array( 'Rack.rack_level_section' => $level[0].'-'.$level[1].'-S2' ) ));
		   $this->request->data['RackStore']['Rack']['id'] = $rackExistingData[0]['Rack']['id'];
		   $this->request->data['RackStore']['Rack']['locking_stage_section'] = 0;
		   $this->Rack->saveAll( $this->request->data['RackStore'] );
		   echo "done"; 		   		   		   
		   exit;
	   }
	   
	   public function rackBarcodePrintAccordingToSection_level( $rack_level_section = null )
	   {
			$this->layout = '';
			$this->autoRender = false;
		
			require_once(APP . 'Vendor' . DS . 'dompdf' . DS . 'dompdf_config.inc.php'); 
			
			spl_autoload_register('DOMPDF_autoload'); 
			$dompdf = new DOMPDF();
		
			$dompdf->set_paper(array(0, 0, 238, 143), 'portrait');
							
			$barcodePath = Router::url('/', true).'/img/racks/barcodes/'.$rack_level_section.'.png';				
			$html2 = '<body>
						<table width="200" border="1">
						  <tr>
							<td>'.$rack_level_section.'</td>
							<td>&nbsp;<img src='.$barcodePath.' width="120" height="50" /></td>
						  </tr>
						</table>
						</body>';
			$cssPath = WWW_ROOT .'css/';
			$html2 .= '<style>'.file_get_contents($cssPath.'pdfstyle.css').'</style>';
			
			//echo $html2;
			//exit;
			//$dompdf->load_html($html2);
			$dompdf->load_html(utf8_decode($html2), Configure::read('App.encoding'));
			$dompdf->render();
			//$dompdf->stream("hello.pdf");
			
			$imgPath = WWW_ROOT .'img/racks/barcodes_pdf/'; 
			$path = Router::url('/', true).'img/racks/barcodes_pdf/';
			
			$date = new DateTime();
			$timestamp = $date->getTimestamp();
			$name = $rack_level_section.'.pdf';
			
			file_put_contents($imgPath.$name, $dompdf->output());
			$serverPath   =  $path.$name ;
							
			$sendData = array(
			 'printerId' => '73390',
			 'title' => 'Now Print',
			 'contentType' => 'raw_uri',
			 'content' => $serverPath,
			 'source' => 'Direct'
			);
			
			
			//App::import( 'Controller' , 'Coreprinters' );
			//$Coreprinter = new CoreprintersController();
			//$d = $Coreprinter->toPrint( $sendData );
			//pr($d); exit;
			
	  }
	  
	  /*
	   * 
	   * Params, New rack cordinates upto 10
	   * 
	   */ 
	 public function addRackCordinatesByOne()
	 {
		   $this->layout = "";
		   $this->autoRander = false;
		   $rackSectionCordinate = $this->request->data;		   
		   
		   //Store cordinates of specific Rack-Level-Section
		   $this->loadModel( 'Rack' );
		   
		   //Racks Barcodes
		   $uploadUrl = $this->getUrlBase();
		   $imgPath = WWW_ROOT .'img/racks/barcodes/';   
		   
		   //$rackName = explode('-',$rackSectionCordinate['rack_level_section'])[0];	  
		   $rack_level_section = explode('-',$rackSectionCordinate['rack_level_section']);
		   
		   // Section 1
		   $content = file_get_contents($uploadUrl.$rackSectionCordinate['rack_level_section']);
		   file_put_contents($imgPath.$rackSectionCordinate['rack_level_section'].'.png', $content);
		   //$this->rackBarcodePrintAccordingToSection_level( $rackSectionCordinate['rack_level_section'] );
		   
		   // Get continous data now		
		   /*$this->request->data['RackStore']['Rack']['rack_name'] = $rackSectionCordinate['rackName'];  
		   $this->request->data['RackStore']['Rack']['level_association'] = explode('_',$rack_level_section[0])[1].'-'.$rack_level_section[1];
		   $this->request->data['RackStore']['Rack']['rack_level_section'] = $rackSectionCordinate['rack_level_section'];   
		   $this->request->data['RackStore']['Rack']['locking_stage_section'] = 0;
		   $this->Rack->saveAll( $this->request->data['RackStore'] );*/
		   
		    $floor = explode('_',$rack_level_section[0]); // Floor
			$rack = explode('_',$rack_level_section[0]); // Rack	
			$level = explode('_',$rack_level_section[0]);	
			
			$this->request->data['RackStore']['Rack']['floor_name'] = $floor[0]; // Floor
			$this->request->data['RackStore']['Rack']['rack_floorName'] = $rack[1]; // Rack			
			$this->request->data['RackStore']['Rack']['rack_name'] = $rackSectionCordinate['rackName'];
			
			$this->request->data['RackStore']['Rack']['level_association'] = $level[1].'-'.$rack_level_section[1];
			$this->request->data['RackStore']['Rack']['rack_level_section'] = $rackSectionCordinate['rack_level_section'];
			$this->request->data['RackStore']['Rack']['rack_section'] = $rack_level_section[2];	
			
			$this->Rack->saveAll( $this->request->data['RackStore'] );
			//$this->request->data['NewRack']['Rack']['rack_section'] = 'S'.$incDouble;										
			//$this->request->data['NewRack']['Rack']['locking_stage_section'] = 0;
					
		   
		   
		   echo "done"; 		   		   		   
		   exit;
	 } 
	 
	 /*
	  * 
	  * Params, Racks Labels print
	  * 
	  */
	 public function getPrint()
     {
			$rack_level_section = 'R1-L1-S1';

			$this->layout = '';
			$this->autoRender = false;
			
			//Load Model
			$this->loadModel( 'Rack' );
			$params = array(
				'conditions' => array(
					'Rack.print_bulk' => 0
				),
				'fields' => array(
					'Rack.rack_level_section as AssoiatedName'
				),
				'order' => array(
					'Rack.rack_level_section ASC'
				)
			);
			
			$rackPrintData = json_decode(json_encode($this->Rack->find( 'all' , $params )),0);						
			$outerTotal = count(array_chunk( $this->Rack->find( 'all' , $params ) ,  21 ) );
			$arrayDivision = array_chunk( $this->Rack->find( 'all' , $params ) ,  21 );
			
			$incOuter = 1;$incIncrement = 1;$incTotal = 0;while( $incTotal < $outerTotal )
			{
				
				require_once(APP . 'Vendor' . DS . 'dompdf' . DS . 'dompdf_config.inc.php'); 
				spl_autoload_register('DOMPDF_autoload'); 
				$dompdf = new DOMPDF();
				
				$dompdf->set_paper(array(0, 0, 794, 1123), 'portrait');
				$html2 = '';				
				$html2 = '<body><table cellpadding="5px" cellspacing="0" border="0" width="100%" style="margin-top: 34px;" >';				
				$inc = 1;$i = 0; while( $i < count($arrayDivision[$incTotal]) )
				{		
					$barcodePath = Router::url('/', true).'/img/racks/barcodes/'.$arrayDivision[$incTotal][$i]['Rack']['AssoiatedName'].'.png';
					if( $i == 0 )
					{
						$html2 .= '<tr>';
						$html2 .= '<td style="padding:25px 0 29px;">';
						$html2 .= '<table border="0" cellpadding="5px" cellspacing="0" align="center" >';
						$html2 .= '<tr>';
						$html2 .= '<td align="center"><img src='.$barcodePath.' width="230" height="75" /></td>';
						$html2 .= '</tr>';
						$html2 .= '<tr>';
						$html2 .= '<td  align="center" style="font-size:32px;border:1px solid grey;">'.$arrayDivision[$incTotal][$i]['Rack']['AssoiatedName'].'</td>';
						$html2 .= '</tr>';
						$html2 .= '</table>';
						if( $inc % 3 == 0 )				
						{
							$html2 .= '</tr>';
							$html2 .= '<tr>';
						}
					}
					else
					{
						$html2 .= '<td style="padding:25px 0 29px;">';
						$html2 .= '<table border="0" cellpadding="5px" cellspacing="0" align="center" >';
						$html2 .= '<tr>';
						$html2 .= '<td align="center"><img src='.$barcodePath.' width="230" height="75" /></td>';
						$html2 .= '</tr>';
						$html2 .= '<tr>';
						$html2 .= '<td  align="center" style="font-size:32px;border:1px solid grey;">'.$arrayDivision[$incTotal][$i]['Rack']['AssoiatedName'].'</td>';
						$html2 .= '</tr>';
						$html2 .= '</table>';
						if( $inc % 3 == 0 )				
						{
							$html2 .= '</tr>';
							$html2 .= '<tr>';
						}
					}				
				$i++;$inc++;
				}
				
				$html2 .= '</table></body>';
				
				$dompdf->load_html($html2);
				$dompdf->load_html(utf8_decode($html2), Configure::read('App.encoding'));
				$dompdf->render();
				//$dompdf->stream("hello.pdf");
				
				$imgPath = WWW_ROOT .'img/racks/barcodes_pdf/'; 
				$path = Router::url('/', true).'img/racks/barcodes_pdf/';

				$date = new DateTime();
				$timestamp = $date->getTimestamp();
				$name = $incIncrement.'.pdf';
				file_put_contents($imgPath.$name, $dompdf->output());
				
			$incTotal++;$incIncrement++;	
			}
			
			/*file_put_contents($imgPath.$name, $dompdf->output());
			$serverPath   =  $path.$rack_level_section.'.pdf' ;                              
			$sendData = array(
			'printerId' => '72096',
			'title' => 'Rack Labels Printing Mode',
			'contentType' => 'pdf_uri',
			'content' => $serverPath,
			'source' => 'Direct'
			);
						
			App::import( 'Controller' , 'Coreprinters' );
			$Coreprinter = new CoreprintersController();
			$d = $Coreprinter->toPrint( $sendData );
			pr($d); exit;*/

		}

	 /*
	  * 
	  * Params, Remove section 
	  * 
	  */	
	 public function removeRackCordinatesByOne()
	 {
		   $this->layout = "";
		   $this->autoRander = false;
		   $rackSectionCordinate = $this->request->data;
		   
		   //Store cordinates of specific Rack-Level-Section
		   $this->loadModel( 'Rack' );
		   
		   $getData = $this->Rack->find( 'first', array('fields' => array('Rack.id'), 'conditions' => array( 'Rack.rack_level_section' => $rackSectionCordinate['rack_level_section'] ) ) );
		   $this->Rack->delete( $getData['Rack']['id'] );		   
		   echo "delete"; exit;
	 }  
	 
	 public function getRackdetailForWarehouseOnClick()
     {
		$this->layout = "";
		$this->autoRander = false;	
		$rackName = $this->request->data['rackInputName'];
		//Load model
		$this->loadModel( 'Rack' );
				
		$rackData = json_decode(json_encode($this->Rack->find('all' , array( 'fields' => array( 'count( Rack.level_association ) as sectionCounter' , 'Rack.rack_name' , 'Rack.level_association' , 'Rack.rack_level_section' , 'Rack.locking_stage_section') , 'conditions' => array( 'Rack.rack_name' => $rackName ),'group' => array('Rack.level_association'), 'order' => array( 'Rack.level_association ASC' ) ))),0);						
		$this->set(compact('rackData') );
		$this->render( 'warehouse_rack' );			
    }
    
    public function updatePackQty()
	{
		
		$this->autoLayout = 'ajax';
		$this->autoRender = false;
		
		$this->loadModel('Product');
		$this->loadModel('ProductDesc');
		$this->loadModel('ProductPrice');
		$this->loadModel('ProductImage');
		$this->loadModel('AttributeOption');
		
			
		App::import('Vendor', 'PHPExcel/IOFactory');
		
		$objPHPExcel = new PHPExcel();
		$objReader= PHPExcel_IOFactory::createReader('CSV');
		$objReader->setReadDataOnly(true);				
		$objPHPExcel=$objReader->load('files/total_Stock.csv');
		$objWorksheet=$objPHPExcel->setActiveSheetIndex(0);
		$lastRow = $objPHPExcel->getActiveSheet()->getHighestRow();
		$colString	=	 $highestColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
		$colNumber = PHPExcel_Cell::columnIndexFromString($colString);
				 
		for($i=2;$i<=$lastRow;$i++) 
		{
			$sku		=	$objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
			$qty		=	$objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
			$this->Product->updateAll(array('Product.uploaded_stock' => $qty ,  'Product.current_stock_level' => $qty  ), array('Product.product_sku' => $sku));
		}
	}
	
	/*  code start for assign postal service */
	public function getDhlRates( $mergeId = null , $order_id = null , $mergeSubOrderId = null , $openOrder = null )
	{
		$this->loadModel( 'MergeUpdate' );
		$this->loadModel( 'Product' );
		$this->loadModel( 'ProductDesc' );
		$this->loadModel( 'Country' );
		//$this->loadModel( 'DhlMatrix' );
		$this->loadModel( 'PostalServiceDesc' );
		//echo $order_id;
		$cInfo = $openOrder['customer_info']; 
 		$_orders = $this->MergeUpdate->find('first',array('conditions' => array('MergeUpdate.id' => $mergeId),'fields'=>array('sku','product_order_id_identify','order_id')));		
		$pos = strpos($_orders['MergeUpdate']['sku'],",");
		
		if ($pos === false) {
			$val  = $_orders['MergeUpdate']['sku'];			
			$s    = explode("XS-", $val);
			$_qqty = $s[0];
			$_sku = "S-".$s[1];		
				
			$product = $this->Product->find('first',array('conditions' => array('Product.product_sku' => $_sku),'fields'=>array('ProductDesc.length','ProductDesc.width','ProductDesc.height','ProductDesc.weight','Product.category_name')));
			/*$product = $this->ProductDesc->find('first',array('conditions' => array('ProductDesc.product_defined_skus' => $_sku),'fields'=>array('length','width','height','weight','Product.category_name')));*/
			
			$length = $product['ProductDesc']['length'] / 10;
			$width  = $product['ProductDesc']['width'] / 10;
			$height = $product['ProductDesc']['height'] / 10;
			$weight = $product['ProductDesc']['weight'] * $_qqty;	
			$cat_name = $product['Product']['category_name'];	
					
		}else{			
			$sks = explode(",",$_orders['MergeUpdate']['sku']);
			foreach($sks as $val){
				$s    = explode("XS-", $val);
				$_qqty = $s[0];
				$_sku = "S-".$s[1];	
				$product = $this->Product->find('first',array('conditions' => array('Product.product_sku' => $_sku),'fields'=>array('ProductDesc.length','ProductDesc.width','ProductDesc.height','ProductDesc.weight','Product.category_name')));
				$_length[] = $product['ProductDesc']['length'];
				$_width[]  = $product['ProductDesc']['width'];
				$_height[] = $product['ProductDesc']['height'];
				$_weight[] = $product['ProductDesc']['weight'] * $_qqty;		
				$_catname[$product['Product']['category_name']] = $product['Product']['category_name'];				
			}		
			$length	= array_sum($_length) / 10;
			$width	= array_sum($_width) / 10;
			$height	= array_sum($_height) / 10;
			$weight	= array_sum($_weight);	
			$cat_name	= implode("<br>",$_catname);
		}
		 	
		$PostCode = trim($cInfo->Address->PostCode)?trim($cInfo->Address->PostCode):'JE2 6LF';
		
		$country_data = $this->Country->find('first',array('conditions' => array("Country.name" => $cInfo->Address->Country)));
 			
		$FromCompany = 'FRESHER BUSINESS LIMITED';
		$FromPersonName = 'C/O ProFulfillment and Logistics';
		$FromAddress1 = 'Unit 4 Airport Cargo Centre';
		$FromAddress2 = 'L\'avenue De La Commune';
		$FromCity = 'St Peter';
		$FromDivision = 'JE';
		$FromPostCode = 'JE3 7BY';
		$FromCountryCode = 'JE';
		$FromCountryName = 'JERSEY';
		$FromPhoneNumber = '+40123456789'; 
		
		if(strpos($openOrder['sub_source'],'CostBreaker')!== false){
			$FromCompany = 'EURACO GROUP LTD';
			/*-------Updated on 12-02-2020-----*/
			$FromPersonName = 'C/O ProFulfillment and Logistics';
			$FromAddress1 = 'Unit 4 Airport Cargo Centre';
			$FromAddress2 = 'L\'avenue De La Commune';
			$FromCity = 'St Peter';
			$FromDivision = 'JE';
			$FromPostCode = 'JE3 7BY';
			/*$FromPersonName = 'EURACO GROUP LTD';
			$FromAddress1 = '49';
			$FromAddress2 = 'Oxford Road';
			$FromCity = 'ST HELIER';
			$FromDivision = 'JE';
			$FromPostCode = 'JE2 4LJ';*/
			$FromCountryCode = 'JE';
			$FromCountryName = 'JERSEY';
			$FromPhoneNumber = '+44 3301170104';
		}else if(strpos($openOrder['sub_source'],'Marec')!== false){
			$FromCompany = 'ESL LIMITED';
			$FromPersonName = 'ESL LIMITED';
			/*-------Updated on 12-02-2020-----*/
			$FromAddress1 = 'Unit 4 Airport Cargo Centre';
			$FromAddress2 = 'L\'avenue De La Commune';
			$FromCity = 'St Peter';
			$FromDivision = 'JE';
			$FromPostCode = 'JE3 7BY';
		   /*$FromAddress1 = 'Beachside Business Centre';
			$FromAddress2 = 'L\'avenue De La Commune';
			$FromCity = 'St Peter';
			$FromDivision = 'JE';
			$FromPostCode = 'JE3 7BY';*/
			$FromCountryCode = 'JE';
			$FromCountryName = 'JERSEY';
			$FromPhoneNumber = '+443301170238';
		 }	
		else if(strpos($openOrder['sub_source'],'RAINBOW')!== false){
			$FromCompany = 'FRESHER BUSINESS LIMITED';
			$FromPersonName = 'C/O ProFulfillment and Logistics';
			/*-------Updated on 12-02-2020-----*/
			$FromAddress1 = 'Unit 4 Airport Cargo Centre';
			$FromAddress2 = 'L\'avenue De La Commune';
			$FromCity = 'St Peter';
			$FromDivision = 'JE';
			$FromPostCode = 'JE3 7BY';
			
			/*$FromAddress1 = 'BEACHSIDE BUSINESS CENTRE';			
			$FromAddress2 = 'RUE DU HOCQ';
			$FromCity = 'ST CLEMENT';
			$FromDivision = 'JE';
			$FromPostCode = 'JE2 6LF';*/
			$FromCountryCode = 'JE';
			$FromCountryName = 'JERSEY';
			$FromPhoneNumber = '0123456789';
		} else if(stristr($sub_source, 'RRRetail')) {	
			$FromCompany = 'FRESHER BUSINESS LIMITED';
			$FromPersonName = 'C/O ProFulfillment and Logistics';
			/*-------Updated on 12-02-2020-----*/
			$FromAddress1 = 'Unit 4 Airport Cargo Centre';
			$FromAddress2 = 'L\'avenue De La Commune';
			$FromCity = 'St Peter';
			$FromDivision = 'JE';
			$FromPostCode = 'JE3 7BY';
			
			/*$FromAddress1 = 'BEACHSIDE BUSINESS CENTRE';			
			$FromAddress2 = 'RUE DU HOCQ';
			$FromCity = 'ST CLEMENT';
			$FromDivision = 'JE';
			$FromPostCode = 'JE2 6LF';*/
			$FromCountryCode = 'JE';
			$FromCountryName = 'JERSEY';
			$FromPhoneNumber = '0123456789';
		}
		else if(strpos($openOrder['sub_source'],'Tech_Drive')!== false){
			$FromCompany = 'TECH DRIVE SUPPLIES';
			$FromPersonName = 'TECH DRIVE SUPPLIES';
			$FromAddress1 = '4 NORWOOD COURT';
			$FromAddress2 = 'LA RUE MILITAIRE';
			$FromCity = 'ST JOHN';
			$FromDivision = 'JE';
			$FromPostCode = 'JE3 4DP';
			$FromCountryCode = 'JE';
			$FromCountryName = 'JERSEY';
			$FromPhoneNumber = '0123456789';
		}
		elseif(strpos($openOrder['sub_source'],'BBD')!== false){
			$FromCompany = 'BBD EU LIMITED';
			$FromPersonName = 'BBD EU LIMITED';
			/*$FromAddress1 = 'Unit A1 21/F';
			$FromAddress2 = 'Officeplus Among Kok';
			$FromCity = '998 Canton Road';
			$FromDivision = 'HK';
			$FromPostCode = '999077';
			$FromCountryCode = 'HK';
			$FromCountryName = 'Hong Kong';*/
			$FromAddress1 = '4 NORWOOD COURT';
			$FromAddress2 = 'LA RUE MILITAIRE';
			$FromCity = 'ST JOHN';
			$FromDivision = 'JE';
			$FromPostCode = 'JE3 4DP';
			$FromCountryCode = 'JE';
			$FromCountryName = 'JERSEY';
			$FromPhoneNumber = '0123456789'; 
		} 		
			$Address1 = $cInfo->Address->Address1;
			$Address2 = $cInfo->Address->Address2;
			
			if($order_id == '2652383'){
				//pr($cInfo);exit;
				 $Address1 ='Paseo Manuel Girona 75';
			}
			
			App::import( 'Controller' , 'Invoice' );		
			$_invoice = new InvoiceController();	
			//$order = $_invoice->getOrderByNumIdDhl( $order_id ); date 07NOV2019
			$order = $_invoice->getOrderByNumId_openorder( $order_id );	
			 
			if($weight < 0.1) $weight = 0.1;
			
			$sub_total = $order->TotalsInfo->Subtotal ;
			$total_charge  = $order->TotalsInfo->TotalCharge;
			$order_currency  = $order->TotalsInfo->Currency;
			$postage_cost = $order->TotalsInfo->PostageCost;
			
			/*if($order->TotalsInfo->Currency == 'GBP'){
				$sub_total = $order->TotalsInfo->Subtotal  * (1.12);
				$total_charge  = $order->TotalsInfo->TotalCharge  * (1.12);
				$postage_cost = $order->TotalsInfo->PostageCost * (1.12);
				$order_currency  = 'EUR';
			}*/
			$full_name = $cInfo->Address->FullName;
			$Town = $cInfo->Address->Town;
			/*if($_orders['MergeUpdate']['product_order_id_identify'] == '2510688-1'){
				$Address1 = 'TALLERS RANI,S.A. C/CORRO 10'; 
				$Address2 = 'POLIG.IND. L AMETLLA PARK';           
            	$Town = 'L AMETLLA DEL VALLES';
			}*/
			 
			
			$_details = array('merge_id'=> $mergeId,'order_id'=> $_orders['MergeUpdate']['product_order_id_identify'],'sub_total' => number_format($sub_total,2),'order_total'=> number_format($total_charge,2),'postage_cost' => number_format($postage_cost,2),'order_currency'=>$order_currency,'products'=> $cat_name,'length'=> ceil($length),'width'=> ceil($width),'height'=> ceil($height),'weight'=> number_format($weight,1),'Company'=>$cInfo->Address->Company,'FullName'=> utf8_decode($full_name),'Address1' =>utf8_decode($Address1),'Address2' =>utf8_decode($Address2),'Town' =>utf8_decode($Town),'Region' =>utf8_decode($cInfo->Address->Region),'PostCode' =>$PostCode,'CountryName'=>$cInfo->Address->Country,'CountryCode' =>$country_data['Country']['iso_2'],'PhoneNumber'=>$cInfo->Address->PhoneNumber,'FromCompany'=>$FromCompany,'FromPersonName'=>$FromPersonName,'FromAddress1' =>$FromAddress1,'FromAddress2' =>$FromAddress2,'FromCity' =>$FromCity,'FromDivision' =>$FromDivision,'FromPostCode' =>$FromPostCode,'FromCountryCode' =>$FromCountryCode,'FromCountryName' =>$FromCountryName,'FromPhoneNumber'=>$FromPhoneNumber);
		if($_orders['MergeUpdate']['product_order_id_identify'] == '2377329-1'){
				
			//pr($_details); 
				/*$_details['Town'] ='Paris';
				$_details['Region'] ='';				
				$_details['Address1'] ='3 Rue Marc Seguin';
				$_details['PostCode'] = '75018'; 
				$_details['Address2'] ='';
				print_r($_details);*/
				
			}
			
		file_put_contents(WWW_ROOT."img/dhl/send_".$_orders['MergeUpdate']['product_order_id_identify'].".log",print_r($_details,true));
		/* 
		$_details = array('order_id'=> $_orders['MergeUpdate']['product_order_id_identify'],'length'=>$length,'width'=>$width,'height'=>$height,'weight'=>$weight,'Company'=>$cInfo->Address->Company,'FullName'=>$cInfo->Address->FullName,'Address1' =>$cInfo->Address->Address1,'Address2' =>$cInfo->Address->Address2,'Town' =>$cInfo->Address->Town,'Region' =>$cInfo->Address->Region,'PostCode' =>$PostCode,'Country' =>$country_data['Country']['iso_2'],'PhoneNumber'=>$cInfo->Address->PhoneNumber,'FromCompany'=>'DHL Test','FromPersonName'=>'Mr Sender','FromAddress1' =>'DHL Test Adr','FromAddress2' =>'DHL Test D2','FromCity' =>'HOUNSLOW','FromDivision' =>'GB','FromPostCode' =>'TW4 W32A','FromCountryCode' =>'GB','FromCountryName' =>'United Kingdom','FromPhoneNumber'=>'0123456789');*/		
 
	
		//$getShippingRates = $objDhlController->dhlServiceStart( 1 , 'DHL_REQUEST_WORLD_WIDE_CAP_QUOTE',$_details);
	 	
		$waybill_number = '';
		App::import( 'Controller' , 'DhlPostals' );		
		$objDhlController = new DhlPostalsController();
		
		$SHIP_REG = $objDhlController->dhlServiceStart(2, 'DHL_REQUEST_WORLD_WIDE_SHIP_REG',$_details);	 
		file_put_contents(WWW_ROOT."img/dhl/details_".$_orders['MergeUpdate']['product_order_id_identify'].".log",print_r($SHIP_REG,true));		 
		// pr( $SHIP_REG );
		// exit;
		if($SHIP_REG->Response->Status->Condition->ConditionCode){
			$this->loadModel( 'OrderNote' );
			$this->loadModel( 'OpenOrder' );
			$this->MergeUpdate->updateAll( array( 'MergeUpdate.status' => 3) , array( 'MergeUpdate.order_id' => $_orders['MergeUpdate']['order_id'] ) );
			$this->OpenOrder->updateAll( array( 'OpenOrder.status' => 3 ) , array( 'OpenOrder.num_order_id' => $_orders['MergeUpdate']['order_id'] ) );
			$dataval = $SHIP_REG->Response->Status->Condition->ConditionData;
			if($dataval == ''){
				$dataval = ' ';
			}
			$noteDate['order_id'] 	= 	 $_orders['MergeUpdate']['order_id'];
			$noteDate['note'] 		= 	 $SHIP_REG->Response->Status->Condition->ConditionData;
			$noteDate['type'] 		= 	 'Lock';
			$noteDate['user'] 		= 	 'DHL ';
			$noteDate['date'] 		= 	 date('Y-m-d H:i:s');
			$this->OrderNote->saveAll( $noteDate );
					
 			$mailBody = '<p><strong>'.$_orders['MergeUpdate']['product_order_id_identify'].' have below issue please review and solve it.</strong></p>';
			$mailBody .= '<p>Error Code : '.$SHIP_REG->Response->Status->Condition->ConditionCode.'</p>';
			$mailBody .= '<p>Error Message : '.$SHIP_REG->Response->Status->Condition->ConditionData.'</p>';
			
			App::uses('CakeEmail', 'Network/Email');
			$email = new CakeEmail('');
			$email->emailFormat('html');
			$email->from('info@euracogroup.co.uk');
			$email->to( array('avadhesh.jij@rediffmail.com','shashi@euracogroup.co.uk','abhishek@euracogroup.co.uk','deepak@euracogroup.com','vikas.kumar@euracogroup.co.uk','ankit.nagar@euracogroup.co.uk'));					  
			$getBase = Router::url('/', true);
			$email->subject($_orders['MergeUpdate']['product_order_id_identify'].' DHL orders issue in Xsensys' );
			$email->send( $mailBody );
					
		}else{
		
				$filterResults	=	$this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.provider_ref_code' => 'Dhl_Zone', 'PostalServiceDesc.courier' => 'DHL')));
 			 	
				$service_id = $filterResults['PostalServiceDesc']['id'];
				$waybill_number = $SHIP_REG->AirwayBillNumber ;
				$data['MergeUpdate']['service_name'] 		= 'Express';
				$data['MergeUpdate']['postal_service'] 		= 'Express';
				$data['MergeUpdate']['provider_ref_code'] 	= 'DHL';
				$data['MergeUpdate']['service_id'] 			= $service_id;
				$data['MergeUpdate']['service_provider'] 	= 'DHL';
				$data['MergeUpdate']['packet_weight'] 		= $weight;
				$data['MergeUpdate']['packet_length'] 		= $length;
				$data['MergeUpdate']['packet_width'] 		= $width;
				$data['MergeUpdate']['packet_height'] 		= $height;
				$data['MergeUpdate']['warehouse'] 			= 'Jersey';
				$data['MergeUpdate']['delevery_country'] 	= $cInfo->Address->Country; 
				$data['MergeUpdate']['country_code'] 		= $country_data['Country']['iso_2'];
				$data['MergeUpdate']['track_id'] 			= $SHIP_REG->AirwayBillNumber ;
				$data['MergeUpdate']['reg_post_number']		= $SHIP_REG->AirwayBillNumber ;
				$data['MergeUpdate']['id'] 					= $mergeId;
				$this->MergeUpdate->saveAll( $data );
				$this->store_updatePackagingVariant($order_id);		
				
							
				//App::import( 'Controller' , 'Invoice' );		
			//	$_invoice = new InvoiceController();	
				
				
			//	$order = $_invoice->getOrderByNumIdDhl( $order_id );	
		 	 //   pr($order); exit;
			
			
			if(is_object($order)){			
				require_once(APP . 'Vendor' . DS . 'dompdf' . DS . 'dompdf_config.inc.php'); 
				spl_autoload_register('DOMPDF_autoload'); 
				$dompdf = new DOMPDF();	
				$result 		=	'';
				$htmlTemplate	=	'';	
		
				$htmlTemplate	=	$_invoice->getTemplateDHL($order,$waybill_number);
				$SubSource		=	$order->GeneralInfo->SubSource;
				$result			=	ucfirst(strtolower(substr($SubSource, 0, 4)));
				
				/**************** for tempplate *******************/
				$html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					 <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
					 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
					 <meta content="" name="description"/>
					 <meta content="" name="author"/>
					 <style>'.file_get_contents(WWW_ROOT .'css/pdfstyle.css').' body { font-family: times }</style>';
				$html .= '<body>'.$htmlTemplate.'</body>';
						
				$name	= $mergeSubOrderId.'.pdf';							
				$dompdf->load_html(utf8_encode($html), Configure::read('App.encoding'));
				$dompdf->render();			
								
				$file_to_save = WWW_ROOT .'img/dhl/invoice_'.$name;
				//save the pdf file on the server
				file_put_contents($file_to_save, $dompdf->output()); 
				$_invoice->getDHLSlip($_orders['MergeUpdate']['order_id']);
				  
			}
		}															
/*	$this->MergeUpdate->updateAll( array('MergeUpdate.postal_service' => "'Express'",'MergeUpdate.delevery_country' => "'Jersey'" ,'MergeUpdate.service_name' => "'express_worldwide'" , 'MergeUpdate.provider_ref_code' => "'DHL'" , 'MergeUpdate.service_provider' => "'DHL'",'MergeUpdate.service_id' => $service_id), array('MergeUpdate.product_order_id_identify' => $mergeSubOrderId, 'MergeUpdate.order_id' => $mergeOrderId) );
											 
*/										/*$this->MergeUpdate->updateAll( array('MergeUpdate.postal_service' => "'Express'",'MergeUpdate.delevery_country' => "'Jersey'" ,'MergeUpdate.service_name' => "'express_worldwide'" , 'MergeUpdate.provider_ref_code' => "'DHL'" , 'MergeUpdate.service_provider' => "'DHL'",'MergeUpdate.service_id' => $service_id), array('MergeUpdate.product_order_id_identify' => $mergeSubOrderId, 'MergeUpdate.order_id' => $mergeOrderId) );
		*/
		//pr($getShippingRates);exit;
		 	
			
	}
	
	/*  code start for assign postal service */
	
	public function deliveryService( $orderId = null )
	{
		/**********************************************/
		$this->layout = '';
		$this->autoRender = false;
		$this->loadModel( 'OpenOrder' );
		$this->loadModel( 'MergeUpdate' );
		
		$orders		=	$this->MergeUpdate->find('all', array( 'conditions' => array( 'MergeUpdate.order_id' => $orderId ) , 'fields' => array( 'MergeUpdate.id' , 'MergeUpdate.order_id' , 'MergeUpdate.product_order_id_identify' ) ));
		
		$getorderDetail		=	$this->getOpenOrderById( $orderId );		
		 
		$postalServiceName	=	$getorderDetail['shipping_info']->PostalServiceName;		
		$customer_info		= 	$getorderDetail['customer_info'];
		$country			=   $getorderDetail['customer_info']->Address->Country;
		$sub_source			= 	$getorderDetail['sub_source'];
	 	$total_charge		= 	$getorderDetail['totals_info']->TotalCharge;
 		 
 		$store_id			= 	0;
 		echo "<br>sub_source : ".$sub_source; 
		 
 		if(strpos(strtolower($sub_source), 'costbreaker') !== false ){
			$store_id = 1;
		}else if(strpos(strtolower($sub_source), 'marec') !== false ){
			$store_id = 2;
		}else if(strpos(strtolower($sub_source), 'rainbow') !== false){
			$store_id = 3;
		}else if(stristr($sub_source, 'RRRetail')) {	
			$store_id = 3;
		}else if(strpos(strtolower($sub_source), 'tech') !== false){
			$store_id = 4;
		}else if(strpos(strtolower($sub_source), 'bbd') !== false){
			$store_id = 5;
		}else if(strpos(strtolower($sub_source), 'ebay') !== false){
			$store_id = 6;
		}else if(strpos(strtolower($sub_source), 'costdropper') !== false){
			$store_id = 7;
		}else if(strpos(strtolower($sub_source), 'onbuy') !== false){
			$store_id = 8;
		}else if(strpos(strtolower($sub_source), 'euraco.fyndiq') !== false || strpos(strtolower($sub_source), 'fyndiq_costdropper') !== false){
			$store_id = 9;
		}

  		echo "<br>store_id : ".$store_id; 

		$this->MergeUpdate->updateAll(array('MergeUpdate.store_id'=> $store_id,'MergeUpdate.source_coming'=> "'".$sub_source."'"), array( 'MergeUpdate.order_id' => $orderId) );
		
		if($postalServiceName == 'Express' && $country != 'United Kingdom'){
			foreach($orders as $order)
			{ 
				$this->getDhlRates( $order['MergeUpdate']['id'], $order['MergeUpdate']['order_id'], $order['MergeUpdate']['product_order_id_identify'], $getorderDetail);					
			}
		}else if($total_charge > 150 && $country != 'United Kingdom'){  
			foreach($orders as $order)
			{ 
				$this->getDhlRates( $order['MergeUpdate']['id'], $order['MergeUpdate']['order_id'], $order['MergeUpdate']['product_order_id_identify'], $getorderDetail);					
			}
		}else{
			foreach($orders as $order)
			{
						
				$this->setPostalServiceToOrder( $order['MergeUpdate']['id'], $order['MergeUpdate']['order_id'], $order['MergeUpdate']['product_order_id_identify'] );
				
		
			}
		}
	} 
	public function setServiceJpForAll( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null,$totalCharge = 0,$sub_source = null,$postalServiceName = 'Standard' )
	{
			
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			$isoCode = Configure::read('customIsoCodes');
			$conditions = array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post');
			if($destinationCountry == 'France' )
			{
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['FRO', 'FRU']);
			}else if($destinationCountry == 'Germany' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['DEE', 'DEO']);
			}else if($destinationCountry == 'Spain' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['ESE', 'ESO']);
			}else if($destinationCountry == 'Italy' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['ITE', 'ITO']);
			}else if($destinationCountry == 'Poland' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['PLE', 'PLO']);
			}else if($destinationCountry == 'Netherlands' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['NLE', 'NLO']);
			}  			 
  			 

			 $filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' =>$conditions));
  			
			//pr($filterResults);
			if($filterResults)
			{ 		
				$k = 0;	
				foreach($filterResults as $filterResult)
					{
						if($filterResult['ServiceLevel']['service_name'] == 'Standard')
						{
								if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
								{
									$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
									$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
									$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
									$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
									$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
									$k++;														
								}
						}																					
					}
					$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
					$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
					unset($perItem); unset($perkilo); unset($weightKilo);
					$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
					if($postalservicessel)
					{
						$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
						$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
						$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
						$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
						$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
						$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
						$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
						$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
						$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
						$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
						$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
						$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
 						$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
						$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
						$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
						$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
					}
					$data['MergeUpdate']['service_name'] 		= $serviceName;
					$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
					$data['MergeUpdate']['service_id'] 			= $postalServiceID;
					$data['MergeUpdate']['service_provider'] 	= $postalProvider;
					$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
					$data['MergeUpdate']['packet_length'] 		= $orderLength;
					$data['MergeUpdate']['packet_width'] 		= $orderWidth;
					$data['MergeUpdate']['packet_height'] 		= $orderHeight;
					$data['MergeUpdate']['warehouse'] 			= $warehouse;
					$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
					$data['MergeUpdate']['template_id'] 		= $templateid;
					$data['MergeUpdate']['manifest'] 			= $manifest;
					$data['MergeUpdate']['lvcr'] 				= $lvcr;
					$data['MergeUpdate']['cn_required'] 		= $cnrequired;
					$data['MergeUpdate']['postal_service'] 		= $postalServiceName;
					$data['MergeUpdate']['country_code'] 		= @$countryCode;
					$data['MergeUpdate']['id'] 					= $splitOrderID;
					$this->MergeUpdate->saveAll( $data );
			}
			else
			{
				$data['MergeUpdate']['service_name'] 		= "Over Weight";
				$data['MergeUpdate']['provider_ref_code'] 	= "Over Weight";
				$data['MergeUpdate']['service_id'] 			= "Over Weight";
				$data['MergeUpdate']['service_provider'] 	= "Over Weight";
				$data['MergeUpdate']['postal_service'] 		= $postalServiceName;
				$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
				$data['MergeUpdate']['country_code'] 		= @$countryCode;	
				$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
				$data['MergeUpdate']['packet_length'] 		= $orderLength;
				$data['MergeUpdate']['packet_width'] 		= $orderWidth;
				$data['MergeUpdate']['packet_height'] 		= $orderHeight;
 				$data['MergeUpdate']['id'] 					= $splitOrderID;
				$this->MergeUpdate->saveAll( $data );
			}
		}
	public function setServiceJpForAll123( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null,$totalCharge = 0,$sub_source = null )
	{
			
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			$isoCode = Configure::read('customIsoCodes');
			$conditions = array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post');
			if($destinationCountry == 'France' )
			{
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['FRO', 'FRU']);
			}else if($destinationCountry == 'Germany' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['DEE', 'DEO']);
			}else if($destinationCountry == 'Spain' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['ESE', 'ESO']);
			}else if($destinationCountry == 'Italy' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['ITE', 'ITO']);
			}else if($destinationCountry == 'Poland' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['PLE', 'PLO']);
			}else if($destinationCountry == 'Netherlands' ){
				$conditions =  array('PostalServiceDesc.warehouse' => 'Jersey', 'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['NLE', 'NLO']);
			}
  			 
			 $filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' =>$conditions));
			
 			
			//pr($filterResults);
			if($filterResults)
			{ 		
				$k = 0;	
				foreach($filterResults as $filterResult)
					{
						if($filterResult['ServiceLevel']['service_name'] == 'Standard')
						{
								if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
								{
									$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
									$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
									$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
									$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
									$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
									$k++;														
								}
						}																					
					}
					$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
					$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
					unset($perItem); unset($perkilo); unset($weightKilo);
					$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
					if($postalservicessel)
					{
						$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
						$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
						$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
						$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
						$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
						$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
						$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
						$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
						$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
						$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
						$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
						$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
						$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
						$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
						$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
						$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
						$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
					}
					$data['MergeUpdate']['service_name'] 		= $serviceName;
					$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
					$data['MergeUpdate']['service_id'] 			= $postalServiceID;
					$data['MergeUpdate']['service_provider'] 	= $postalProvider;
					$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
					$data['MergeUpdate']['packet_length'] 		= $orderLength;
					$data['MergeUpdate']['packet_width'] 		= $orderWidth;
					$data['MergeUpdate']['packet_height'] 		= $orderHeight;
					$data['MergeUpdate']['warehouse'] 			= $warehouse;
					$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
					$data['MergeUpdate']['template_id'] 		= $templateid;
					$data['MergeUpdate']['manifest'] 			= $manifest;
					$data['MergeUpdate']['lvcr'] 				= $lvcr;
					$data['MergeUpdate']['cn_required'] 		= $cnrequired;
					$data['MergeUpdate']['postal_service'] 		= 'Standard';
					$data['MergeUpdate']['country_code'] 		= $countryCode;
					$data['MergeUpdate']['id'] 					= $splitOrderID;
					$this->MergeUpdate->saveAll( $data );
			}
			else
			{
				$data['MergeUpdate']['service_name'] = "Over Weight";
				$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
				$data['MergeUpdate']['service_id'] = "Over Weight";
				$data['MergeUpdate']['service_provider'] = "Over Weight";
				$data['MergeUpdate']['id'] 			= $splitOrderID;
				$this->MergeUpdate->saveAll( $data );
			}
		}
		
	public function setServicePostnl( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null, $postalServiceName = null )
	{
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
  			$this->loadModel('Country');
			$this->loadModel('OpenOrder');
			$ref_code =['sizeg-reg','sizee-reg'];
			if($postalServiceName == 'Standard'){
 				 $ref_code =['sizeg-boxable','sizee-nonboxable'];
 			}
			
			$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.courier' => 'Belgium Post','PostalServiceDesc.provider_ref_code'=> $ref_code)));
 				
			if(count($filterResults) == 0){
				$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => 'France', 'PostalServiceDesc.courier' => 'Belgium Post','PostalServiceDesc.provider_ref_code'=> $ref_code)));
			}
			
			$country_code = '';
			$_country = $this->Country->find('first', array('conditions' => array('custom_name' => $destinationCountry)));
			if(count($_country) > 0){
				$country_code = $_country['Country']['iso_2'];
			}
								
			$k = 0;	
			foreach($filterResults as $filterResult)
			{
 				$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
				$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
				$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
				$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
				$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
				$k++;														
 
			}
			
			$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
			$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
			unset($perItem); unset($perkilo); unset($weightKilo);
			$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
			if($postalservicessel)
			{
				$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
				$postalProvider			=	 'PostNL';
				$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
				$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
				$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
				$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
				$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
				$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
				$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
				$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
				$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
				$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
				$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
				$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
				$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
				$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
				$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
			}
			$data['MergeUpdate']['service_name'] 		= $serviceName;
			$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
			$data['MergeUpdate']['service_id'] 			= $postalServiceID;
			$data['MergeUpdate']['service_provider'] 	= $postalProvider;
			$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
			$data['MergeUpdate']['packet_length'] 		= $orderLength;
			$data['MergeUpdate']['packet_width'] 		= $orderWidth;
			$data['MergeUpdate']['packet_height'] 		= $orderHeight;
			$data['MergeUpdate']['warehouse'] 			= $warehouse;
			$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
			$data['MergeUpdate']['country_code'] 		= $country_code;
			$data['MergeUpdate']['template_id'] 		= $templateid;
			$data['MergeUpdate']['manifest'] 			= $manifest;
			$data['MergeUpdate']['lvcr'] 				= $lvcr;
			$data['MergeUpdate']['cn_required'] 		= $cnrequired;
			$data['MergeUpdate']['postal_service'] 		= $postalServiceName;
			$data['MergeUpdate']['country_code'] 		= $countryCode;
			$data['MergeUpdate']['id'] 					= $splitOrderID;
			$this->MergeUpdate->saveAll( $data );
						
	}
	
	//setServiceUkTrack
  	public function setServiceUk( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null, $postalServiceName = null )
	{
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			
			$ref_code = 'UKE';
			if(in_array( strtolower($postalServiceName),['tracked','express'])){
				$ref_code = 'UKP';
			}
			
			$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.courier' => 'Jersey Post', 'PostalServiceDesc.provider_ref_code' => $ref_code)));
 								
			$k = 0;	
			foreach($filterResults as $filterResult)
			{
 				$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
				$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
				$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
				$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
				$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
				$k++;														
 
			}
			
			$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
			$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
			unset($perItem); unset($perkilo); unset($weightKilo);
			$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
			if($postalservicessel)
			{
				$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
				$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
				$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
				$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
				$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
				$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
				$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
				$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
				$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
				$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
				$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
				$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
				$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
				$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
				$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
				$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
				$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
			}
			$data['MergeUpdate']['service_name'] 		= $serviceName;
			$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
			$data['MergeUpdate']['service_id'] 			= $postalServiceID;
			$data['MergeUpdate']['service_provider'] 	= $postalProvider;
			$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
			$data['MergeUpdate']['packet_length'] 		= $orderLength;
			$data['MergeUpdate']['packet_width'] 		= $orderWidth;
			$data['MergeUpdate']['packet_height'] 		= $orderHeight;
			$data['MergeUpdate']['warehouse'] 			= $warehouse;
			$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
			$data['MergeUpdate']['country_code'] 		= 'GB';
			$data['MergeUpdate']['template_id'] 		= $templateid;
			$data['MergeUpdate']['manifest'] 			= $manifest;
			$data['MergeUpdate']['lvcr'] 				= $lvcr;
			$data['MergeUpdate']['cn_required'] 		= $cnrequired;
			$data['MergeUpdate']['postal_service'] 		= $postalServiceName;
			$data['MergeUpdate']['country_code'] 		= $countryCode;
			$data['MergeUpdate']['id'] 					= $splitOrderID;
			$this->MergeUpdate->saveAll( $data );
						
	}
	
	public function setServiceForEurapo( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null )
	{
		
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey','PostalServiceDesc.courier' => 'Jersey Post','PostalServiceDesc.provider_ref_code' => 'ERE' )));
			if($filterResults)
				{ 		
					$k = 0;	
					foreach($filterResults as $filterResult)
						{
							if($filterResult['ServiceLevel']['service_name'] == 'Standard')
							{
									if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
									{
										$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
										$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
										$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
										$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
										$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
										$k++;														
									}
							}																					
						}
						$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
						$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
						unset($perItem); unset($perkilo); unset($weightKilo);
						$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
						if($postalservicessel)
						{
							$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
							$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
							$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
							$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
							$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
							$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
							$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
							$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
							$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
							$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
							$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
							$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
							$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
							$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
							$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
							$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
							$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
						}
						$data['MergeUpdate']['service_name'] 		= $serviceName;
						$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
						$data['MergeUpdate']['service_id'] 			= $postalServiceID;
						$data['MergeUpdate']['service_provider'] 	= $postalProvider;
						$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
						$data['MergeUpdate']['packet_length'] 		= $orderLength;
						$data['MergeUpdate']['packet_width'] 		= $orderWidth;
						$data['MergeUpdate']['packet_height'] 		= $orderHeight;
						$data['MergeUpdate']['warehouse'] 			= $warehouse;
						$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
						$data['MergeUpdate']['template_id'] 		= $templateid;
						$data['MergeUpdate']['manifest'] 			= $manifest;
						$data['MergeUpdate']['lvcr'] 				= $lvcr;
						$data['MergeUpdate']['cn_required'] 		= $cnrequired;
						$data['MergeUpdate']['postal_service'] 		= 'Standard';
						$data['MergeUpdate']['country_code'] 		= $countryCode;
						$data['MergeUpdate']['id'] 					= $splitOrderID;
						$this->MergeUpdate->saveAll( $data );
				}
				else
				{
					$data['MergeUpdate']['service_name'] = "Over Weight";
					$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
					$data['MergeUpdate']['service_id'] = "Over Weight";
					$data['MergeUpdate']['service_provider'] = "Over Weight";
					$data['MergeUpdate']['id'] 			= $splitOrderID;
					$this->MergeUpdate->saveAll( $data );
				}
		}
	
	public function setServiceUkFlubit( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null, $postalServiceName = null )
	{
			
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 			'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','ServiceLevel.service_name ' => $postalServiceName,'PostalServiceDesc.provider_ref_code IN' => array('UKO','UKE','UKF','UKP','UST') )));
			if($filterResults)
				{ 		
					$k = 0;	
					foreach($filterResults as $filterResult)
						{
							if($filterResult['ServiceLevel']['service_name'] == 'Standard' || $filterResult['ServiceLevel']['service_name'] == 'Tracked' || $filterResult['ServiceLevel']['service_name'] == 'Express')
							{
									if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
									{
										$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
										$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
										$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
										$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
										$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
										$k++;														
									}
							}																					
						}
						$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
						$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
						unset($perItem); unset($perkilo); unset($weightKilo);
						$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
						if($postalservicessel)
						{
							$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
							$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
							$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
							$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
							$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
							$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
							$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
							$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
							$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
							$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
							$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
							$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
							$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
							$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
							$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
							$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
							$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
						}
						$data['MergeUpdate']['service_name'] 		= $serviceName;
						$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
						$data['MergeUpdate']['service_id'] 			= $postalServiceID;
						$data['MergeUpdate']['service_provider'] 	= $postalProvider;
						$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
						$data['MergeUpdate']['packet_length'] 		= $orderLength;
						$data['MergeUpdate']['packet_width'] 		= $orderWidth;
						$data['MergeUpdate']['packet_height'] 		= $orderHeight;
						$data['MergeUpdate']['warehouse'] 			= $warehouse;
						$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
						$data['MergeUpdate']['template_id'] 		= $templateid;
						$data['MergeUpdate']['manifest'] 			= $manifest;
						$data['MergeUpdate']['lvcr'] 				= $lvcr;
						$data['MergeUpdate']['cn_required'] 		= $cnrequired;
						$data['MergeUpdate']['postal_service'] 		= $postalServiceName;
						$data['MergeUpdate']['country_code'] 		= $countryCode;
						$data['MergeUpdate']['id'] 					= $splitOrderID;
						$this->MergeUpdate->saveAll( $data );
				}
				else
				{
					$data['MergeUpdate']['service_name'] = "Over Weight";
					$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
					$data['MergeUpdate']['service_id'] = "Over Weight";
					$data['MergeUpdate']['service_provider'] = "Over Weight";
					$data['MergeUpdate']['id'] 			= $splitOrderID;
					$this->MergeUpdate->saveAll( $data );
				}
		}
	public function setServiceUkOnbuy( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null, $postalServiceName = null)
	{
			
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			
			$services = ['UKO','UKE','UKF']; //Standard
			
			if($postalServiceName  == 'Express'){
				$services = ['UKP'];
			}
			
			$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 			'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','ServiceLevel.service_name ' => $postalServiceName,'PostalServiceDesc.provider_ref_code' => $services )));
			if($filterResults)
				{ 		
					$k = 0;	
					foreach($filterResults as $filterResult)
					{
							
							if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
							{
								$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
								$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
								$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
								$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
								$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
								$k++;														
							}
																												
						}
						$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
						$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
						unset($perItem); unset($perkilo); unset($weightKilo);
						$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
						if($postalservicessel)
						{
							$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
							$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
							$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
							$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
							$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
							$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
							$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
							$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
							$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
							$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
							$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
							$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
							$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
							$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
							$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
							$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
							$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
						}
						$data['MergeUpdate']['service_name'] 		= $serviceName;
						$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
						$data['MergeUpdate']['service_id'] 			= $postalServiceID;
						$data['MergeUpdate']['service_provider'] 	= $postalProvider;
						$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
						$data['MergeUpdate']['packet_length'] 		= $orderLength;
						$data['MergeUpdate']['packet_width'] 		= $orderWidth;
						$data['MergeUpdate']['packet_height'] 		= $orderHeight;
						$data['MergeUpdate']['warehouse'] 			= $warehouse;
						$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
						$data['MergeUpdate']['template_id'] 		= $templateid;
						$data['MergeUpdate']['manifest'] 			= $manifest;
						$data['MergeUpdate']['lvcr'] 				= $lvcr;
						$data['MergeUpdate']['cn_required'] 		= $cnrequired;
						$data['MergeUpdate']['postal_service'] 		= $postalServiceName;
						$data['MergeUpdate']['country_code'] 		= $countryCode;
						$data['MergeUpdate']['id'] 					= $splitOrderID;
						$this->MergeUpdate->saveAll( $data );
				}
				else
				{
					$data['MergeUpdate']['service_name'] = "Over Weight";
					$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
					$data['MergeUpdate']['service_id'] = "Over Weight";
					$data['MergeUpdate']['service_provider'] = "Over Weight";
					$data['MergeUpdate']['id'] 			= $splitOrderID;
					$this->MergeUpdate->saveAll( $data );
				 
					file_put_contents( WWW_ROOT .'logs/onbuy_services_'.date('dmy').'.log', date('d-m-y H:i:s')."\t".$destinationCountry."\t".$postalServiceName."\t".$splitOrderID."\t".$totalWeight."\t".$orderLength."\t".$orderWidth."\t".$orderHeight."\t". $postalServiceName ."\t".$ids."\t". $productOrderId ."\t".implode(";",$services)."\n", FILE_APPEND | LOCK_EX);
				}
		}
	
	public function setServiceForBelgiumItaly( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null, $postalServiceName = null )
	{
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			
			$serviceCode 	= 	 array_search($destinationCountry, $gatservicearray);
			
			$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 			'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','ServiceLevel.service_name ' => 'Standard','PostalServiceDesc.provider_ref_code IN' => array('BEO','BGE'))));
			
						if($filterResults)
						{ 		
							$k = 0;	
							foreach($filterResults as $filterResult)
								{
									if($filterResult['ServiceLevel']['service_name'] == 'Standard')
									{
											if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
											{
												$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
												$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
												$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
												$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
												$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
												$k++;														
											}
									}																					
								}
								$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
								$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
								unset($perItem); unset($perkilo); unset($weightKilo);
								$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
								if($postalservicessel)
								{
									$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
									$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
									$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
									$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
									$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
									$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
									$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
									$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
									$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
									$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
									$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
									$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
									$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
									$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
									$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
									$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
									$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
								}
								$data['MergeUpdate']['service_name'] 		= $serviceName;
								$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
								$data['MergeUpdate']['service_id'] 			= $postalServiceID;
								$data['MergeUpdate']['service_provider'] 	= $postalProvider;
								$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
								$data['MergeUpdate']['packet_length'] 		= $orderLength;
								$data['MergeUpdate']['packet_width'] 		= $orderWidth;
								$data['MergeUpdate']['packet_height'] 		= $orderHeight;
								$data['MergeUpdate']['warehouse'] 			= $warehouse;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['template_id'] 		= $templateid;
								$data['MergeUpdate']['manifest'] 			= $manifest;
								$data['MergeUpdate']['lvcr'] 				= $lvcr;
								$data['MergeUpdate']['cn_required'] 		= $cnrequired;
								$data['MergeUpdate']['postal_service'] 		= 'Standard';
								$data['MergeUpdate']['country_code'] 		= $countryCode;
								$data['MergeUpdate']['id'] 					= $splitOrderID;
								$this->MergeUpdate->saveAll( $data );
						}
						else
						{
							$data['MergeUpdate']['service_name'] = "Over Weight";
							$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
							$data['MergeUpdate']['service_id'] = "Over Weight";
							$data['MergeUpdate']['service_provider'] = "Over Weight";
							$data['MergeUpdate']['id'] 			= $splitOrderID;
							$this->MergeUpdate->saveAll( $data );
						}
	}
	
	public function setServiceForFrAndDeDir( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null,$totalCharge = 0,$sub_source = null )
	{
			
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			$isoCode = Configure::read('customIsoCodes');
			
			$gatservicearray =   array('GYE' => 'Germany','FRU' => 'France');
			$countryCode	 =	 $isoCode[$destinationCountry];
			$serviceCode 	 = 	 array_search($destinationCountry, $gatservicearray);
			
 			if($destinationCountry == 'Germany' && $sub_source == 'Costdropper' && $totalCharge >= 26){
				$filterResults	= array();
			}
			else if($destinationCountry == 'Germany' || $destinationCountry == 'France' )
			{
				$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 			'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','PostalServiceDesc.provider_ref_code' => $serviceCode )));
			} 
			else 
			{
				$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 			'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post')));
			}
			
			
			//pr($filterResults);
			if($filterResults)
			{ 		
				$k = 0;	
				foreach($filterResults as $filterResult)
					{
						if($filterResult['ServiceLevel']['service_name'] == 'Standard')
						{
								if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
								{
									$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
									$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
									$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
									$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
									$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
									$k++;														
								}
						}																					
					}
					$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
					$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
					unset($perItem); unset($perkilo); unset($weightKilo);
					$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
					if($postalservicessel)
					{
						$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
						$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
						$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
						$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
						$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
						$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
						$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
						$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
						$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
						$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
						$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
						$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
						$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
						$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
						$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
						$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
						$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
					}
					$data['MergeUpdate']['service_name'] 		= $serviceName;
					$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
					$data['MergeUpdate']['service_id'] 			= $postalServiceID;
					$data['MergeUpdate']['service_provider'] 	= $postalProvider;
					$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
					$data['MergeUpdate']['packet_length'] 		= $orderLength;
					$data['MergeUpdate']['packet_width'] 		= $orderWidth;
					$data['MergeUpdate']['packet_height'] 		= $orderHeight;
					$data['MergeUpdate']['warehouse'] 			= $warehouse;
					$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
					$data['MergeUpdate']['template_id'] 		= $templateid;
					$data['MergeUpdate']['manifest'] 			= $manifest;
					$data['MergeUpdate']['lvcr'] 				= $lvcr;
					$data['MergeUpdate']['cn_required'] 		= $cnrequired;
					$data['MergeUpdate']['postal_service'] 		= 'Standard';
					$data['MergeUpdate']['country_code'] 		= $countryCode;
					$data['MergeUpdate']['id'] 					= $splitOrderID;
					$this->MergeUpdate->saveAll( $data );
			}
			else
			{
				$data['MergeUpdate']['service_name'] = "Over Weight";
				$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
				$data['MergeUpdate']['service_id'] = "Over Weight";
				$data['MergeUpdate']['service_provider'] = "Over Weight";
				$data['MergeUpdate']['id'] 			= $splitOrderID;
				$this->MergeUpdate->saveAll( $data );
			}
		}
		//13022020 on recommendation of shashi
	public function setServiceForCdiscount( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null,$totalCharge = 0,$sub_source = null )
	{
			
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			$isoCode = Configure::read('customIsoCodes');
			
			if($destinationCountry == 'France' )
			{
				 $filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 			'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','provider_ref_code'=>['FCE', 'FRU'] )));
			}
 			
			//pr($filterResults);
			if($filterResults)
			{ 		
				$k = 0;	
				foreach($filterResults as $filterResult)
					{
						if($filterResult['ServiceLevel']['service_name'] == 'Standard')
						{
								if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
								{
									$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
									$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
									$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
									$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
									$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
									$k++;														
								}
						}																					
					}
					$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
					$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
					unset($perItem); unset($perkilo); unset($weightKilo);
					$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
					if($postalservicessel)
					{
						$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
						$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
						$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
						$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
						$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
						$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
						$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
						$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
						$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
						$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
						$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
						$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
						$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
						$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
						$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
						$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
						$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
					}
					$data['MergeUpdate']['service_name'] 		= $serviceName;
					$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
					$data['MergeUpdate']['service_id'] 			= $postalServiceID;
					$data['MergeUpdate']['service_provider'] 	= $postalProvider;
					$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
					$data['MergeUpdate']['packet_length'] 		= $orderLength;
					$data['MergeUpdate']['packet_width'] 		= $orderWidth;
					$data['MergeUpdate']['packet_height'] 		= $orderHeight;
					$data['MergeUpdate']['warehouse'] 			= $warehouse;
					$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
					$data['MergeUpdate']['template_id'] 		= $templateid;
					$data['MergeUpdate']['manifest'] 			= $manifest;
					$data['MergeUpdate']['lvcr'] 				= $lvcr;
					$data['MergeUpdate']['cn_required'] 		= $cnrequired;
					$data['MergeUpdate']['postal_service'] 		= 'Standard';
					$data['MergeUpdate']['country_code'] 		= $countryCode;
					$data['MergeUpdate']['id'] 					= $splitOrderID;
					$this->MergeUpdate->saveAll( $data );
			}
			else
			{
				$data['MergeUpdate']['service_name'] = "Over Weight";
				$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
				$data['MergeUpdate']['service_id'] = "Over Weight";
				$data['MergeUpdate']['service_provider'] = "Over Weight";
				$data['MergeUpdate']['id'] 			= $splitOrderID;
				$this->MergeUpdate->saveAll( $data );
			}
		}
 
	
	
	/*public function deliveryService( $orderId = null )
	{
		 
		$this->layout = '';
		$this->autoRender = false;
		$this->loadModel( 'OpenOrder' );
		$this->loadModel( 'MergeUpdate' );
		$orders		=	$this->MergeUpdate->find('all', array( 'conditions' => array( 'MergeUpdate.order_id' => $orderId ) , 'fields' => array( 'MergeUpdate.id' , 'MergeUpdate.order_id' , 'MergeUpdate.product_order_id_identify' ) ));
		
		foreach($orders as $order)
		{
			$this->setPostalServiceToOrder( $order['MergeUpdate']['id'], $order['MergeUpdate']['order_id'], $order['MergeUpdate']['product_order_id_identify'] );
		}
	}*/
	
	public function setPostalServiceToOrder( $splitOrderID = null, $ids = null, $productOrderId = null )
	{
		$this->loadModel( 'Product' );
		$this->loadModel( 'PostalServiceDesc' );
		$this->loadModel( 'MergeUpdate' );
		$this->loadModel( 'ServiceAssignCountry' );
		$countryArray 		= 	Configure::read('customCountry');
		$regPostCountry 	= 	array('Italy', 'France');
		$directCountry 		= 	array('Germany', 'France');
		
		/* start european country iso code*/
		$isoCode = Configure::read('customIsoCodes');
		/* end european country iso code*/
				
		App::import('controller', 'Currents');
		$currentObj	=	new CurrentsController();
		
		
		$getorderDetail		=	$this->getOpenOrderById ( $ids );
		$this->loadModel( 'OrderItem' );
		$this->loadModel( 'Product' );
		$orderItemMain 	=	$this->MergeUpdate->find('all', array('conditions' => array('MergeUpdate.order_id' => $ids, 'MergeUpdate.product_order_id_identify' => $productOrderId) , 'fields' => array( 'MergeUpdate.sku' , 'MergeUpdate.id','MergeUpdate.price','MergeUpdate.postal_service','MergeUpdate.source_coming' ) ));
		
		$totalWeight = 0; 
		foreach( $orderItemMain as $orderItem )
		{
			
			
			$sku 					=	$orderItem['MergeUpdate']['sku'];
			$margedorderId 			=	$orderItem['MergeUpdate']['id'];
			
			$skus	=	$this->getMargedSku( $margedorderId );			
			
			$length = 0;
			$width = 0;
			$height = 0;
			$hei = array();
			$i=0;
			/*-------------------Updated By Avadhesh 19-12-2016---------------*/
			$cat_name = array();
			for( $jk = 0; $jk <= count($skus)-1; $jk++ )
			{
				
				for( $j = 0; $j <= count($skus[$jk])-1; $j++ )
				{
					//pr($skus[$jk][$j]);
					$skuValue = $skus[$jk][$j][1];
					
					$orderItems 			=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $skuValue)));
					$route[] 				= 	$orderItems['Product']['route'];
					$volumetric[] 			= 	$orderItems['Product']['volumetric'];
					//$cat_name[] 			= 	$orderItems['Product']['category_name'];
					/*$boxes[$j]['length']	=	($orderItems['ProductDesc']['length'] != '') ? $orderItems['ProductDesc']['length'] : '120' ;
					$boxes[$j]['width']		=	($orderItems['ProductDesc']['width'] != '') ? $orderItems['ProductDesc']['width'] : '80';
					$boxes[$j]['height']	=	($orderItems['ProductDesc']['height'] != '') ? $orderItems['ProductDesc']['height'] : '25';*/
					
					if( $length == 0 )
					{
						$length = $orderItems['ProductDesc']['length'];
					}
					else if( $length < $orderItems['ProductDesc']['length'] )
					{
						$length = $orderItems['ProductDesc']['length'];
					}
					
					if( $width == 0 )
					{
						$width = $orderItems['ProductDesc']['width'];
					}
					else if( $width < $orderItems['ProductDesc']['width'] )
					{
						$width = $orderItems['ProductDesc']['width'];
					}
					
					
					if( $height == 0 )
					{
						$height = $orderItems['ProductDesc']['height'];
						$hei[] = $orderItems['ProductDesc']['height'];
					}
					else if( $height <= $orderItems['ProductDesc']['height'] )
					{
						$height = $orderItems['ProductDesc']['height'];
						$hei[] = $orderItems['ProductDesc']['height'];
					}
					
					if( $orderItems['ProductDesc']['weight'] == '' )
						$totalWeight = '0.500';
					else 
						$totalWeight =  $totalWeight + $orderItems['ProductDesc']['weight'];
				}
				
			}
			$i++;
		}
		
		$newdimensions['length'] = $length;
		$newdimensions['width']  = $width;
		//$newdimensions['height'] = $height;
		$newdimensions['height'] = array_sum($hei);
		
		$sourceCountry		=	'Jersey';
		$destinationCountry	=	$getorderDetail['customer_info']->Address->Country;
		$postalServiceName	=	$getorderDetail['shipping_info']->PostalServiceName;
		$subTotal			=	$getorderDetail['totals_info']->Subtotal;
		$totalCharge		=	$getorderDetail['totals_info']->TotalCharge;
		$currency			=	$getorderDetail['totals_info']->Currency;
		$postageCost		=	$getorderDetail['shipping_info']->PostageCost;
		$currency			=	$getorderDetail['totals_info']->Currency;
		$postCode			=	$getorderDetail['customer_info']->Address->PostCode;
		$sub_source			=	$getorderDetail['sub_source'];
		
		
		$value				=	 $orderItemMain[0]['MergeUpdate']['price'];
		$postal_service		=	 $orderItemMain[0]['MergeUpdate']['postal_service'];
		$orderValue			=	 $currentObj->getCurrentRate( $currency , 'EUR' , $value );
		
		$Source 			= $getorderDetail['general_info']->Source;
		$subSource 			= $getorderDetail['general_info']->SubSource;
		
		
		if($currency == 'GBP')
			$subTotal	=	$subTotal * 1.38;
		else
			$subTotal	=	$subTotal;
			
		$postalservices	=	$this->PostalServiceDesc->find('all' , array( 'conditions' => array( 'PostalServiceDesc.status' => 0 ) ));
		
		$orderLength	=	isset($newdimensions['length']) ? $newdimensions['length'] : '0';
		$orderWidth		=	isset($newdimensions['width']) ? $newdimensions['width'] : '0';
		$orderHeight	=	isset($newdimensions['height']) ? $newdimensions['height'] : '0';
		
		$this->setServiceJpForAll( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry,$totalCharge,$sub_source,$postalServiceName );
			
			$postalName = @$data['MergeUpdate']['service_provider'];
			$id = @$order['MergeUpdate']['id'];
			
			//App::import('Controller', 'Profits');
			//$ProfitsObj = new ProfitsController(); 
			//$ProfitsObj->setSkuProfitLoss( $ids );
			//Assign Packaging Slip and Label
			//$this->packingORlabelAssign( $splitOrderID , $postalName , $subSource );
			$this->assignPackagingSlipAndLabel( $ids, $splitOrderID, $subSource );
			
		}
	public function setPostalServiceToOrder_01072021( $splitOrderID = null, $ids = null, $productOrderId = null )
	{
		$this->loadModel( 'Product' );
		$this->loadModel( 'PostalServiceDesc' );
		$this->loadModel( 'MergeUpdate' );
		$this->loadModel( 'ServiceAssignCountry' );
		$countryArray 		= 	Configure::read('customCountry');
		$regPostCountry 	= 	array('Italy', 'France');
		$directCountry 		= 	array('Germany', 'France');
		
		/* start european country iso code*/
		$isoCode = Configure::read('customIsoCodes');
		/* end european country iso code*/
				
		App::import('controller', 'Currents');
		$currentObj	=	new CurrentsController();
		
		
		$getorderDetail		=	$this->getOpenOrderById ( $ids );
		$this->loadModel( 'OrderItem' );
		$this->loadModel( 'Product' );
		$orderItemMain 	=	$this->MergeUpdate->find('all', array('conditions' => array('MergeUpdate.order_id' => $ids, 'MergeUpdate.product_order_id_identify' => $productOrderId) , 'fields' => array( 'MergeUpdate.sku' , 'MergeUpdate.id','MergeUpdate.price','MergeUpdate.postal_service','MergeUpdate.source_coming' ) ));
		
		$totalWeight = 0; 
		foreach( $orderItemMain as $orderItem )
		{
			
			
			$sku 					=	$orderItem['MergeUpdate']['sku'];
			$margedorderId 			=	$orderItem['MergeUpdate']['id'];
			
			$skus	=	$this->getMargedSku( $margedorderId );			
			
			$length = 0;
			$width = 0;
			$height = 0;
			$hei = array();
			/*-------------------Updated By Avadhesh 19-12-2016---------------*/
			$cat_name = array();
			for( $jk = 0; $jk <= count($skus)-1; $jk++ )
			{
				
				for( $j = 0; $j <= count($skus[$jk])-1; $j++ )
				{
					//pr($skus[$jk][$j]);
					$skuValue = $skus[$jk][$j][1];
					
					$orderItems 			=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $skuValue)));
					$route[] 				= 	$orderItems['Product']['route'];
					$volumetric[] 			= 	$orderItems['Product']['volumetric'];
					//$cat_name[] 			= 	$orderItems['Product']['category_name'];
					/*$boxes[$j]['length']	=	($orderItems['ProductDesc']['length'] != '') ? $orderItems['ProductDesc']['length'] : '120' ;
					$boxes[$j]['width']		=	($orderItems['ProductDesc']['width'] != '') ? $orderItems['ProductDesc']['width'] : '80';
					$boxes[$j]['height']	=	($orderItems['ProductDesc']['height'] != '') ? $orderItems['ProductDesc']['height'] : '25';*/
					
					if( $length == 0 )
					{
						$length = $orderItems['ProductDesc']['length'];
					}
					else if( $length < $orderItems['ProductDesc']['length'] )
					{
						$length = $orderItems['ProductDesc']['length'];
					}
					
					if( $width == 0 )
					{
						$width = $orderItems['ProductDesc']['width'];
					}
					else if( $width < $orderItems['ProductDesc']['width'] )
					{
						$width = $orderItems['ProductDesc']['width'];
					}
					
					
					if( $height == 0 )
					{
						$height = $orderItems['ProductDesc']['height'];
						$hei[] = $orderItems['ProductDesc']['height'];
					}
					else if( $height <= $orderItems['ProductDesc']['height'] )
					{
						$height = $orderItems['ProductDesc']['height'];
						$hei[] = $orderItems['ProductDesc']['height'];
					}
					
					if( $orderItems['ProductDesc']['weight'] == '' )
						$totalWeight = '0.500';
					else 
						$totalWeight =  $totalWeight + $orderItems['ProductDesc']['weight'];
				}
				
			}
			$i++;
		}
		
		$newdimensions['length'] = $length;
		$newdimensions['width']  = $width;
		//$newdimensions['height'] = $height;
		$newdimensions['height'] = array_sum($hei);
		
		$sourceCountry		=	'Jersey';
		$destinationCountry	=	$getorderDetail['customer_info']->Address->Country;
		$postalServiceName	=	$getorderDetail['shipping_info']->PostalServiceName;
		$subTotal			=	$getorderDetail['totals_info']->Subtotal;
		$totalCharge		=	$getorderDetail['totals_info']->TotalCharge;
		$currency			=	$getorderDetail['totals_info']->Currency;
		$postageCost		=	$getorderDetail['shipping_info']->PostageCost;
		$currency			=	$getorderDetail['totals_info']->Currency;
		$postCode			=	$getorderDetail['customer_info']->Address->PostCode;
		$sub_source			=	$getorderDetail['sub_source'];
		
		
		$value				=	 $orderItemMain[0]['MergeUpdate']['price'];
		$postal_service		=	 $orderItemMain[0]['MergeUpdate']['postal_service'];
		$orderValue			=	 $currentObj->getCurrentRate( $currency , 'EUR' , $value );
		
		$Source 			= $getorderDetail['general_info']->Source;
		$subSource 			= $getorderDetail['general_info']->SubSource;
		
		
		if($currency == 'GBP')
			$subTotal	=	$subTotal * 1.38;
		else
			$subTotal	=	$subTotal;
			
		$postalservices	=	$this->PostalServiceDesc->find('all' , array( 'conditions' => array( 'PostalServiceDesc.status' => 0 ) ));
		
		$orderLength	=	isset($newdimensions['length']) ? $newdimensions['length'] : '0';
		$orderWidth		=	isset($newdimensions['width']) ? $newdimensions['width'] : '0';
		$orderHeight	=	isset($newdimensions['height']) ? $newdimensions['height'] : '0';
		//Commented on 29th DEC 2020
		/*if( in_array($destinationCountry, $directCountry) && $totalCharge < 26 && in_array( '1', $volumetric ))
		{
			$this->setServiceForFrAndDeDir( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry  );
		}
		elseif($destinationCountry == 'Germany' && $sub_source == 'Costdropper')
		{
			$this->setServiceForFrAndDeDir( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry,$totalCharge,$sub_source );
		}*/
		  
		
	/*	if( $destinationCountry == 'Belgium' && $value > 22)
		{ 
			$this->setServiceForBelgiumItaly( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry,$postalServiceName  );
		}
		*/
		if( $destinationCountry == 'United Kingdom')
		{ 
			$this->setServiceUk( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry,$postalServiceName  );
		}
		else if( $destinationCountry == 'United Kingdom' && $subSource == 'Onbuy')
		{ 
			$this->setServiceUkOnbuy( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry,$postalServiceName, $postal_service  );
		}
		else if( $destinationCountry == 'United Kingdom' && $subSource == 'Flubit')
		{ 
			$this->setServiceUkFlubit( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry,$postalServiceName  );
		}
		else if(!empty($ser_as_country) && $ser_as_country['ServiceAssignCountry']['in_list'] == 0 && $ser_as_country['ServiceAssignCountry']['postal_provider'] == 'Jersey Post' )
		{  
			$this->setServiceForEurapo( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry,$postalServiceName  );
		}else if(in_array($destinationCountry,['Martinique','Monaco','Guadaloupe','French Polynesia','French Guiana','Reunion','Mayotte']))
		{
			$this->setServicePostnl( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry,$postalServiceName  );
		}	
		/*elseif($Source == 'CDISCOUNT' || $sub_source == 'costbreaker')
		{//added on 13022020
			$this->setServiceForCdiscount( $splitOrderID, $ids, $productOrderId, $orderLength, $orderWidth, $orderHeight,$orderItemMain,$totalWeight,$destinationCountry,$totalCharge,$sub_source );
		}*/
		else
		{
		
			if( $destinationCountry != '' && $destinationCountry != 'UNKNOWN')
			{		/* for EU country  */		
				if( in_array( $destinationCountry , $countryArray ) )
				{
					$countryCode	=	 $isoCode[$destinationCountry];
					
					$exceptionPost = '';
					if( $postalServiceName == "Standard_Jpost" )
					{
						$postalServiceName = "Standard";
						$exceptionPost = "Standard_Jpost";
					}
					else
					{
						$exceptionPost = "";
					}
					
					if($destinationCountry != 'United Kingdom' )
					{
						if( $postalServiceName == 'Tracked' && in_array( $destinationCountry, $regPostCountry))
						{
							$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >= ' => $orderHeight, 'ServiceLevel.service_name ' => $postalServiceName)));
						}
						else
						{
							$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >= ' => $orderHeight)));
						}
					}
					else
					{ 	
						$dim = array($orderWidth,$orderHeight,$orderLength);
						 asort($dim);
						 $final_dim = array_values($dim) ;
					 	//file_put_contents( WWW_ROOT .'logs/uk.log', date('d-m-y H:i:s').$destinationCountry."\t".$postalServiceName."\t".$totalWeight."\t".$splitOrderID."\n", FILE_APPEND | LOCK_EX);
						
						
						//file_put_contents( WWW_ROOT .'logs/uk.log', date('d-m-y H:i:s').print_r( $final_dim,true)."\t".$splitOrderID."\n", FILE_APPEND | LOCK_EX);
						$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $final_dim[2], 'PostalServiceDesc.max_width >=' => $final_dim[1], 'PostalServiceDesc.max_height >= ' => $final_dim[0], 'ServiceLevel.service_name ' => $postalServiceName)));
						//file_put_contents( WWW_ROOT .'logs/uk.log', date('d-m-y H:i:s').print_r( $filterResults,true)."\t".$splitOrderID."\n", FILE_APPEND | LOCK_EX);	
					}
				$i = 0;
				if($filterResults)
					{
						  	   if($destinationCountry != 'United Kingdom' )
								{
										$k = 0;
										
										foreach($filterResults as $filterResult)
											{
												//pr($filterResult);
												if($filterResult['ServiceLevel']['service_name'] == 'Standard')
												{
													if( isset($exceptionPost) && $exceptionPost == "Standard_Jpost" )	
													{
														if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
														{
															$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
															$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
															$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
															$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
															$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
															$k++;														
														}
													}
													else
													{
														
														if($orderValue < 100)
														{
															
															if($filterResult['PostalServiceDesc']['courier'] == 'Belgium Post')
															{
																//echo $filterResult['Location']['county_name'] .'=='. $filterResult['ServiceLevel']['service_name']."<br>";
																$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
																$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
																$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
																$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
																$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
																$k++;
															}
														}
														else
														{
															if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
															{
																$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
																$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
																$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
																$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
																$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
																$k++;
															}
														}
													}
													
												}
												elseif( $postalServiceName == 'Tracked' && in_array( $destinationCountry, $regPostCountry) )
												{
													if($filterResult['PostalServiceDesc']['courier'] == 'Belgium Post' )
													{
															
															$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
															$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
															$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
															$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
															$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
															$k++;
													}
													
												}																						
											}
												//pr($perItem);
											$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
											
											$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
											unset($perItem);
											unset($perkilo);
											unset($weightKilo);
											//pr($id);
											
											if(count($postalservices) != 0)
											{
												$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
												if($postalservicessel)
												{
													$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
													$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
													$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
													$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
													$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
													$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
													$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
													$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
													$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
													$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
													$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
													$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
													$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
													$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
													$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
													$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
													$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
												}
											}
											$data['MergeUpdate']['service_name'] 		= $serviceName;
											$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
											$data['MergeUpdate']['service_id'] 			= $postalServiceID;
											$data['MergeUpdate']['service_provider'] 	= $postalProvider;
											$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
											$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
											$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
											$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
											$data['MergeUpdate']['warehouse'] 			= $warehouse;
											$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
											$data['MergeUpdate']['template_id'] 		= $templateid;
											$data['MergeUpdate']['manifest'] 			= $manifest;
											$data['MergeUpdate']['lvcr'] 				= $lvcr;
											$data['MergeUpdate']['cn_required'] 		= $cnrequired;
											if($exceptionPost != '')
											{
												$data['MergeUpdate']['postal_service'] 		= $exceptionPost;
											}
											elseif($postalServiceName == 'Tracked' && in_array( $destinationCountry, $regPostCountry))
											{
												$data['MergeUpdate']['postal_service'] 		= 'Tracked';
											}
											else
											{
												$data['MergeUpdate']['postal_service'] 		= 'Standard';
											}
											$data['MergeUpdate']['country_code'] 		= $countryCode;
											$data['MergeUpdate']['id'] 					= $margedorderId;
											$this->MergeUpdate->saveAll( $data );
								}
								elseif( $destinationCountry == 'United Kingdom')
								{
										//file_put_contents( WWW_ROOT .'logs/uk.log', date('d-m-y H:i:s')."\tC1\n", FILE_APPEND | LOCK_EX);
										$k = 0;
										foreach($filterResults as $filterResult)
											{
												
												if($postalServiceName == 'Standard')
												{
													//file_put_contents( WWW_ROOT .'logs/uk.log', date('d-m-y H:i:s')."\tC2\n", FILE_APPEND | LOCK_EX);
													if( isset($exceptionPost) && $exceptionPost == "Standard_Jpost" )	
													{
														if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post' && $filterResult['PostalServiceDesc']['provider_ref_code'] != 'Parcelforce')
														{
															$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
															$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
															$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
															$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
															$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
															$k++;		
															//file_put_contents( WWW_ROOT .'logs/uk.log', date('d-m-y H:i:s')."\tC3\n", FILE_APPEND | LOCK_EX);												
														}
													}
													else
													{
															if($filterResult['PostalServiceDesc']['courier'] == 'Belgium Post')
															{
																$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
																$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
																$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
																$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
																$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
																$k++;
																//file_put_contents( WWW_ROOT .'logs/uk.log', date('d-m-y H:i:s')."\tC4\n", FILE_APPEND | LOCK_EX);
															}
													}	
													
												}
												elseif( $postalServiceName == 'Tracked' || $postalServiceName == 'Express')
												{
													if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post' )
													{
															$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
															$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
															$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
															$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
															$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
															$k++;
													}
													
												}
																							
											}
											//file_put_contents( WWW_ROOT .'logs/uk.log',date('d-m-y H:i:s').print_r($postalid, true)."\tC5\n", FILE_APPEND | LOCK_EX);
											$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
											
											$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
											unset($perItem);
											unset($perkilo);
											unset($weightKilo);
											
											if(count($postalservices) != 0)
											{
												$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
												if($postalservicessel)
												{
													$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
													$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
													$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
													$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
													$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
													$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
													$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
													$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
													$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
													$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
													$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
													$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
													$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
													$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
													$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
													$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
													$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
												}
											}
								
											$data['MergeUpdate']['service_name'] 		= $serviceName;
											$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
											$data['MergeUpdate']['service_id'] 			= $postalServiceID;
											$data['MergeUpdate']['service_provider'] 	= $postalProvider;
											$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
											$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
											$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
											$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
											$data['MergeUpdate']['warehouse'] 			= $warehouse;
											$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
											$data['MergeUpdate']['template_id'] 		= $templateid;
											$data['MergeUpdate']['manifest'] 			= $manifest;
											$data['MergeUpdate']['lvcr'] 				= $lvcr;
											$data['MergeUpdate']['cn_required'] 		= $cnrequired;
											
											if( $exceptionPost != "" )
											{
												$data['MergeUpdate']['postal_service']		= $exceptionPost;
											}
											else
											{
												$data['MergeUpdate']['postal_service']		= $postalServiceName;
											}
											
											//$data['MergeUpdate']['postal_service']		= $postalServiceName;
											$data['MergeUpdate']['country_code'] 		= $countryCode;
											$data['MergeUpdate']['id'] 					= $margedorderId;										
											//file_put_contents( WWW_ROOT .'logs/uk.log', date('d-m-y H:i:s').print_r($data, true)."\n", FILE_APPEND | LOCK_EX);
											$this->MergeUpdate->saveAll( $data );
								}
						}
						else
						{
							$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
							$data['MergeUpdate']['packet_length'] 		= $orderLength;
							$data['MergeUpdate']['packet_width'] 		= $orderWidth;
							$data['MergeUpdate']['packet_height'] 		= $orderHeight;
							$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
							$data['MergeUpdate']['service_name'] = "Over Weight";
							$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
							$data['MergeUpdate']['service_id'] = "Over Weight";
							$data['MergeUpdate']['service_provider'] = "Over Weight";
							$data['MergeUpdate']['id'] 			= $margedorderId;
							$this->MergeUpdate->saveAll( $data );
						}
					}
				elseif( $destinationCountry == 'Italy')
				{
						
						$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => 'Italy', 'PostalServiceDesc.courier' => 'Jersey Post', 'ServiceLevel.service_name ' => $postalServiceName)));
						
						$exceptionPost = '';
						if($filterResults)
						{
							if( $postalServiceName == "Standard_Jpost" )
							{
								$postalServiceName = "Standard";
								$exceptionPost = "Standard_Jpost";
							}
							else
							{
								$exceptionPost = "";
							}
							$k = 0;
							foreach($filterResults as $filterResult)
								{
									if($postalServiceName == 'Standard')
									{
											$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
											$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
											$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
											$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
											$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
											$k++;
									}
									else 
									{
											$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
											$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
											$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
											$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
											$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
											$k++;
									}
								}
							
								$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
								
								$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
								unset($perItem);
								unset($perkilo);
								unset($weightKilo);
								
								if(count($postalservices) != 0)
								{
									$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
									if($postalservicessel)
									{
										$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
										$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
										$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
										$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
										$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
										$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
										$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
										$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
										$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
										$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
										$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
										$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
										$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
										$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
										$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
										$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
										$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
									}
								}
				
								$data['MergeUpdate']['service_name'] 		= $serviceName;
								$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
								$data['MergeUpdate']['service_id'] 			= $postalServiceID;
								$data['MergeUpdate']['service_provider'] 	= $postalProvider;
								$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
								$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
								$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
								$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
								$data['MergeUpdate']['warehouse'] 			= $warehouse;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['template_id'] 		= $templateid;
								$data['MergeUpdate']['manifest'] 			= $manifest;
								$data['MergeUpdate']['lvcr'] 				= $lvcr;
								$data['MergeUpdate']['cn_required'] 		= $cnrequired;
								
								if( $exceptionPost != "" )
								{
									$data['MergeUpdate']['postal_service']		= $exceptionPost;
								}
								else
								{
									$data['MergeUpdate']['postal_service']		= $postalServiceName;
								}
								$data['MergeUpdate']['country_code'] 		= $countryCode;
								$data['MergeUpdate']['id'] 					= $margedorderId;										
								$this->MergeUpdate->saveAll( $data );
							}
							else
							{
								$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
								$data['MergeUpdate']['packet_length'] 		= $orderLength;
								$data['MergeUpdate']['packet_width'] 		= $orderWidth;
								$data['MergeUpdate']['packet_height'] 		= $orderHeight;
								$data['MergeUpdate']['service_name'] 		= $serviceName;
								$data['MergeUpdate']['warehouse'] 			= $sourceCountry;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['service_name'] = "Over Weight";
								$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
								$data['MergeUpdate']['service_id'] = "Over Weight";
								$data['MergeUpdate']['service_provider'] = "Over Weight";
								$data['MergeUpdate']['id'] 			= $margedorderId;
								$this->MergeUpdate->saveAll( $data );
							}	
							
				}
				elseif( $destinationCountry == 'Spain')
				{
						//$spainRegion  	= 	$this->getCorreosRegion( $postCode, $route );
						$countryCode	=	 $isoCode[$destinationCountry];
						
						/*$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => 'Spain', 'PostalServiceDesc.courier' => 'Jersey Post', 'PostalServiceDesc.provider_ref_code' => $spainRegion)));*/
						
						/*$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => 'Spain', 'PostalServiceDesc.courier' => 'Belgium Post')));*/
						
						 $dim = array($orderWidth,$orderHeight,$orderLength);
						 asort($dim);
						 $final_dim = array_values($dim) ;
						 $filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => 'Spain', 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $final_dim[2], 'PostalServiceDesc.max_width >=' => $final_dim[1], 'PostalServiceDesc.max_height >= ' => $final_dim[0], 'ServiceLevel.service_name ' => $postalServiceName,'PostalServiceDesc.courier' => 'Belgium Post')));
						
						$exceptionPost = '';
						if($filterResults)
						{
							if( $postalServiceName == "Standard_Jpost" )
							{
								$postalServiceName = "Standard";
								$exceptionPost = "Standard_Jpost";
							}
							else
							{
								$exceptionPost = "";
							}
							$k = 0;
							foreach($filterResults as $filterResult)
								{
									if($postalServiceName == 'Standard')
									{
											$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
											$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
											$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
											$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
											$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
											$k++;
									}
								}
							
								$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
								
								$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
								unset($perItem);
								unset($perkilo);
								unset($weightKilo);
								
								if(count($postalservices) != 0)
								{
									$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
									if($postalservicessel)
									{
										$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
										$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
										$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
										$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
										$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
										$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
										$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
										$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
										$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
										$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
										$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
										$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
										$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
										$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
										$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
										$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
										$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
									}
								}
				
								$data['MergeUpdate']['service_name'] 		= $serviceName;
								$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
								$data['MergeUpdate']['service_id'] 			= $postalServiceID;
								$data['MergeUpdate']['service_provider'] 	= $postalProvider;
								$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
								$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
								$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
								$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
								$data['MergeUpdate']['warehouse'] 			= $warehouse;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['template_id'] 		= $templateid;
								$data['MergeUpdate']['manifest'] 			= $manifest;
								$data['MergeUpdate']['lvcr'] 				= $lvcr;
								$data['MergeUpdate']['cn_required'] 		= $cnrequired;
								
								if( $exceptionPost != "" )
								{
									$data['MergeUpdate']['postal_service']		= $exceptionPost;
								}
								else
								{
									$data['MergeUpdate']['postal_service']		= $postalServiceName;
								}
								$data['MergeUpdate']['country_code'] 		= $countryCode;
								$data['MergeUpdate']['id'] 					= $margedorderId;										
								$this->MergeUpdate->saveAll( $data );
							}
							else
							{
								
								$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
								$data['MergeUpdate']['packet_length'] 		= $orderLength;
								$data['MergeUpdate']['packet_width'] 		= $orderWidth;
								$data['MergeUpdate']['packet_height'] 		= $orderHeight;
								$data['MergeUpdate']['service_name'] 		= $serviceName;
								$data['MergeUpdate']['warehouse'] 			= $sourceCountry;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['service_name'] 		= "Over Weight";
								$data['MergeUpdate']['provider_ref_code'] 	= "Over Weight";
								$data['MergeUpdate']['service_id'] 			= "Over Weight";
								$data['MergeUpdate']['service_provider'] 	= "Over Weight";
								$data['MergeUpdate']['id'] 					= $margedorderId;
								$this->MergeUpdate->saveAll( $data );
							}	
							
				}
				/*elseif( $destinationCountry == 'Spain')
				{
		
						//$spainRegion  	= 	$this->getCorreosRegion( $postCode );
						
						$countryCode	=	 $isoCode[$destinationCountry];
						
						if(in_array( '1', $route ))
						{
							
							$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => $destinationCountry, 'PostalServiceDesc.courier' => 'Jersey Post', 'PostalServiceDesc.service_name' => 'International Standard Road','PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >= ' => $orderHeight, 'ServiceLevel.service_name ' => $postalServiceName)));
						} else {
							$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => $destinationCountry, 'PostalServiceDesc.courier' => 'Jersey Post', 'PostalServiceDesc.service_name' => 'International Standard','PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >= ' => $orderHeight, 'ServiceLevel.service_name ' => $postalServiceName)));
						}
						
						
						$exceptionPost = '';
						if($filterResults)
						{
							if( $postalServiceName == "Standard_Jpost" )
							{
								$postalServiceName = "Standard";
								$exceptionPost = "Standard_Jpost";
							}
							else
							{
								$exceptionPost = "";
							}
							$k = 0;
							foreach($filterResults as $filterResult)
								{
									if($postalServiceName == 'Standard')
									{
											$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
											$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
											$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
											$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
											$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
											$k++;
									}
								}
							
								$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
								
								$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
								unset($perItem);
								unset($perkilo);
								unset($weightKilo);
								
								if(count($postalservices) != 0)
								{
									$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
									if($postalservicessel)
									{
										$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
										$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
										$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
										$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
										$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
										$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
										$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
										$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
										$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
										$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
										$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
										$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
										$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
										$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
										$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
										$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
										$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
									}
								}
				
								$data['MergeUpdate']['service_name'] 		= $serviceName;
								$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
								$data['MergeUpdate']['service_id'] 			= $postalServiceID;
								$data['MergeUpdate']['service_provider'] 	= $postalProvider;
								$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
								$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
								$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
								$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
								$data['MergeUpdate']['warehouse'] 			= $warehouse;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['template_id'] 		= $templateid;
								$data['MergeUpdate']['manifest'] 			= $manifest;
								$data['MergeUpdate']['lvcr'] 				= $lvcr;
								$data['MergeUpdate']['cn_required'] 		= $cnrequired;
								
								if( $exceptionPost != "" )
								{
									$data['MergeUpdate']['postal_service']		= $exceptionPost;
								}
								else
								{
									$data['MergeUpdate']['postal_service']		= $postalServiceName;
								}
								$data['MergeUpdate']['country_code'] 		= $countryCode;
								$data['MergeUpdate']['id'] 					= $margedorderId;										
								$this->MergeUpdate->saveAll( $data );
							}
							else
							{
								$data['MergeUpdate']['service_name'] = $serviceName;
								$data['MergeUpdate']['warehouse'] 			= $sourceCountry;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['service_name'] = "Over Weight";
								$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
								$data['MergeUpdate']['service_id'] = "Over Weight";
								$data['MergeUpdate']['service_provider'] = "Over Weight";
								$data['MergeUpdate']['id'] 			= $margedorderId;
								$this->MergeUpdate->saveAll( $data );
							}	
							
				}*/
				elseif( $destinationCountry == 'United States' || $destinationCountry == 'Canada')
				{
						$countryCode	=	 $isoCode[$destinationCountry];
						
						$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => $destinationCountry, 'PostalServiceDesc.courier' => 'Jersey Post', 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >= ' => $orderHeight, 'ServiceLevel.service_name' => $postalServiceName, 'PostalServiceDesc.provider_ref_code IN' => array('UST','USE','CAO','CAS','CAE'))));
						$exceptionPost = '';
						if($filterResults)
						{
							if( $postalServiceName == "Standard_Jpost" )
							{
								$postalServiceName = "Standard";
								$exceptionPost = "Standard_Jpost";
							}
							else
							{
								$exceptionPost = "";
							}
							$k = 0;
							foreach($filterResults as $filterResult)
								{
									if($postalServiceName == 'Standard')
									{
											$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
											$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
											$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
											$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
											$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
											$k++;
									}
								}
							
								$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
								
								$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
								unset($perItem);
								unset($perkilo);
								unset($weightKilo);
								
								if(count($postalservices) != 0)
								{
									$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
									if($postalservicessel)
									{
										$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
										$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
										$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
										$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
										$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
										$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
										$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
										$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
										$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
										$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
										$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
										$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
										$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
										$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
										$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
										$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
										$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
									}
								}
				
								$data['MergeUpdate']['service_name'] 		= $serviceName;
								$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
								$data['MergeUpdate']['service_id'] 			= $postalServiceID;
								$data['MergeUpdate']['service_provider'] 	= $postalProvider;
								$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
								$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
								$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
								$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
								$data['MergeUpdate']['warehouse'] 			= $warehouse;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['template_id'] 		= $templateid;
								$data['MergeUpdate']['manifest'] 			= $manifest;
								$data['MergeUpdate']['lvcr'] 				= $lvcr;
								$data['MergeUpdate']['cn_required'] 		= $cnrequired;
								
								if( $exceptionPost != "" )
								{
									$data['MergeUpdate']['postal_service']		= $exceptionPost;
								}
								else
								{
									$data['MergeUpdate']['postal_service']		= $postalServiceName;
								}
								$data['MergeUpdate']['country_code'] 		= $countryCode;
								$data['MergeUpdate']['id'] 					= $margedorderId;										
								$this->MergeUpdate->saveAll( $data );
							}
							else
							{
								$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
								$data['MergeUpdate']['packet_length'] 		= $orderLength;
								$data['MergeUpdate']['packet_width'] 		= $orderWidth;
								$data['MergeUpdate']['packet_height'] 		= $orderHeight;
								$data['MergeUpdate']['service_name'] 		= $serviceName;
								$data['MergeUpdate']['warehouse'] 			= $sourceCountry;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['service_name'] 		= "Over Weight";
								$data['MergeUpdate']['provider_ref_code'] 	= "Over Weight";
								$data['MergeUpdate']['service_id'] 			= "Over Weight";
								$data['MergeUpdate']['service_provider'] 	= "Over Weight";
								$data['MergeUpdate']['id'] 					= $margedorderId;
								$this->MergeUpdate->saveAll( $data );
							}	
							
				}
				else
				{
					$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => 'Blended', 'PostalServiceDesc.courier' => 'Jersey Post','PostalServiceDesc.max_weight >=' => $totalWeight,'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight)));
					$i = 0;
					if($filterResults)
					{
							if(isset($filterResults) || !empty($filterResults))
							{
									foreach($filterResults as $filterResult)
									{
										$perItem[$i] 		=	$filterResult['PostalServiceDesc']['per_item'];
										$perkilo[$i] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
										$weightKilo[$i] 	=	$filterResult['PostalServiceDesc']['max_weight'];
										$postalid[$i] 		=	$filterResult['PostalServiceDesc']['id'];
										$ccyprice[$i] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
										$i++;
									}
									$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
									$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
									unset($perItem);
									unset($perkilo);
									unset($weightKilo);
							}	
							if(count($postalservices) != 0)
							{
								$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
								if($postalservicessel)
								{
									$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
									$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
									$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
									$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
									$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
									$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
									$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
									$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
									$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
									$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
									$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
									$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
									$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
									$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
									$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
									$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
									$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
								}
							}
							if( count($postalservicessel) != ''  )
							{
								
										$data['MergeUpdate']['service_name'] 		= $serviceName;
										$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
										$data['MergeUpdate']['service_id'] 			= $postalServiceID;
										$data['MergeUpdate']['service_provider'] 	= $postalProvider;
										$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
										$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
										$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
										$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
										$data['MergeUpdate']['warehouse'] 			= $warehouse;
										$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
										$data['MergeUpdate']['template_id'] 		= $templateid;
										$data['MergeUpdate']['manifest'] 			= $manifest;
										$data['MergeUpdate']['lvcr'] 				= $lvcr;
										$data['MergeUpdate']['cn_required'] 		= $cnrequired;
										if($exceptionPost != '')
										{
											$data['MergeUpdate']['postal_service'] 		= $exceptionPost;
										}
										else
										{
											$data['MergeUpdate']['postal_service'] 		= 'Standard';
										}
										
										$data['MergeUpdate']['id'] 					= $margedorderId;
										$this->MergeUpdate->saveAll( $data );
							}
						}		
						else
						{
							
										$data['MergeUpdate']['service_name'] 		= $serviceName;
										$data['MergeUpdate']['warehouse'] 			= $sourceCountry;
										$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
										$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
										$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
										$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
										$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
										$data['MergeUpdate']['provider_ref_code'] 	= "Over Weight";
										$data['MergeUpdate']['service_id'] 			= "Over Weight";
										$data['MergeUpdate']['service_provider'] 	= "Over Weight";
										$data['MergeUpdate']['id'] 					= $margedorderId;
										$this->MergeUpdate->saveAll( $data );
						}
					}
				}
				else
				{
										$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
										$data['MergeUpdate']['packet_length'] 		= $orderLength;
										$data['MergeUpdate']['packet_width'] 		= $orderWidth;
										$data['MergeUpdate']['packet_height'] 		= $orderHeight;
										$data['MergeUpdate']['service_name'] 		= $serviceName;
										$data['MergeUpdate']['warehouse'] 			= $sourceCountry;
										$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
										$data['MergeUpdate']['service_name'] = "Over Weight";
										$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
										$data['MergeUpdate']['service_id'] = "Over Weight";
										$data['MergeUpdate']['service_provider'] = "Over Weight";
										$data['MergeUpdate']['id'] 			= $margedorderId;
										$this->MergeUpdate->saveAll( $data );
					
				}
			}
			
			
			$postalName = $data['MergeUpdate']['service_provider'];
			$id = $order['MergeUpdate']['id'];
			
			//App::import('Controller', 'Profits');
			//$ProfitsObj = new ProfitsController(); 
			//$ProfitsObj->setSkuProfitLoss( $ids );
			//Assign Packaging Slip and Label
			//$this->packingORlabelAssign( $splitOrderID , $postalName , $subSource );
			$this->assignPackagingSlipAndLabel( $ids, $splitOrderID, $subSource );
			
		}
		
		public function setServiceForVolumatric( $splitOrderID = null, $ids = null, $productOrderId = null, $orderLength = null, $orderWidth = null, $orderHeight = null, $orderItemMain = null, $totalWeight = null,$destinationCountry = null )
		{
			
			$this->loadModel('PostalServiceDesc');
			$this->loadModel('MergeUpdate');
			$isoCode = Configure::read('customIsoCodes');
			
			$gatservicearray = array('DP1' => 'Germany','FR7' => 'France');
			$countryCode	=	 $isoCode[$destinationCountry];
			$serviceCode 	= 	 array_search($destinationCountry, $gatservicearray);
			if($destinationCountry == 'Germany' || $destinationCountry == 'France' )
			{
				$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 			'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post','PostalServiceDesc.provider_ref_code' => $serviceCode )));
			} else {
				$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',							'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 			'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight, 'PostalServiceDesc.courier' => 'Jersey Post')));
				   }
						if($filterResults)
						{ 		
							$k = 0;	
							foreach($filterResults as $filterResult)
								{
									if($filterResult['ServiceLevel']['service_name'] == 'Standard')
									{
											if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
											{
												$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
												$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
												$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
												$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
												$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
												$k++;														
											}
									}																					
								}
								$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
								$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
								unset($perItem); unset($perkilo); unset($weightKilo);
								$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
								if($postalservicessel)
								{
									$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
									$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
									$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
									$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
									$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
									$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
									$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
									$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
									$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
									$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
									$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
									$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
									$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
									$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
									$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
									$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
									$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
								}
								$data['MergeUpdate']['service_name'] 		= $serviceName;
								$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
								$data['MergeUpdate']['service_id'] 			= $postalServiceID;
								$data['MergeUpdate']['service_provider'] 	= $postalProvider;
								$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
								$data['MergeUpdate']['packet_length'] 		= $orderLength;
								$data['MergeUpdate']['packet_width'] 		= $orderWidth;
								$data['MergeUpdate']['packet_height'] 		= $orderHeight;
								$data['MergeUpdate']['warehouse'] 			= $warehouse;
								$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
								$data['MergeUpdate']['template_id'] 		= $templateid;
								$data['MergeUpdate']['manifest'] 			= $manifest;
								$data['MergeUpdate']['lvcr'] 				= $lvcr;
								$data['MergeUpdate']['cn_required'] 		= $cnrequired;
								$data['MergeUpdate']['postal_service'] 		= 'Standard';
								$data['MergeUpdate']['country_code'] 		= $countryCode;
								$data['MergeUpdate']['id'] 					= $splitOrderID;
								$this->MergeUpdate->saveAll( $data );
						}
						else
						{
							$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
							$data['MergeUpdate']['packet_length'] 		= $orderLength;
							$data['MergeUpdate']['packet_width'] 		= $orderWidth;
							$data['MergeUpdate']['packet_height'] 		= $orderHeight;
							$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
							$data['MergeUpdate']['service_name'] 		= "Over Weight";
							$data['MergeUpdate']['provider_ref_code'] 	= "Over Weight";
							$data['MergeUpdate']['service_id'] 			= "Over Weight";
							$data['MergeUpdate']['service_provider'] 	= "Over Weight";
							$data['MergeUpdate']['id'] 					= $splitOrderID;
							$this->MergeUpdate->saveAll( $data );
						}
		}
		
		public function getCorreosRegion( $reg = null, $route = null)
			{
					$countryCode = $reg;
					if((strlen($countryCode) < 5) ){
						$countryCode = '0'.$countryCode;
						$regionCode =  substr($countryCode, 0, 2);
					} else {
						$regionCode =  substr($countryCode, 0, 2);
					}
					
					/*$regionArray 	= 	array( '02'=> 'madrid_capital','06' => 'madrid_capital','10'=>'madrid_capital','280'=>'madrid_capital','45'=>'madrid_capital',
											   '28' => 'madrid_region','13'=>'madrid_region','16'=>'madrid_region','19'=>'madrid_region',
											   '08' => 'barcelona_region','17' => 'barcelona_region','25'=>'barcelona_region','43'=>'barcelona_region'
						
											 );*/
					if($regionCode == 28)
					{
						if( in_array( '1', $route )){
							   $region 	= 	'mad_road';} 
						else { $region 	= 	'mad_air'; }
					} 
					else if(in_array($regionCode, array('08','43','17','25') ) )
					{
						if( in_array( '1', $route )){ 
							   $region 	= 	'cat_road'; } 
						else { $region 	= 	'cat_air'; }
					}
					else
					{
						if( in_array( '1', $route )){					 
							   $region 	= 	'sp_road'; } 
						else { $region 	= 	'sp_air'; }
					
					}
				
					
					/*if( isset($regionArray[$regionCode]) && count($regionArray[$regionCode]) == 1){
						$region = $regionArray[$regionCode];
					} else {
						$region = 'rest_of_country';
					}*/
					return $region;
			}
		
		public function getCorreosRegion_160418( $reg = null )
			{
					$countryCode = $reg;
					if((strlen($countryCode) < 5) ){
						$countryCode = '0'.$countryCode;
						$regionCode =  substr($countryCode, 0, 2);
					} else {
						$regionCode =  substr($countryCode, 0, 3);
						if( $regionCode == '280' ){
								$regionCode == '280';
							} else {
								$regionCode =  substr($countryCode, 0, 2);
							}
					}
					$regionArray 	= 	array( '02'=> 'madrid_capital','06' => 'madrid_capital','10'=>'madrid_capital','280'=>'madrid_capital','45'=>'madrid_capital',
											   '28' => 'madrid_region','13'=>'madrid_region','16'=>'madrid_region','19'=>'madrid_region',
											   '08' => 'barcelona_region','17' => 'barcelona_region','25'=>'barcelona_region','43'=>'barcelona_region'
											 );
					if( isset($regionArray[$regionCode]) && count($regionArray[$regionCode]) == 1){
						$region = $regionArray[$regionCode];
					} else {
						$region = 'rest_of_country';
					}
					return $region;
			}
		
		
		
		public function assignPackagingSlipAndLabel( $ids =null, $mergeOrderID =null, $subSource=null )
		{
			$this->layout = '';
			$this->autoRender = false;
			
			$this->loadModel( 'MergeUpdate' );
			$this->loadModel( 'PackagingSlip' );
			$this->loadModel( 'Template' );
			
			
			$getorderDetail		=	$this->getOpenOrderById ( $ids );
			$subSource = $getorderDetail['sub_source'];
			$country = $getorderDetail['destination'];
			
			/**************** for tempplate *******************/
				$countryArray = Configure::read('customCountry');
				$labelDetail =	$this->Template->find('first', array(
											'conditions' => array( 
													'Template.location_name' => $country,
													'Template.store_name' => $subSource,
													)
												)
											);
				
				if( empty( $labelDetail ) )
				{
					if( in_array( $country , $countryArray ) && $country != 'United Kingdom' )
						{
							$labelDetail =	$this->Template->find('first', array(
													'conditions' => array( 
															'Template.location_name' => 'Rest Of EU',
															'Template.store_name' => $subSource,
															)
														)
													);
						}
					else
						{
							$labelDetail =	$this->Template->find('first', array(
													'conditions' => array( 
															'Template.location_name' => 'Other',
															'Template.store_name' => $subSource,
															)
														)
													);
						}
				}
				
				$getmerge = $this->MergeUpdate->find( 'first', array( 'conditions' => array( 'MergeUpdate.id' => $mergeOrderID ) ) );
			
				if( $getmerge['MergeUpdate']['service_provider'] == 'Belgium Post' )
				{			
					$data['service_provider'] 		= 	'PostNL';
					$datanew['service_provider'] 	= 	'PostNL';
				}
				else
				{
					$data['service_provider'] 			= 	$getmerge['MergeUpdate']['service_provider'];
					$datanew['service_provider'] 		= 	$getmerge['MergeUpdate']['service_provider'];
					
				}
							
				if( count( $labelDetail ) > 0 )
				{			
					$data['lable_id'] 			= 	$labelDetail['Template']['id'];
					$data['id'] 				= 	$mergeOrderID;
					$this->MergeUpdate->saveAll( $data );
				}
				
				
				$gethtml =	$this->PackagingSlip->find('first', array(
											'conditions' => array( 
													'PackagingSlip.name' => $subSource,
													)
												)
										);
				
				if( count( $gethtml ) > 0 )
				{						
					$datanew['packSlipId'] 	= 	$gethtml['PackagingSlip']['id'];
					$datanew['id'] 			= 	$mergeOrderID;
					$this->MergeUpdate->saveAll( $datanew );
				}
				
			//App::import('Controller', 'Profits');
			//$ProfitsObj = new ProfitsController(); 
			//$ProfitsObj->setSkuProfitLoss( $ids );
				
			
		}
	
		
		
		
	public function setPostalServiceToOrder_18_3_2016( $splitOrderID = null, $ids = null, $productOrderId = null )
	{
		$this->loadModel( 'Product' );
		$this->loadModel( 'PostalServiceDesc' );
		$this->loadModel( 'MergeUpdate' );
		$countryArray = Configure::read('customCountry');
		
		/* start european country iso code*/
		$isoCode = Configure::read('customIsoCodes');
		/* end european country iso code*/
				
		App::import('controller', 'Currents');
		$currentObj	=	new CurrentsController();
		
		
		$getorderDetail		=	$this->getOpenOrderById ( $ids );
		$this->loadModel( 'OrderItem' );
		$this->loadModel( 'Product' );
		$orderItemMain 	=	$this->MergeUpdate->find('all', array('conditions' => array('MergeUpdate.order_id' => $ids, 'MergeUpdate.product_order_id_identify' => $productOrderId) , 'fields' => array( 'MergeUpdate.sku' , 'MergeUpdate.id' ) ));
		
		$totalWeight = 0; 
		foreach( $orderItemMain as $orderItem )
		{
			
			
			$sku 					=	$orderItem['MergeUpdate']['sku'];
			$margedorderId 			=	$orderItem['MergeUpdate']['id'];
			
			$skus	=	$this->getMargedSku( $margedorderId );			
			
			$length = 0;
			$width = 0;
			$height = 0;
			
			for( $jk = 0; $jk <= count($skus)-1; $jk++ )
			{
				
				for( $j = 0; $j <= count($skus[$jk])-1; $j++ )
				{
					//pr($skus[$jk][$j]);
					$skuValue = $skus[$jk][$j][1];
					
					$orderItems 			=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $skuValue)));
					
					/*$boxes[$j]['length']	=	($orderItems['ProductDesc']['length'] != '') ? $orderItems['ProductDesc']['length'] : '120' ;
					$boxes[$j]['width']		=	($orderItems['ProductDesc']['width'] != '') ? $orderItems['ProductDesc']['width'] : '80';
					$boxes[$j]['height']	=	($orderItems['ProductDesc']['height'] != '') ? $orderItems['ProductDesc']['height'] : '25';*/
					
					if( $length == 0 )
					{
						$length = $orderItems['ProductDesc']['length'];
					}
					else if( $length < $orderItems['ProductDesc']['length'] )
					{
						$length = $orderItems['ProductDesc']['length'];
					}
					
					if( $width == 0 )
					{
						$width = $orderItems['ProductDesc']['width'];
					}
					else if( $width < $orderItems['ProductDesc']['width'] )
					{
						$width = $orderItems['ProductDesc']['width'];
					}
					
					
					if( $height == 0 )
					{
						$height = $orderItems['ProductDesc']['height'];
					}
					else if( $height < $orderItems['ProductDesc']['height'] )
					{
						$height = $orderItems['ProductDesc']['height'];
					}
					
					if( $orderItems['ProductDesc']['weight'] == '' )
						$totalWeight = '0.500';
					else 
						$totalWeight =  $totalWeight + $orderItems['ProductDesc']['weight'];
				}
				
			}
			$i++;
		}
		
		$newdimensions['length'] = $length;
		$newdimensions['width'] = $width;
		$newdimensions['height'] = $height;
		
		$sourceCountry		=	'Jersey';
		$destinationCountry	=	$getorderDetail['customer_info']->Address->Country;
		$postalServiceName	=	$getorderDetail['shipping_info']->PostalServiceName;
		$subTotal			=	$getorderDetail['totals_info']->Subtotal;
		$totalCharge		=	$getorderDetail['totals_info']->TotalCharge;
		$currency			=	$getorderDetail['totals_info']->Currency;
		$postageCost		=	$getorderDetail['shipping_info']->PostageCost;
		$currency			=	$getorderDetail['totals_info']->Currency;
		
		$value			=	 $orderItemMain[0]['MergeUpdate']['price'];
		$orderValue		=	$currentObj->getCurrentRate( $currency , 'EUR' , $value );
		
		$subSource = $getorderDetail['general_info']->SubSource;
		
		if($currency == 'GBP')
			$subTotal	=	$subTotal * 1.38;
		else
			$subTotal	=	$subTotal;
			
		$postalservices	=	$this->PostalServiceDesc->find('all');
		
		$orderLength	=	isset($newdimensions['length']) ? $newdimensions['length'] : '0';
		$orderWidth		=	isset($newdimensions['width']) ? $newdimensions['width'] : '0';
		$orderHeight	=	isset($newdimensions['height']) ? $newdimensions['height'] : '0';
		
		if( $destinationCountry != '' && $destinationCountry != 'UNKNOWN')
		{		/* for EU country  */		
			if( in_array( $destinationCountry , $countryArray ) )
			{
				$countryCode	=	 $isoCode[$destinationCountry];
				
				$exceptionPost = '';
				if( $postalServiceName == "Standard_Jpost" )
				{
					$postalServiceName = "Standard";
					$exceptionPost = "Standard_Jpost";
				}
				else
				{
					$exceptionPost = "";
				}
				
				if($destinationCountry != 'United Kingdom')
				{
					$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
					'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >= ' => $orderHeight)));
				}
				else
				{
					$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
					'Location.county_name' => $destinationCountry, 'PostalServiceDesc.max_weight >=' => $totalWeight, 'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >= ' => $orderHeight, 'ServiceLevel.service_name ' => $postalServiceName)));
				}
		//pr($filterResults);
		//exit;
				$i = 0;
				if($filterResults)
				{
					
			
					
					/*if( count($postalservicessel) != '' )
					{*/
								if($destinationCountry != 'United Kingdom')
								{
									$k = 0;
									
									foreach($filterResults as $filterResult)
										{
											//pr($filterResult);
											if($filterResult['ServiceLevel']['service_name'] == 'Standard')
											{
												if( isset($exceptionPost) && $exceptionPost == "Standard_Jpost" )	
												{
													if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
													{
														$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
														$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
														$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
														$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
														$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
														$k++;														
													}
												}
												else
												{
													
													if($orderValue < 60)
													{
														
														if($filterResult['PostalServiceDesc']['courier'] == 'Belgium Post')
														{
															//echo $filterResult['Location']['county_name'] .'=='. $filterResult['ServiceLevel']['service_name']."<br>";
															$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
															$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
															$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
															$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
															$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
															$k++;
														}
													}
													else
													{
														if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
														{
															$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
															$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
															$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
															$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
															$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
															$k++;
														}
													}
												}
												
											}																						
										}
											//pr($perItem);
										$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
										
										$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
										unset($perItem);
										unset($perkilo);
										unset($weightKilo);
										//pr($id);
										
										if(count($postalservices) != 0)
										{
											$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
											if($postalservicessel)
											{
												$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
												$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
												$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
												$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
												$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
												$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
												$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
												$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
												$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
												$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
												$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
												$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
												$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
												$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
												$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
												$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
												$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
											}
										}
										$data['MergeUpdate']['service_name'] 		= $serviceName;
										$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
										$data['MergeUpdate']['service_id'] 			= $postalServiceID;
										$data['MergeUpdate']['service_provider'] 	= $postalProvider;
										$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
										$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
										$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
										$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
										$data['MergeUpdate']['warehouse'] 			= $warehouse;
										$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
										$data['MergeUpdate']['template_id'] 		= $templateid;
										$data['MergeUpdate']['manifest'] 			= $manifest;
										$data['MergeUpdate']['lvcr'] 				= $lvcr;
										$data['MergeUpdate']['cn_required'] 		= $cnrequired;
										if($exceptionPost != '')
										{
											$data['MergeUpdate']['postal_service'] 		= $exceptionPost;
										}
										else
										{
											$data['MergeUpdate']['postal_service'] 		= 'Standard';
										}
										$data['MergeUpdate']['country_code'] 		= $countryCode;
										$data['MergeUpdate']['id'] 					= $margedorderId;
										$this->MergeUpdate->saveAll( $data );
							}
							elseif( $destinationCountry == 'United Kingdom')
							{
								
									$k = 0;
									foreach($filterResults as $filterResult)
										{
											
											if($postalServiceName == 'Standard')
											{
												
												if( isset($exceptionPost) && $exceptionPost == "Standard_Jpost" )	
												{
													if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
													{
														$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
														$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
														$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
														$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
														$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
														$k++;														
													}
												}
												else
												{
													if($orderValue <= 60)
													{
														
														if($filterResult['PostalServiceDesc']['courier'] == 'Belgium Post')
														{
															$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
															$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
															$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
															$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
															$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
															$k++;
														}
													}
													else
													{
														if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post')
														{
															$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
															$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
															$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
															$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
															$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
															$k++;
														}
													}
												}	
												
											}
											elseif( $postalServiceName == 'Tracked' || $postalServiceName == 'Express')
											{
												
												if($filterResult['PostalServiceDesc']['courier'] == 'Jersey Post' )
												{
														
														$perItem[$k] 		=	$filterResult['PostalServiceDesc']['per_item'];
														$perkilo[$k] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
														$weightKilo[$k] 	=	$filterResult['PostalServiceDesc']['max_weight'];
														$postalid[$k] 		=	$filterResult['PostalServiceDesc']['id'];
														$ccyprice[$k] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
														$k++;
												}
												
											}
																						
										}
										
											$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
											
											$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
											unset($perItem);
											unset($perkilo);
											unset($weightKilo);
											
											if(count($postalservices) != 0)
											{
												$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
												if($postalservicessel)
												{
													$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
													$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
													$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
													$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
													$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
													$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
													$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
													$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
													$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
													$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
													$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
													$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
													$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
													$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
													$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
													$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
													$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
												}
											}
								
										$data['MergeUpdate']['service_name'] 		= $serviceName;
										$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
										$data['MergeUpdate']['service_id'] 			= $postalServiceID;
										$data['MergeUpdate']['service_provider'] 	= $postalProvider;
										$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
										$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
										$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
										$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
										$data['MergeUpdate']['warehouse'] 			= $warehouse;
										$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
										$data['MergeUpdate']['template_id'] 		= $templateid;
										$data['MergeUpdate']['manifest'] 			= $manifest;
										$data['MergeUpdate']['lvcr'] 				= $lvcr;
										$data['MergeUpdate']['cn_required'] 		= $cnrequired;
										
										if( $exceptionPost != "" )
										{
											$data['MergeUpdate']['postal_service']		= $exceptionPost;
										}
										else
										{
											$data['MergeUpdate']['postal_service']		= $postalServiceName;
										}
										
										//$data['MergeUpdate']['postal_service']		= $postalServiceName;
										$data['MergeUpdate']['country_code'] 		= $countryCode;
										$data['MergeUpdate']['id'] 					= $margedorderId;										
										$this->MergeUpdate->saveAll( $data );
							}
						
					}
					else
					{
										$data['MergeUpdate']['service_name'] = "Over Weight";
										$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
										$data['MergeUpdate']['service_id'] = "Over Weight";
										$data['MergeUpdate']['service_provider'] = "Over Weight";
										$data['MergeUpdate']['id'] 			= $margedorderId;
										$this->MergeUpdate->saveAll( $data );
					}
				}
				
				else
				{
					/* code for out of EU country */
					$filterResults	=	$this->PostalServiceDesc->find('all', array('conditions' => array('PostalServiceDesc.warehouse' => 'Jersey',
						'Location.county_name' => 'Blended', 'PostalServiceDesc.max_weight >=' => $totalWeight,'PostalServiceDesc.max_length >=' => $orderLength, 'PostalServiceDesc.max_width >=' => $orderWidth, 'PostalServiceDesc.max_height >=' => $orderHeight)));
					$i = 0;
					if($filterResults)
					{
							if(isset($filterResults) || !empty($filterResults))
							{
									foreach($filterResults as $filterResult)
									{
										$perItem[$i] 		=	$filterResult['PostalServiceDesc']['per_item'];
										$perkilo[$i] 		=	$filterResult['PostalServiceDesc']['per_kilo'];
										$weightKilo[$i] 	=	$filterResult['PostalServiceDesc']['max_weight'];
										$postalid[$i] 		=	$filterResult['PostalServiceDesc']['id'];
										$ccyprice[$i] 		=	$filterResult['PostalServiceDesc']['ccy_prices'];
										$i++;
									}
									$getPerItem_PerKilo = $this->getAdditionOfItem_PerKilo( $perItem , $perkilo , $weightKilo, $postalid, $ccyprice );
									$id	=	array_keys($getPerItem_PerKilo, min($getPerItem_PerKilo));
									unset($perItem);
									unset($perkilo);
									unset($weightKilo);
							}	
							if(count($postalservices) != 0)
							{
								$postalservicessel 		= 	 $this->PostalServiceDesc->find('first', array('conditions' => array('PostalServiceDesc.id' => $id[0]) ));
								if($postalservicessel)
								{
									$postalServiceID		=	 $postalservicessel['PostalServiceDesc']['id'];
									$postalProvider			=	 $postalservicessel['PostalServiceDesc']['courier'];
									$providerRefCode		=	 $postalservicessel['PostalServiceDesc']['provider_ref_code'];
									$serviceLavel			=	 $postalservicessel['ServiceLevel']['service_name'];
									$serviceName			=	 $postalservicessel['PostalServiceDesc']['service_name'];
									$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
									$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
									$length					=	 $postalservicessel['PostalServiceDesc']['max_length'];
									$width					=	 $postalservicessel['PostalServiceDesc']['max_width'];
									$height					=	 $postalservicessel['PostalServiceDesc']['max_height'];
									$postalserviceID		=	 $postalservicessel['PostalServiceDesc']['id'];
									$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
									$warehouse				=	 $postalservicessel['PostalServiceDesc']['warehouse'];
									$templateid				=	 $postalservicessel['PostalServiceDesc']['template_id'];
									$manifest				=	 $postalservicessel['PostalServiceDesc']['manifest'];
									$lvcr					=	 $postalservicessel['PostalServiceDesc']['lvcr'];
									$cnrequired				=	 $postalservicessel['PostalServiceDesc']['cn_required'];
								}
							}
							if( count($postalservicessel) != ''  )
							{
								
										$data['MergeUpdate']['service_name'] 		= $serviceName;
										$data['MergeUpdate']['provider_ref_code'] 	= $providerRefCode;
										$data['MergeUpdate']['service_id'] 			= $postalServiceID;
										$data['MergeUpdate']['service_provider'] 	= $postalProvider;
										$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
										$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
										$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
										$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
										$data['MergeUpdate']['warehouse'] 			= $warehouse;
										$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
										$data['MergeUpdate']['template_id'] 		= $templateid;
										$data['MergeUpdate']['manifest'] 			= $manifest;
										$data['MergeUpdate']['lvcr'] 				= $lvcr;
										$data['MergeUpdate']['cn_required'] 		= $cnrequired;
										if($exceptionPost != '')
										{
											$data['MergeUpdate']['postal_service'] 		= $exceptionPost;
										}
										else
										{
											$data['MergeUpdate']['postal_service'] 		= 'Standard';
										}
										
										$data['MergeUpdate']['id'] 					= $margedorderId;
										$this->MergeUpdate->saveAll( $data );
							}
						}		
						else
						{
							
										$data['MergeUpdate']['service_name'] = $serviceName;
										$data['MergeUpdate']['warehouse'] 			= $sourceCountry;
										$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
										$data['MergeUpdate']['packet_weight'] 		= $totalWeight;
										$data['MergeUpdate']['packet_length'] 		= $newdimensions['length'];
										$data['MergeUpdate']['packet_width'] 		= $newdimensions['width'];
										$data['MergeUpdate']['packet_height'] 		= $newdimensions['height'];
										$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
										$data['MergeUpdate']['service_id'] = "Over Weight";
										$data['MergeUpdate']['service_provider'] = "Over Weight";
										$data['MergeUpdate']['id'] 			= $margedorderId;
										$this->MergeUpdate->saveAll( $data );
						}
					}
				}
			else
			{
										$data['MergeUpdate']['service_name'] = $serviceName;
										$data['MergeUpdate']['warehouse'] 			= $sourceCountry;
										$data['MergeUpdate']['delevery_country'] 	= $destinationCountry;
										$data['MergeUpdate']['service_name'] = "Over Weight";
										$data['MergeUpdate']['provider_ref_code'] = "Over Weight";
										$data['MergeUpdate']['service_id'] = "Over Weight";
										$data['MergeUpdate']['service_provider'] = "Over Weight";
										$data['MergeUpdate']['id'] 			= $margedorderId;
										$this->MergeUpdate->saveAll( $data );
				
			}
			
			
			$postalName = $data['MergeUpdate']['service_provider'];
			$id = $order['MergeUpdate']['id'];
			
			//Assign Packaging Slip and Label
			$this->packingORlabelAssign( $splitOrderID , $postalName , $subSource );
			
		}
	
	
		/*
		 * 
		 * Params, Assign Packaging or Label accordign rules
		 * 
		 */ 
		
		public function packingORlabelAssign( $orderId , $postalName , $subSource )
		{
			
			$this->layout = '';
			$this->autoRender = false;
			
			//Load Tables
			$this->loadModel( 'MergeUpdate' );
			$this->loadModel( 'PackagingSlip' );
			$this->loadModel( 'Template' );
			
			$id = $orderId;
			
			$serviceName = $postalName;
			$subSource = $subSource;
			
			if( $serviceName != '' || $serviceName != 'Over Weight' )
			{
				
				if( $serviceName == 'Belgium Post' )
				{
					
					//Packagin Slip
					$getSlipId = json_decode(json_encode($this->PackagingSlip->find( 'first' , array( 'conditions' => array( 'PackagingSlip.name' => $subSource ) ) )),0);
					$data['packSlipId'] = $getSlipId->PackagingSlip->id;
					$data['id'] = $id;
					$this->MergeUpdate->saveAll( $data );
					
					//Delivery Labels Id					
					$getSlipId = json_decode(json_encode($this->Template->find( 'first' , array( 'conditions' => array( 'Template.label_name' => $serviceName ) ) )),0);
					$data['lable_id'] = $getSlipId->Template->id;
					$data['id'] = $id;
					$this->MergeUpdate->saveAll( $data );
					
				}
				else if( $serviceName == 'Jersey Post' )
				{
					
					$getSlipId = json_decode(json_encode($this->PackagingSlip->find( 'first' , array( 'conditions' => array( 'PackagingSlip.name' => $subSource ) ) )),0);
					$data['packSlipId'] = $getSlipId->PackagingSlip->id;
					$data['id'] = $id;
					$this->MergeUpdate->saveAll( $data );
					
					//Delivery Labels Id					
					$getSlipId = json_decode(json_encode($this->Template->find( 'first' , array( 'conditions' => array( 'Template.label_name' => $serviceName ) ) )),0);
					$data['lable_id'] = $getSlipId->Template->id;
					$data['id'] = $id;
					$this->MergeUpdate->saveAll( $data );
					
				}
				else
				{
					//Packagin Slip
					$getSlipId = json_decode(json_encode($this->PackagingSlip->find( 'first' , array( 'conditions' => array( 'PackagingSlip.name' => $subSource ) ) )),0);
					$data['packSlipId'] = $getSlipId->PackagingSlip->id;
					$data['id'] = $id;
					$this->MergeUpdate->saveAll( $data );
					
					//Delivery Labels Id					
					$getSlipId = json_decode(json_encode($this->Template->find( 'first' , array( 'conditions' => array( 'Template.label_name' => $serviceName ) ) )),0);
					$data['lable_id'] = $getSlipId->Template->id;
					$data['id'] = $id;
					$this->MergeUpdate->saveAll( $data );	
				}
				
			}
			
		}
	
	public function getPackgingDimension( $boxes = null )
	{
				App::import('Vendor', 'phplaff/laff-pack');
				$lp = new LAFFPack();
				$lp->pack($boxes);
				$c_size = $lp->get_container_dimensions();
				$c_volume = $lp->get_container_volume();
				$c_levels = $lp->get_levels();
				
				// Collect remaining boxes details
				$r_boxes = $lp->get_remaining_boxes();
				$r_volume = $lp->get_remaining_volume();
				$r_num_boxes = 0;
				if(is_array($r_boxes)) {
					foreach($r_boxes as $level)
						$r_num_boxes += count($level);
				}

				// Collect packed boxes details
				$p_boxes = $lp->get_packed_boxes();
				$p_volume = $lp->get_packed_volume();
				$p_num_boxes = 0;
				if(is_array($p_boxes)) {
					foreach($p_boxes as $level)
						$p_num_boxes += count($level);
				};
				return $c_size;
		}
		
		
		public function getMargedSku( $rulesOrderId = null )
		{
			
			
			$this->layout = '';
			$this->autoRender = false;					
			$this->loadModel( 'MergeUpdate' );
			
			//Params
			$paramsNew = array(
				'conditions' => array(
					array(
						'MergeUpdate.id' => $rulesOrderId
					)
				)
			);
			
			//Main calculation for split orders
			$totalRows = json_decode(json_encode($this->MergeUpdate->find( 'all' , $paramsNew )),0);
			//pr($totalRows);
			//exit;
			$outerArray = array();	
			$mergeBarcodes = '';foreach( $totalRows as $totalRowsIndex => $totalRowsValue )
			{	
			
				//Now Split //2
				$explodeArray = explode( ',', $totalRowsValue->MergeUpdate->sku );
				
				//2
				$in = 0;$i = 0;while( $i <= count( $explodeArray )-1 )
				{	
					
					$inExplode = explode( '-' , $explodeArray[$i] );
					//$outerArray[$i][0] = str_replace('XS','',$inExplode[0]);					
					//$outerArray[$i][1] = 'S-'.$inExplode[1];				
					
					(int)$extQusantity = (int)str_replace('XS','',$inExplode[0]);
					$inc = 0;$kj = 0;while( $kj <= $extQusantity-1 )					
					{
						
						//Innermost Loop for each sku quantity
						$outerArray[$i][$kj][0] = $inc + 1;
						$outerArray[$i][$kj][1] = 'S-'.$inExplode[1];
						
					$kj++;	
					}
									
				$i++;
				}				
				return $outerArray;
			}
			
		}
		
		/*  code end for assign postal service */
		
		/*
  * 
  * Params, Assign custom service
  * 
  */ 
			 public function customServiceAssign()
			 {
				 $this->autoRender = false;	
				 $this->layout = '';
			   
				 $this->loadModel('MergeUpdate');
				 $this->loadModel('PostalServiceDesc');
				 $this->loadModel('Template');
				 
				 parse_str($this->request->data['id'], $searcharray);
				 parse_str($this->request->data['query'], $searcharray);
				 
				 //pr($searcharray);
				 
				 $deliveryMatrix = $this->PostalServiceDesc->find( 'all' , array( 'conditions' => array( 'PostalServiceDesc.id' => $searcharray['service_id'] ) ) );
				 if( count($deliveryMatrix) > 0 )
				 {
					   $searcharrayNew['id'] = $searcharray['id'];
					   $searcharrayNew['service_id'] = $deliveryMatrix[0]['PostalServiceDesc']['id'];
					   $searcharrayNew['service_name']  = $deliveryMatrix[0]['PostalServiceDesc']['service_name'];
					   $searcharrayNew['provider_ref_code']  = $deliveryMatrix[0]['PostalServiceDesc']['provider_ref_code'];
					   $searcharrayNew['service_provider']  = $deliveryMatrix[0]['PostalProvider']['provider_name'];
					   $searcharrayNew['postal_service']  = $deliveryMatrix[0]['ServiceLevel']['service_name'];
					   $searcharrayNew['custom_service_status']  = 1;
					   
					   //get template id accordign to service provider
					   $getTemplate = $this->Template->find( 'first' , array( 'conditions' => array( 'Template.label_name' => $deliveryMatrix[0]['PostalProvider']['provider_name'] ) ));
					   $getTemplateId = $getTemplate['Template']['id'];
					   
					   $searcharrayNew['lable_id']  = $getTemplateId; 
					   
					   $result = $this->MergeUpdate->saveAll( $searcharrayNew );
					   echo "1";
					   exit;
				}
				else
				{
				   echo "2";
				   exit;
				}
			 }
			 
			 /* save seprate sku in table   */
		public function savechannelSku( $rulesOrderId = null)
		{
			$this->loadModel( 'OpenOrder' );
			$this->loadModel( 'ChannelDetail' );
			$this->loadModel( 'Product' );
			$getallskuDetail = $this->OpenOrder->find('first', array( 'conditions' => array( 'OpenOrder.num_order_id' => $rulesOrderId ) ) );
		
			$orderDate =  $getallskuDetail['OpenOrder']['open_order_date'];//explode(' ',$getallskuDetail['OpenOrder']['open_order_date']);
			
			$data['sub_source'] 				= $getallskuDetail['OpenOrder']['sub_source'];
			$data['destination'] 				= $getallskuDetail['OpenOrder']['destination'];
			$data['general_info'] 				= unserialize( $getallskuDetail['OpenOrder']['general_info'] );
			$data['shipping_info'] 				= unserialize( $getallskuDetail['OpenOrder']['shipping_info'] );
			$data['customer_info'] 				= unserialize( $getallskuDetail['OpenOrder']['customer_info'] );
			$data['totals_info'] 				= unserialize( $getallskuDetail['OpenOrder']['totals_info'] );
			
			$skuData['currency'] 				= $data['totals_info']->Currency;
			$skuData['subTotal'] 				= $data['totals_info']->Subtotal;
			$skuData['totlaCaharge'] 			= $data['totals_info']->TotalCharge;
			$items 								= unserialize( $getallskuDetail['OpenOrder']['items'] );
			
			foreach( $items as $item )
			{
				$getProductUser = $this->Product->find('first', array( 'conditions' => array( 'Product.product_sku' => $item->SKU ), 'fields' => array( 'ProductDesc.user_id','ProductDesc.purchase_user_id' ) ) ); 
				$channelResult = $this->ChannelDetail->find('first', array( 'conditions' => array( 'ChannelDetail.sku' =>  $item->SKU , 'ChannelDetail.order_id' => $rulesOrderId) ) );
				if(count($channelResult) == 0)
				{
					$listingUser  = ($getProductUser['ProductDesc']['user_id'] != '') ? $getProductUser['ProductDesc']['user_id'] : '0' ;
					$purchaseUser = ($getProductUser['ProductDesc']['purchase_user_id'] != '') ? $getProductUser['ProductDesc']['purchase_user_id'] : '0';
					
					$channalData['sub_source'] 		= $getallskuDetail['OpenOrder']['sub_source'];
					$channalData['destination'] 	= $getallskuDetail['OpenOrder']['destination'];
					$channalData['currency'] 		= $data['totals_info']->Currency;
					$channalData['subTotal'] 		= $data['totals_info']->Subtotal;
					$channalData['totalCharge'] 	= $data['totals_info']->TotalCharge;
					$channalData['sku'] 			= $item->SKU;
					$channalData['quantity'] 		= $item->Quantity;
					$channalData['pricePerUnite'] 	= $item->PricePerUnit;
					$channalData['unitCost'] 		= $item->UnitCost;
					$channalData['cost'] 			= $item->Cost;
					$channalData['channelSKU'] 		= $item->ChannelSKU;
					$channalData['order_id'] 		= $rulesOrderId;	
					$channalData['listing_user'] 	= $listingUser;
					$channalData['purchase_user']	= $purchaseUser;
					$channalData['order_date'] 		= $orderDate;
					//pr($channalData);
					$this->ChannelDetail->saveAll( $channalData );	
				}
				
			}
		}
			 
		public function saveSeparateSku( $rulesOrderId = null )
		{
			$this->layout = '';
			$this->autoRender = false;	
			$this->loadModel( 'MergeUpdate' );
			$this->loadModel( 'ScanOrder' );
			date_default_timezone_set('Europe/Jersey');
			$params = array(
				'conditions' => array(
					'MergeUpdate.order_id' => $rulesOrderId
				)
			);
			$this->savechannelSku( $rulesOrderId );
			$getAllSplitOrders	=	$this->MergeUpdate->find('all', $params);
			//Now Split
			if( count( $getAllSplitOrders ) > 0 )
			{
				$e = 0;
			   foreach( $getAllSplitOrders as $getItem )
			   {
				   $id					=	 $getItem['MergeUpdate']['id'];
				   $splitOrderID		=	 $getItem['MergeUpdate']['product_order_id_identify'];
				   $skus	=	 explode( ',', $getItem['MergeUpdate']['sku']);
				   $barcode	=	 explode( ',', $getItem['MergeUpdate']['barcode']);
				   $i = 0;
					   foreach($skus as $sku)
					   {
						   $newSku[$i]			=	 explode( 'XS-', $sku);
						   $newSku[$i][2]		=	 $barcode[$i];
						   $searchsku	=	$this->ScanOrder->find( 'first', array('conditions' =>array('ScanOrder.split_order_id' => $splitOrderID, 'ScanOrder.barcode' =>  $newSku[$i][2])));
						   $quantity = 0;
						   $scanId = '';
						   if( count($searchsku) > 0)
						   {
							   $quantity = $searchsku['ScanOrder']['quantity'];
							   $scanId = $searchsku['ScanOrder']['id'];
						   }
						   $data['ScanOrder']['id']					=	$scanId;
						   $data['ScanOrder']['quantity']			=	$quantity + $newSku[$i][0];
						   $data['ScanOrder']['sku']				=	'S-'.$newSku[$i][1];
						   $data['ScanOrder']['barcode'] 			= 	$newSku[$i][2];
						   $data['ScanOrder']['split_order_id'] 	= 	$splitOrderID;
						   $data['ScanOrder']['order_id'] 			= 	$id;
						   $data['ScanOrder']['scan_date']    		=  date( 'Y-m-d' );
						   $this->ScanOrder->saveAll( $data );
						   $quantity = 0;
						   $i++;
					   }
				$e++;
				$k = 0;
			   }
		   }
		   else
		   {
			   echo "There is no Order";
		   }
		}
		
		/*public function removePickList()
		{
			$this->layout = '';
			$this->autoRender = false;
			
			App::uses('Folder', 'Utility');
			App::uses('File', 'Utility');
			$imgPath = WWW_ROOT .'img/printPickList/'; 
			$dir = new Folder($imgPath, true, 0755);
			$files = $dir->find('.*\.pdf');
			if(count($files) > 0)
			{
				foreach($files as $file )
				{
					unlink($imgPath.$file);
				}
			}
			else
			{
				echo "There is no pick list";
			}
		}*/
		
		public function removeCutOff()
		{
			$this->layout = '';
			$this->autoRender = false;
			
			App::uses('Folder', 'Utility');
			App::uses('File', 'Utility');
			$imgPath = WWW_ROOT .'img/cut_off/'; 
			//$dir = new Folder($imgPath, true, 0777);
			
			$dir = $imgPath;
			foreach(glob($imgPath.'*.*') as $v)
			{
				chmod($v, 0777);
				$this->recursiveRemoveDirectory($v);
			}
		}
		
		function recursiveRemoveDirectory($directory)
		{
			foreach(glob("{$directory}/*") as $file)
			{
				if(is_dir($file)) { 
					recursiveRemoveDirectory($file);
				} else {
					unlink($file);
				}
			}
			rmdir($directory);
		}
		
		
		public function storeInventoryRecord( $productData = null , $quantity = null , $orderId = null , $orderType = null , $barcode = null, $paramOption = null,$product_order_id_identify = null )
		{
			
			$this->loadModel( 'Product' );
			$this->loadModel( 'MergeUpdate' );
			$this->loadModel( 'InventoryRecord' );	
			$return = 0;
			$data['InventoryRecord']['sku'] = $productData->Product->product_sku;					
			$data['InventoryRecord']['barcode'] = $productData->ProductDesc->barcode;
			$data['InventoryRecord']['currentStock'] = $productData->Product->CurrentStock;
			$data['InventoryRecord']['quantity'] = $quantity;
			$data['InventoryRecord']['split_order_id'] = $orderId;
			$data['InventoryRecord']['channel_sku'] = $paramOption;
			
			if($orderType == 'Cancel')
			{
				//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
				//$data['InventoryRecord']['action_type'] = $orderType;
				
				//$oldStock = $productData->Product->CurrentStock - $quantity;
				//$data['InventoryRecord']['currentStock'] = $oldStock;
				$newQuantity = $productData->Product->CurrentStock + $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = $orderId.'-Order Cancelled -increase inventory by-'.$quantity;
				$data['InventoryRecord']['status'] = 'Cancel Open Order';
				
			}
			else if($orderType == 'Cancel With Location')
			{
				//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
				//$data['InventoryRecord']['action_type'] = $orderType;
				
				//$oldStock = $productData->Product->CurrentStock - $quantity;
				//$data['InventoryRecord']['currentStock'] = $oldStock;
				
				$newQuantity = $productData->Product->CurrentStock+ $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = $orderId.'-Order Cancelled -increase inventory by-'.$quantity;
				$data['InventoryRecord']['status'] = 'Cancel Open Order With Location';
			}
			else if($orderType == 'Delete')
			{
				//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
				//$data['InventoryRecord']['action_type'] = $orderType;
				
				//$oldStock = $productData->Product->CurrentStock - $quantity;
				//$data['InventoryRecord']['currentStock'] = $oldStock;
				$newQuantity = $productData->Product->CurrentStock + $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = $orderId.'-Order Deleted -increase inventory by-'.$quantity;
				$data['InventoryRecord']['status'] = 'Delete Open Order';
				
			}
			else if($orderType == 'Reserve Inventory')
			{
				$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock - $quantity;
				$data['InventoryRecord']['action_type'] = $orderId.'-Order Pament Pending -deduct inventory by-'.$quantity;
				$data['InventoryRecord']['status'] = 'Unprapered Order';
				
				/*-----------Implemented By Avadhesh 10May2017 ------------*/
				/*-----------Unpaid Orders Deduct qty from location with P.O. Name-------*/				
				$this->order_location($data['InventoryRecord'],$product_order_id_identify);	   
				/*--------------------------------------------*/
			}
			else if($orderType == 'Reserve Up')
			{
				//19-AUG-2021 
				$oldStock = $productData->Product->current_stock_level;
				$newQuantity = $oldStock + $quantity;
				$data['InventoryRecord']['currentStock'] = $oldStock;
 				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
  				$data['InventoryRecord']['action_type'] = 'Inventory incraese because order went to open order';
				$data['InventoryRecord']['status'] 		= 'Open Order';
				file_put_contents(WWW_ROOT .'logs-ord/test_del_'.date('dmY').'.log',$product_order_id_identify."\n", FILE_APPEND|LOCK_EX);
			}
			else if($orderType == 'Update Inventory')
			{
				$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock - $quantity;
				$data['InventoryRecord']['action_type'] = $orderId.'-deduct inventory by-'.$quantity;
				$data['InventoryRecord']['status'] = 'Open Order';
				/*-----------Implemented By Avadhesh 11May2017 ------------*/
				/*-----------Paid Orders Deduct qty from location with P.O. Name-------*/
				$return = $this->order_location($data['InventoryRecord'],$product_order_id_identify);	   
				
			}
			else if($orderType == 'CheckIn')
			{
				$oldStock = $productData->Product->current_stock_level - $quantity;
				$data['InventoryRecord']['currentStock'] = $oldStock;
				$newQuantity = $oldStock + $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = $orderType;
				$data['InventoryRecord']['status'] = 'Check in';
			}
			else if($orderType == 'CustomUpdate')
			{
				//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
				//$data['InventoryRecord']['action_type'] = $orderType;
			}
			else if($orderType == 'Custom Inventory Update')
			{
				if( $paramOption == 0 )
				{
					//increment
					//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
					//$data['InventoryRecord']['action_type'] = $orderType;
				}
				else if( $paramOption == 1 )
				{
					//decrement
					//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock - $quantity;
					//$data['InventoryRecord']['action_type'] = $orderType;
				}
				else{}				
			}
			else if($orderType == 'Cancel Unprapered')
			   {
				$newQuantity = $productData->Product->CurrentStock + $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = $orderId.'-Order Canceled -increase inventory by-'.$quantity;
				$data['InventoryRecord']['status'] = 'Cancel Unprapered Order';
			   }
			   else if($orderType == 'Cancel With Location After Process')
			   {
			  
				$newQuantity = $productData->Product->CurrentStock + $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = $orderId.'-Order Canceled -increase inventory by-'.$quantity;
				$data['InventoryRecord']['status'] = 'Cancel Order After Processing';
			   }
			
			$data['InventoryRecord']['date'] = date( 'Y-m-d' );
			if($return == 0){
				$this->InventoryRecord->saveAll( $data ); 
			}
			
		}
		
		
		/*public function storeInventoryRecord( $productData = null , $quantity = null , $orderId = null , $orderType = null , $barcode = null, $paramOption = null )
		{
			
			$this->loadModel( 'Product' );
			$this->loadModel( 'MergeUpdate' );
			$this->loadModel( 'InventoryRecord' );	
			
			$data['InventoryRecord']['sku'] = $productData->Product->product_sku;					
			$data['InventoryRecord']['barcode'] = $productData->ProductDesc->barcode;
			$data['InventoryRecord']['currentStock'] = $productData->Product->CurrentStock;
			$data['InventoryRecord']['quantity'] = $quantity;
			$data['InventoryRecord']['split_order_id'] = $orderId;
			if($orderType == 'Cancel')
			{
				//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
				//$data['InventoryRecord']['action_type'] = $orderType;
				
				$oldStock = $productData->Product->CurrentStock - $quantity;
				$data['InventoryRecord']['currentStock'] = $oldStock;
				$newQuantity = $oldStock + $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = 'Cancelled By User but increase inventory';
				
			}
			else if($orderType == 'Cancel With Location')
			{
				//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
				//$data['InventoryRecord']['action_type'] = $orderType;
				
				$oldStock = $productData->Product->CurrentStock - $quantity;
				$data['InventoryRecord']['currentStock'] = $oldStock;
				$newQuantity = $oldStock + $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = 'Cance order, Manage location';
				
			}
			else if($orderType == 'Delete')
			{
				//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
				//$data['InventoryRecord']['action_type'] = $orderType;
				
				$oldStock = $productData->Product->CurrentStock - $quantity;
				$data['InventoryRecord']['currentStock'] = $oldStock;
				$newQuantity = $oldStock + $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = 'Delete By User but increase inventory';
				
			}
			else if($orderType == 'Reserve Inventory')
			{
				$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock - $quantity;
				$data['InventoryRecord']['action_type'] = 'Inventory deduct for reserve purpose';
			}
			//else if($orderType == 'Reserve Up')
			//{
				//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
				//$data['InventoryRecord']['action_type'] = 'Inventory incraese because order went to open order';
			//}
			else if($orderType == 'Update Inventory')
			{
				$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock - $quantity;
				$data['InventoryRecord']['action_type'] = 'Successfull order came into open order';
			}
			else if($orderType == 'CheckIn')
			{
				$oldStock = $productData->Product->current_stock_level - $quantity;
				$data['InventoryRecord']['currentStock'] = $oldStock;
				$newQuantity = $oldStock + $quantity;
				$data['InventoryRecord']['after_maniplation'] = $newQuantity;
				$data['InventoryRecord']['action_type'] = $orderType;
			}
			else if($orderType == 'CustomUpdate')
			{
				//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
				//$data['InventoryRecord']['action_type'] = $orderType;
			}
			else if($orderType == 'Custom Inventory Update')
			{
				if( $paramOption == 0 )
				{
					//increment
					//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock + $quantity;
					//$data['InventoryRecord']['action_type'] = $orderType;
				}
				else if( $paramOption == 1 )
				{
					//decrement
					//$data['InventoryRecord']['after_maniplation'] = $productData->Product->CurrentStock - $quantity;
					//$data['InventoryRecord']['action_type'] = $orderType;
				}
				else{}				
			}
			
			$data['InventoryRecord']['date'] = date( 'Y-m-d' );
			
			$this->InventoryRecord->saveAll( $data );
			
		}*/

		//Sources List
			public function getSourceList()
			{
					
				$this->layout = 'index';
				$this->autoRender = false;
				
				App::import('Vendor', 'Linnworks/src/php/Auth');
				App::import('Vendor', 'Linnworks/src/php/Factory');
				App::import('Vendor', 'Linnworks/src/php/Orders');
				App::import('Vendor', 'Linnworks/src/php/Inventory');
				
				$this->loadModel( 'SourceList' );
				
				$username = Configure::read('linnwork_api_username');
				$password = Configure::read('linnwork_api_password');
				
				$token = Configure::read('access_new_token');
				$applicationId = Configure::read('application_id');
				$applicationSecret = Configure::read('application_secret');
				
				$multi = AuthMethods::Multilogin($username, $password);
				
				$auth = AuthMethods::AuthorizeByApplication($applicationId,$applicationSecret,$token);	

				$token = $auth->Token;	
				$server = $auth->Server;
				
				$getChannel = InventoryMethods::GetStockLocations( $token, $server );
				
				$this->SourceList->query( 'TRUNCATE source_lists' );
				//$this->SourceList->query('truncate source_lists');
				if( count( $getChannel ) > 0 )
				{					
					foreach( $getChannel as $source ):					
						$sourceData['SourceList']['StockLocationId'] = $source->StockLocationId;
						$sourceData['SourceList']['LocationName'] = $source->LocationName;
						$this->SourceList->saveAll( $sourceData );					
					endforeach;					
				}
			}			
			
			
			public function deleteUnprepare( $orders = null ) 
			{
				
				$this->layout = '';
				$this->autoRender = false;
				
				$this->loadModel( 'UnprepareOrder' );
				//Delete cancel UNKNOWN orders
				$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
							'conditions' => array(
								'UnprepareOrder.order_id' => $orders
								),									
							  'fields' => array( 
								'UnprepareOrder.order_id',
								'UnprepareOrder.num_order_id',
								'UnprepareOrder.unprepare_check',
								'UnprepareOrder.id',  
								'UnprepareOrder.items'  
							)
						)
					);
				
				foreach( $getAllUnprepardId  as $getAllId )
				{
					$itemsReserve = unserialize($getAllId['UnprepareOrder']['items']);
					$this->rs( $itemsReserve , 4 , $getAllId['UnprepareOrder']['num_order_id'] , $getAllId['UnprepareOrder']['unprepare_check'] );
					$this->UnprepareOrder->delete( $getAllId['UnprepareOrder']['id'] );
				}
				
			}
			
			//calling sych
			public function callOpenOrders()
			{				
				//die("#############");
				//Sync start
				$locationName = '';
				// App::import( 'Controller' , 'MyExceptions' );
				// $exception = new MyExceptionsController();
				// $exception->syncCalling( $locationName );
				
				$this->layout = '';
				$this->autoRender = false;
				$this->loadModel('SellbriteCron');
				
				## pull last cron execution time pull calculate time rang for cron - start 
					$id = 0;
					$lastexecutiontime = date('Y-m-d\TH:i:s\z', strtotime('-2 hour')); 	
					$counter = 500;
					$param = array(
					
						'conditions' => array(
						
							'SellbriteCron.id' => '1' 
						
						)
					
					); 
									 
					$getCronDet = json_decode(json_encode($this->SellbriteCron->find( 'all' , $param )),0);
					if( count( $getCronDet ) > 0 )
					{				
						
						foreach( $getCronDet as $crondet ):		

							$this->fetchOrdersb($crondet->SellbriteCron->id , $crondet->SellbriteCron->counter , $crondet->SellbriteCron->last_execution );					
							
						endforeach;
						
					} else {
						$this->fetchOrdersb($id , $lastexecutiontime);	
					}
				## End 			

				//$this->fetchOrdersb();			
				
			}
			
			public function fetchOrdersb($id,$counterFN,$min_ordered_atFN)
			{			

				$this->layout = '';
				$this->autoRender = false;
				$this->loadModel('OpenOrder');
				$this->loadModel('AssignService');
				$this->loadModel('Customer');
				$this->loadModel('OrderItem');
				$this->loadModel('Product');
				$this->loadModel('UnprepareOrder');
				$this->loadModel('ProChannels');
				$this->loadModel('SellbritePostalservices');
				$this->loadModel( 'Skumapping' );
				$this->loadModel( 'Countrie' );
				$this->loadModel('SellbriteCron');
				$this->loadModel('FbaLinnOrder'); 
				$this->loadModel('FbaLinnOrderItem');

				if(trim($min_ordered_at) == ''){
					$min_ordered_at = date('Y-m-d\TH:i:s\z', strtotime('-2 hour'));
				}	

				//$max_ordered_at =  date('Y-m-d\TH:i:s\z'); 

				//$max_ordered_at =  '2022-01-31T09:00:00z'; 

				#echo "<br>min_ordered_at : ".$min_ordered_at; 
				#echo "<br>max_ordered_at : ".$max_ordered_at."<br>";
				#die;
				
				//$max_ordered_at =  date('Y-m-d\TH:i:s\z');
				//$min_ordered_at =  '2022-02-02T11:47:02Z'; 
				//$max_ordered_at =  '2022-02-02T11:48:02Z';				

				$min_ordered_at =  '2022-02-04T08:54:30Z'; 

				$max_ordered_at =  '2022-02-04T08:55:30Z'; 

				$min_ordered_at =  '2022-02-11T00:00:00Z'; 

				$max_ordered_at =  '2022-02-11T23:59:59Z'; 

				#$min_ordered_at =  '2022-02-11T07:35:30Z'; 

				#$max_ordered_at =  '2022-02-11T07:38:30Z'; 


				//$min_ordered_at =  '2022-02-05T22:58:59Z'; 
				//$max_ordered_at =  '2022-02-05T22:60:59Z'; 	
				

				//##$max_ordered_at =  date('Y-m-d\TH:i:s\z'); 
				//##$min_ordered_at = $min_ordered_atFN; 	

				// echo "<br>min_ordered_at : ".$min_ordered_at; 
				// echo "<br>max_ordered_at : ".$max_ordered_at."<br>";				
				// die; 				

				###################################### Sellbrite API Integration to pull orders - start #######################
				if($counterFN == '' || $counterFN <= 0){
					$counters = 5000;
				} else {
					$counters = $counterFN;
				}
				
				## API Key from sellbrite account 
					$user_name = 'c96ef6ba-7c9c-4f46-ae1c-bccf57452e0a';
					$password = '38b4fcada6fc582c8fc9391d77301478';
				#end

				## loop is used if the date rang is big and pulled data has paggination - start	
					$finalOrderArr = array();	
					for($i=1; $i<=$counters; $i++){

						$curl = curl_init();

						curl_setopt_array($curl, [
						  CURLOPT_URL => 'https://api.sellbrite.com/v1/orders?page='.$i.'&limit=100&min_ordered_at='.urlencode($min_ordered_at).'&max_ordered_at='.urlencode($max_ordered_at).'&sb_status=open',
						  CURLOPT_RETURNTRANSFER => true,
						  CURLOPT_ENCODING => "",
						  CURLOPT_MAXREDIRS => 10,
						  CURLOPT_TIMEOUT => 30,
						  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						  CURLOPT_CUSTOMREQUEST => "GET",
						  CURLOPT_HTTPHEADER => [
						    "Accept: application/json",
						    "Authorization: Basic ". base64_encode("$user_name:$password")
						  ],
						]);

						$response = curl_exec($curl);
						$err = curl_error($curl);
						$resultArry = json_decode($response);
						curl_close($curl); 
						
						if ($err) {
						  echo "cURL Error #:" . $err;
						} else {
							if(empty($resultArry)){
								break;
							}else {
								$finalOrderArr = array_merge($finalOrderArr,$resultArry);
							}
						}		
						break;			
					} // for($i=1; $i<=$counters; $i++)		
				########### End ###########		

				//echo "<pre>finalOrderArr :: ".count($finalOrderArr); print_r($finalOrderArr);
				//die;

				#################################### End #############################################

				########### Fetch Sellbrite Postal services - start ############# 											
					$sellbritepostaldetails	=	$this->SellbritePostalservices->find('all');
					$masterSlbrtPtl = array();
					foreach($sellbritepostaldetails as $sbpsdetail)
					{						
						$indx = base64_encode($sbpsdetail['SellbritePostalservices']['name']);
						$masterSlbrtPtl[$indx] =  $sbpsdetail['SellbritePostalservices']['service_name'];
					}					
				###################### End ###########	 

				######################### Covert sellbrite array into linnwork array format ############### 
				$linnworkformatArry = array();
				$fbaordersformatArry = array();
				$finalFBAordersAry = array();
				$noskumappingflagstr = '';
				$updtecrnflg = 0;
				if(!empty($finalOrderArr)){

					foreach($finalOrderArr as $key => $valueData ){

						if($valueData->sb_order_seq == '8781'){ continue;}
						$linnvertInternal = array();

						$markerFlg = 0;
						switch($valueData->sb_status){
							case 'open' : $markerFlg = '0'; break;
							case 'completed' : $markerFlg = '1'; break;
							case 'cancelled' : $markerFlg = '2'; break;
						}
						//$markerFlg = 0;

						$statusord = 1;
						if($valueData->sb_payment_status == 'none' || $valueData->sb_payment_status == 'partial'){
							$statusord = '0';
						} else {
							$statusord = '1';
						}					

						//$linnvertInternal['NumOrderId'] = rand("000000","999999").$valueData->sb_order_seq;
						$linnvertInternal['NumOrderId'] = (10000000+$valueData->sb_order_seq);
						
						$linnvertInternal['GeneralInfo'] = array(
																	"Status" => $statusord,
																	"LabelPrinted" => '',
																	"InvoicePrinted" => '',
																	"PickListPrinted" => '',
																	"IsRuleRun" => '',
																	"Notes" => '0',
																	"PartShipped" => '',
																	"Marker" => $markerFlg,
																	"IsParked" => '',
																	"ReferenceNum" => $valueData->display_ref,
																	"SecondaryReference" => $valueData->display_ref,
																	"ExternalReferenceNum" => $valueData->display_ref,
																	"ReceivedDate" => $valueData->ordered_at,
																	"Source" => $valueData->channel_type_display_name,
																	"SubSource" => $valueData->channel_name,
																	"HoldOrCancel" => '',
																	"DespatchByDate" => $valueData->ship_by_date,
																	"HasScheduledDelivery" => '',
																	"Location" => $valueData->channel_uuid,
																	"NumItems" => 0
																);

						$postalServiceName = 'Standard';
						$indxtr = base64_encode($valueData->requested_shipping_service);
						$valpostlservice = @$masterSlbrtPtl[$indxtr];
						if(trim($valpostlservice) != ''){
							$postalServiceName = $valpostlservice;
						}
						
						$linnvertInternal['ShippingInfo'] = array(
																"Vendor" => 'other',
																"PostalServiceId" => '',
																"PostalServiceName" => $postalServiceName,
																"TotalWeight" => '',
																"ItemWeight" => '',
																"PackageCategoryId" => '00000000-0000-0000-0000-000000000000',
																"PackageCategory" => 'Default',
																"PackageTypeId" => '00000000-0000-0000-0000-000000000000',
																"PackageType" => 'Default',
																"PostageCost" => '0',
																"PostageCostExTax" => '0',
																"TrackingNumber" => '',
																"ManualAdjust" => '',
																);

						$country = $valueData->shipping_country_code;
						$currency = '';
						if($valueData->shipping_country_code != ''){

							$fetchCountryDet 	=	$this->ProChannels->find('first', array('conditions'=>array('ProChannels.country' => $valueData->shipping_country_code))); 							
							
							$fetchCountryDet = json_decode(json_encode($fetchCountryDet));
							if( count( $fetchCountryDet ) > 0 )
							{												
								foreach( $fetchCountryDet as $countryDet ):	
									$country = $valueData->shipping_country_code;
									$currency = $countryDet->currency;
								endforeach;

							}	else {
								$country = $valueData->shipping_country_code;
								$currency = '';
							}							
						}							

						$Countriedetails	= $this->Countrie->find('first', array('conditions'=>array('Countrie.iso_2' => $valueData->shipping_country_code)));

						$Countrie = $valueData->shipping_country_code;

						if(!empty($Countriedetails)){
							foreach($Countriedetails as $countriedet)
							{					
								$Countrie = $countriedet['custom_name'];
							}
						}

						if($statusord == 0){
							$Countrie = 'UNKNOWN';
						}

						$address1  = ($valueData->shipping_address_1 != '') ? $valueData->shipping_address_1 : $valueData->shipping_address_2;

						$linnvertInternal['CustomerInfo'] = array(
																"ChannelBuyerName" => $valueData->shipping_contact_name,
																"Address" => array(
																			"EmailAddress" => $valueData->billing_email,
																			"Address1" => $address1,
																			"Address2" => $valueData->shipping_address_2,
																			"Address3" => $valueData->shipping_address_3,
																			"Town" => $valueData->shipping_city,
																			"Region" => $valueData->shipping_state_region,
																			"PostCode" => $valueData->shipping_postal_code,
																			"Country" => $Countrie,
																			"FullName" => $valueData->shipping_contact_name,
																			"Company" => '',
																			"PhoneNumber" => $valueData->shipping_phone_number,
																			"CountryId" => '',
																		)
																);

						$linnvertInternal['TotalsInfo'] = array(
																"Subtotal" => $valueData->subtotal,
																"PostageCost" => 0,
																"PostageCostExTax" => 0,
																"Tax" => $valueData->tax,
																"TotalCharge" => $valueData->total,
																"PaymentMethod" => 'Default',
																"PaymentMethodId" => '00000000-0000-0000-0000-000000000000',
																"ProfitMargin" => 0,
																"TotalDiscount" => $valueData->discount,
																"Currency" => $currency,
																"CountryTaxRate" => '',
																"ConversionRate" => ''
															);

						$linnvertInternal['TaxInfo'] = array();
						$linnvertInternal['FolderName'] = array();
						$linnvertInternal['IsPostFilteredOut'] = '';
						$linnvertInternal['CanFulfil'] = '';
						$linnvertInternal['Fulfillment'] = array(
																"FulfillmentState" => ''
															);						
						$linnvertInternal['HasItems'] = count($valueData->items);
						$linnvertInternal['TotalItemsSum'] = '';
						//$linnvertInternal['OrderId'] = $valueData->display_ref;
						$linnvertInternal['OrderId'] = $valueData->sb_order_seq;						
						$linnvertInternal['RowId'] = $valueData->display_ref;
						$linnvertInternal['StockItemId'] = '';
						$linnvertInternal['location'] = $valueData->channel_name;

						if($valueData->sb_payment_status == 'none' || $valueData->sb_payment_status == 'partial'){
							$linnvertInternal['ordstus'] = '0';
						} else {
							$linnvertInternal['ordstus'] = '1';
						}

						$orderItemsRow = $valueData->items;

						## FBA Orders array format for insert - start 
						$fbaordersformatArry['num_order_id'] = (10000000+$valueData->sb_order_seq);
						$fbaordersformatArry['reference_num'] = $valueData->display_ref;
						$fbaordersformatArry['source'] = $valueData->channel_type_display_name;
						$fbaordersformatArry['sub_source'] = $valueData->channel_name;
						$fbaordersformatArry['marker'] = $markerFlg;
						$fbaordersformatArry['received_date'] = date("Y-m-d H:i:s",strtotime($valueData->ordered_at));
						$fbaordersformatArry['despatch_by_date'] = date("Y-m-d H:i:s",strtotime($valueData->ship_by_date));
						$fbaordersformatArry['postal_service_name'] = $postalServiceName;
						$fbaordersformatArry['postage_cost'] = '0.00';
						$fbaordersformatArry['tracking_number'] = '';
						$fbaordersformatArry['channel_buyer_name'] = $valueData->shipping_contact_name;
						$fbaordersformatArry['email_address'] = $valueData->billing_email;
						$fbaordersformatArry['address1'] = $valueData->shipping_address_1;
						$fbaordersformatArry['address2'] = $valueData->shipping_address_2;
						$fbaordersformatArry['address3'] = $valueData->shipping_address_3;
						$fbaordersformatArry['town'] = $valueData->shipping_city;
						$fbaordersformatArry['post_code'] = $valueData->shipping_postal_code;
						//$fbaordersformatArry['country_codexyz'] = $valueData->shipping_country_code;
						$fbaordersformatArry['country'] = $Countrie;
						$fbaordersformatArry['company'] = '';
						$fbaordersformatArry['phone_number'] = $valueData->shipping_phone_number;
						$fbaordersformatArry['subtotal'] = $valueData->subtotal;
						$fbaordersformatArry['postage_cost_total'] = '0.00';
						$fbaordersformatArry['tax_total'] = $valueData->tax;
						$fbaordersformatArry['total_charge'] = $valueData->total;
						$fbaordersformatArry['currency'] = $currency;
						$fbaordersformatArry['status'] = $statusord;
						$fbaordersformatArry['order_id'] = $valueData->sb_order_seq;
						$fbaordersformatArry['added_date'] = date("Y-m-d H:i:s");					

						$orderItems = array();
						$fbaorderItems = array();
						if(!empty($orderItemsRow)){

							$i = 0 ;
							$orderFBAFlg = 0;
							$noskumappingflag = 0;
							
							foreach($orderItemsRow as $keyRw => $valueItemRw){ 

								## fetch correct SKU using channel SKU - start 
									$skuIdArr = explode( '-', trim($valueItemRw->sku) );									
									if(!empty($skuIdArr)){
										$skuId = trim($skuIdArr[0]);
									} else {
										$skuId = trim($valueItemRw->sku);
									}

									$Skumappingdetails	= $this->Skumapping->find('first', array('conditions'=>array('Skumapping.channel_sku' => $skuId)));									

									$productSKU = $valueItemRw->sku;	

									//echo "<pre>"; print_r($Skumappingdetails); die;

									if(!empty($Skumappingdetails)){
										foreach($Skumappingdetails as $skunamedet)
										{					
											$productSKU = $skunamedet['sku'];	
											if($skunamedet['channel_type'] == 'fba'){
												$orderFBAFlg = 1;
											} else {
												$orderFBAFlg = 0;
											}
										}
									}  else {

										$skuId = trim($valueItemRw->sku);

										$Skumappingdetails	= $this->Skumapping->find('first', array('conditions'=>array('Skumapping.channel_sku' => $skuId)));

										$productSKU = $valueItemRw->sku;

										if(!empty($Skumappingdetails)){

											foreach($Skumappingdetails as $skunamedet)
											{					
												$productSKU = $skunamedet['sku'];	
												if($skunamedet['channel_type'] == 'fba'){
													$orderFBAFlg = 1;
												} else {
													$orderFBAFlg = 0;
												}
											}
											
										} else {

											## if SKU is not found in SKU mapping table then skip order processing and send email for the same 
												echo "<br> Order Num Id : ".(10000000+$valueData->sb_order_seq)." || Order Id :".$valueData->sb_order_seq." || SKU :".$valueItemRw->sku;	

												$noskumappingflag = 1;	
												

												$msg = 'Hi,<br><br> Please check below SKU Details Which is missing in SKU mapping : <br>';
												$msg .= "<br> Order Num Id : ".(10000000+$valueData->sb_order_seq)." 
												<br> Order Id : ".$valueData->sb_order_seq." <br> SKU : ".$valueItemRw->sku;
												
												$to = 'bhushan.deshmukh@euracogroup.co.uk';												
												$subject = 'SKU not found in SKU-Mapping';													
												$headers  = 'MIME-Version: 1.0' . "\r\n";
												$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

												// Mail it
												if( stristr( $noskumappingflagstr , '"'.$valueItemRw->sku.'"' ) == false ) {
													//echo "<br> send :: ".mail($to, $subject, $msg, $headers);
												}
												
												$noskumappingflagstr .= '"'.$valueItemRw->sku.'" | '; 
												//echo "<br> ".$noskumappingflagstr;
												$updtecrnflg = 1;
											# end	
										}
										//echo "<pre> else Skumappingdetails :: ".$skuId; print_r($Skumappingdetails); //die;
									}
								## end 

								//echo "<br>display_ref : ".$valueData->display_ref." | productSKU :".$skuId.": ".$valueItemRw->sku." == ".$productSKU;	

								$orderItems[$i]['ItemId'] = $valueItemRw->order_item_ref;
								$orderItems[$i]['ItemNumber'] = $valueItemRw->order_item_ref;
								$orderItems[$i]['SKU'] = $productSKU;
								$orderItems[$i]['ItemSource'] = $valueData->channel_type_display_name;
								$orderItems[$i]['Title'] = $valueItemRw->title;
								$orderItems[$i]['Quantity'] = $valueItemRw->quantity;
								$orderItems[$i]['CategoryName'] = 'Default';
								$orderItems[$i]['StockLevelsSpecified'] = '';
								$orderItems[$i]['OnOrder'] = 0; 
								$orderItems[$i]['InOrderBook'] = 0;
								$orderItems[$i]['Level'] = '';
								$orderItems[$i]['MinimumLevel'] = '';
								$orderItems[$i]['AvailableStock'] = '';
								$orderItems[$i]['PricePerUnit'] = $valueItemRw->unit_price;
								$orderItems[$i]['UnitCost'] = $valueItemRw->unit_price;
								$orderItems[$i]['DespatchStockUnitCost'] = '';
								$orderItems[$i]['Discount'] = 0;
								$orderItems[$i]['Tax'] = $valueItemRw->tax;
								$orderItems[$i]['TaxRate'] = '';
								$orderItems[$i]['Cost'] = ($valueItemRw->total - $valueItemRw->tax);
								$orderItems[$i]['CostIncTax'] = $valueItemRw->total;
								$orderItems[$i]['CompositeSubItems'] = array();
								$orderItems[$i]['IsService'] = '';
								$orderItems[$i]['SalesTax'] = $valueItemRw->tax;
								$orderItems[$i]['TaxCostInclusive'] = '';
								$orderItems[$i]['PartShipped'] = '';
								$orderItems[$i]['Weight'] = '';
								$orderItems[$i]['BarcodeNumber'] = '';
								$orderItems[$i]['Market'] = 0;
								$orderItems[$i]['ChannelSKU'] = $valueItemRw->sku;
								$orderItems[$i]['ChannelTitle'] = $valueItemRw->title;
								$orderItems[$i]['DiscountValue'] = 0;
								$orderItems[$i]['HasImage'] = '';
								$orderItems[$i]['AdditionalInfo'] = array();
								$orderItems[$i]['StockLevelIndicator'] = '';
								$orderItems[$i]['ShippingCost'] = 0;
								$orderItems[$i]['PartShippedQty'] = 0;
								$orderItems[$i]['BatchNumberScanRequired'] = '';
								$orderItems[$i]['SerialNumberScanRequired'] = '';
								$orderItems[$i]['InventoryTrackingType'] = '';
								$orderItems[$i]['isBatchedStockItem'] = '';
								$orderItems[$i]['IsWarehouseManaged'] = '';
								$orderItems[$i]['IsUnlinked'] = '';
								$orderItems[$i]['StockItemIntId'] = '';
								$orderItems[$i]['RowId'] = $valueData->display_ref;
								$orderItems[$i]['OrderId'] = $valueData->display_ref;
								$orderItems[$i]['StockItemId'] = ''; 

								//$orderItems[$i]['order_inc_id'] = $valueItemRw->order_item_ref;
								$fbaorderItems[$i]['num_order_id'] = (10000000+$valueData->sb_order_seq);;
								$fbaorderItems[$i]['item_number'] = $valueItemRw->order_item_ref;
								$fbaorderItems[$i]['item_source'] = $valueData->channel_type_display_name;
								$fbaorderItems[$i]['master_sku'] = '';
								$fbaorderItems[$i]['channel_sku'] = $valueItemRw->sku;
								$fbaorderItems[$i]['title'] = $valueItemRw->title;
								$fbaorderItems[$i]['quantity'] = $valueItemRw->quantity;
								$fbaorderItems[$i]['price_per_unit'] = $valueItemRw->unit_price;
								$fbaorderItems[$i]['tax'] = $valueItemRw->tax;
								$fbaorderItems[$i]['tax_rate'] = '';
								$fbaorderItems[$i]['cost'] = ($valueItemRw->total - $valueItemRw->tax);
								$fbaorderItems[$i]['cost_inc_tax'] = $valueItemRw->total;
								$fbaorderItems[$i]['sales_tax'] = $valueItemRw->tax;
								$fbaorderItems[$i]['channel_title'] = $valueItemRw->title;
								$fbaorderItems[$i]['added_date'] = date("Y-m-d H:i:s");	
							}
						}

						$linnvertInternal['Items'] = $orderItems;	
						$fbaordersformatArry['Items'] = $fbaorderItems;	
						
						$fbaordersformatArry['linnworkarry'] = $linnvertInternal;				

						## this condition is added - if SKY mapping is not found then we need skip order processing 
						if($noskumappingflag == 0){
							if($orderFBAFlg == 1){
								$fbaordersformatArry['fbastatus'] = $orderFBAFlg;
								$finalFBAordersAry[] = $fbaordersformatArry;							
								
							} else {
								$linnvertInternal['fbastatus'] = $orderFBAFlg;
								$linnworkformatArry[] = $linnvertInternal;
								
							}	
						}
						## end 
					} // for
				} // if				

				//echo "<pre>fbaorderItems :: "; print_r($fbaorderItems);	
				//echo "<pre>fbaordersformatArry :: "; print_r($fbaordersformatArry);	

				//echo "<pre>finalFBAordersAry :: ".count($finalFBAordersAry); print_r($finalFBAordersAry);
				//echo "<pre>linnworkformatArry :: ".count($linnworkformatArry); print_r($linnworkformatArry);
				//die;

				######################################## End ############################################## 
				
				//echo "<pre>finalFBAordersAry :: "; print_r($finalFBAordersAry);
				//die;
				if(!empty($finalFBAordersAry)){ 
					
					echo "<br>##################### FBA Orders process ##############";

					foreach($finalFBAordersAry as $fbaorderDets){

							echo "<br>start - @@@@@@@@@@@@@@@";
							//echo "<pre>fbaorderDets :: "; print_r($fbaorderDets);
							//die;							
							$statusord = $fbaorderDets['status'];
							$orderFBAFlg = $fbaorderDets['fbastatus'];
							
							#echo "<br>statusord : ".$statusord;
							#echo "<br>orderFBAFlg : ".$orderFBAFlg;
							//die;
							
							## code for FBA and Paid Orders 
							if($orderFBAFlg == 1 && $statusord == 1){	
								echo "<br> 1-1 - s";					
								if(!empty($fbaorderDets)){
									$numid = $fbaorderDets['num_order_id'];

									echo "<br>numid : ".$numid;

									$check = $this->FbaLinnOrder->find('first', array('conditions'=>array('num_order_id' => $numid)));
									echo "<pre>check :: ".count($check); print_r($check);	
									if(!empty($fbaordersformatArry)){
										
										$numid = $fbaorderDets['num_order_id']; 										
										$fbaorderItems = $fbaorderDets['Items'];
										$fbastatus = $fbaorderDets['fbastatus'];

										unset($fbaorderDets['linnworkarry']);										
										unset($fbaorderDets['Items']);
										unset($fbaorderDets['fbastatus']);
										echo "<br>numid : ".$numid;

										//echo "<pre>fbaorderDets :: "; print_r($fbaorderDets); 
										//echo "<pre>fbaorderItems :: "; print_r($fbaorderItems);
										//die;
										
										$check = $this->FbaLinnOrder->find('first', array('conditions'=>array('num_order_id' => $numid)));
										//echo "<pre>check :: ".count($check); print_r($check);
										echo "<pre>check :: ".count($fbaorderssave); print_r($fbaorderssave);
										//die;

										if(count($check) == 0){
											$this->FbaLinnOrder->saveAll($fbaorderDets);
											$order_inc_id	=	$this->FbaLinnOrder->getLastInsertId();
											##$order_inc_id = '382'; = 
											$fbaorderItems = $fbaorderItems;
											foreach($fbaorderItems as $itemData){
												$itemData['order_inc_id'] = $order_inc_id;
											 	echo "<pre>item :: ".$order_inc_id; print_r($itemData);	
											 	$this->FbaLinnOrderItem->saveAll($itemData);
											}
											$removeIds[] = $numid;
										}	else {
											//echo "<br> else ".$numid;
										}	
									} 		
								}
								echo "<br> 1-1-E";								
							} else if($orderFBAFlg == 1 && $statusord == 0){
									
								echo "<br> 1-0-S";	
								## code for FBA and pending orders process 	
								$unpreArryrow = $fbaorderDets['linnworkarry'];
								if(!empty($unpreArryrow)){	
									$result = json_decode(json_encode($unpreArryrow));
									//echo "<pre>result :: ".count($result); print_r($result); die;
									if(!empty($result)){
										## insert into unprepareorder for FBA orders - start 
											$getAllUnprepardId = $this->UnprepareOrder->find('first', array('conditions'=>array('UnprepareOrder.order_id' => $result->OrderId) , 'fields' => array( 'UnprepareOrder.order_id')));
											
											##echo "<pre>getAllUnprepardId :: "; print_r($getAllUnprepardId);
											//echo "<pre>getAllUnprepardId :: "; print_r($result); //die;

											if( count($getAllUnprepardId) == 0)
											{	
												$data = array();
												$data['order_id']		= $result->OrderId;
												$data['num_order_id']	= $result->NumOrderId;
												$data['general_info']	= serialize($result->GeneralInfo);
												$data['shipping_info']	= serialize($result->ShippingInfo);
												$data['customer_info']	= serialize($result->CustomerInfo);
												$data['totals_info']	= serialize($result->TotalsInfo);
												$data['folder_name']	= serialize($result->FolderName);
												$data['items']			= serialize($result->Items);
												$data['destination'] = 	$result->CustomerInfo->Address->Country;
												$data['date']			= date('Y-m-d H:i:s');
												$data['unprepare_check']= 1;
												$data['source_name']= $result->GeneralInfo->SubSource;											
												echo "<pre>data :: "; print_r($data); //die;	
												$this->UnprepareOrder->create();											
												$save = $this->UnprepareOrder->save($data);
												echo "<pre>save :: "; print_r($save);
											}
										## End 
									}
								}	
								echo "<br> 1-0-E";								
							}	

							echo "<br>End - @@@@@@@@@@@@@@@";						
					}
				}
				
				//die("<br> Wait here");
				echo "<br>##################### Merchant ORders process ##############";
				## process merchant Orders only - start
				if(!empty($linnworkformatArry)){

 					//die("######################");
 					$linnworkformatArry = json_decode(json_encode($linnworkformatArry));
					if(!empty($linnworkformatArry)){
						$openorder = $linnworkformatArry;
					}

					App::import('Controller', 'Virtuals');
					$virtualModel = new VirtualsController(); 
					//$virtualModel->creatFeedSheet_old( $locationName );

					$unPaid = [];	
					if(count( $openorder) > 0){
					
						//$log_file = WWW_ROOT .'cron-logs/'.$locationName.".log";	
						//@unlink($log_file);
					
						foreach($openorder as $orderidsArr)
						{ 							 
								
							$orderids = $orderidsArr->OrderId;
							//file_put_contents($log_file, $orderids."\t".date('Y-m-d H:i:s')."\n",  FILE_APPEND|LOCK_EX);
							//echo $orderids. "otderID <br>"; die; 						
							//$orders[]	=	$orderids->OrderId;

							$result = $orderids;
							//exit;
							$checkOpenOrder 	=	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $orderids)));
							//echo $checkOpenOrder."one <br>"; die;
							//pr($checkOpenOrder);
							//echo "<pre>checkOpenOrder :: "; print_r($checkOpenOrder);	
							if( count($checkOpenOrder) == 0 )
							{
								//echo 'Insert >> '.$orderids->OrderId."<br>";
								$orders[]	=	$orderids; 								
							}
							else
							{
								//Clean Orders
								$this->cleanOrders();
								$unPaid[]	=	$orderids;
								//CHECK IF ORDER EXISTS OR NOT
								// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
								//$order_status = OrdersMethods::GetOrders($unPaid,$location,true,true,$token, $server);
								//pr($order_status);
								//$results = $order_status[0];
								$dataUpdate['OpenOrder']['id'] = $checkOpenOrder['OpenOrder']['id'];
								$dataUpdate['OpenOrder']['linn_fetch_orders'] = '1';//$results->GeneralInfo->Status;	
								//echo $NumOrderid.' Status-> '.$results->GeneralInfo->Status.'<br>';
								
								//$log_file = WWW_ROOT .'cron-logs/'.$locationName."_unpaid.log";		 
								//file_put_contents($log_file, print_r($order_status,true));	
					
					
								//exit;						
								//$dataUpdate['OpenOrder']['linn_fetch_orders'] = serialize($result->CustomerInfo);
								//echo "<pre>dataUpdate :: "; print_r($dataUpdate);	 die;
								$this->OpenOrder->saveAll( $dataUpdate ); 
								//Now update into Merge Section
								$ordStatus  = '1';//$results->GeneralInfo->Status;	
								$NumOrderid = $checkOpenOrder['OpenOrder']['num_order_id'];
								//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
								$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $ordStatus), array('MergeUpdate.order_id' => $NumOrderid) );
								unset( $unPaid ); 
							
							} 						
							//$orders[]	=	$orderids->OrderId;
						}

						#echo "<pre>orders :: "; print_r($orders);						
						#die; 						
										
						$result = '';
						unset( $checkOpenOrder );
						
						//$orders[]	=	'63bac14d-dcca-4322-9a0a-d311c558144c';
						
						if( !empty( $orders ) && count( $orders ) > 0 )
						{																
							$this->saveOpenOrder( $linnworkformatArry , "sellbrite" , $orders );	
							//$log_file = WWW_ROOT .'cron-logs/ord_'.$locationName."_".date('Ymd').".log";		 
							//file_put_contents($log_file, print_r($results,true), FILE_APPEND | LOCK_EX);			
							
						}

					}else{
						echo 'no open orders';				
					}
				}
				## End 
				
				if($updtecrnflg == 0){
					// last_execution datetime after whole process completed for cron 
						$this->SellbriteCron->updateAll( array('SellbriteCron.last_execution' => "'".date('Y-m-d\TH:i:s\z')."'"), array('SellbriteCron.id' => $id));
					//End 
				}	

				// $log_file = WWW_ROOT .'cron-logs/crons_'.date('Ymd').".log";	
				// file_put_contents($log_file, $locationName."\t".date('Y-m-d H:i:s')."\n",  FILE_APPEND | LOCK_EX);	
				// $this->updateIoss($token, $server);
				
				exit; 
			}

			public function callcheckOpenOrders()
			{				
				//die("#############");
				//Sync start
				$locationName = '';
				// App::import( 'Controller' , 'MyExceptions' );
				// $exception = new MyExceptionsController();
				// $exception->syncCalling( $locationName );
				
				$this->layout = '';
				$this->autoRender = false;
				$this->loadModel('SellbriteCron');
				
				## pull pending orders from open order table and check its status and if paid then proceed with flow 
					$this->checkfetchOrdersb();	
				## End 
			}

			public function checkfetchOrdersb()
			{			

				$this->layout = '';
				$this->autoRender = false;
				$this->loadModel('OpenOrder');
				$this->loadModel('AssignService');
				$this->loadModel('Customer');
				$this->loadModel('OrderItem');
				$this->loadModel('Product');
				$this->loadModel('UnprepareOrder');
				$this->loadModel('ProChannels');
				$this->loadModel('SellbritePostalservices');
				$this->loadModel('Skumapping');
				$this->loadModel('Countrie');
				$this->loadModel('FbaLinnOrder'); 
				$this->loadModel('FbaLinnOrderItem');

				$start_date = date("Y-m-d H:i:s", strtotime('-96 hour')); 
				$end_date   = date("Y-m-d H:i:s");

				//echo "<br>start_date : ".$start_date; 
				//echo "<br>end_date : ".$end_date."<br>";
				//die;		

				/*$unprepareOrderList  = $this->UnprepareOrder->find('all', array('fields' => array("UnprepareOrder.id","UnprepareOrder.order_id","UnprepareOrder.num_order_id"),'conditions' => array('UnprepareOrder.date BETWEEN "'.$start_date.'" AND "'.$end_date.'" '))); */

				$unprepareOrderList  = $this->UnprepareOrder->find('all', array('fields' => array("UnprepareOrder.id","UnprepareOrder.order_id","UnprepareOrder.num_order_id") ));

				//echo "<pre>unprepareOrderList :: "; print_r($unprepareOrderList);
				//die;

				###################################### Sellbrite API Integration to pull orders by order Id - start #######################
				## API Key from sellbrite account 
					$user_name = 'c96ef6ba-7c9c-4f46-ae1c-bccf57452e0a';
					$password = '38b4fcada6fc582c8fc9391d77301478';
				#end
				$finalOrderArr = array();
				if(!empty($unprepareOrderList)){
					
					foreach($unprepareOrderList as $orderdetList)
					{						
						$orderId = $orderdetList['UnprepareOrder']['order_id'];
						//echo "<br>".$orderId; die; 
						if($orderId != ''){
							## loop is used if the date rang is big and pulled data has paggination - start								

								$curl = curl_init();
								curl_setopt_array($curl, [
								  CURLOPT_URL => 'https://api.sellbrite.com/v1/orders/'.$orderId,
								  CURLOPT_RETURNTRANSFER => true,
								  CURLOPT_ENCODING => "",
								  CURLOPT_MAXREDIRS => 10,
								  CURLOPT_TIMEOUT => 30,
								  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
								  CURLOPT_CUSTOMREQUEST => "GET",
								  CURLOPT_HTTPHEADER => [
								    "Accept: application/json",
								    "Authorization: Basic ". base64_encode("$user_name:$password")
								  ],
								]);

								$response = curl_exec($curl);
								$err = curl_error($curl);
								$resultArry = json_decode($response);
								curl_close($curl); 
								
								// die;
								
								if ($err) {
								  echo "cURL Error #:" . $err;
								} else {
									if(!empty($resultArry)){										
										if(empty($resultArry->error)){										
											$finalOrderArr[] = $resultArry;
										}										
									} 
								}					
							
							########### End ###########
						}
					}	
				}				

				//echo "<pre>finalOrderArr :: "; print_r($finalOrderArr);
				//die;

				#################################### End #############################################

				########### Fetch Sellbrite Postal services - start ############# 											
					$sellbritepostaldetails	=	$this->SellbritePostalservices->find('all');
					$masterSlbrtPtl = array();
					foreach($sellbritepostaldetails as $sbpsdetail)
					{						
						$indx = base64_encode($sbpsdetail['SellbritePostalservices']['name']);
						$masterSlbrtPtl[$indx] =  $sbpsdetail['SellbritePostalservices']['service_name'];
					}					
				###################### End ###########	

				######################### Covert sellbrite array into linnwork array format ############### 
				$linnworkformatArry = array();
				$fbaordersformatArry = array();
				$finalFBAordersAry = array();
				if(!empty($finalOrderArr)){

					foreach($finalOrderArr as $key => $valueData ){

						$linnvertInternal = array();

						$markerFlg = 0;
						switch($valueData->sb_status){
							case 'open' : $markerFlg = '0'; break;
							case 'completed' : $markerFlg = '1'; break;
							case 'cancelled' : $markerFlg = '2'; break;
						}
						//$markerFlg = 0;

						$statusord = 1;
						if($valueData->sb_payment_status == 'none' || $valueData->sb_payment_status == 'partial'){
							$statusord = '0';
						} else {
							$statusord = '1';
						}					


						//$linnvertInternal['NumOrderId'] = rand("000000","999999").$valueData->sb_order_seq;
						$linnvertInternal['NumOrderId'] = (10000000+$valueData->sb_order_seq);
						
						$linnvertInternal['GeneralInfo'] = array(
																	"Status" => $statusord,
																	"LabelPrinted" => '',
																	"InvoicePrinted" => '',
																	"PickListPrinted" => '',
																	"IsRuleRun" => '',
																	"Notes" => '0',
																	"PartShipped" => '',
																	"Marker" => $markerFlg,
																	"IsParked" => '',
																	"ReferenceNum" => $valueData->display_ref,
																	"SecondaryReference" => $valueData->display_ref,
																	"ExternalReferenceNum" => $valueData->display_ref,
																	"ReceivedDate" => $valueData->ordered_at,
																	"Source" => $valueData->channel_type_display_name,
																	"SubSource" => $valueData->channel_name,
																	"HoldOrCancel" => '',
																	"DespatchByDate" => $valueData->ship_by_date,
																	"HasScheduledDelivery" => '',
																	"Location" => $valueData->channel_uuid,
																	"NumItems" => 0
																);

						$postalServiceName = 'Standard';
						$indxtr = base64_encode($valueData->requested_shipping_service);
						$valpostlservice = @$masterSlbrtPtl[$indxtr];
						if(trim($valpostlservice) != ''){
							$postalServiceName = $valpostlservice;
						}
						
						$linnvertInternal['ShippingInfo'] = array(
																"Vendor" => 'other',
																"PostalServiceId" => '',
																"PostalServiceName" => $postalServiceName,
																"TotalWeight" => '',
																"ItemWeight" => '',
																"PackageCategoryId" => '00000000-0000-0000-0000-000000000000',
																"PackageCategory" => 'Default',
																"PackageTypeId" => '00000000-0000-0000-0000-000000000000',
																"PackageType" => 'Default',
																"PostageCost" => '0',
																"PostageCostExTax" => '0',
																"TrackingNumber" => '',
																"ManualAdjust" => '',
																);

						$country = $valueData->shipping_country_code;
						$currency = '';
						if($valueData->shipping_country_code != ''){

							$fetchCountryDet 	=	$this->ProChannels->find('first', array('conditions'=>array('ProChannels.country' => $valueData->shipping_country_code))); 							
							
							$fetchCountryDet = json_decode(json_encode($fetchCountryDet));
							if( count( $fetchCountryDet ) > 0 )
							{												
								foreach( $fetchCountryDet as $countryDet ):	
									$country = $valueData->shipping_country_code;
									$currency = $countryDet->currency;
								endforeach;

							}	else {
								$country = $valueData->shipping_country_code;
								$currency = '';
							}							
						}							

						$Countriedetails	= $this->Countrie->find('first', array('conditions'=>array('Countrie.iso_2' => $valueData->shipping_country_code)));

						$Countrie = $valueData->shipping_country_code;

						if(!empty($Countriedetails)){
							foreach($Countriedetails as $countriedet)
							{					
								$Countrie = $countriedet['custom_name'];
							}
						}

						if($statusord == 0){
							$Countrie = 'UNKNOWN';
						}

						$address1  = ($valueData->shipping_address_1 != '') ? $valueData->shipping_address_1 : $valueData->shipping_address_2;

						$linnvertInternal['CustomerInfo'] = array(
																"ChannelBuyerName" => $valueData->shipping_contact_name,
																"Address" => array(
																			"EmailAddress" => $valueData->billing_email,
																			"Address1" => $address1,
																			"Address2" => $valueData->shipping_address_2,
																			"Address3" => $valueData->shipping_address_3,
																			"Town" => $valueData->shipping_city,
																			"Region" => $valueData->shipping_state_region,
																			"PostCode" => $valueData->shipping_postal_code,
																			"Country" => $Countrie,
																			"FullName" => $valueData->shipping_contact_name,
																			"Company" => '',
																			"PhoneNumber" => $valueData->shipping_phone_number,
																			"CountryId" => '',
																		)
																);

						$linnvertInternal['TotalsInfo'] = array(
																"Subtotal" => $valueData->subtotal,
																"PostageCost" => 0,
																"PostageCostExTax" => 0,
																"Tax" => $valueData->tax,
																"TotalCharge" => $valueData->total,
																"PaymentMethod" => 'Default',
																"PaymentMethodId" => '00000000-0000-0000-0000-000000000000',
																"ProfitMargin" => 0,
																"TotalDiscount" => $valueData->discount,
																"Currency" => $currency,
																"CountryTaxRate" => '',
																"ConversionRate" => ''
															);

						$linnvertInternal['TaxInfo'] = array();
						$linnvertInternal['FolderName'] = array();
						$linnvertInternal['IsPostFilteredOut'] = '';
						$linnvertInternal['CanFulfil'] = '';
						$linnvertInternal['Fulfillment'] = array(
																"FulfillmentState" => ''
															);						
						$linnvertInternal['HasItems'] = count($valueData->items);
						$linnvertInternal['TotalItemsSum'] = '';
						//$linnvertInternal['OrderId'] = $valueData->display_ref;
						$linnvertInternal['OrderId'] = $valueData->sb_order_seq;						
						$linnvertInternal['RowId'] = $valueData->display_ref;
						$linnvertInternal['StockItemId'] = '';
						$linnvertInternal['location'] = $valueData->channel_name;

						if($valueData->sb_payment_status == 'none' || $valueData->sb_payment_status == 'partial'){
							$linnvertInternal['ordstus'] = '0';
						} else {
							$linnvertInternal['ordstus'] = '1';
						}

						$orderItemsRow = $valueData->items;

						## FBA Orders array format for insert - start 
						$fbaordersformatArry['num_order_id'] = (10000000+$valueData->sb_order_seq);
						$fbaordersformatArry['reference_num'] = $valueData->display_ref;
						$fbaordersformatArry['source'] = $valueData->channel_type_display_name;
						$fbaordersformatArry['sub_source'] = $valueData->channel_name;
						$fbaordersformatArry['marker'] = $markerFlg;
						$fbaordersformatArry['received_date'] = date("Y-m-d H:i:s",strtotime($valueData->ordered_at));
						$fbaordersformatArry['despatch_by_date'] = date("Y-m-d H:i:s",strtotime($valueData->ship_by_date));
						$fbaordersformatArry['postal_service_name'] = $postalServiceName;
						$fbaordersformatArry['postage_cost'] = '0.00';
						$fbaordersformatArry['tracking_number'] = '';
						$fbaordersformatArry['channel_buyer_name'] = $valueData->shipping_contact_name;
						$fbaordersformatArry['email_address'] = $valueData->billing_email;
						$fbaordersformatArry['address1'] = $valueData->shipping_address_1;
						$fbaordersformatArry['address2'] = $valueData->shipping_address_2;
						$fbaordersformatArry['address3'] = $valueData->shipping_address_3;
						$fbaordersformatArry['town'] = $valueData->shipping_city;
						$fbaordersformatArry['post_code'] = $valueData->shipping_postal_code;
						$fbaordersformatArry['country'] = $Countrie;
						$fbaordersformatArry['company'] = '';
						$fbaordersformatArry['phone_number'] = $valueData->shipping_phone_number;
						$fbaordersformatArry['subtotal'] = $valueData->subtotal;
						$fbaordersformatArry['postage_cost_total'] = '0.00';
						$fbaordersformatArry['tax_total'] = $valueData->tax;
						$fbaordersformatArry['total_charge'] = $valueData->total;
						$fbaordersformatArry['currency'] = $currency;
						$fbaordersformatArry['status'] = $statusord;
						$fbaordersformatArry['order_id'] = $valueData->sb_order_seq;
						$fbaordersformatArry['added_date'] = date("Y-m-d H:i:s");					

						$orderItems = array();
						$fbaorderItems = array();
						if(!empty($orderItemsRow)){

							$i = 0 ;
							$orderFBAFlg = 0;
							foreach($orderItemsRow as $keyRw => $valueItemRw){ 

								## fetch correct SKU using channel SKU - start 
									$skuIdArr = explode( '-', trim($valueItemRw->sku) );									
									if(!empty($skuIdArr)){
										$skuId = trim($skuIdArr[0]);
									} else {
										$skuId = trim($valueItemRw->sku);
									}

									$Skumappingdetails	= $this->Skumapping->find('first', array('conditions'=>array('Skumapping.channel_sku' => $skuId)));

									$productSKU = $valueItemRw->sku;									

									if(!empty($Skumappingdetails)){
										foreach($Skumappingdetails as $skunamedet)
										{					
											$productSKU = $skunamedet['sku'];	
											if($skunamedet['channel_type'] == 'fba'){
												$orderFBAFlg = 1;
											} else {
												$orderFBAFlg = 0;
											}
										}
									} else {

										$skuId = trim($valueItemRw->sku);

										$Skumappingdetails	= $this->Skumapping->find('first', array('conditions'=>array('Skumapping.channel_sku' => $skuId)));

										$productSKU = $valueItemRw->sku;

										if(!empty($Skumappingdetails)){
											foreach($Skumappingdetails as $skunamedet)
											{					
												$productSKU = $skunamedet['sku'];	
												if($skunamedet['channel_type'] == 'fba'){
													$orderFBAFlg = 1;
												} else {
													$orderFBAFlg = 0;
												}
											}
										}										
									}
									
								## end 

								//echo "<br>display_ref : ".$valueData->display_ref." | productSKU :".$skuId.": ".$valueItemRw->sku." == ".$productSKU;	

								$orderItems[$i]['ItemId'] = $valueItemRw->order_item_ref;
								$orderItems[$i]['ItemNumber'] = $valueItemRw->order_item_ref;
								$orderItems[$i]['SKU'] = $productSKU;
								$orderItems[$i]['ItemSource'] = $valueData->channel_type_display_name;
								$orderItems[$i]['Title'] = $valueItemRw->title;
								$orderItems[$i]['Quantity'] = $valueItemRw->quantity;
								$orderItems[$i]['CategoryName'] = 'Default';
								$orderItems[$i]['StockLevelsSpecified'] = '';
								$orderItems[$i]['OnOrder'] = 0; 
								$orderItems[$i]['InOrderBook'] = 0;
								$orderItems[$i]['Level'] = '';
								$orderItems[$i]['MinimumLevel'] = '';
								$orderItems[$i]['AvailableStock'] = '';
								$orderItems[$i]['PricePerUnit'] = $valueItemRw->unit_price;
								$orderItems[$i]['UnitCost'] = $valueItemRw->unit_price;
								$orderItems[$i]['DespatchStockUnitCost'] = '';
								$orderItems[$i]['Discount'] = 0;
								$orderItems[$i]['Tax'] = $valueItemRw->tax;
								$orderItems[$i]['TaxRate'] = '';
								$orderItems[$i]['Cost'] = ($valueItemRw->total - $valueItemRw->tax);
								$orderItems[$i]['CostIncTax'] = $valueItemRw->total;
								$orderItems[$i]['CompositeSubItems'] = array();
								$orderItems[$i]['IsService'] = '';
								$orderItems[$i]['SalesTax'] = $valueItemRw->tax;
								$orderItems[$i]['TaxCostInclusive'] = '';
								$orderItems[$i]['PartShipped'] = '';
								$orderItems[$i]['Weight'] = '';
								$orderItems[$i]['BarcodeNumber'] = '';
								$orderItems[$i]['Market'] = 0;
								$orderItems[$i]['ChannelSKU'] = $valueItemRw->sku;
								$orderItems[$i]['ChannelTitle'] = $valueItemRw->title;
								$orderItems[$i]['DiscountValue'] = 0;
								$orderItems[$i]['HasImage'] = '';
								$orderItems[$i]['AdditionalInfo'] = array();
								$orderItems[$i]['StockLevelIndicator'] = '';
								$orderItems[$i]['ShippingCost'] = 0;
								$orderItems[$i]['PartShippedQty'] = 0;
								$orderItems[$i]['BatchNumberScanRequired'] = '';
								$orderItems[$i]['SerialNumberScanRequired'] = '';
								$orderItems[$i]['InventoryTrackingType'] = '';
								$orderItems[$i]['isBatchedStockItem'] = '';
								$orderItems[$i]['IsWarehouseManaged'] = '';
								$orderItems[$i]['IsUnlinked'] = '';
								$orderItems[$i]['StockItemIntId'] = '';
								$orderItems[$i]['RowId'] = $valueData->display_ref;
								$orderItems[$i]['OrderId'] = $valueData->display_ref;
								$orderItems[$i]['StockItemId'] = ''; 

								//$orderItems[$i]['order_inc_id'] = $valueItemRw->order_item_ref;
								$fbaorderItems[$i]['num_order_id'] = (10000000+$valueData->sb_order_seq);;
								$fbaorderItems[$i]['item_number'] = $valueItemRw->order_item_ref;
								$fbaorderItems[$i]['item_source'] = $valueData->channel_type_display_name;
								$fbaorderItems[$i]['master_sku'] = '';
								$fbaorderItems[$i]['channel_sku'] = $valueItemRw->sku;
								$fbaorderItems[$i]['title'] = $valueItemRw->title;
								$fbaorderItems[$i]['quantity'] = $valueItemRw->quantity;
								$fbaorderItems[$i]['price_per_unit'] = $valueItemRw->unit_price;
								$fbaorderItems[$i]['tax'] = $valueItemRw->tax;
								$fbaorderItems[$i]['tax_rate'] = '';
								$fbaorderItems[$i]['cost'] = ($valueItemRw->total - $valueItemRw->tax);
								$fbaorderItems[$i]['cost_inc_tax'] = $valueItemRw->total;
								$fbaorderItems[$i]['sales_tax'] = $valueItemRw->tax;
								$fbaorderItems[$i]['channel_title'] = $valueItemRw->title;
								$fbaorderItems[$i]['added_date'] = date("Y-m-d H:i:s");	
							}
						}

						$linnvertInternal['Items'] = $orderItems;	
						$fbaordersformatArry['Items'] = $fbaorderItems;	

						$fbaordersformatArry['linnworkarry'] = $linnvertInternal;				

						if($orderFBAFlg == 1){
							$fbaordersformatArry['fbastatus'] = $orderFBAFlg;
							$finalFBAordersAry[] = $fbaordersformatArry;							
							
						} else {
							$linnvertInternal['fbastatus'] = $orderFBAFlg;
							$linnworkformatArry[] = $linnvertInternal;
							
						}	
					} // for
				} // if				

				//echo "<pre>fbaorderItems :: "; print_r($fbaorderItems);	
				//echo "<pre>fbaordersformatArry :: "; print_r($fbaordersformatArry);	
				//echo "<pre>1 finalFBAordersAry :: ".count($finalFBAordersAry); print_r($finalFBAordersAry);		
				//echo "<pre>1 linnworkformatArry :: ".count($linnworkformatArry); print_r($linnworkformatArry);						
				//die;

				######################################## End ############################################## 
				$removeIds = array();
				if(!empty($finalFBAordersAry)){ 
					
					echo "<br>##################### FBA Orders process ##############";
					#echo "<pre>finalFBAordersAry :: "; print_r($finalFBAordersAry);	 die;
					foreach($finalFBAordersAry as $fbaorderDets){

							//echo "<pre>fbaorderDets :: "; print_r($fbaorderDets);
							//die;							
							$statusord = $fbaorderDets['status'];
							$orderFBAFlg = $fbaorderDets['fbastatus'];

							#echo "<br>statusord : ".$statusord;
							#echo "<br>orderFBAFlg : ".$orderFBAFlg;
							#die;
							//echo "<pre>fbaordersformatArry :: "; print_r($fbaordersformatArry); die;

							## code for FBA and Paid Orders 
								if($orderFBAFlg == 1 && $statusord == 1){	

									echo "<br> 	1-1 - s - ".$numid;

									if(!empty($fbaordersformatArry)){
										
										$numid = $fbaorderDets['num_order_id']; 										
										$fbaorderItems = $fbaorderDets['Items'];
										$fbastatus = $fbaorderDets['fbastatus'];
										unset($fbaorderDets['linnworkarry']);										
										unset($fbaorderDets['Items']);
										unset($fbaorderDets['fbastatus']);
										echo "<br>numid : ".$numid;

										//echo "<pre>fbaorderDets :: "; print_r($fbaorderDets); 
										//echo "<pre>fbaorderItems :: "; print_r($fbaorderItems);
										//die;
										
										$check = $this->FbaLinnOrder->find('first', array('conditions'=>array('num_order_id' => $numid)));
										//echo "<pre>check :: ".count($check); print_r($check);
										#echo "<pre>check :: ".count($fbaorderssave); print_r($fbaorderssave);
										//die;

										if(count($check) == 0){
											$this->FbaLinnOrder->saveAll($fbaorderDets);
											$order_inc_id	=	$this->FbaLinnOrder->getLastInsertId();
											##$order_inc_id = '382'; = 
											$fbaorderItems = $fbaorderItems;
											foreach($fbaorderItems as $itemData){
												$itemData['order_inc_id'] = $order_inc_id;
											 	//echo "<pre>item :: ".$order_inc_id; print_r($itemData);	
											 	$this->FbaLinnOrderItem->saveAll($itemData);
											}
											$removeIds[] = $numid;
										}	else {
											//echo "<br> else ".$numid;
											$removeIds[] = $numid;
										}	
									} 								
									echo "<br> 1-1-E ".$numid;						
								} else if($orderFBAFlg == 1 && $statusord == 0){
									
								echo "<br> 1-0-S";	
								## code for FBA and pending orders process 	
								$unpreArryrow = $fbaorderDets['linnworkarry'];
								if(!empty($unpreArryrow)){	
									$result = json_decode(json_encode($unpreArryrow));
									//echo "<pre>result :: ".count($result); print_r($result); die;
									if(!empty($result)){
										## insert into unprepareorder for FBA orders - start 
											$getAllUnprepardId = $this->UnprepareOrder->find('first', array('conditions'=>array('UnprepareOrder.order_id' => $result->OrderId) , 'fields' => array( 'UnprepareOrder.order_id')));
											
											//echo "<pre>getAllUnprepardId :: "; print_r($getAllUnprepardId);
											//echo "<pre>getAllUnprepardId :: "; print_r($result); //die;

											if( count($getAllUnprepardId) == 0)
											{	
												$data = array();
												$data['order_id']		= $result->OrderId;
												$data['num_order_id']	= $result->NumOrderId;
												$data['general_info']	= serialize($result->GeneralInfo);
												$data['shipping_info']	= serialize($result->ShippingInfo);
												$data['customer_info']	= serialize($result->CustomerInfo);
												$data['totals_info']	= serialize($result->TotalsInfo);
												$data['folder_name']	= serialize($result->FolderName);
												$data['items']			= serialize($result->Items);
												$data['destination'] = $result->CustomerInfo->Address->Country;
												$data['date']			= date('Y-m-d H:i:s');
												$data['unprepare_check']= 1;
												$data['source_name']= $result->GeneralInfo->SubSource;											
												echo "<pre>data :: "; print_r($data); //die;	
												$this->UnprepareOrder->create();											
												$save = $this->UnprepareOrder->save($data);
												//echo "<pre>save :: "; print_r($save);
											}
										## End 
									}
								}	
								echo "<br> 1-0-E";								
								}
					}
				} 				
								
				echo "<pre>1 removeIds :: ".count($removeIds); print_r($removeIds);		 
						
				#removed paid order entry's from open order table - start 
					# this code is delete orders which are proces after status got change from unpaid to paid - start 
						if(!empty($removeIds)){
							if(count($removeIds) == 1){
								$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
										'conditions' => array(
											'UnprepareOrder.num_order_id' => $removeIds
											),									
										  'fields' => array( 
											'UnprepareOrder.order_id',
											'UnprepareOrder.num_order_id',
											'UnprepareOrder.unprepare_check',
											'UnprepareOrder.id',  
											'UnprepareOrder.items'  
										)
									)
								);
							}else{
								$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
										'conditions' => array(
											'UnprepareOrder.num_order_id IN ' => $removeIds
											),									
										  'fields' => array( 
											'UnprepareOrder.order_id',
											'UnprepareOrder.num_order_id',
											'UnprepareOrder.unprepare_check',
											'UnprepareOrder.id',  
											'UnprepareOrder.items'  
										)
									)
								);
							}

							//echo "<pre>1 getAllUnprepardId :: ".count($getAllUnprepardId); print_r($getAllUnprepardId);	die;

							foreach( $getAllUnprepardId  as $getAllId )
							{								
								$this->UnprepareOrder->delete( $getAllId['UnprepareOrder']['id'] );
							}	
						}
					## End
				# End		

				echo "<br>##################### Merchant Order process ##############";
				## process merchant Orders only 
				if(!empty($linnworkformatArry)){

					$linnworkformatArry = json_decode(json_encode($linnworkformatArry));
					if(!empty($linnworkformatArry)){
						$openorder = $linnworkformatArry;
					} 				
					App::import('Controller', 'Virtuals');
					$virtualModel = new VirtualsController(); 
					//$virtualModel->creatFeedSheet_old( $locationName );

					$unPaid = [];	
					if(count( $openorder) > 0){
					
						//$log_file = WWW_ROOT .'cron-logs/'.$locationName.".log";	
						//@unlink($log_file);
					
						foreach($openorder as $orderidsArr)
						{ 							 
								
							$orderids = $orderidsArr->OrderId;
							//file_put_contents($log_file, $orderids."\t".date('Y-m-d H:i:s')."\n",  FILE_APPEND|LOCK_EX);
							//echo $orderids. "otderID <br>"; die; 						
							//$orders[]	=	$orderids->OrderId;

							$result = $orderids;
							//exit;
							$checkOpenOrder 	=	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $orderids)));
							//echo $checkOpenOrder."one <br>"; die;
							//pr($checkOpenOrder);
							//echo "<pre>checkOpenOrder :: "; print_r($checkOpenOrder);	
							if( count($checkOpenOrder) == 0 )
							{
								//echo 'Insert >> '.$orderids->OrderId."<br>";
								$orders[]	=	$orderids;
								
							}
							else
							{
								//Clean Orders
								$this->cleanOrders();
								$unPaid[]	=	$orderids;
								//CHECK IF ORDER EXISTS OR NOT
								// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
								//$order_status = OrdersMethods::GetOrders($unPaid,$location,true,true,$token, $server);
								//pr($order_status);
								//$results = $order_status[0];
								$dataUpdate['OpenOrder']['id'] = $checkOpenOrder['OpenOrder']['id'];
								$dataUpdate['OpenOrder']['linn_fetch_orders'] = '1';//$results->GeneralInfo->Status;	
								//echo $NumOrderid.' Status-> '.$results->GeneralInfo->Status.'<br>';
								
								//$log_file = WWW_ROOT .'cron-logs/'.$locationName."_unpaid.log";		 
								//file_put_contents($log_file, print_r($order_status,true));	
					
					
								//exit;						
								//$dataUpdate['OpenOrder']['linn_fetch_orders'] = serialize($result->CustomerInfo);
								//echo "<pre>dataUpdate :: "; print_r($dataUpdate);	 die;
								$this->OpenOrder->saveAll( $dataUpdate ); 
								//Now update into Merge Section
								$ordStatus  = '1';//$results->GeneralInfo->Status;	
								$NumOrderid = $checkOpenOrder['OpenOrder']['num_order_id'];
								//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
								$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $ordStatus), array('MergeUpdate.order_id' => $NumOrderid) );
								unset( $unPaid ); 
							
							} 						
							//$orders[]	=	$orderids->OrderId;
						}					

						//echo "<pre>orders :: "; print_r($orders);						
						//die; 					
										
						$result = '';
						unset( $checkOpenOrder );
						
						//$orders[]	=	'63bac14d-dcca-4322-9a0a-d311c558144c';
						
						if( !empty( $orders ) && count( $orders ) > 0 )
						{					
											
							$this->saveOpenOrderunprepord( $linnworkformatArry , "sellbrite" , $orders );	
							//$log_file = WWW_ROOT .'cron-logs/ord_'.$locationName."_".date('Ymd').".log";		 
							//file_put_contents($log_file, print_r($results,true), FILE_APPEND | LOCK_EX);			
							
						}

					}else{
						echo 'no open orders';				
					}
				}
				# End

					
				 
				
				exit; 
			}
			
			public function testOrder( $location = null , $locationName = null )
			{
				//die('Working on db cleanup');
				
				$this->layout = '';
				$this->autoRender = false;
				$this->loadModel('OpenOrder');
				$this->loadModel('AssignService');
				$this->loadModel('Customer');
				$this->loadModel('OrderItem');
				$this->loadModel('Product');
				$this->loadModel('UnprepareOrder');
				
				/*App::import('Vendor', 'linnwork/api/Auth');
				App::import('Vendor', 'linnwork/api/Factory');
				App::import('Vendor', 'linnwork/api/Orders');
			
				$username = Configure::read('linnwork_api_username');
				$password = Configure::read('linnwork_api_password');
				
				$multi = AuthMethods::Multilogin($username, $password);
				
				$auth = AuthMethods::Authorize($username, $password, $multi[0]->Id);*/
				
				App::import('Vendor', 'Linnworks/src/php/Auth');
				App::import('Vendor', 'Linnworks/src/php/Factory');
				App::import('Vendor', 'Linnworks/src/php/Orders');
			
				$username = Configure::read('linnwork_api_username');
				$password = Configure::read('linnwork_api_password');
				
				$token = Configure::read('access_new_token');
				$applicationId = Configure::read('application_id');
				$applicationSecret = Configure::read('application_secret');
				
				//$multi = AuthMethods::Multilogin($username, $password);
				
				$auth = AuthMethods::AuthorizeByApplication($applicationId,$applicationSecret,$token);	

				$token = $auth->Token;	
				$server = $auth->Server;
				
			   // $openorder	=	OrdersMethods::GetOpenOrders('3000','1','','',$location,'',$token, $server);
			 
			    $openorder	=	OrdersMethods::GetAllOpenOrders("","",$location,"",$token, $server);  
			
				//pr($openorder);
				//if($locationName == 'CostBreaker_IT'){echo 'CostBreaker_IT';} 
				/*
				$unPaids  = ['0'=>'ae4d4f66-1ab9-4909-94c4-745fe46eca6d','1'=>'7a607d6b-4efc-4eeb-9674-6a57a699a0ff','2'=>'61ae72d7-16e0-4a99-8548-3076df6d93b4','3'=>'d25d1df7-211a-475c-aa50-a10c3a9709b7'];
				
				$order_status = OrdersMethods::GetOrders($openorder,$location,true,true,$token, $server);
				pr($order_status );
				exit;*/
 				
				App::import('Controller', 'Virtuals');
				$virtualModel = new VirtualsController(); 
				//$virtualModel->creatFeedSheet_old( $locationName );
				$unPaid = [];	
				if(count( $openorder) > 0){
				
					$log_file = WWW_ROOT .'cron-logs/'.$locationName.".log";	
					@unlink($log_file);
				
					foreach($openorder as $orderids)
					{
 							 
						file_put_contents($log_file, $orderids."\t".date('Y-m-d H:i:s')."\n",  FILE_APPEND|LOCK_EX);	
				
						//echo $orderids. "otderID <br>";
						
						//$orders[]	=	$orderids->OrderId;
						$result = $orderids;
						//exit;
						$checkOpenOrder 	=	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $orderids)));
						//echo $checkOpenOrder."one <br>";
						//pr($checkOpenOrder);
						if( count($checkOpenOrder) == 0 )
						{
							//echo 'Insert >> '.$orderids->OrderId."<br>";
							$orders[]	=	$orderids;							
						}
						else
						{
							//Clean Orders
							$this->cleanOrders();
							$unPaid[]	=	$orderids;
							//CHECK IF ORDER EXISTS OR NOT
							// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
							//$order_status = OrdersMethods::GetOrders($unPaid,$location,true,true,$token, $server);
							//pr($order_status);
							//$results = $order_status[0];
							$dataUpdate['OpenOrder']['id'] = $checkOpenOrder['OpenOrder']['id'];
							$dataUpdate['OpenOrder']['linn_fetch_orders'] = '1';//$results->GeneralInfo->Status;	
							//echo $NumOrderid.' Status-> '.$results->GeneralInfo->Status.'<br>';
							
							//$log_file = WWW_ROOT .'cron-logs/'.$locationName."_unpaid.log";		 
							//file_put_contents($log_file, print_r($order_status,true));	
				
				
							//exit;						
							//$dataUpdate['OpenOrder']['linn_fetch_orders'] = serialize($result->CustomerInfo);
							$this->OpenOrder->saveAll( $dataUpdate ); 
							//Now update into Merge Section
							$ordStatus  = '1';//$results->GeneralInfo->Status;	
							$NumOrderid = $checkOpenOrder['OpenOrder']['num_order_id'];
							//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
							$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $ordStatus), array('MergeUpdate.order_id' => $NumOrderid) );
							unset( $unPaid );
						
						}
						
						//$orders[]	=	$orderids->OrderId;
					}
					//print_r($orders);
					//exit;
					$result = '';
					unset( $checkOpenOrder );
					
					//$orders[]	=	'63bac14d-dcca-4322-9a0a-d311c558144c';
					
					if( !empty( $orders ) && count( $orders ) > 0 )
					{
						
						$results = OrdersMethods::GetOrders($orders,$location,true,true,$token, $server);
						 //pr($results);
						//$locationName .'== '. count( $results );					
						$this->saveOpenOrder( $results , $locationName , $orders );	
						//$log_file = WWW_ROOT .'cron-logs/ord_'.$locationName."_".date('Ymd').".log";		 
						//file_put_contents($log_file, print_r($results,true), FILE_APPEND | LOCK_EX);				
						
					}
				}else{
					echo 'no open orders';				
				}
			
				$log_file = WWW_ROOT .'cron-logs/crons_'.date('Ymd').".log";	
				file_put_contents($log_file, $locationName."\t".date('Y-m-d H:i:s')."\n",  FILE_APPEND | LOCK_EX);	
				//$this->updateIoss($token, $server);
				exit;
			}
			
			public function testOrder200317( $location = null , $locationName = null )
			{
				
				$this->layout = '';
				$this->autoRender = false;
				$this->loadModel('OpenOrder');
				$this->loadModel('AssignService');
				$this->loadModel('Customer');
				$this->loadModel('OrderItem');
				$this->loadModel('Product');
				$this->loadModel('UnprepareOrder');
				
				/*App::import('Vendor', 'linnwork/api/Auth');
				App::import('Vendor', 'linnwork/api/Factory');
				App::import('Vendor', 'linnwork/api/Orders');
			
				$username = Configure::read('linnwork_api_username');
				$password = Configure::read('linnwork_api_password');
				
				$multi = AuthMethods::Multilogin($username, $password);
				
				$auth = AuthMethods::Authorize($username, $password, $multi[0]->Id);*/
				
				App::import('Vendor', 'Linnworks/src/php/Auth');
				App::import('Vendor', 'Linnworks/src/php/Factory');
				App::import('Vendor', 'Linnworks/src/php/Orders');
			
				$username = Configure::read('linnwork_api_username');
				$password = Configure::read('linnwork_api_password');
				
				$token = Configure::read('access_new_token');
				$applicationId = Configure::read('application_id');
				$applicationSecret = Configure::read('application_secret');
				
				$multi = AuthMethods::Multilogin($username, $password);
				
				$auth = AuthMethods::AuthorizeByApplication($applicationId,$applicationSecret,$token);	

				$token = $auth->Token;	
				$server = $auth->Server;
				
			    $openorder	=	OrdersMethods::GetOpenOrders('3000','1','','',$location,'',$token, $server);
			 
			  
				App::import('Controller', 'Virtuals');
				$virtualModel = new VirtualsController(); 
				//$virtualModel->creatFeedSheet_old( $locationName );
				
				
				foreach($openorder->Data as $orderids)
				{
					//echo $orderids->OrderId."otderID <br>";
					//$orders[]	=	$orderids->OrderId;
					$result = $orderids;
					$checkOpenOrder 	=	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $orderids->OrderId)));
					//echo $checkOpenOrder."one <br>";
					if( count($checkOpenOrder) == 0 )
					{
						//echo 'Insert >> '.$orderids->OrderId."<br>";
						$orders[]	=	$orderids->OrderId;
						
					}
					else
					{
					
						//Clean Orders
						$this->cleanOrders();
						
						//CHECK IF ORDER EXISTS OR NOT
						// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
						$linnStatus = $result->GeneralInfo->Status;
						$dataUpdate['OpenOrder']['id'] = $checkOpenOrder['OpenOrder']['id'];
						$dataUpdate['OpenOrder']['linn_fetch_orders'] = $result->GeneralInfo->Status;							
						//$dataUpdate['OpenOrder']['linn_fetch_orders'] = serialize($result->CustomerInfo);
						$this->OpenOrder->saveAll( $dataUpdate ); 
						
						//Now update into Merge Section
						$this->loadModel( 'MergeUpdate' );
						
						//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
						$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $result->GeneralInfo->Status), array('MergeUpdate.order_id' => $result->NumOrderId) );
						
					}
					
					//$orders[]	=	$orderids->OrderId;
				}
				
				$result = '';
				unset( $result );
				
				//$orders[]	=	'd169ad13-d9b7-4653-a782-ebe561e885d5';
				
				if( !empty( $orders ) && count( $orders ) > 0 )
				{
					
					$results = OrdersMethods::GetOrders($orders,$location,true,true,$token, $server);
					
					//$locationName .'== '. count( $results );
					echo "2222222";
					echo $orders;
					echo $locationName;
					pr($results);	
					exit;				
					$this->saveOpenOrder( $results , $locationName , $orders );					
					
				}
				
			}
			
			public function updateIoss($token, $server)
			{
 				$this->layout = '';
				$this->autoRender = false;
				$this->loadModel('OpenOrder');
				
				$log_file = WWW_ROOT .'logs/ioss_'.date('Ymd').".log";	
				$order_date = date('Y-m-d',strtotime("-2"));
				if(file_exists($log_file)){
					$fdata = explode("\n", file_get_contents($log_file));
   					$orders = $this->OpenOrder->find( 'all', array( 'conditions' => array( 'destination !=' =>'United Kingdom','open_order_date >' => $order_date,'extended_properties IS NULL','order_id NOT IN'=>$fdata),'fields' => array('order_id','num_order_id','id'),'limit'=> 50 ) ); 
				}else{
					$orders = $this->OpenOrder->find( 'all', array( 'conditions' => array( 'destination !=' =>'United Kingdom','open_order_date >' =>'2021-07-15','extended_properties IS NULL'),'fields' => array('order_id','num_order_id','id'),'limit'=> 50 ) ); 
				}
					
				if(count( $orders ) > 0 )
				{ 	
					$data = [];
					foreach($orders as $v){
					
						$result = OrdersMethods::GetOrderById($v['OpenOrder']['order_id'],$token, $server);
						 
						if(isset($result->Notes) && count($result->Notes) > 0){
							$data['id'] = $v['OpenOrder']['id'];
							$data['notes']			= json_encode($result->Notes);
						}
						if(isset($result->ExtendedProperties) && count($result->ExtendedProperties) > 0){
							$data['id'] = $v['OpenOrder']['id'];
							$data['extended_properties'] = json_encode($result->ExtendedProperties);
							foreach($result->ExtendedProperties as $p){
								if($p->Name == 'MARKETPLACE_IOSS'){
									$data['ioss_no'] = $p->Value;
								}
							}
						}
						//pr($data);
						if(count($data) > 0){
							$this->OpenOrder->saveAll( $data );
						}
						file_put_contents($log_file,$v['OpenOrder']['order_id']."\n",  FILE_APPEND | LOCK_EX);	
 					 }
				}				 
				else{
					echo 'no order';
				}
				exit;
			}
	
			//xsensys copy
			public function saveOpenOrder( $results = null , $locationName = null , $orders = null ) 
			{				
				//echo "<pre>"; print_r($results);die;


				$itt = 1;
				$countryArray = Configure::read('customCountry');
				$this->loadModel('Country');
  				
				foreach($results as $result)
				{					
 					
					if(count($result->Items) == 0){
						$log_file = WWW_ROOT .'cron-logs/item_'.$locationName.date('dmy').".log";					
						//file_put_contents($log_file, print_r($result,true),  FILE_APPEND|LOCK_EX);
						//return "done";	
					}
					 
					/*if( ($result->GeneralInfo->Status == 1 || $result->GeneralInfo->Status == 4 ) && $result->GeneralInfo->HoldOrCancel == ''  )
					{*/
					
					if($result->ShippingInfo->PostalServiceName == 'Stamps/Franking'){
						$result->ShippingInfo->PostalServiceName = 'Standard';
 						file_put_contents(WWW_ROOT .'logs/stamps_franking_'.date('Ymd').".log", $result->NumOrderId."\n",  FILE_APPEND | LOCK_EX);	
					}
					
					$data['order_id']		= $result->OrderId;
					$data['num_order_id']	= $result->NumOrderId;
					$data['amazon_order_id']= $result->GeneralInfo->ReferenceNum;
					$data['general_info']	= serialize($result->GeneralInfo);
					$data['shipping_info']	= serialize($result->ShippingInfo);
					$data['customer_info']	= serialize($result->CustomerInfo);
					$data['totals_info']	= serialize($result->TotalsInfo);
					$data['folder_name']	= serialize($result->FolderName);
					$data['items']			= serialize($result->Items);
					$data['linn_fetch_orders'] = $result->GeneralInfo->Status;
					$data['order_parked'] 	= $result->GeneralInfo->Marker;
					$data['sub_source']		= $result->GeneralInfo->SubSource;

					//echo "<pre>data :: "; print_r($data); die; 
					
					if(isset($result->TaxInfo) && count($result->TaxInfo) > 0){
						if($result->TaxInfo->TaxNumber != ''){
							$data['buyer_vat_number'] = $result->TaxInfo->TaxNumber;
						}
					}
					
					if(isset($result->Notes) && count($result->Notes) > 0){
						$data['notes']			= json_encode($result->Notes);
					}
					
  					if(isset($result->ExtendedProperties) && count($result->ExtendedProperties) > 0){
						$data['extended_properties'] = json_encode($result->ExtendedProperties);
						foreach($result->ExtendedProperties as $p){
							if($p->Name == 'MARKETPLACE_IOSS'){
								$data['ioss_no'] = $p->Value;
							}
						}
					}
					
					$ddNew = explode("T",$result->GeneralInfo->ReceivedDate);
					$ddNew = $ddNew[0] .' '. $ddNew[1];
					
					$data['open_order_date'] = $ddNew;
					
					//Extra information will save my according to manage sorting station section
					//$checkOpenOrder =	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $orderids)));
					$country = $data['destination'] = $result->CustomerInfo->Address->Country;
					$orderitems	=	unserialize($data['items']);
					
					$flagVar = 3;
					
					//echo "<br>";
					//echo $itt . '==' . $data['num_order_id']	= $result->NumOrderId;
					//echo "<br>";
					
					$itt++;
					
 					#echo "<pre>result :: "; print_r($result); die; 

					//Check Unpreparee step by step
					$getAllUnprepardId = $this->UnprepareOrder->find('first', array('conditions'=>array('UnprepareOrder.order_id' => $result->OrderId) , 'fields' => array( 'UnprepareOrder.order_id', 'UnprepareOrder.num_order_id' , 'UnprepareOrder.id' , 'UnprepareOrder.linn_fetch_orders' , 'UnprepareOrder.destination' )));
					
					//echo "<pre>getAllUnprepardId :: "; print_r($getAllUnprepardId);
					//echo "<pre>getAllUnprepardId :: "; print_r($result); //die;

					if( count($getAllUnprepardId) > 0)
					{				

						if( $result->ordstus == 1)
						{								
						 	$flagVar = 3;
						} else if( $country !== $getAllUnprepardId['UnprepareOrder']['destination'] )
						{							
							//$flagVar = $this->unprepareOrder( $result , $result->OrderId );
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
						} elseif( $result->ordstus == 0)
						{								
						 	# if the payment status is none or partial - order is not fully paid then mark order as incomp 
							//$flagVar = $this->unprepareOrder($result);
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
						} else if(count($result->Items) == 0){							
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
							
							$log_file = WWW_ROOT .'cron-logs/Orders_1_'.$result->location.date('dmy').".log";			
							file_put_contents($log_file, $result->NumOrderId."\t".$result->GeneralInfo->ReferenceNum."\n",  FILE_APPEND|LOCK_EX);
							$log_file = WWW_ROOT .'cron-logs/item_1_'.$result->location.date('dmy').".log";					
							file_put_contents($log_file, print_r($result,true),  FILE_APPEND|LOCK_EX);
						}
						else
						{							
							//$flagVar = $this->unprepareOrder( $result , $result->OrderId );
							$flagVar = $this->unprepareOrder($result , $locationName , $result->NumOrderId );
						}
						
					}
					else
					{						
						//If it is UNKNOWN or not
						if( $result->ordstus == 1)
						{								
						 	$flagVar = 3;
						} else if( $country == "UNKNOWN")
						{
							//$flagVar = $this->unprepareOrder($result);
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
						} elseif( $result->ordstus == 0)
						{	
						 	# if the payment status is none or partial - order is not fully paid then mark order as incomp 
							//$flagVar = $this->unprepareOrder($result);
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
						} else if(count($result->Items) == 0){
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
							
							//$log_file = WWW_ROOT .'cron-logs/Orders_'.$locationName.date('dmy').".log";			
							file_put_contents($log_file, $result->NumOrderId."\t".$result->GeneralInfo->ReferenceNum."\n",  FILE_APPEND|LOCK_EX);
							//$log_file = WWW_ROOT .'cron-logs/item_'.$locationName.date('dmy').".log";					
							file_put_contents($log_file, print_r($result,true),  FILE_APPEND|LOCK_EX);
						}
						else
						{
							$flagVar = 3;
						} 
					}
					
					/*------Added on 17 AUG2021----*/
					$chkOpenOrder =	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.num_order_id' => $result->NumOrderId))); 
					if(count($chkOpenOrder) > 0){
						$flagVar = 2;
						$log__file = WWW_ROOT .'logs-ord/duplicates_'.date('dmY').".log";					
						file_put_contents($log__file,$result->NumOrderId."\t".date('Y-m-d H:i:s')."\n",  FILE_APPEND|LOCK_EX);
					}
					//sleep(1);
 					/*-------End----*/
									 
					/*------Added on 19 AUG 2021----*/
					$ord_flag = WWW_ROOT .'logs-ord/'.$result->NumOrderId.".flag";	
					if(file_exists($ord_flag)){
						$flagVar = 2; $skip = 1;
					}else{				
						file_put_contents($ord_flag,$result->NumOrderId,  FILE_APPEND|LOCK_EX);
					}
					/*-------End----*/	

					if( $result->ordstus == 1)
					{								
					 	$flagVar = 3;
					}
					
					echo "<br>flagVar: ".$flagVar; //die;
					
					if( $flagVar != 2 )
					{							

						//Check OpenOrder
						$this->OpenOrder->create();
						$checkorder 	=	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $result->OrderId,'OpenOrder.num_order_id' => $result->NumOrderId) , 'fields' => array( 'OpenOrder.order_id', 'OpenOrder.num_order_id' , 'OpenOrder.id' , 'OpenOrder.linn_fetch_orders' )));
						
						if(count($checkorder) > 0)
						{ 							
							//Clean Orders
							$this->cleanOrders();
							
							//CHECK IF ORDER EXISTS OR NOT
							// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
							//Order Marker -> 0 ----- 7 
							$linnStatus = '1';//$result->GeneralInfo->Status; 
							$dataUpdate['OpenOrder']['id'] = $checkorder['OpenOrder']['id'];
							$dataUpdate['OpenOrder']['linn_fetch_orders'] = '1';//$result->GeneralInfo->Status;
							$dataUpdate['order_parked'] 					= $result->GeneralInfo->Marker;														
							$dd = explode("T",$result->GeneralInfo->ReceivedDate);
							$dd = $dd[0] .' '. $dd[1];
							$dataUpdate['OpenOrder']['open_order_date'] = $dd;
							
                            //$dataUpdate['OpenOrder']['linn_fetch_orders'] = serialize($result->CustomerInfo);
							$this->OpenOrder->saveAll( $dataUpdate ); 
							
							//Now update into Merge Section
							$this->loadModel( 'MergeUpdate' );
							
							//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
							$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $linnStatus), array('MergeUpdate.order_id' => $result->NumOrderId) );
						}
						else //if( $flagVar != 2 )
						{
							echo "<br>NumOrderId: ".$result->NumOrderId;

							echo "<pre>result :: "; print_r($result);

							//echo "<pre>!!!!!!!!! : "; print_r($data); die; 
							$this->OpenOrder->save($data);
							
							//Clean Orders
							$this->cleanOrders();
							
							$getCurrencyText = $result->TotalsInfo->Currency;
							
							echo "<br>getCurrencyText: ".$getCurrencyText;

							if( $getCurrencyText == "EUR")
							{
								$baseRate = '1';
							}
							else
							{
								$baseRate = '1.13';
							}
							
							echo "<br>baserate: ".$baseRate;

							/***************** split the order item ******************/
							$orderItemValueTotal = 0;
							foreach( $orderitems as $orderitem )
							{
								$orderItemValueTotal = $orderItemValueTotal + $orderitem->Cost;
							}
							
							echo "<br>orderItemValueTotal: ".$orderItemValueTotal;

							//Get special postal service name as discussed by shashi at run time when did launch
							$serviceNameNow = unserialize($data['shipping_info']);
							$servicePostal = $serviceNameNow->PostalServiceName;

							echo "<br>servicePostal: ".$servicePostal;
							//die;


							if( $servicePostal == "Standard_Jpost" )
							{	
								
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}
								
								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								$channel_sku ='';
								foreach( $orderitems as $orderitem )
								{
									$channel_sku = $orderitem->RowId.'__';
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
											
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;
										
									}
																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']	=   "single";			
								$splititem['price']						=	$combinePrice;
								$splititem['product_order_id_identify']	=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']			=	$result->NumOrderId;
								$splititem['sku']				=	$combineSkuVisit;								
								$splititem['barcode']			=	$combineBarcode;
								$splititem['channel_sku']		=	rtrim($channel_sku,'__');
								//pr($splititem);

								echo "<pre>splititem :: "; print_r($splititem);
								
								$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else if(in_array($servicePostal,['Express','Tracked','Standard','Stamps/Franking']))
							{	
								//Change on 1 july 2021
								//pr($serviceNameNow);
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}
								
								echo "<br>orderGroup: ".$orderGroup; 
								//die; 

								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								$channel_sku ='';
								foreach( $orderitems as $orderitem )
								{
									$channel_sku = $orderitem->RowId.'__';
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = @$productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . @$productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = @$productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . @$productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;
										
									}
																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$combinePrice;
								$splititem['product_order_id_identify']		=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$result->NumOrderId;
								$splititem['sku']			=	$combineSkuVisit;								
								$splititem['barcode']		=	$combineBarcode;
								$splititem['channel_sku']	=	rtrim($channel_sku,'__');
								//pr($splititem);

								//echo "<pre>splititem :: "; print_r($splititem); die; 

								echo "<pre>11 splititem :: "; print_r($splititem);
								
								$productDetail['Product']['current_stock_level']	= @$productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= @$productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else if( ($orderItemValueTotal * $baseRate) > 100 )
							{	
								
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}
								
								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								$channel_sku ='';
								foreach( $orderitems as $orderitem )
								{
									$channel_sku = $orderitem->RowId.'__';
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;
										
									}
																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$combinePrice;
								$splititem['product_order_id_identify']		=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$result->NumOrderId;
								$splititem['sku']			=	$combineSkuVisit;								
								$splititem['barcode']		=	$combineBarcode;
								$splititem['channel_sku']	=	rtrim($channel_sku,'__');
								//pr($splititem);
								
								$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else
							{	$channel_sku = '';
								$bundleIdentity = 0;foreach( $orderitems as $orderitem )
								{
									//echo $orderitem->SKU . '==' . $orderitem->PricePerUnit;
									//echo "<br>";
									$channel_sku = $orderitem->RowId.'__';
									$splitskus	=	explode('-', $orderitem->SKU);
									$count	=	count($splitskus);
									
									if( count( $orderitems ) > 1 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
									
									//Find Country
									if( in_array( $country , $countryArray ) )
									{			
										
										//It means, Inside EU country shipping for bundle with sameSKU
										if($splitskus['0'] == 'B')
										{
											
											for( $i = 1; $i <= count($splitskus)-2 ; $i++ )
											{																						
												if( $count == 3 )
												{													
													$value = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
													
													//echo $value = $orderitem->CostIncTax * $baseRate;
													//echo "<br>";
													
													//For Euro												
													if( $value <= 54.20 || $value > 100 )
													{													
														$numId = '';
														if( $bundleIdentity > 0 )
														{
															$bundleIdentity = $bundleIdentity + 1;												
															$numId = $result->NumOrderId .'-'. $bundleIdentity;
															$splititem['product_order_id_identify']		=	$numId;
															$splititem['order_split']		=	$orderGroup;
															//$splititem['order_split']		=	"split";
														}
														else 
														{
															if( count($orderitems) == 1 )
															{
																$numId = $result->NumOrderId;	
															}
															else
															{
																$bundleIdentity = $bundleIdentity + 1;	
																$numId = $result->NumOrderId .'-'. $bundleIdentity;	
																$splititem['product_order_id_identify']		=	$numId;
																$splititem['order_split']		=	$orderGroup;
																//$splititem['order_split']		=	"split";
															}
														}
														
														$splititem['order_split']		=	$orderGroup;
														$splititem['pack_order_quantity']		=	$splitskus[$count-1];
														$splititem['product_sku_identifier']		= "single";			
														$splititem['price']		=	( $orderitem->Quantity * $splitskus[2] ) * (($orderitem->Quantity * $orderitem->PricePerUnit) / ( $orderitem->Quantity * $splitskus[2] ));   //$splitskus[2] * ($orderitem->PricePerUnit / $splitskus[2]);
														
														$splititem['quantity']			=	( $orderitem->Quantity * $splitskus[2] ); //$orderitem->Quantity;
														$splititem['product_type']		=	"bundle";
														$splititem['order_id']		=	$result->NumOrderId;
														
														if( count($orderitems) == 1 )
															$splititem['sku']			=	( $orderitem->Quantity * $splitskus[2] ) .'X'. 'S-'.$splitskus[$i];
														else
															$splititem['sku']			=	'S-'.$splitskus[$i];	
															
														$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => 'S-'.$splitskus[$i] )));
														$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
														
														//pr($splititem);
														
														$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
														$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
														
														$splititem['channel_sku'] =  rtrim($channel_sku,'__');
														/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																					array('Product.product_sku' => 'S-'.$splitskus[$i]));*/
																					
														//pr($productDetail); 
																					
														$this->OrderItem->create();
														$this->OrderItem->save( $splititem );													
													}
													else
													{
														
														if( $value <= 100 && $value > 54.20 )	
														{
															$total = 0;
															$perUnitPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
															$orderQuantity = $orderitem->Quantity * $splitskus[$count-1];
															
															$itemPrice = $perUnitPrice / $orderQuantity;
															
															$inc = 0;														
															$checkOuter = 0;
															$isLeader = false;
															
															if( ( $orderQuantity > 1 ) )
															{														
																//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
																$e = 0;while( $e <= ($orderQuantity-1) )
																{	
																	
																	//$total = $total + ( $baseRate * $itemPrice );
																	
																	if( ( $total + ( $baseRate * $itemPrice ) ) <= 54.20 )
																	{
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;
																		//echo "<br>";
																		$inc++;
																		$checkOuter++;
																		$isLeader = true;
																		
																		if( $e == ($orderQuantity-1) )
																		{
																			//echo "Now Split" . $total;
																			//echo "<br>*********<br>";
																			
																			//Splitting the order accordign the rule
																			//Store previous then initialized																	
																			$bundleIdentity++;																	
																			//Store and split the same SKU bundle order
																			$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																			$splititem['product_sku_identifier']		= "single";			
																			$splititem['price']		=	$total / $baseRate;
																			$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																			
																			$splititem['order_split']		=	$orderGroup;
																			$splititem['quantity']			=	$inc;
																			$splititem['product_type']		=	"bundle";
																			$splititem['order_id']		=	$result->NumOrderId;
																			$splititem['sku']			=	'S-'.$splitskus[$i];
																			$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																			$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																			
																			//pr($splititem);
																			
																			$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																			$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																			
																			$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																			/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																										array('Product.product_sku' => $splititem['sku']));*/
																			$this->OrderItem->create();
																			$this->OrderItem->save( $splititem );
																			
																			$total = 0;
																			$inc = 1;
																			$total = $total + ( $baseRate * $itemPrice );
																			//echo $total;																	
																			//echo "<br>";
																		}
																	}
																	else
																	{
																		
																		if( $isLeader == false )
																		{
																			//Increase Counter
																			$checkOuter++;
																			$total = $total + ( $baseRate * $itemPrice );
																			
																			if( $e == ($orderQuantity-1) )
																			{																			
																				$inc = 1;
																				//echo "Now Split " . $total;
																				//echo "<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				$splititem['order_split']		=	$orderGroup;
																				//$splititem['order_split']		=	"split";
																				$splititem['quantity']			=	$checkOuter;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem); 
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 0;
																				
																			}
																			
																		}
																		else
																		{
																			
																			if( $e == ($orderQuantity-1) )
																			{
																				
																				//For Previous calculate and store it split order into database
																				//echo "Now Split------" . $total;
																				//echo "<br>*********<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																				//Now store last index calculation if reaches at end point then 
																				//need to be remind , there is last one we have to also store into database
																				//echo "Now Split" . $total;
																				//echo "<br>*********<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																			}
																			else
																			{
																				
																				//echo "Now Split " . $total;
																				//echo "<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																			}
																					
																		}
																		
																	}
																	
																$e++;	
																}
															}
															
														}
														else
														{
															//echo "Exceed Limit to split."; exit;
														}												
													}
												}
												else
												{	
													
													//Get Count Sku for bundle with multiple
													$getLastIndex = $splitskus[count($splitskus)-1];
													
													//Handle Multiple Sku with type bundle												
													$value = $orderitem->Quantity * ($orderitem->PricePerUnit / $getLastIndex) * $baseRate;
													
													//echo $value = $orderitem->CostIncTax * $baseRate;
													//echo "<br>";
													
													$anotherValue = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
													
													//For Euro												
													if( $anotherValue <= 54.20 || $anotherValue > 100 )
													{	
														if( (count($splitskus)-2) == $i )
														{
															$totalQuantity = $orderitem->Quantity;
															//echo "<br>";
															$combinedSkuForMulti .= ',' . $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
															
															if( $bundleIdentity > 0 )
															{
																if( count($orderitems) > 1 )
																{
																	$bundleIdentity = $bundleIdentity + 1;												
																	$numId = $result->NumOrderId .'-'. $bundleIdentity;
																}
																else
																{										
																	$numId = $result->NumOrderId;
																}															
															}
															else
															{
																if( count($orderitems) > 1 )
																{
																	$bundleIdentity = $bundleIdentity + 1;												
																	$numId = $result->NumOrderId .'-'. $bundleIdentity;
																}
																else
																{											
																	$numId = $result->NumOrderId;
																}
															}
															
															$splititem['product_order_id_identify']		=	$numId;
															
															$splititem['order_split']		=	"Group B";
															$splititem['pack_order_quantity']		=	$splitskus[$count-1];
															$splititem['product_sku_identifier']		= "multiple";			
															$splititem['price']		=	$orderitem->Quantity * $orderitem->PricePerUnit;
															
															$splititem['quantity']			=	$orderitem->Quantity * $getLastIndex; //$totalQuantity;
															$splititem['product_type']		=	"bundle";
															$splititem['order_id']		=	$result->NumOrderId;
															$splititem['sku']			=	$combinedSkuForMulti;
															$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => 'S-'.$splitskus[$i] )));
															$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
															
															$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
															$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
															$splititem['channel_sku'] =  rtrim($channel_sku,'__');
															/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																						array('Product.product_sku' => $splititem['sku']));*/
															$this->OrderItem->create();
															$this->OrderItem->save( $splititem );
															$combinedSkuForMulti = '';
														}
														else
														{
															$totalQuantity = $orderitem->Quantity * $getLastIndex; //$orderitem->Quantity;
															//echo "xxxxx<br>";
															
															//if( $i == 1 )
															
															if( $combinedSkuForMulti == '' )
																$combinedSkuForMulti = $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
															else	
																$combinedSkuForMulti .= ',' . $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
														}
														
													}
													else
													{
														
														if( $anotherValue <= 100 && $anotherValue > 54.20 )	
														{
															$total = 0;
															
															//total price
															$perUnitPrice = ( $orderitem->Quantity * $orderitem->PricePerUnit );
																		
															//total quantity														
															$orderQuantity = $orderitem->Quantity * $getLastIndex;
															
															//unit price
															$itemPrice = $perUnitPrice / $orderQuantity;
															
															$inc = 0;														
															$checkOuter = 0;
															$isLeader = false;
															$total = 0;
															
															if( ( $orderQuantity > 0 ) )
															{														
																
																//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
																$inc = 0;$out = 0;while( $out <= $orderitem->Quantity-1 )
																{
																	
																	//Store
																	//echo " Bundle Multiple SKUxx " . $total;
																	//echo "<br>";
																			
																	//Splitting the order accordign the rule
																	//Store previous then initialized																	
																	$bundleIdentity++;			
																	$inc++;;														
																	//Store and split the same SKU bundle order
																	$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																	$splititem['product_sku_identifier']		= "single";			
																	$splititem['price']		=	$itemPrice;
																	$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																	
																	//$splititem['order_split']		=	"split";
																	$splititem['order_split']		=	"Group B";
																	$splititem['quantity']			=	$inc;
																	$splititem['product_type']		=	"bundle";
																	$splititem['order_id']		=	$result->NumOrderId;
																	$splititem['sku']			=	'S-'.$splitskus[$i];
																	$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																	$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																	
																	//pr($splititem);
																	
																	$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																	$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																	$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																	/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																								array('Product.product_sku' => $splititem['sku']));*/
																	$this->OrderItem->create();
																	$this->OrderItem->save( $splititem );
																	
																	$inc = 0;
																$out++;	
																}															
															}
														}
													}
												}										
											}
										}
										else
										{
											
											// Single SKU order splitting
											$value = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
												
											//echo $value = $orderitem->CostIncTax * $baseRate;
											//echo "<br>";
											
											//For Euro												
											if( $value <= 54.20 || $value > 100 )
											{
												
												$numId = '';
												if( $bundleIdentity > 0 )
												{
													$bundleIdentity = $bundleIdentity + 1;												
													$numId = $result->NumOrderId .'-'. $bundleIdentity;
													$splititem['product_order_id_identify']		=	$numId;
													//$splititem['order_split']		=	"split";
												}
												else
												{
													if( count($orderitems) == 1 )
													{
														$numId = $result->NumOrderId;	
													}
													else
													{
														$bundleIdentity = $bundleIdentity + 1;	
														$numId = $result->NumOrderId .'-'. $bundleIdentity;	
														$splititem['product_order_id_identify']		=	$numId;
														//$splititem['order_split']		=	"split";
													}
												}
												
												$splititem['order_split']		=	$orderGroup;
												$splititem['pack_order_quantity']		=	0;
												$splititem['product_sku_identifier']		= "single";			
												$splititem['price']		=	$orderitem->Quantity * $orderitem->PricePerUnit;
												
												$splititem['quantity']			=	$orderitem->Quantity;
												$splititem['product_type']		=	"single";
												$splititem['order_id']		=	$result->NumOrderId;
												
												if( count( $orderitems ) == 1 )
													$splititem['sku']			=	$orderitem->Quantity .'X'. $orderitem->SKU;
												else
													$splititem['sku']			=	$orderitem->SKU;
													
												$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
												$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
												
												//pr($splititem);
												$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
												$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
												$splititem['channel_sku'] =  rtrim($channel_sku,'__');
												/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																			array('Product.product_sku' => $orderitem->SKU));*/
												$this->OrderItem->create();
												$this->OrderItem->save( $splititem );
																									
											}
											else
											{
												
												if( $value <= 100 && $value > 54.20 )	
												{
													
													$total = 0;
													$perUnitPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
													
													$orderQuantity = $orderitem->Quantity;
													
													$itemPrice = $perUnitPrice / $orderQuantity;
													
													$inc = 0;
													$checkOuter = 0;
													$isLeader = false;
													
													if( ( $orderQuantity > 1 ) )
													{		
																										
														//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
														$e = 0;while( $e <= ($orderQuantity-1) )
														{	
															
															//$total = $total + ( $baseRate * $itemPrice );
															
															if( ( $total + ( $baseRate * $itemPrice ) ) <= 54.20 )
															{
																$total = $total + ( $baseRate * $itemPrice );
																//echo $total;
																//echo "<br>";
																$inc++;
																$checkOuter++;
																$isLeader = true;
																
																if( $e == ($orderQuantity-1) )
																{
																	//echo "Now Split" . $total;
																	//echo "<br>*********<br>";
																	
																	//Splitting the order accordign the rule
																	//Store previous then initialized																	
																	$bundleIdentity++;																	
																	//Store and split the same SKU bundle order
																	$splititem['pack_order_quantity']		=	0;
																	$splititem['product_sku_identifier']		= "single";			
																	$splititem['price']		=	$total / $baseRate;
																	$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																	//$splititem['order_split']		=	"split";
																	
																	$splititem['order_split']		=	$orderGroup;
																	$splititem['quantity']			=	$inc;
																	$splititem['product_type']		=	"single";
																	$splititem['order_id']		=	$result->NumOrderId;
																	$splititem['sku']			=	$orderitem->SKU;
																	$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																	$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																	
																	//pr($splititem);
																	
																	$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																	$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																	
																	$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																	/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																								array('Product.product_sku' => $splititem['sku']));*/
																	$this->OrderItem->create();
																	$this->OrderItem->save( $splititem );
																	
																	$total = 0;
																	$inc = 1;
																	$total = $total + ( $baseRate * $itemPrice );
																	//echo $total;																	
																	//echo "<br>";
																}
															}
															else
															{
																
																if( $isLeader == false )
																{
																	//Increase Counter
																	$checkOuter++;
																	$total = $total + ( $baseRate * $itemPrice );
																	
																	if( $e == ($orderQuantity-1) )
																	{																			
																		$inc = 1;
																		//echo "Now Split " . $total;
																		//echo "<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$checkOuter;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem); 
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 0;
																		
																	}
																	
																}
																else
																{
																	
																	if( $e == ($orderQuantity-1) )
																	{
																		
																		//For Previous calculate and store it split order into database
																		//echo "Now Split------" . $total;
																		//echo "<br>*********<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		//$splititem['order_split']		=	"split";
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																		//Now store last index calculation if reaches at end point then 
																		//need to be remind , there is last one we have to also store into database
																		//echo "Now Split" . $total;
																		//echo "<br>*********<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																	}
																	else
																	{
																		
																		//echo "Now Split " . $total;
																		//echo "<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																	}
																			
																}
																
															}
															
														$e++;	
														}
													}
													else
													{
														
														//If order item count is 1 then would be store directly
														$splititem['pack_order_quantity']		=	0;
														$splititem['product_sku_identifier']		= "single";			
														$splititem['price']		=	$orderitem->PricePerUnit;
														
														$splititem['order_split']		=	$orderGroup;
														$splititem['quantity']			=	$orderitem->Quantity;
														$splititem['product_type']		=	"single";
														$splititem['order_id']		=	$result->NumOrderId;
														$splititem['sku']			=	$orderitem->SKU;
														$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
														$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
														
														$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
														$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
														$splititem['channel_sku'] =  rtrim($channel_sku,'__');
														/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																					array('Product.product_sku' => $splititem['sku']));*/
														$this->OrderItem->create();
														$this->OrderItem->save( $splititem );
														
													}
													
												}
												else
												{
													//echo "Exceed Limit to split."; exit;
												}												
											}
												
										}
										
									}
									else
									{
										
										$getCurrencyText = $result->TotalsInfo->Currency;
										$getCountryText = $result->CustomerInfo->Address->Country;
										
										if( $getCurrencyText == "EUR" )
										{
											$baseRate = '1';
										}
										else
										{
											$baseRate = '1.13';
										}
										
										/***************** split the order item ******************/
										$orderItemValueTotal = 0;foreach( $orderitems as $orderitem )
										{
											$orderItemValueTotal = $orderItemValueTotal + $orderitem->Cost;
										}
										
										//if( ($orderItemValueTotal * $baseRate) > 250 || ($orderItemValueTotal * $baseRate) <= 54.20 )
										
										if( ($orderItemValueTotal * $baseRate) >= 0 )
										{	
											if( count( $orderitems ) > 1 )
											{
												$orderGroup = "Group B";
											}
											else
											{
												if( count(explode('-',$orderitems[0]->SKU)) > 3 )
												{
													$orderGroup = "Group B";
												}
												else
												{
													$orderGroup = "Group A";
												}
											}
											
											//Store direct into storage
											$combineSkuVisit = '';
											$combinePrice = 0;
											$combineQuantity = 0;
											$combineBarcode = '';											
											foreach( $orderitems as $orderitem )
											{	$channel_sku = $orderitem->RowId.'__';
												if( count( explode( '-', $orderitem->SKU ) ) == 2 )
												{
													if( $combineSkuVisit == '' )
													{
														$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
														$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
														$combineQuantity = $combineQuantity + $orderitem->Quantity;
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
														$combineBarcode = $productDetail['ProductDesc']['barcode'];
													}
													else
													{
														$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
														$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
														$combineQuantity = $combineQuantity + $orderitem->Quantity;
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
														$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
													}
													
												}
												else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
												{
													$splitskus = explode( '-' , $orderitem->SKU);										
													if( $combineSkuVisit == '' )
													{
														$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
														$combineQuantity = $combineQuantity + $splitskus[2];
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
														$combineBarcode = $productDetail['ProductDesc']['barcode'];
													}
													else
													{
														$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
														$combineQuantity = $combineQuantity + $splitskus[2];
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
														$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
													}
													
												}
												else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
												{
													// For Bundle with muti type Sku
													$splitskus = explode( '-', $orderitem->SKU );
													
													$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
													$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
													
													$in = 1; while( $in <= count( $splitskus )-2 ):											
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
														if( $combineSkuVisit == '' )
														{
															//$quantity = $quantity + $orderitem->Quantity;
															$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
															$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
														}
														else
														{
															//$quantity = $quantity + $orderitem->Quantity;
															$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
															$combineQuantity = $combineQuantity + $orderitem->Quantity;
														}
													$in++;
													endwhile;
													
												}
																					
											}
											
											//Saving
											// For Bundle with same type Sku				
											//Store and split the same SKU bundle order
											$splititem['pack_order_quantity']		=	0;
											$splititem['product_sku_identifier']		= "single";			
											$splititem['price']		=	$combinePrice;
											$splititem['product_order_id_identify']		=	$result->NumOrderId;
											
											$splititem['order_split']		=	$orderGroup;
											$splititem['quantity']			=	$combineQuantity;
											$splititem['product_type']		=	"bundle";
											$splititem['order_id']		=	$result->NumOrderId;
											$splititem['sku']			=	$combineSkuVisit;								
											$splititem['barcode']		=	$combineBarcode;
											$splititem['channel_sku'] 	=  rtrim($channel_sku,'__');
											//pr($splititem);
											
											$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
											$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
											
											/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																		array('Product.product_sku' => $splititem['sku']));*/
											$this->OrderItem->create();
											$this->OrderItem->save( $splititem );
											//echo "saved";
											break;
										}										
										
									}
									
								}
								
							}	

							echo "<br>flagVar : ".$flagVar;							

							//************** Merge Order after splitting with different scenarios **************
							$this->mergeSplitOrdersByOrderId_AccordingRules( $result->NumOrderId , $flagVar );
                                                        
                            echo "<br>after mergeSplitOrdersByOrderId_AccordingRules : ";
                                                        
							//$this->loadModel( 'MergeOrder' );							
							//pr( $this->MergeOrder->find('all') ); exit;
							
							$emailaddress = $result->CustomerInfo->Address->EmailAddress;

							if($emailaddress != ''){
								
								// code for save customer detail
								$customerInfo['Customer']['email']		=	$result->CustomerInfo->Address->EmailAddress;
								$customerInfo['Customer']['address1']	=	$result->CustomerInfo->Address->Address1;
								$customerInfo['Customer']['address2']	=	$result->CustomerInfo->Address->Address2;
								$customerInfo['Customer']['address3']	=	$result->CustomerInfo->Address->Address3;
								$customerInfo['Customer']['town']		=	$result->CustomerInfo->Address->Town;
								$customerInfo['Customer']['region']		=	$result->CustomerInfo->Address->Region;
								$customerInfo['Customer']['postcode']	=	$result->CustomerInfo->Address->PostCode;
								$customerInfo['Customer']['country']	=	$result->CustomerInfo->Address->Country;
								$customerInfo['Customer']['name']		=	$result->CustomerInfo->Address->FullName;
								$customerInfo['Customer']['company']	=	$result->CustomerInfo->Address->Company;
								$customerInfo['Customer']['phone']		=	$result->CustomerInfo->Address->PhoneNumber;
								$customerInfo['Customer']['source']		=	$result->GeneralInfo->Source;
								$customerInfo['Customer']['subsource']	=	$result->GeneralInfo->SubSource;
								
								$customerdetails	=	$this->Customer->find('first', array('conditions' => array('Customer.email' => $customerInfo['Customer']['email'])));
								
								if( count($customerdetails) > 0 )
								{
									//$customerdetails['Customer']['count'] = $customerdetails['Customer']['count'] + '1';
									$this->Customer->updateAll(array('Customer.order_id' => $result->NumOrderId),
									array('Customer.email' => $customerdetails['Customer']['email']));
								}
								else
								{
									$customerInfo['Customer']['order_id']		=	$result->NumOrderId;
									$this->Customer->create();
									$this->Customer->saveAll( $customerInfo );
								}
							}
							//App::import('Controller', 'Profits');
							//$ProfitsObj = new ProfitsController(); 
							//$ProfitsObj->setSkuProfitLoss( $result->NumOrderId );
						}	
					}
					else
					{
						//Do something you required
					}	
					
					/*
					 * 
					 * 
					 * If, order will blank saved thne will try to flush
					 * 
					 * 
					 */ 
					 //$this->checkOrderIsBlank( $result->NumOrderId );
					
					/*
					 * 
					 * 
					 * If, order will blank saved thne will try to flush
					 * 
					 * 
					 */ 
					 $this->removeDuplicateOrderByID( $result->NumOrderId );

					 echo "<br>after removeDuplicateOrderByID : ";

					//$this->flushBlankOrder( $result->NumOrderId , $locationName );
					//pr($result);
					//pr($result); 
					if($result->CustomerInfo->Address->Country == 'Italy' && $result->ShippingInfo->PostalServiceName != 'Express') 
					{
								App::import('Controller', 'Brt');
								$brtObj = new BrtController(); 
							//	$brtObj->generateBrtLabels( $result->NumOrderId );
						/*$orderdetails	=	$this->MergeUpdate->find('all', array( 'conditions' => array( 'MergeUpdate.order_id' => $result->NumOrderId) ) );
						foreach($orderdetails as $orderdetail)
						{
							if($orderdetail['MergeUpdate']['price'] < 22)
							{
								App::import('Controller', 'Brt');
								$brtObj = new BrtController(); 
								$brtObj->generateBrtLabels( $result->NumOrderId );
							}
						}*/
					}else if($result->CustomerInfo->Address->Country == 'United Kingdom' && $result->ShippingInfo->PostalServiceName != 'Express' && $result->GeneralInfo->SubSource != 'Onbuy'){					
						/*****************Apply RoyalMail 16042018*******************/			
						/*App::Import('Controller', 'RoyalMail'); 
						$royal = new RoyalMailController;
						$royal->applyRoyalMail($result->NumOrderId); */
					}
					
					if($result->CustomerInfo->Address->Country == 'United Kingdom'){					
  						/*****************Apply Whistl 28122020*******************/			
						App::Import('Controller', 'Whistl'); 
						$whistl = new WhistlController;
						//$whistl->applyWhistl($result->NumOrderId); 15-01-2021
					}else{		
						App::Import('Controller', 'Postnl'); 
						$pnl = new PostnlController;
						//$pnl->Labelling($result->NumOrderId);
 					}
					
					/*--More than one Real.DE order and total is greather than 26(Apply on 06-11-2019 )--*/
					if($result->GeneralInfo->SubSource == 'Costdropper'){
						App::import('Controller', 'Reports');
						$rObj = new ReportsController(); 
						//$rObj->RealDeOrder();
					}
					if($result->GeneralInfo->Source == 'CDISCOUNT'){
						App::import('Controller', 'Reports');
						$rObj = new ReportsController(); 
						//$rObj->cDiscountOrder();
					}
					//if(!in_array($result->CustomerInfo->Address->Country,['United Kingdom','UNKNOWN'])){
						/*-------Jersey Post API 30-12-2020-------*/
						App::Import('Controller', 'Jerseypost'); 
						$jp = new JerseypostController();
						$jp->createShipment($result->NumOrderId );
					//}
					//App::import('Controller', 'Profits');
					//$ProfitsObj = new ProfitsController(); 
					//$ProfitsObj->setSkuProfitLoss( $result->NumOrderId );
									
				}
				
				echo "<br>after JerseypostController : ";
						
				/* update tyose order has 0 quantity */
				//$this->updateMergeOrder();
				
				/* call the function for assign the postal servises */
				//$this->assign_services();
				$this->getBarcode();
				
				echo "<br>after getBarcode : ";

				$this->assignRegisteredBarcode();	
				
				echo "<br>after assignRegisteredBarcode : ";

				//$this->setAgainAssignedServiceToAllOrder(); // Euraco Group	 
				
				//pr($orders); 
				
				App::import('Controller', 'Virtuals');
				$virtualModel = new VirtualsController(); 
				//$virtualModel->creatFeedSheet_old( $locationName );
				
				//Sync start
				App::import( 'Controller' , 'MyExceptions' ); 
				$exception = new MyExceptionsController();
				$exception->syncComp( $locationName );
				
				//Delete cancel UNKNOWN orders
				if(count($orders) > 1){					
					$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
								'conditions' => array(
									'UnprepareOrder.order_id NOT IN ' => $orders,
									'UnprepareOrder.source_name' => $locationName
									),									
								  'fields' => array( 
									'UnprepareOrder.order_id',
									'UnprepareOrder.num_order_id',
									'UnprepareOrder.unprepare_check',
									'UnprepareOrder.id',  
									'UnprepareOrder.items'  
								)
							)
						);
					}else{				
						
						$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
									'conditions' => array(
										'UnprepareOrder.order_id !=' => $orders[0],
										'UnprepareOrder.source_name' => $locationName
										),									
									  'fields' => array( 
										'UnprepareOrder.order_id',
										'UnprepareOrder.num_order_id',
										'UnprepareOrder.unprepare_check',
										'UnprepareOrder.id',  
										'UnprepareOrder.items'  
									)
								)
							);

					}
				
				foreach( $getAllUnprepardId  as $getAllId )
				{
					$itemsReserve = unserialize($getAllId['UnprepareOrder']['items']);
					$this->reserveInventoryForUnknown( $itemsReserve , 4 , $getAllId['UnprepareOrder']['num_order_id'] , $getAllId['UnprepareOrder']['unprepare_check'] );
					##$this->UnprepareOrder->delete( $getAllId['UnprepareOrder']['id'] );
				}	
				
			return "done";	
			}


			//xsensys copy
			public function saveOpenOrderunprepord( $results = null , $locationName = null , $orders = null ) 
			{				
				//echo "<pre>"; print_r($results);die;
				$itt = 1;
				$countryArray = Configure::read('customCountry');
				$this->loadModel('Country');
  				
				foreach($results as $result)
				{				
 					
					if(count($result->Items) == 0){
						$log_file = WWW_ROOT .'cron-logs/item_'.$locationName.date('dmy').".log";					
						//file_put_contents($log_file, print_r($result,true),  FILE_APPEND|LOCK_EX);
						//return "done";	
					}
					 
					/*if( ($result->GeneralInfo->Status == 1 || $result->GeneralInfo->Status == 4 ) && $result->GeneralInfo->HoldOrCancel == ''  )
					{*/
					
					if($result->ShippingInfo->PostalServiceName == 'Stamps/Franking'){
						$result->ShippingInfo->PostalServiceName = 'Standard';
 						file_put_contents(WWW_ROOT .'logs/stamps_franking_'.date('Ymd').".log", $result->NumOrderId."\n",  FILE_APPEND | LOCK_EX);	
					}
					
					$data['order_id']		= $result->OrderId;
					$data['num_order_id']	= $result->NumOrderId;
					$data['amazon_order_id']= $result->GeneralInfo->ReferenceNum;
					$data['general_info']	= serialize($result->GeneralInfo);
					$data['shipping_info']	= serialize($result->ShippingInfo);
					$data['customer_info']	= serialize($result->CustomerInfo);
					$data['totals_info']	= serialize($result->TotalsInfo);
					$data['folder_name']	= serialize($result->FolderName);
					$data['items']			= serialize($result->Items);
					$data['linn_fetch_orders'] = $result->GeneralInfo->Status;
					$data['order_parked'] 	= $result->GeneralInfo->Marker;
					$data['sub_source']		= $result->GeneralInfo->SubSource;

					//echo "<pre>data :: "; print_r($data); die; 
					
					if(isset($result->TaxInfo) && count($result->TaxInfo) > 0){
						if($result->TaxInfo->TaxNumber != ''){
							$data['buyer_vat_number'] = $result->TaxInfo->TaxNumber;
						}
					}
					
					if(isset($result->Notes) && count($result->Notes) > 0){
						$data['notes']			= json_encode($result->Notes);
					}
					
  					if(isset($result->ExtendedProperties) && count($result->ExtendedProperties) > 0){
						$data['extended_properties'] = json_encode($result->ExtendedProperties);
						foreach($result->ExtendedProperties as $p){
							if($p->Name == 'MARKETPLACE_IOSS'){
								$data['ioss_no'] = $p->Value;
							}
						}
					}
					
					$ddNew = explode("T",$result->GeneralInfo->ReceivedDate);
					$ddNew = $ddNew[0] .' '. $ddNew[1];
					
					$data['open_order_date'] = $ddNew;
					
					//Extra information will save my according to manage sorting station section
					//$checkOpenOrder =	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $orderids)));
					$country = $data['destination'] = $result->CustomerInfo->Address->Country;
					$orderitems	=	unserialize($data['items']);
					
					$flagVar = 3;
							
					
					$itt++;
					
 					#echo "<pre>result :: "; print_r($result); die; 

					//Check Unpreparee step by step
					$getAllUnprepardId = $this->UnprepareOrder->find('first', array('conditions'=>array('UnprepareOrder.order_id' => $result->OrderId) , 'fields' => array( 'UnprepareOrder.order_id', 'UnprepareOrder.num_order_id' , 'UnprepareOrder.id' , 'UnprepareOrder.linn_fetch_orders' , 'UnprepareOrder.destination' )));
					
					//echo "<pre>getAllUnprepardId :: "; print_r($getAllUnprepardId);
					//echo "<pre>getAllUnprepardId :: "; print_r($result); //die;

					if( count($getAllUnprepardId) > 0)
					{				
						//If it is UNKNOWN or not and if order is paid 
						if( $result->ordstus == 1)
						{								
						 	$flagVar = 3;
						} else if( $country !== $getAllUnprepardId['UnprepareOrder']['destination'] )
						{							
							//$flagVar = $this->unprepareOrder( $result , $result->OrderId );
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
						} elseif( $result->ordstus == 0)
						{								
						 	# if the payment status is none or partial - order is not fully paid then mark order as incomp 
							//$flagVar = $this->unprepareOrder($result);
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
						} else if(count($result->Items) == 0){							
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
							
							$log_file = WWW_ROOT .'cron-logs/Orders_1_'.$result->location.date('dmy').".log";			
							file_put_contents($log_file, $result->NumOrderId."\t".$result->GeneralInfo->ReferenceNum."\n",  FILE_APPEND|LOCK_EX);
							$log_file = WWW_ROOT .'cron-logs/item_1_'.$result->location.date('dmy').".log";					
							file_put_contents($log_file, print_r($result,true),  FILE_APPEND|LOCK_EX);
						}
						else
						{							
							//$flagVar = $this->unprepareOrder( $result , $result->OrderId );
							$flagVar = $this->unprepareOrder($result , $locationName , $result->NumOrderId );
						}
						
					}
					else
					{						
						//If it is UNKNOWN or not and if order is paid 
						if( $result->ordstus == 1)
						{								
						 	$flagVar = 3;
						} else if( $country == "UNKNOWN")
						{
							//$flagVar = $this->unprepareOrder($result);
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
						} elseif( $result->ordstus == 0)
						{	
						 	# if the payment status is none or partial - order is not fully paid then mark order as incomp 
							//$flagVar = $this->unprepareOrder($result);
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
						} else if(count($result->Items) == 0){
							$flagVar = $this->unprepareOrder($result , $result->location , $result->NumOrderId );
							
							//$log_file = WWW_ROOT .'cron-logs/Orders_'.$locationName.date('dmy').".log";			
							file_put_contents($log_file, $result->NumOrderId."\t".$result->GeneralInfo->ReferenceNum."\n",  FILE_APPEND|LOCK_EX);
							//$log_file = WWW_ROOT .'cron-logs/item_'.$locationName.date('dmy').".log";					
							file_put_contents($log_file, print_r($result,true),  FILE_APPEND|LOCK_EX);
						}
						else
						{
							$flagVar = 3;
						} 
					}
					
					/*------Added on 17 AUG2021----*/
					$chkOpenOrder =	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.num_order_id' => $result->NumOrderId))); 
					if(count($chkOpenOrder) > 0){
						$flagVar = 2;
						$log__file = WWW_ROOT .'logs-ord/duplicates_'.date('dmY').".log";					
						file_put_contents($log__file,$result->NumOrderId."\t".date('Y-m-d H:i:s')."\n",  FILE_APPEND|LOCK_EX);
					}
					//sleep(1);
 					/*-------End----*/
									 
					/*------Added on 19 AUG 2021----*/
					$ord_flag = WWW_ROOT .'logs-ord/'.$result->NumOrderId.".flag";	
					if(file_exists($ord_flag)){
						$flagVar = 2; $skip = 1;
					}else{				
						file_put_contents($ord_flag,$result->NumOrderId,  FILE_APPEND|LOCK_EX);
					}
					/*-------End----*/	
					$removeIds = array();
					if( $result->ordstus == 1)
					{								
					 	$flagVar = 3;
					 	$removeIds[] = $result->OrderId;
					}
					
					//echo "<pre>removeIds :: "; print_r($removeIds);
					//die;
					//echo "<br>flagVar: ".$flagVar; die;
					
					if( $flagVar != 2 )
					{							

						//Check OpenOrder
						$this->OpenOrder->create();
						$checkorder 	=	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $result->OrderId,'OpenOrder.num_order_id' => $result->NumOrderId) , 'fields' => array( 'OpenOrder.order_id', 'OpenOrder.num_order_id' , 'OpenOrder.id' , 'OpenOrder.linn_fetch_orders' )));
						
						if(count($checkorder) > 0)
						{ 							
							//Clean Orders
							$this->cleanOrders();
							
							//CHECK IF ORDER EXISTS OR NOT
							// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
							//Order Marker -> 0 ----- 7 
							$linnStatus = '1';//$result->GeneralInfo->Status; 
							$dataUpdate['OpenOrder']['id'] = $checkorder['OpenOrder']['id'];
							$dataUpdate['OpenOrder']['linn_fetch_orders'] = '1';//$result->GeneralInfo->Status;
							$dataUpdate['order_parked'] 					= $result->GeneralInfo->Marker;														
							$dd = explode("T",$result->GeneralInfo->ReceivedDate);
							$dd = $dd[0] .' '. $dd[1];
							$dataUpdate['OpenOrder']['open_order_date'] = $dd;
							
                            //$dataUpdate['OpenOrder']['linn_fetch_orders'] = serialize($result->CustomerInfo);
							$this->OpenOrder->saveAll( $dataUpdate ); 
							
							//Now update into Merge Section
							$this->loadModel('MergeUpdate' );
							
							//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
							$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $linnStatus), array('MergeUpdate.order_id' => $result->NumOrderId) );
						}
						else //if( $flagVar != 2 )
						{
							echo "<br>NumOrderId: ".$result->NumOrderId;

							echo "<pre>result :: "; print_r($result);

							//echo "<pre>!!!!!!!!! : "; print_r($data); die; 
							$this->OpenOrder->save($data);
							
							//Clean Orders
							$this->cleanOrders();
							
							$getCurrencyText = $result->TotalsInfo->Currency;
							
							echo "<br>getCurrencyText: ".$getCurrencyText;

							if( $getCurrencyText == "EUR" )
							{
								$baseRate = '1';
							}
							else
							{
								$baseRate = '1.13';
							}
							
							echo "<br>baserate: ".$baseRate;

							/***************** split the order item ******************/
							$orderItemValueTotal = 0;
							foreach( $orderitems as $orderitem )
							{
								$orderItemValueTotal = $orderItemValueTotal + $orderitem->Cost;
							}
							
							echo "<br>orderItemValueTotal: ".$orderItemValueTotal;

							//Get special postal service name as discussed by shashi at run time when did launch
							$serviceNameNow = unserialize($data['shipping_info']);
							$servicePostal = $serviceNameNow->PostalServiceName;

							echo "<br>servicePostal: ".$servicePostal;
							//die;


							if( $servicePostal == "Standard_Jpost" )
							{	
								
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}
								
								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								$channel_sku ='';
								foreach( $orderitems as $orderitem )
								{
									$channel_sku = $orderitem->RowId.'__';
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
											
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;										
									}
																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']	=   "single";			
								$splititem['price']						=	$combinePrice;
								$splititem['product_order_id_identify']	=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']			=	$result->NumOrderId;
								$splititem['sku']				=	$combineSkuVisit;								
								$splititem['barcode']			=	$combineBarcode;
								$splititem['channel_sku']		=	rtrim($channel_sku,'__');
								//pr($splititem);

								echo "<pre>splititem :: "; print_r($splititem);
								
								$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else if(in_array($servicePostal,['Express','Tracked','Standard','Stamps/Franking']))
							{	
								//Change on 1 july 2021
								//pr($serviceNameNow);
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}
								
								echo "<br>orderGroup: ".$orderGroup; 
								//die; 

								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								$channel_sku ='';

								echo "<pre>orderitems :: "; print_r($orderitems); //die;

								foreach( $orderitems as $orderitem )
								{
									$channel_sku = $orderitem->RowId.'__';
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = @$productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . @$productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = @$productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . @$productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;
										
									}																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$combinePrice;
								$splititem['product_order_id_identify']		=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$result->NumOrderId;
								$splititem['sku']			=	$combineSkuVisit;								
								$splititem['barcode']		=	$combineBarcode;
								$splititem['channel_sku']	=	rtrim($channel_sku,'__');
								//pr($splititem);

								//echo "<pre>splititem :: "; print_r($splititem); die; 

								echo "<pre>11 productDetail :: "; print_r($productDetail);

								echo "<pre>11 splititem :: "; print_r($splititem);
								
								$productDetail['Product']['current_stock_level']	= @$productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= @$productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else if( ($orderItemValueTotal * $baseRate) > 100 )
							{	
								
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}
								
								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								$channel_sku ='';
								foreach( $orderitems as $orderitem )
								{
									$channel_sku = $orderitem->RowId.'__';
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;
										
									}
																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$combinePrice;
								$splititem['product_order_id_identify']		=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$result->NumOrderId;
								$splititem['sku']			=	$combineSkuVisit;								
								$splititem['barcode']		=	$combineBarcode;
								$splititem['channel_sku']	=	rtrim($channel_sku,'__');
								//pr($splititem);
								
								$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else
							{	$channel_sku = '';
								$bundleIdentity = 0;
								foreach( $orderitems as $orderitem )
								{
									//echo $orderitem->SKU . '==' . $orderitem->PricePerUnit;
									//echo "<br>";
									$channel_sku = $orderitem->RowId.'__';
									$splitskus	=	explode('-', $orderitem->SKU);
									$count	=	count($splitskus);
									
									if( count( $orderitems ) > 1 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
									
									//Find Country
									if( in_array( $country , $countryArray ) )
									{			
										
										//It means, Inside EU country shipping for bundle with sameSKU
										if($splitskus['0'] == 'B')
										{
											
											for( $i = 1; $i <= count($splitskus)-2 ; $i++ )
											{																						
												if( $count == 3 )
												{													
													$value = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
													
													//echo $value = $orderitem->CostIncTax * $baseRate;
													//echo "<br>";
													
													//For Euro												
													if( $value <= 54.20 || $value > 100 )
													{													
														$numId = '';
														if( $bundleIdentity > 0 )
														{
															$bundleIdentity = $bundleIdentity + 1;												
															$numId = $result->NumOrderId .'-'. $bundleIdentity;
															$splititem['product_order_id_identify']		=	$numId;
															$splititem['order_split']		=	$orderGroup;
															//$splititem['order_split']		=	"split";
														}
														else 
														{
															if( count($orderitems) == 1 )
															{
																$numId = $result->NumOrderId;	
															}
															else
															{
																$bundleIdentity = $bundleIdentity + 1;	
																$numId = $result->NumOrderId .'-'. $bundleIdentity;	
																$splititem['product_order_id_identify']		=	$numId;
																$splititem['order_split']		=	$orderGroup;
																//$splititem['order_split']		=	"split";
															}
														}
														
														$splititem['order_split']		=	$orderGroup;
														$splititem['pack_order_quantity']		=	$splitskus[$count-1];
														$splititem['product_sku_identifier']		= "single";			
														$splititem['price']		=	( $orderitem->Quantity * $splitskus[2] ) * (($orderitem->Quantity * $orderitem->PricePerUnit) / ( $orderitem->Quantity * $splitskus[2] ));   //$splitskus[2] * ($orderitem->PricePerUnit / $splitskus[2]);
														
														$splititem['quantity']			=	( $orderitem->Quantity * $splitskus[2] ); //$orderitem->Quantity;
														$splititem['product_type']		=	"bundle";
														$splititem['order_id']		=	$result->NumOrderId;
														
														if( count($orderitems) == 1 )
															$splititem['sku']			=	( $orderitem->Quantity * $splitskus[2] ) .'X'. 'S-'.$splitskus[$i];
														else
															$splititem['sku']			=	'S-'.$splitskus[$i];	
															
														$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => 'S-'.$splitskus[$i] )));
														$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
														
														//pr($splititem);
														
														$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
														$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
														
														$splititem['channel_sku'] =  rtrim($channel_sku,'__');
														/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																					array('Product.product_sku' => 'S-'.$splitskus[$i]));*/
																					
														//pr($productDetail); 
																					
														$this->OrderItem->create();
														$this->OrderItem->save( $splititem );													
													}
													else
													{
														
														if( $value <= 100 && $value > 54.20 )	
														{
															$total = 0;
															$perUnitPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
															$orderQuantity = $orderitem->Quantity * $splitskus[$count-1];
															
															$itemPrice = $perUnitPrice / $orderQuantity;
															
															$inc = 0;														
															$checkOuter = 0;
															$isLeader = false;
															
															if( ( $orderQuantity > 1 ) )
															{														
																//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
																$e = 0;while( $e <= ($orderQuantity-1) )
																{	
																	
																	//$total = $total + ( $baseRate * $itemPrice );
																	
																	if( ( $total + ( $baseRate * $itemPrice ) ) <= 54.20 )
																	{
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;
																		//echo "<br>";
																		$inc++;
																		$checkOuter++;
																		$isLeader = true;
																		
																		if( $e == ($orderQuantity-1) )
																		{
																			//echo "Now Split" . $total;
																			//echo "<br>*********<br>";
																			
																			//Splitting the order accordign the rule
																			//Store previous then initialized																	
																			$bundleIdentity++;																	
																			//Store and split the same SKU bundle order
																			$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																			$splititem['product_sku_identifier']		= "single";			
																			$splititem['price']		=	$total / $baseRate;
																			$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																			
																			$splititem['order_split']		=	$orderGroup;
																			$splititem['quantity']			=	$inc;
																			$splititem['product_type']		=	"bundle";
																			$splititem['order_id']		=	$result->NumOrderId;
																			$splititem['sku']			=	'S-'.$splitskus[$i];
																			$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																			$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																			
																			//pr($splititem);
																			
																			$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																			$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																			
																			$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																			/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																										array('Product.product_sku' => $splititem['sku']));*/
																			$this->OrderItem->create();
																			$this->OrderItem->save( $splititem );
																			
																			$total = 0;
																			$inc = 1;
																			$total = $total + ( $baseRate * $itemPrice );
																			//echo $total;																	
																			//echo "<br>";
																		}
																	}
																	else
																	{
																		
																		if( $isLeader == false )
																		{
																			//Increase Counter
																			$checkOuter++;
																			$total = $total + ( $baseRate * $itemPrice );
																			
																			if( $e == ($orderQuantity-1) )
																			{																			
																				$inc = 1;
																				//echo "Now Split " . $total;
																				//echo "<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				$splititem['order_split']		=	$orderGroup;
																				//$splititem['order_split']		=	"split";
																				$splititem['quantity']			=	$checkOuter;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem); 
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 0;
																				
																			}
																			
																		}
																		else
																		{
																			
																			if( $e == ($orderQuantity-1) )
																			{
																				
																				//For Previous calculate and store it split order into database
																				//echo "Now Split------" . $total;
																				//echo "<br>*********<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																				//Now store last index calculation if reaches at end point then 
																				//need to be remind , there is last one we have to also store into database
																				//echo "Now Split" . $total;
																				//echo "<br>*********<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																			}
																			else
																			{
																				
																				//echo "Now Split " . $total;
																				//echo "<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																			}
																					
																		}
																		
																	}
																	
																$e++;	
																}
															}
															
														}
														else
														{
															//echo "Exceed Limit to split."; exit;
														}												
													}
												}
												else
												{	
													
													//Get Count Sku for bundle with multiple
													$getLastIndex = $splitskus[count($splitskus)-1];
													
													//Handle Multiple Sku with type bundle												
													$value = $orderitem->Quantity * ($orderitem->PricePerUnit / $getLastIndex) * $baseRate;
													
													//echo $value = $orderitem->CostIncTax * $baseRate;
													//echo "<br>";
													
													$anotherValue = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
													
													//For Euro												
													if( $anotherValue <= 54.20 || $anotherValue > 100 )
													{	
														if( (count($splitskus)-2) == $i )
														{
															$totalQuantity = $orderitem->Quantity;
															//echo "<br>";
															$combinedSkuForMulti .= ',' . $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
															
															if( $bundleIdentity > 0 )
															{
																if( count($orderitems) > 1 )
																{
																	$bundleIdentity = $bundleIdentity + 1;												
																	$numId = $result->NumOrderId .'-'. $bundleIdentity;
																}
																else
																{										
																	$numId = $result->NumOrderId;
																}															
															}
															else
															{
																if( count($orderitems) > 1 )
																{
																	$bundleIdentity = $bundleIdentity + 1;												
																	$numId = $result->NumOrderId .'-'. $bundleIdentity;
																}
																else
																{											
																	$numId = $result->NumOrderId;
																}
															}
															
															$splititem['product_order_id_identify']		=	$numId;
															
															$splititem['order_split']		=	"Group B";
															$splititem['pack_order_quantity']		=	$splitskus[$count-1];
															$splititem['product_sku_identifier']		= "multiple";			
															$splititem['price']		=	$orderitem->Quantity * $orderitem->PricePerUnit;
															
															$splititem['quantity']			=	$orderitem->Quantity * $getLastIndex; //$totalQuantity;
															$splititem['product_type']		=	"bundle";
															$splititem['order_id']		=	$result->NumOrderId;
															$splititem['sku']			=	$combinedSkuForMulti;
															$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => 'S-'.$splitskus[$i] )));
															$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
															
															$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
															$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
															$splititem['channel_sku'] =  rtrim($channel_sku,'__');
															/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																						array('Product.product_sku' => $splititem['sku']));*/
															$this->OrderItem->create();
															$this->OrderItem->save( $splititem );
															$combinedSkuForMulti = '';
														}
														else
														{
															$totalQuantity = $orderitem->Quantity * $getLastIndex; //$orderitem->Quantity;
															//echo "xxxxx<br>";
															
															//if( $i == 1 )
															
															if( $combinedSkuForMulti == '' )
																$combinedSkuForMulti = $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
															else	
																$combinedSkuForMulti .= ',' . $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
														}
														
													}
													else
													{
														
														if( $anotherValue <= 100 && $anotherValue > 54.20 )	
														{
															$total = 0;
															
															//total price
															$perUnitPrice = ( $orderitem->Quantity * $orderitem->PricePerUnit );
																		
															//total quantity														
															$orderQuantity = $orderitem->Quantity * $getLastIndex;
															
															//unit price
															$itemPrice = $perUnitPrice / $orderQuantity;
															
															$inc = 0;														
															$checkOuter = 0;
															$isLeader = false;
															$total = 0;
															
															if( ( $orderQuantity > 0 ) )
															{														
																
																//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
																$inc = 0;$out = 0;while( $out <= $orderitem->Quantity-1 )
																{
																	
																	//Store
																	//echo " Bundle Multiple SKUxx " . $total;
																	//echo "<br>";
																			
																	//Splitting the order accordign the rule
																	//Store previous then initialized																	
																	$bundleIdentity++;			
																	$inc++;;														
																	//Store and split the same SKU bundle order
																	$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																	$splititem['product_sku_identifier']		= "single";			
																	$splititem['price']		=	$itemPrice;
																	$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																	
																	//$splititem['order_split']		=	"split";
																	$splititem['order_split']		=	"Group B";
																	$splititem['quantity']			=	$inc;
																	$splititem['product_type']		=	"bundle";
																	$splititem['order_id']		=	$result->NumOrderId;
																	$splititem['sku']			=	'S-'.$splitskus[$i];
																	$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																	$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																	
																	//pr($splititem);
																	
																	$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																	$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																	$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																	/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																								array('Product.product_sku' => $splititem['sku']));*/
																	$this->OrderItem->create();
																	$this->OrderItem->save( $splititem );
																	
																	$inc = 0;
																$out++;	
																}															
															}
														}
													}
												}										
											}
										}
										else
										{
											
											// Single SKU order splitting
											$value = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
												
											//echo $value = $orderitem->CostIncTax * $baseRate;
											//echo "<br>";
											
											//For Euro												
											if( $value <= 54.20 || $value > 100 )
											{
												
												$numId = '';
												if( $bundleIdentity > 0 )
												{
													$bundleIdentity = $bundleIdentity + 1;												
													$numId = $result->NumOrderId .'-'. $bundleIdentity;
													$splititem['product_order_id_identify']		=	$numId;
													//$splititem['order_split']		=	"split";
												}
												else
												{
													if( count($orderitems) == 1 )
													{
														$numId = $result->NumOrderId;	
													}
													else
													{
														$bundleIdentity = $bundleIdentity + 1;	
														$numId = $result->NumOrderId .'-'. $bundleIdentity;	
														$splititem['product_order_id_identify']		=	$numId;
														//$splititem['order_split']		=	"split";
													}
												}
												
												$splititem['order_split']		=	$orderGroup;
												$splititem['pack_order_quantity']		=	0;
												$splititem['product_sku_identifier']		= "single";			
												$splititem['price']		=	$orderitem->Quantity * $orderitem->PricePerUnit;
												
												$splititem['quantity']			=	$orderitem->Quantity;
												$splititem['product_type']		=	"single";
												$splititem['order_id']		=	$result->NumOrderId;
												
												if( count( $orderitems ) == 1 )
													$splititem['sku']			=	$orderitem->Quantity .'X'. $orderitem->SKU;
												else
													$splititem['sku']			=	$orderitem->SKU;
													
												$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
												$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
												
												//pr($splititem);
												$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
												$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
												$splititem['channel_sku'] =  rtrim($channel_sku,'__');
												/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																			array('Product.product_sku' => $orderitem->SKU));*/
												$this->OrderItem->create();
												$this->OrderItem->save( $splititem );
																									
											}
											else
											{
												
												if( $value <= 100 && $value > 54.20 )	
												{
													
													$total = 0;
													$perUnitPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
													
													$orderQuantity = $orderitem->Quantity;
													
													$itemPrice = $perUnitPrice / $orderQuantity;
													
													$inc = 0;
													$checkOuter = 0;
													$isLeader = false;
													
													if( ( $orderQuantity > 1 ) )
													{		
																										
														//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
														$e = 0;while( $e <= ($orderQuantity-1) )
														{	
															
															//$total = $total + ( $baseRate * $itemPrice );															
															if( ( $total + ( $baseRate * $itemPrice ) ) <= 54.20 )
															{
																$total = $total + ( $baseRate * $itemPrice );
																//echo $total;
																//echo "<br>";
																$inc++;
																$checkOuter++;
																$isLeader = true;
																
																if( $e == ($orderQuantity-1) )
																{
																	//echo "Now Split" . $total;
																	//echo "<br>*********<br>";
																	
																	//Splitting the order accordign the rule
																	//Store previous then initialized																	
																	$bundleIdentity++;																	
																	//Store and split the same SKU bundle order
																	$splititem['pack_order_quantity']		=	0;
																	$splititem['product_sku_identifier']		= "single";			
																	$splititem['price']		=	$total / $baseRate;
																	$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																	//$splititem['order_split']		=	"split";
																	
																	$splititem['order_split']		=	$orderGroup;
																	$splititem['quantity']			=	$inc;
																	$splititem['product_type']		=	"single";
																	$splititem['order_id']		=	$result->NumOrderId;
																	$splititem['sku']			=	$orderitem->SKU;
																	$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																	$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																	
																	//pr($splititem);
																	
																	$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																	$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																	
																	$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																	/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																								array('Product.product_sku' => $splititem['sku']));*/
																	$this->OrderItem->create();
																	$this->OrderItem->save( $splititem );
																	
																	$total = 0;
																	$inc = 1;
																	$total = $total + ( $baseRate * $itemPrice );
																	//echo $total;																	
																	//echo "<br>";
																}
															}
															else
															{
																
																if( $isLeader == false )
																{
																	//Increase Counter
																	$checkOuter++;
																	$total = $total + ( $baseRate * $itemPrice );
																	
																	if( $e == ($orderQuantity-1) )
																	{																			
																		$inc = 1;
																		//echo "Now Split " . $total;
																		//echo "<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$checkOuter;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem); 
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 0;
																		
																	}
																	
																}
																else
																{
																	
																	if( $e == ($orderQuantity-1) )
																	{
																		
																		//For Previous calculate and store it split order into database
																		//echo "Now Split------" . $total;
																		//echo "<br>*********<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		//$splititem['order_split']		=	"split";
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																		//Now store last index calculation if reaches at end point then 
																		//need to be remind , there is last one we have to also store into database
																		//echo "Now Split" . $total;
																		//echo "<br>*********<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																	}
																	else
																	{
																		
																		//echo "Now Split " . $total;
																		//echo "<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		$splititem['channel_sku'] =  rtrim($channel_sku,'__');
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																	}
																			
																}
																
															}
															
														$e++;	
														}
													}
													else
													{
														
														//If order item count is 1 then would be store directly
														$splititem['pack_order_quantity']		=	0;
														$splititem['product_sku_identifier']		= "single";			
														$splititem['price']		=	$orderitem->PricePerUnit;
														
														$splititem['order_split']		=	$orderGroup;
														$splititem['quantity']			=	$orderitem->Quantity;
														$splititem['product_type']		=	"single";
														$splititem['order_id']		=	$result->NumOrderId;
														$splititem['sku']			=	$orderitem->SKU;
														$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
														$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
														
														$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
														$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
														$splititem['channel_sku'] =  rtrim($channel_sku,'__');
														/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																					array('Product.product_sku' => $splititem['sku']));*/
														$this->OrderItem->create();
														$this->OrderItem->save( $splititem );
														
													}
													
												}
												else
												{
													//echo "Exceed Limit to split."; exit;
												}												
											}
												
										}
										
									}
									else
									{
										
										$getCurrencyText = $result->TotalsInfo->Currency;
										$getCountryText = $result->CustomerInfo->Address->Country;
										
										if( $getCurrencyText == "EUR" )
										{
											$baseRate = '1';
										}
										else
										{
											$baseRate = '1.13';
										}
										
										/***************** split the order item ******************/
										$orderItemValueTotal = 0;foreach( $orderitems as $orderitem )
										{
											$orderItemValueTotal = $orderItemValueTotal + $orderitem->Cost;
										}
										
										//if( ($orderItemValueTotal * $baseRate) > 250 || ($orderItemValueTotal * $baseRate) <= 54.20 )
										
										if( ($orderItemValueTotal * $baseRate) >= 0 )
										{	
											if( count( $orderitems ) > 1 )
											{
												$orderGroup = "Group B";
											}
											else
											{
												if( count(explode('-',$orderitems[0]->SKU)) > 3 )
												{
													$orderGroup = "Group B";
												}
												else
												{
													$orderGroup = "Group A";
												}
											}
											
											//Store direct into storage
											$combineSkuVisit = '';
											$combinePrice = 0;
											$combineQuantity = 0;
											$combineBarcode = '';											
											foreach( $orderitems as $orderitem )
											{	$channel_sku = $orderitem->RowId.'__';
												if( count( explode( '-', $orderitem->SKU ) ) == 2 )
												{
													if( $combineSkuVisit == '' )
													{
														$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
														$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
														$combineQuantity = $combineQuantity + $orderitem->Quantity;
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
														$combineBarcode = $productDetail['ProductDesc']['barcode'];
													}
													else
													{
														$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
														$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
														$combineQuantity = $combineQuantity + $orderitem->Quantity;
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
														$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
													}
													
												}
												else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
												{
													$splitskus = explode( '-' , $orderitem->SKU);										
													if( $combineSkuVisit == '' )
													{
														$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
														$combineQuantity = $combineQuantity + $splitskus[2];
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
														$combineBarcode = $productDetail['ProductDesc']['barcode'];
													}
													else
													{
														$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
														$combineQuantity = $combineQuantity + $splitskus[2];
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
														$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
													}
													
												}
												else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
												{
													// For Bundle with muti type Sku
													$splitskus = explode( '-', $orderitem->SKU );
													
													$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
													$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
													
													$in = 1; while( $in <= count( $splitskus )-2 ):											
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
														if( $combineSkuVisit == '' )
														{
															//$quantity = $quantity + $orderitem->Quantity;
															$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
															$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
														}
														else
														{
															//$quantity = $quantity + $orderitem->Quantity;
															$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
															$combineQuantity = $combineQuantity + $orderitem->Quantity;
														}
													$in++;
													endwhile;
													
												}
																					
											}
											
											//Saving
											// For Bundle with same type Sku				
											//Store and split the same SKU bundle order
											$splititem['pack_order_quantity']		=	0;
											$splititem['product_sku_identifier']		= "single";			
											$splititem['price']		=	$combinePrice;
											$splititem['product_order_id_identify']		=	$result->NumOrderId;
											
											$splititem['order_split']		=	$orderGroup;
											$splititem['quantity']			=	$combineQuantity;
											$splititem['product_type']		=	"bundle";
											$splititem['order_id']		=	$result->NumOrderId;
											$splititem['sku']			=	$combineSkuVisit;								
											$splititem['barcode']		=	$combineBarcode;
											$splititem['channel_sku'] 	=  rtrim($channel_sku,'__');
											//pr($splititem);
											
											$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
											$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
											
											/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																		array('Product.product_sku' => $splititem['sku']));*/
											$this->OrderItem->create();
											$this->OrderItem->save( $splititem );
											//echo "saved";
											break;
										}										
										
									}
									
								}
								
							}	

							echo "<br>flagVar : ".$flagVar;							

							//************** Merge Order after splitting with different scenarios **************
							$this->mergeSplitOrdersByOrderId_AccordingRules( $result->NumOrderId , $flagVar );
                                                        
                            echo "<br>after mergeSplitOrdersByOrderId_AccordingRules : ";
                                                        
							//$this->loadModel( 'MergeOrder' );							
							//pr( $this->MergeOrder->find('all') ); exit;
							
							$emailaddress = $result->CustomerInfo->Address->EmailAddress;

							if($emailaddress != ''){
								
								// code for save customer detail
								$customerInfo['Customer']['email']		=	$result->CustomerInfo->Address->EmailAddress;
								$customerInfo['Customer']['address1']	=	$result->CustomerInfo->Address->Address1;
								$customerInfo['Customer']['address2']	=	$result->CustomerInfo->Address->Address2;
								$customerInfo['Customer']['address3']	=	$result->CustomerInfo->Address->Address3;
								$customerInfo['Customer']['town']		=	$result->CustomerInfo->Address->Town;
								$customerInfo['Customer']['region']		=	$result->CustomerInfo->Address->Region;
								$customerInfo['Customer']['postcode']	=	$result->CustomerInfo->Address->PostCode;
								$customerInfo['Customer']['country']	=	$result->CustomerInfo->Address->Country;
								$customerInfo['Customer']['name']		=	$result->CustomerInfo->Address->FullName;
								$customerInfo['Customer']['company']	=	$result->CustomerInfo->Address->Company;
								$customerInfo['Customer']['phone']		=	$result->CustomerInfo->Address->PhoneNumber;
								$customerInfo['Customer']['source']		=	$result->GeneralInfo->Source;
								$customerInfo['Customer']['subsource']	=	$result->GeneralInfo->SubSource;
								
								$customerdetails	=	$this->Customer->find('first', array('conditions' => array('Customer.email' => $customerInfo['Customer']['email'])));
								
								if( count($customerdetails) > 0 )
								{
									//$customerdetails['Customer']['count'] = $customerdetails['Customer']['count'] + '1';
									$this->Customer->updateAll(array('Customer.order_id' => $result->NumOrderId),
									array('Customer.email' => $customerdetails['Customer']['email']));
								}
								else
								{
									$customerInfo['Customer']['order_id']		=	$result->NumOrderId;
									$this->Customer->create();
									$this->Customer->saveAll( $customerInfo );
								}
							}
							//App::import('Controller', 'Profits');
							//$ProfitsObj = new ProfitsController(); 
							//$ProfitsObj->setSkuProfitLoss( $result->NumOrderId );
						}	
					}
					else
					{
						//Do something you required
					}	
					
					/*
					 * 
					 * 
					 * If, order will blank saved thne will try to flush
					 * 
					 * 
					 */ 
					 //$this->checkOrderIsBlank( $result->NumOrderId );
					
					/*
					 * 
					 * 
					 * If, order will blank saved thne will try to flush
					 * 
					 * 
					 */ 
					 $this->removeDuplicateOrderByID( $result->NumOrderId );

					 echo "<br>after removeDuplicateOrderByID : ";

					//$this->flushBlankOrder( $result->NumOrderId , $locationName );
					//pr($result);
					//pr($result); 
					if($result->CustomerInfo->Address->Country == 'Italy' && $result->ShippingInfo->PostalServiceName != 'Express') 
					{
								App::import('Controller', 'Brt');
								$brtObj = new BrtController(); 
							//	$brtObj->generateBrtLabels( $result->NumOrderId );
						/*$orderdetails	=	$this->MergeUpdate->find('all', array( 'conditions' => array( 'MergeUpdate.order_id' => $result->NumOrderId) ) );
						foreach($orderdetails as $orderdetail)
						{
							if($orderdetail['MergeUpdate']['price'] < 22)
							{
								App::import('Controller', 'Brt');
								$brtObj = new BrtController(); 
								$brtObj->generateBrtLabels( $result->NumOrderId );
							}
						}*/
					}else if($result->CustomerInfo->Address->Country == 'United Kingdom' && $result->ShippingInfo->PostalServiceName != 'Express' && $result->GeneralInfo->SubSource != 'Onbuy'){					
						/*****************Apply RoyalMail 16042018*******************/			
						/*App::Import('Controller', 'RoyalMail'); 
						$royal = new RoyalMailController;
						$royal->applyRoyalMail($result->NumOrderId); */
					}
					
					if($result->CustomerInfo->Address->Country == 'United Kingdom'){					
  						/*****************Apply Whistl 28122020*******************/			
						App::Import('Controller', 'Whistl'); 
						$whistl = new WhistlController;
						//$whistl->applyWhistl($result->NumOrderId); 15-01-2021
					}else{		
						App::Import('Controller', 'Postnl'); 
						$pnl = new PostnlController;
						//$pnl->Labelling($result->NumOrderId);
 					}
					
					/*--More than one Real.DE order and total is greather than 26(Apply on 06-11-2019 )--*/
					if($result->GeneralInfo->SubSource == 'Costdropper'){
						App::import('Controller', 'Reports');
						$rObj = new ReportsController(); 
						//$rObj->RealDeOrder();
					}
					if($result->GeneralInfo->Source == 'CDISCOUNT'){
						App::import('Controller', 'Reports');
						$rObj = new ReportsController(); 
						//$rObj->cDiscountOrder();
					}
					//if(!in_array($result->CustomerInfo->Address->Country,['United Kingdom','UNKNOWN'])){
						/*-------Jersey Post API 30-12-2020-------*/
						App::Import('Controller', 'Jerseypost'); 
						$jp = new JerseypostController();
						$jp->createShipment($result->NumOrderId );
					//}
					//App::import('Controller', 'Profits');
					//$ProfitsObj = new ProfitsController(); 
					//$ProfitsObj->setSkuProfitLoss( $result->NumOrderId );
									
				}
				
				echo "<br>after JerseypostController : ";
						
				/* update tyose order has 0 quantity */
				//$this->updateMergeOrder();
				
				/* call the function for assign the postal servises */
				//$this->assign_services();
				$this->getBarcode();
				
				echo "<br>after getBarcode : ";

				$this->assignRegisteredBarcode();	
				
				echo "<br>after assignRegisteredBarcode : ";

				//$this->setAgainAssignedServiceToAllOrder(); // Euraco Group	 
				
				//pr($orders); 
				
				App::import('Controller', 'Virtuals');
				$virtualModel = new VirtualsController(); 
				//$virtualModel->creatFeedSheet_old( $locationName );
				
				//Sync start
				App::import( 'Controller' , 'MyExceptions' ); 
				$exception = new MyExceptionsController();
				$exception->syncComp( $locationName );
				
				//Delete cancel UNKNOWN orders
				if(!empty($orders)){
					if(count($orders) > 1){					
					$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
								'conditions' => array(
									'UnprepareOrder.order_id NOT IN ' => $orders,
									'UnprepareOrder.source_name' => $locationName
									),									
								  'fields' => array( 
									'UnprepareOrder.order_id',
									'UnprepareOrder.num_order_id',
									'UnprepareOrder.unprepare_check',
									'UnprepareOrder.id',  
									'UnprepareOrder.items'  
								)
							)
						);
					}else{				
						
						$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
									'conditions' => array(
										'UnprepareOrder.order_id !=' => $orders[0],
										'UnprepareOrder.source_name' => $locationName
										),									
									  'fields' => array( 
										'UnprepareOrder.order_id',
										'UnprepareOrder.num_order_id',
										'UnprepareOrder.unprepare_check',
										'UnprepareOrder.id',  
										'UnprepareOrder.items'  
									)
								)
							);

					}
					foreach( $getAllUnprepardId  as $getAllId )
					{							
						$itemsReserve = unserialize($getAllId['UnprepareOrder']['items']);
						$this->reserveInventoryForUnknown( $itemsReserve , 4 , $getAllId['UnprepareOrder']['num_order_id'] , $getAllId['UnprepareOrder']['unprepare_check'] );
						$this->UnprepareOrder->delete( $getAllId['UnprepareOrder']['id'] );
					}
				}
				

				echo "<pre>removeIds : "; print_r($removeIds); 

				# this code is delete orders which are proces after status got change from unpaid to paid - start 
					if(!empty($removeIds)){

						if(count($removeIds) == 1){

							$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
									'conditions' => array(
										'UnprepareOrder.order_id' => $removeIds
										),									
									  'fields' => array( 
										'UnprepareOrder.order_id',
										'UnprepareOrder.num_order_id',
										'UnprepareOrder.unprepare_check',
										'UnprepareOrder.id',  
										'UnprepareOrder.items'  
									)
								)
							);

						} else {
							$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
									'conditions' => array(
										'UnprepareOrder.order_id IN ' => $removeIds
										),									
									  'fields' => array( 
										'UnprepareOrder.order_id',
										'UnprepareOrder.num_order_id',
										'UnprepareOrder.unprepare_check',
										'UnprepareOrder.id',  
										'UnprepareOrder.items'  
									)
								)
							);
						}						

						foreach( $getAllUnprepardId  as $getAllId )
						{
							$itemsReserve = unserialize($getAllId['UnprepareOrder']['items']);
							$this->reserveInventoryForUnknown( $itemsReserve , 4 , $getAllId['UnprepareOrder']['num_order_id'] , $getAllId['UnprepareOrder']['unprepare_check'] );
							$this->UnprepareOrder->delete( $getAllId['UnprepareOrder']['id'] );
						}	
					}
				## End 
				
			return "done";	
			}
			
			public function assignRegisteredBarcode()
			{
				$this->layout = '';
				$this->autoRender = false;
				$this->loadModel( 'MergeUpdate' );
				$this->loadModel( 'RegisteredNumber' );
				
				require_once(APP . 'Vendor' . DS . 'code39' . DS . 'Barcode39.php'); 			
				$checkRegBarcode	=	$this->MergeUpdate->find('all', array('conditions' => array(
																			   'delevery_country' => 'France', 'postal_service' => 'Tracked','reg_post_number' => '', 'pick_list_status' => 0,'postnl_barcode IS NULL')));
				
				/*$checkRegBarcode	=	$this->MergeUpdate->find('all', array('conditions' => array(
																			   'delevery_country IN' => array('France'), 'postal_service' => 'Tracked','reg_post_number' => '', 'pick_list_status' => 0)));*/
																			   
				if(  $checkRegBarcode > 0 )
				{
					foreach( $checkRegBarcode as $checkRegBarcod )
					{
						$getRegNum	=	$this->RegisteredNumber->find('first', array( 'conditions' => array( 'split_order_id' => '' ),'orders' => 'reg_number DESC' ) );
	
						$id						=	$getRegNum['RegisteredNumber']['id'];
						$regNumber				=	$getRegNum['RegisteredNumber']['reg_number'];
						$regBarcodeNumber		=	$getRegNum['RegisteredNumber']['reg_baecode_number'];
						
						$data['id'] 			= 	$id;
						$data['split_order_id'] = 	$checkRegBarcod['MergeUpdate']['product_order_id_identify'];
						
						$text = $regNumber;
						$barimg = WWW_ROOT."code39barcode/".$text.".png";
						$bc = new barcode() ;
						$result = $bc->code39($text, $height = "100", $widthScale = "2",$barimg); 
						if($result == '1')
						{
							$this->RegisteredNumber->saveAll( $data );
							$mergeUpdateData['MergeUpdate']['id'] 				= 	$checkRegBarcod['MergeUpdate']['id'];
							$mergeUpdateData['MergeUpdate']['reg_post_number']	=	$text;
							$mergeUpdateData['MergeUpdate']['track_id']			=	$text;
							$mergeUpdateData['MergeUpdate']['reg_num_img']		=	$text.".png";
							$this->MergeUpdate->saveAll( $mergeUpdateData );
						}
				 	}
				}
			}
			
			//check blnak order
			public function checkOrderIsBlank( $numOrderId = null )
			{
				
				$this->layout = '';
				$this->autoRender = false;	
				$this->loadModel( 'OpenOrder' );				
				$this->loadModel( 'MergeUpdate' );
				
				//Find into mergeupdate, if not exists the instantly delete
				$param = array(
					
					'conditions' => array(
					
						'MergeUpdate.order_id' => $numOrderId
					
					)
				
				);
				
				$countOpenOrderById = $this->MergeUpdate->find( 'count' , $param );
				if( $countOpenOrderById == 0 )
				{
					
					//update open order with staus where user will track easily and fast
					$this->OpenOrder->updateAll( array('OpenOrder.order_status' => 1), array('OpenOrder.num_order_id' => $numOrderId) );
					
				}
				
			}
			
			
			public function removeDuplicateOrderByID( $numOrderId = null ) 
			{
				
				$this->layout = '';
				$this->autoRender = false;
				
				$this->loadModel( 'OpenOrder' );
				$this->loadModel( 'MergeUpdate' );
				$this->loadModel( 'ScanOrder' );
				
				//Find into mergeupdate, if not exists the instantly delete
				$param = array(
					
					'conditions' => array(
					
						'OpenOrder.num_order_id' => $numOrderId
					
					)
				
				);
				
				$countOpenOrderById = $this->OpenOrder->find( 'count' , $param );
				
				if( $countOpenOrderById >= 2 )
				{
					
					//remove all duplicate order by orderId
					//Sync start
					App::import( 'Controller' , 'Linnworksapis' );
					$linn = new LinnworksapisController();
					$linn->deleteOrder($numOrderId);
										

				}
				
				
			}
			
			public function flushBlankOrder( $numOrderId = null , $locationName = null ) 
			{
				
				$this->layout = '';
				$this->autoRender = false;
				
				$this->loadModel( 'OpenOrder' );
				$this->loadModel( 'MergeUpdate' );
				$this->loadModel( 'ScanOrder' );
				
				//Find into mergeupdate, if not exists the instantly delete
				$param = array(
					
					'conditions' => array(
					
						'MergeUpdate.order_id' => $numOrderId
					
					)
				
				);
				
				$countMeregUpdateOrder = $this->MergeUpdate->find( 'count' , $param );
				if( $countMeregUpdateOrder == 0 )
				{
					
					//Delete from open order
					$delete = array(
					
						'OpenOrder.num_order_id' => $numOrderId 
					
					);
					
					$openOrderCount = $this->OpenOrder->find( 'first' , array( 'conditions' => array( 'OpenOrder.num_order_id' => $numOrderId ) ) );
					if( $openOrderCount > 0 )
					{
						
						$this->OpenOrder->deleteAll( $delete );
					
						//create and setup the logs
						$this->writeLog( $numOrderId , $locationName );
						
						// the message
						$msg = $numOrderId . " is deleted for blank reason!";
						
						// use wordwrap() if lines are longer than 70 characters
						$msg = wordwrap($msg,70);

						// send email					
						mail("ag.ashishaggarwal@gmail.com","Custom Flush!",$msg); 
							
					}
					else
					{
						// the message
						$msg = "Order Not found for flush...";
						
						// use wordwrap() if lines are longer than 70 characters
						$msg = wordwrap($msg,70);

						// send email					
						mail("ag.ashishaggarwal@gmail.com","Order Flush during sync Xsensys cloud!",$msg);
					}
					
				}	
				
			}
			
			public function writeLog( $numOrderId = null , $location = null ) 
			{
				
				$this->layout = '';
				$this->autoRender = false;
				
				//Cake Log
				App::uses('CakeLog', 'Log');
				CakeLog::config('default', array(
					'engine' => 'File'
				));
				
				//create and setup the logs
				CakeLog::write('Euraco Group__[ '. $location .' ]__['.date( 'd-m-Y A' ).']', $numOrderId);
				
			}
			
			public function flushBlankOrder_old( $numOrderId = null , $locationName = null )
			{
				
				$this->layout = '';
				$this->autoRender = false;
				
				$this->loadModel( 'OpenOrder' );
				$this->loadModel( 'MergeUpdate' );
				$this->loadModel( 'ScanOrder' );
				
				//Find into mergeupdate, if not exists the instantly delete
				$param = array(
					
					'conditions' => array(
					
						'MergeUpdate.order_id' => $numOrderId
					
					)
				
				);
				$countMeregUpdateOrder = $this->MergeUpdate->find( 'count' , $param ); 
				if( $countMeregUpdateOrder == 0 )
				{
					
					//Delete from open order
					$delete = array(
					
						'OpenOrder.num_order_id' => $numOrderId 
					
					);
					
					$openOrderCount = $this->OpenOrder->find( 'count' , array( 'conditions' => array( 'OpenOrder.num_order_id' => $numOrderId ) ) );
					
					if( $openOrderCount > 0 )
					{
						
						$this->OpenOrder->deleteAll( $delete );
					
						//create and setup the logs
						$this->writeLog( $numOrderId , $locationName );
						
						// the message
						$msg = $numOrderId . " is deleted for blank reason!";
						
						// use wordwrap() if lines are longer than 70 characters
						$msg = wordwrap($msg,70);

						// send email					
						mail("ag.ashishaggarwal@gmail.com","Order Flush during sync Xsensys cloud!",$msg);
							
					} 
					else
					{
						// the message
						$msg = "Order Not found for flush...";
						
						// use wordwrap() if lines are longer than 70 characters
						$msg = wordwrap($msg,70);

						// send email					
						mail("ag.ashishaggarwal@gmail.com","Order Flush during sync Xsensys cloud!",$msg);
					}
					 
					
				}	
				
			}
			
			public function saveOpenOrder_live_running( $results = null , $locationName = null , $orders = null ) 
			{
				
				$itt = 1;
				$countryArray = Configure::read('customCountry');
				foreach($results as $result)
				{
					/*if( ($result->GeneralInfo->Status == 1 || $result->GeneralInfo->Status == 4 ) && $result->GeneralInfo->HoldOrCancel == ''  )
					{*/
					$data['order_id']		= $result->OrderId;
					$data['num_order_id']	= $result->NumOrderId;
					$data['general_info']	= serialize($result->GeneralInfo);
					$data['shipping_info']	= serialize($result->ShippingInfo);
					$data['customer_info']	= serialize($result->CustomerInfo);
					$data['totals_info']	= serialize($result->TotalsInfo);
					$data['folder_name']	= serialize($result->FolderName);
					$data['items']			= serialize($result->Items);
					$data['linn_fetch_orders'] = $result->GeneralInfo->Status;
					$data['sub_source'] = $result->GeneralInfo->SubSource;
					
					//Extra information will save my according to manage sorting station section
					$country = $data['destination'] = $result->CustomerInfo->Address->Country;
					$orderitems	=	unserialize($data['items']);
					
					$flagVar = 3;
					
					//echo "<br>";
					//echo $itt . '==' . $data['num_order_id']	= $result->NumOrderId;
					//echo "<br>";
					
					$itt++;
					
					//Check Unpreparee step by step
					$getAllUnprepardId = $this->UnprepareOrder->find('first', array('conditions'=>array('UnprepareOrder.order_id' => $result->OrderId) , 'fields' => array( 'UnprepareOrder.order_id', 'UnprepareOrder.num_order_id' , 'UnprepareOrder.id' , 'UnprepareOrder.linn_fetch_orders' , 'UnprepareOrder.destination' )));
					
					if( count($getAllUnprepardId) > 0 )
					{
						
						if( $country !== $getAllUnprepardId['UnprepareOrder']['destination'] )
						{
							$flagVar = $this->unprepareOrder($result);
						}
						else
						{
							$flagVar = $this->unprepareOrder($result);
						}
						
					}
					else
					{
						
						//If it is UNKNOWN or not
						if( $country == "UNKNOWN" )
						{
							$flagVar = $this->unprepareOrder($result);
						}
						else
						{
							$flagVar = 3;
						}
						
					}
						
					if( $flagVar != 2 )
					{	
						
						//Check OpenOrder
						$this->OpenOrder->create();
						$checkorder 	=	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $result->OrderId) , 'fields' => array( 'OpenOrder.order_id', 'OpenOrder.num_order_id' , 'OpenOrder.id' , 'OpenOrder.linn_fetch_orders' )));
						
						if(count($checkorder) > 0)
						{
							
							//Clean Orders
							$this->cleanOrders();
							
							//CHECK IF ORDER EXISTS OR NOT
							// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
							$linnStatus = $result->GeneralInfo->Status;
							$dataUpdate['OpenOrder']['id'] = $checkorder['OpenOrder']['id'];
							$dataUpdate['OpenOrder']['linn_fetch_orders'] = $result->GeneralInfo->Status;							
                            //$dataUpdate['OpenOrder']['linn_fetch_orders'] = serialize($result->CustomerInfo);
							$this->OpenOrder->saveAll( $dataUpdate ); 
							
							//Now update into Merge Section
							$this->loadModel( 'MergeUpdate' );
							
							//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
							$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $result->GeneralInfo->Status), array('MergeUpdate.order_id' => $result->NumOrderId) );
						}
						else //if( $flagVar != 2 )
						{
							
							$this->OpenOrder->save($data);
							
							//Clean Orders
							$this->cleanOrders();
							
							$getCurrencyText = $result->TotalsInfo->Currency;
							
							if( $getCurrencyText == "EUR" )
							{
								$baseRate = '1';
							}
							else
							{
								$baseRate = '1.38';
							}
							
							/***************** split the order item ******************/
							$orderItemValueTotal = 0;foreach( $orderitems as $orderitem )
							{
								$orderItemValueTotal = $orderItemValueTotal + $orderitem->Cost;
							}
							
							//Get special postal service name as discussed by shashi at run time when did launch
							$serviceNameNow = unserialize($data['shipping_info']);
							$servicePostal = $serviceNameNow->PostalServiceName;
							if( $servicePostal == "Standard_Jpost" )
							{	
								
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}
								
								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								foreach( $orderitems as $orderitem )
								{
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;
										
									}
																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$combinePrice;
								$splititem['product_order_id_identify']		=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$result->NumOrderId;
								$splititem['sku']			=	$combineSkuVisit;								
								$splititem['barcode']		=	$combineBarcode;
								
								//pr($splititem);
								
								$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else if( ($orderItemValueTotal * $baseRate) > 100 )
							{	
								
								if( count( $orderitems ) > 1 )
								{
									$orderGroup = "Group B";
								}
								else
								{
									if( count(explode('-',$orderitems[0]->SKU)) > 3 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
								}
								
								//Store direct into storage
								$combineSkuVisit = '';
								$combinePrice = 0;
								$combineQuantity = 0;
								$combineBarcode = '';
								foreach( $orderitems as $orderitem )
								{
									if( count( explode( '-', $orderitem->SKU ) ) == 2 )
									{
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
											$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
											$combineQuantity = $combineQuantity + $orderitem->Quantity;
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
									{
										$splitskus = explode( '-' , $orderitem->SKU);										
										if( $combineSkuVisit == '' )
										{
											$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = $productDetail['ProductDesc']['barcode'];
										}
										else
										{
											$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
											$combineQuantity = $combineQuantity + $splitskus[2];
											
											$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
											$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
										}
										
									}
									else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
									{
										// For Bundle with muti type Sku
										$splitskus = explode( '-', $orderitem->SKU );
										
										$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
										$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
										
										$in = 1; while( $in <= count( $splitskus )-2 ):											
											$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
											if( $combineSkuVisit == '' )
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
											}
											else
											{
												//$quantity = $quantity + $orderitem->Quantity;
												$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
												$combineQuantity = $combineQuantity + $orderitem->Quantity;
											}
										$in++;
										endwhile;
										
									}
																		
								}
								
								//Saving
								// For Bundle with same type Sku				
								//Store and split the same SKU bundle order
								$splititem['pack_order_quantity']		=	0;
								$splititem['product_sku_identifier']		= "single";			
								$splititem['price']		=	$combinePrice;
								$splititem['product_order_id_identify']		=	$result->NumOrderId;
								
								$splititem['order_split']		=	$orderGroup;
								$splititem['quantity']			=	$combineQuantity;
								$splititem['product_type']		=	"bundle";
								$splititem['order_id']		=	$result->NumOrderId;
								$splititem['sku']			=	$combineSkuVisit;								
								$splititem['barcode']		=	$combineBarcode;
								
								//pr($splititem);
								
								$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
								$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
								
								/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
															array('Product.product_sku' => $splititem['sku']));*/
								$this->OrderItem->create();
								$this->OrderItem->save( $splititem );
								//echo "saved";
								
							}
							else
							{
								$bundleIdentity = 0;foreach( $orderitems as $orderitem )
								{
									//echo $orderitem->SKU . '==' . $orderitem->PricePerUnit;
									//echo "<br>";
									
									$splitskus	=	explode('-', $orderitem->SKU);
									$count	=	count($splitskus);
									
									if( count( $orderitems ) > 1 )
									{
										$orderGroup = "Group B";
									}
									else
									{
										$orderGroup = "Group A";
									}
									
									//Find Country
									if( in_array( $country , $countryArray ) )
									{			
										
										//It means, Inside EU country shipping for bundle with sameSKU
										if($splitskus['0'] == 'B')
										{
											
											for( $i = 1; $i <= count($splitskus)-2 ; $i++ )
											{																						
												if( $count == 3 )
												{													
													$value = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
													
													//echo $value = $orderitem->CostIncTax * $baseRate;
													//echo "<br>";
													
													//For Euro												
													if( $value <= 54.20 || $value > 100 )
													{													
														$numId = '';
														if( $bundleIdentity > 0 )
														{
															$bundleIdentity = $bundleIdentity + 1;												
															$numId = $result->NumOrderId .'-'. $bundleIdentity;
															$splititem['product_order_id_identify']		=	$numId;
															$splititem['order_split']		=	$orderGroup;
															//$splititem['order_split']		=	"split";
														}
														else 
														{
															if( count($orderitems) == 1 )
															{
																$numId = $result->NumOrderId;	
															}
															else
															{
																$bundleIdentity = $bundleIdentity + 1;	
																$numId = $result->NumOrderId .'-'. $bundleIdentity;	
																$splititem['product_order_id_identify']		=	$numId;
																$splititem['order_split']		=	$orderGroup;
																//$splititem['order_split']		=	"split";
															}
														}
														
														$splititem['order_split']		=	$orderGroup;
														$splititem['pack_order_quantity']		=	$splitskus[$count-1];
														$splititem['product_sku_identifier']		= "single";			
														$splititem['price']		=	( $orderitem->Quantity * $splitskus[2] ) * (($orderitem->Quantity * $orderitem->PricePerUnit) / ( $orderitem->Quantity * $splitskus[2] ));   //$splitskus[2] * ($orderitem->PricePerUnit / $splitskus[2]);
														
														$splititem['quantity']			=	( $orderitem->Quantity * $splitskus[2] ); //$orderitem->Quantity;
														$splititem['product_type']		=	"bundle";
														$splititem['order_id']		=	$result->NumOrderId;
														
														if( count($orderitems) == 1 )
															$splititem['sku']			=	( $orderitem->Quantity * $splitskus[2] ) .'X'. 'S-'.$splitskus[$i];
														else
															$splititem['sku']			=	'S-'.$splitskus[$i];	
															
														$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => 'S-'.$splitskus[$i] )));
														$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
														
														//pr($splititem);
														
														$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
														$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
														
														
														/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																					array('Product.product_sku' => 'S-'.$splitskus[$i]));*/
																					
														//pr($productDetail); 
																					
														$this->OrderItem->create();
														$this->OrderItem->save( $splititem );													
													}
													else
													{
														
														if( $value <= 100 && $value > 54.20 )	
														{
															$total = 0;
															$perUnitPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
															$orderQuantity = $orderitem->Quantity * $splitskus[$count-1];
															
															$itemPrice = $perUnitPrice / $orderQuantity;
															
															$inc = 0;														
															$checkOuter = 0;
															$isLeader = false;
															
															if( ( $orderQuantity > 1 ) )
															{														
																//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
																$e = 0;while( $e <= ($orderQuantity-1) )
																{	
																	
																	//$total = $total + ( $baseRate * $itemPrice );
																	
																	if( ( $total + ( $baseRate * $itemPrice ) ) <= 54.20 )
																	{
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;
																		//echo "<br>";
																		$inc++;
																		$checkOuter++;
																		$isLeader = true;
																		
																		if( $e == ($orderQuantity-1) )
																		{
																			//echo "Now Split" . $total;
																			//echo "<br>*********<br>";
																			
																			//Splitting the order accordign the rule
																			//Store previous then initialized																	
																			$bundleIdentity++;																	
																			//Store and split the same SKU bundle order
																			$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																			$splititem['product_sku_identifier']		= "single";			
																			$splititem['price']		=	$total / $baseRate;
																			$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																			
																			$splititem['order_split']		=	$orderGroup;
																			$splititem['quantity']			=	$inc;
																			$splititem['product_type']		=	"bundle";
																			$splititem['order_id']		=	$result->NumOrderId;
																			$splititem['sku']			=	'S-'.$splitskus[$i];
																			$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																			$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																			
																			//pr($splititem);
																			
																			$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																			$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																			
																			/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																										array('Product.product_sku' => $splititem['sku']));*/
																			$this->OrderItem->create();
																			$this->OrderItem->save( $splititem );
																			
																			$total = 0;
																			$inc = 1;
																			$total = $total + ( $baseRate * $itemPrice );
																			//echo $total;																	
																			//echo "<br>";
																		}
																	}
																	else
																	{
																		
																		if( $isLeader == false )
																		{
																			//Increase Counter
																			$checkOuter++;
																			$total = $total + ( $baseRate * $itemPrice );
																			
																			if( $e == ($orderQuantity-1) )
																			{																			
																				$inc = 1;
																				//echo "Now Split " . $total;
																				//echo "<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				$splititem['order_split']		=	$orderGroup;
																				//$splititem['order_split']		=	"split";
																				$splititem['quantity']			=	$checkOuter;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem); 
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 0;
																				
																			}
																			
																		}
																		else
																		{
																			
																			if( $e == ($orderQuantity-1) )
																			{
																				
																				//For Previous calculate and store it split order into database
																				//echo "Now Split------" . $total;
																				//echo "<br>*********<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																				//Now store last index calculation if reaches at end point then 
																				//need to be remind , there is last one we have to also store into database
																				//echo "Now Split" . $total;
																				//echo "<br>*********<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																			}
																			else
																			{
																				
																				//echo "Now Split " . $total;
																				//echo "<br>";
																				
																				//Splitting the order accordign the rule
																				//Store previous then initialized																	
																				$bundleIdentity++;																	
																				//Store and split the same SKU bundle order
																				$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																				$splititem['product_sku_identifier']		= "single";			
																				$splititem['price']		=	$total / $baseRate;
																				$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																				
																				//$splititem['order_split']		=	"split";
																				$splititem['order_split']		=	$orderGroup;
																				$splititem['quantity']			=	$inc;
																				$splititem['product_type']		=	"bundle";
																				$splititem['order_id']		=	$result->NumOrderId;
																				$splititem['sku']			=	'S-'.$splitskus[$i];
																				$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																				$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																				
																				//pr($splititem);
																				
																				$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																				$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																				
																				/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																											array('Product.product_sku' => $splititem['sku']));*/
																				$this->OrderItem->create();
																				$this->OrderItem->save( $splititem );
																				
																				$total = 0;
																				$inc = 1;
																				$total = $total + ( $baseRate * $itemPrice );
																				//echo $total;																	
																				//echo "<br>";
																				
																			}
																					
																		}
																		
																	}
																	
																$e++;	
																}
															}
															
														}
														else
														{
															//echo "Exceed Limit to split."; exit;
														}												
													}
												}
												else
												{	
													
													//Get Count Sku for bundle with multiple
													$getLastIndex = $splitskus[count($splitskus)-1];
													
													//Handle Multiple Sku with type bundle												
													$value = $orderitem->Quantity * ($orderitem->PricePerUnit / $getLastIndex) * $baseRate;
													
													//echo $value = $orderitem->CostIncTax * $baseRate;
													//echo "<br>";
													
													$anotherValue = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
													
													//For Euro												
													if( $anotherValue <= 54.20 || $anotherValue > 100 )
													{	
														if( (count($splitskus)-2) == $i )
														{
															$totalQuantity = $orderitem->Quantity;
															//echo "<br>";
															$combinedSkuForMulti .= ',' . $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
															
															if( $bundleIdentity > 0 )
															{
																if( count($orderitems) > 1 )
																{
																	$bundleIdentity = $bundleIdentity + 1;												
																	$numId = $result->NumOrderId .'-'. $bundleIdentity;
																}
																else
																{										
																	$numId = $result->NumOrderId;
																}															
															}
															else
															{
																if( count($orderitems) > 1 )
																{
																	$bundleIdentity = $bundleIdentity + 1;												
																	$numId = $result->NumOrderId .'-'. $bundleIdentity;
																}
																else
																{											
																	$numId = $result->NumOrderId;
																}
															}
															
															$splititem['product_order_id_identify']		=	$numId;
															
															$splititem['order_split']		=	"Group B";
															$splititem['pack_order_quantity']		=	$splitskus[$count-1];
															$splititem['product_sku_identifier']		= "multiple";			
															$splititem['price']		=	$orderitem->Quantity * $orderitem->PricePerUnit;
															
															$splititem['quantity']			=	$orderitem->Quantity * $getLastIndex; //$totalQuantity;
															$splititem['product_type']		=	"bundle";
															$splititem['order_id']		=	$result->NumOrderId;
															$splititem['sku']			=	$combinedSkuForMulti;
															$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => 'S-'.$splitskus[$i] )));
															$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
															
															$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
															$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
															
															/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																						array('Product.product_sku' => $splititem['sku']));*/
															$this->OrderItem->create();
															$this->OrderItem->save( $splititem );
															$combinedSkuForMulti = '';
														}
														else
														{
															$totalQuantity = $orderitem->Quantity * $getLastIndex; //$orderitem->Quantity;
															//echo "xxxxx<br>";
															
															//if( $i == 1 )
															
															if( $combinedSkuForMulti == '' )
																$combinedSkuForMulti = $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
															else	
																$combinedSkuForMulti .= ',' . $orderitem->Quantity .'X' .'S-'.$splitskus[$i];
														}
														
													}
													else
													{
														
														if( $anotherValue <= 100 && $anotherValue > 54.20 )	
														{
															$total = 0;
															
															//total price
															$perUnitPrice = ( $orderitem->Quantity * $orderitem->PricePerUnit );
																		
															//total quantity														
															$orderQuantity = $orderitem->Quantity * $getLastIndex;
															
															//unit price
															$itemPrice = $perUnitPrice / $orderQuantity;
															
															$inc = 0;														
															$checkOuter = 0;
															$isLeader = false;
															$total = 0;
															
															if( ( $orderQuantity > 0 ) )
															{														
																
																//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
																$inc = 0;$out = 0;while( $out <= $orderitem->Quantity-1 )
																{
																	
																	//Store
																	//echo " Bundle Multiple SKUxx " . $total;
																	//echo "<br>";
																			
																	//Splitting the order accordign the rule
																	//Store previous then initialized																	
																	$bundleIdentity++;			
																	$inc++;;														
																	//Store and split the same SKU bundle order
																	$splititem['pack_order_quantity']		=	$splitskus[$count-1];
																	$splititem['product_sku_identifier']		= "single";			
																	$splititem['price']		=	$itemPrice;
																	$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																	
																	//$splititem['order_split']		=	"split";
																	$splititem['order_split']		=	"Group B";
																	$splititem['quantity']			=	$inc;
																	$splititem['product_type']		=	"bundle";
																	$splititem['order_id']		=	$result->NumOrderId;
																	$splititem['sku']			=	'S-'.$splitskus[$i];
																	$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																	$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																	
																	//pr($splititem);
																	
																	$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																	$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																	
																	/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																								array('Product.product_sku' => $splititem['sku']));*/
																	$this->OrderItem->create();
																	$this->OrderItem->save( $splititem );
																	
																	$inc = 0;
																$out++;	
																}															
															}
														}
													}
												}										
											}
										}
										else
										{
											
											// Single SKU order splitting
											$value = $orderitem->Quantity * $orderitem->PricePerUnit * $baseRate;
												
											//echo $value = $orderitem->CostIncTax * $baseRate;
											//echo "<br>";
											
											//For Euro												
											if( $value <= 54.20 || $value > 100 )
											{
												
												$numId = '';
												if( $bundleIdentity > 0 )
												{
													$bundleIdentity = $bundleIdentity + 1;												
													$numId = $result->NumOrderId .'-'. $bundleIdentity;
													$splititem['product_order_id_identify']		=	$numId;
													//$splititem['order_split']		=	"split";
												}
												else
												{
													if( count($orderitems) == 1 )
													{
														$numId = $result->NumOrderId;	
													}
													else
													{
														$bundleIdentity = $bundleIdentity + 1;	
														$numId = $result->NumOrderId .'-'. $bundleIdentity;	
														$splititem['product_order_id_identify']		=	$numId;
														//$splititem['order_split']		=	"split";
													}
												}
												
												$splititem['order_split']		=	$orderGroup;
												$splititem['pack_order_quantity']		=	0;
												$splititem['product_sku_identifier']		= "single";			
												$splititem['price']		=	$orderitem->Quantity * $orderitem->PricePerUnit;
												
												$splititem['quantity']			=	$orderitem->Quantity;
												$splititem['product_type']		=	"single";
												$splititem['order_id']		=	$result->NumOrderId;
												
												if( count( $orderitems ) == 1 )
													$splititem['sku']			=	$orderitem->Quantity .'X'. $orderitem->SKU;
												else
													$splititem['sku']			=	$orderitem->SKU;
													
												$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
												$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
												
												//pr($splititem);
												$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
												$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
												
												/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																			array('Product.product_sku' => $orderitem->SKU));*/
												$this->OrderItem->create();
												$this->OrderItem->save( $splititem );
																									
											}
											else
											{
												
												if( $value <= 100 && $value > 54.20 )	
												{
													
													$total = 0;
													$perUnitPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
													
													$orderQuantity = $orderitem->Quantity;
													
													$itemPrice = $perUnitPrice / $orderQuantity;
													
													$inc = 0;
													$checkOuter = 0;
													$isLeader = false;
													
													if( ( $orderQuantity > 1 ) )
													{		
																										
														//It will be the same as Linnworks custom script term , So now will split the orders with SEQUENCING
														$e = 0;while( $e <= ($orderQuantity-1) )
														{	
															
															//$total = $total + ( $baseRate * $itemPrice );
															
															if( ( $total + ( $baseRate * $itemPrice ) ) <= 54.20 )
															{
																$total = $total + ( $baseRate * $itemPrice );
																//echo $total;
																//echo "<br>";
																$inc++;
																$checkOuter++;
																$isLeader = true;
																
																if( $e == ($orderQuantity-1) )
																{
																	//echo "Now Split" . $total;
																	//echo "<br>*********<br>";
																	
																	//Splitting the order accordign the rule
																	//Store previous then initialized																	
																	$bundleIdentity++;																	
																	//Store and split the same SKU bundle order
																	$splititem['pack_order_quantity']		=	0;
																	$splititem['product_sku_identifier']		= "single";			
																	$splititem['price']		=	$total / $baseRate;
																	$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																	//$splititem['order_split']		=	"split";
																	
																	$splititem['order_split']		=	$orderGroup;
																	$splititem['quantity']			=	$inc;
																	$splititem['product_type']		=	"single";
																	$splititem['order_id']		=	$result->NumOrderId;
																	$splititem['sku']			=	$orderitem->SKU;
																	$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																	$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																	
																	//pr($splititem);
																	
																	$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																	$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																	
																	/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																								array('Product.product_sku' => $splititem['sku']));*/
																	$this->OrderItem->create();
																	$this->OrderItem->save( $splititem );
																	
																	$total = 0;
																	$inc = 1;
																	$total = $total + ( $baseRate * $itemPrice );
																	//echo $total;																	
																	//echo "<br>";
																}
															}
															else
															{
																
																if( $isLeader == false )
																{
																	//Increase Counter
																	$checkOuter++;
																	$total = $total + ( $baseRate * $itemPrice );
																	
																	if( $e == ($orderQuantity-1) )
																	{																			
																		$inc = 1;
																		//echo "Now Split " . $total;
																		//echo "<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$checkOuter;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem); 
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 0;
																		
																	}
																	
																}
																else
																{
																	
																	if( $e == ($orderQuantity-1) )
																	{
																		
																		//For Previous calculate and store it split order into database
																		//echo "Now Split------" . $total;
																		//echo "<br>*********<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		//$splititem['order_split']		=	"split";
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																		//Now store last index calculation if reaches at end point then 
																		//need to be remind , there is last one we have to also store into database
																		//echo "Now Split" . $total;
																		//echo "<br>*********<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																	}
																	else
																	{
																		
																		//echo "Now Split " . $total;
																		//echo "<br>";
																		
																		//Splitting the order accordign the rule
																		//Store previous then initialized																	
																		$bundleIdentity++;																	
																		//Store and split the same SKU bundle order
																		$splititem['pack_order_quantity']		=	0;
																		$splititem['product_sku_identifier']		= "single";			
																		$splititem['price']		=	$total / $baseRate;
																		$splititem['product_order_id_identify']		=	$result->NumOrderId .'-'. $bundleIdentity;
																		
																		$splititem['order_split']		=	$orderGroup;
																		$splititem['quantity']			=	$inc;
																		$splititem['product_type']		=	"single";
																		$splititem['order_id']		=	$result->NumOrderId;
																		$splititem['sku']			=	$orderitem->SKU;
																		$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
																		$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
																		
																		//pr($splititem);
																		
																		$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
																		$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
																		
																		/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																									array('Product.product_sku' => $splititem['sku']));*/
																		$this->OrderItem->create();
																		$this->OrderItem->save( $splititem );
																		
																		$total = 0;
																		$inc = 1;
																		$total = $total + ( $baseRate * $itemPrice );
																		//echo $total;																	
																		//echo "<br>";
																		
																	}
																			
																}
																
															}
															
														$e++;	
														}
													}
													else
													{
														
														//If order item count is 1 then would be store directly
														$splititem['pack_order_quantity']		=	0;
														$splititem['product_sku_identifier']		= "single";			
														$splititem['price']		=	$orderitem->PricePerUnit;
														
														$splititem['order_split']		=	$orderGroup;
														$splititem['quantity']			=	$orderitem->Quantity;
														$splititem['product_type']		=	"single";
														$splititem['order_id']		=	$result->NumOrderId;
														$splititem['sku']			=	$orderitem->SKU;
														$productDetail				=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splititem['sku'] )));
														$splititem['barcode']		=	$productDetail['ProductDesc']['barcode'];
														
														$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
														$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
														
														/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																					array('Product.product_sku' => $splititem['sku']));*/
														$this->OrderItem->create();
														$this->OrderItem->save( $splititem );
														
													}
													
												}
												else
												{
													//echo "Exceed Limit to split."; exit;
												}												
											}
												
										}
										
									}
									else
									{
										
										$getCurrencyText = $result->TotalsInfo->Currency;
										$getCountryText = $result->CustomerInfo->Address->Country;
										
										if( $getCurrencyText == "EUR" )
										{
											$baseRate = '1';
										}
										else
										{
											$baseRate = '1.38';
										}
										
										/***************** split the order item ******************/
										$orderItemValueTotal = 0;foreach( $orderitems as $orderitem )
										{
											$orderItemValueTotal = $orderItemValueTotal + $orderitem->Cost;
										}
										
										//if( ($orderItemValueTotal * $baseRate) > 250 || ($orderItemValueTotal * $baseRate) <= 54.20 )
										
										if( ($orderItemValueTotal * $baseRate) >= 0 )
										{	
											if( count( $orderitems ) > 1 )
											{
												$orderGroup = "Group B";
											}
											else
											{
												if( count(explode('-',$orderitems[0]->SKU)) > 3 )
												{
													$orderGroup = "Group B";
												}
												else
												{
													$orderGroup = "Group A";
												}
											}
											
											//Store direct into storage
											$combineSkuVisit = '';
											$combinePrice = 0;
											$combineQuantity = 0;
											$combineBarcode = '';
											foreach( $orderitems as $orderitem )
											{
												if( count( explode( '-', $orderitem->SKU ) ) == 2 )
												{
													if( $combineSkuVisit == '' )
													{
														$combineSkuVisit = $orderitem->Quantity . 'X' . $orderitem->SKU;
														$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
														$combineQuantity = $combineQuantity + $orderitem->Quantity;
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
														$combineBarcode = $productDetail['ProductDesc']['barcode'];
													}
													else
													{
														$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' . $orderitem->SKU;
														$combinePrice = $combinePrice + $orderitem->Quantity * $orderitem->PricePerUnit;
														$combineQuantity = $combineQuantity + $orderitem->Quantity;
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $orderitem->SKU )));
														$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
													}
													
												}
												else if( count( explode( '-', $orderitem->SKU ) ) == 3 )
												{
													$splitskus = explode( '-' , $orderitem->SKU);										
													if( $combineSkuVisit == '' )
													{
														$combineSkuVisit .= ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
														$combineQuantity = $combineQuantity + $splitskus[2];
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
														$combineBarcode = $productDetail['ProductDesc']['barcode'];
													}
													else
													{
														$combineSkuVisit .= ','  .  ($orderitem->Quantity * $splitskus[2]) .'X'. 'S-'.$splitskus[1];
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $orderitem->PricePerUnit );
														$combineQuantity = $combineQuantity + $splitskus[2];
														
														$productDetail	=	$this->Product->find('first', array('conditions' => array('Product.product_sku' => $splitskus[2] )));
														$combineBarcode = ',' . $productDetail['ProductDesc']['barcode'];
													}
													
												}
												else if( count( explode( '-', $orderitem->SKU ) ) > 3 )
												{
													// For Bundle with muti type Sku
													$splitskus = explode( '-', $orderitem->SKU );
													
													$totalPrice = $orderitem->Quantity * $orderitem->PricePerUnit;
													$itemPrice = $totalPrice / ($orderitem->Quantity * (count($splitskus)-2));
													
													$in = 1; while( $in <= count( $splitskus )-2 ):											
														$combinePrice = $combinePrice + ( $orderitem->Quantity * $itemPrice );
														if( $combineSkuVisit == '' )
														{
															//$quantity = $quantity + $orderitem->Quantity;
															$combineSkuVisit .= $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
															$combineQuantity = $combineQuantity + $quantity + $orderitem->Quantity;
														}
														else
														{
															//$quantity = $quantity + $orderitem->Quantity;
															$combineSkuVisit .= ',' . $orderitem->Quantity . 'X' .'S-'.$splitskus[$in];												
															$combineQuantity = $combineQuantity + $orderitem->Quantity;
														}
													$in++;
													endwhile;
													
												}
																					
											}
											
											//Saving
											// For Bundle with same type Sku				
											//Store and split the same SKU bundle order
											$splititem['pack_order_quantity']		=	0;
											$splititem['product_sku_identifier']		= "single";			
											$splititem['price']		=	$combinePrice;
											$splititem['product_order_id_identify']		=	$result->NumOrderId;
											
											$splititem['order_split']		=	$orderGroup;
											$splititem['quantity']			=	$combineQuantity;
											$splititem['product_type']		=	"bundle";
											$splititem['order_id']		=	$result->NumOrderId;
											$splititem['sku']			=	$combineSkuVisit;								
											$splititem['barcode']		=	$combineBarcode;
											
											//pr($splititem);
											
											$productDetail['Product']['current_stock_level']	= $productDetail['Product']['current_stock_level'] - $splititem['quantity'];
											$productDetail['Product']['lock_qty']				= $productDetail['Product']['lock_qty'] + $splititem['quantity'];
											
											/*$this->Product->updateAll(array('Product.current_stock_level' => $productDetail['Product']['current_stock_level'], 'Product.lock_qty' => $productDetail['Product']['lock_qty']),
																		array('Product.product_sku' => $splititem['sku']));*/
											$this->OrderItem->create();
											$this->OrderItem->save( $splititem );
											//echo "saved";
											break;
										}										
										
									}
									
								}
								
							}	
							
							//************** Merge Order after splitting with different scenarios **************
							$this->mergeSplitOrdersByOrderId_AccordingRules( $result->NumOrderId , $flagVar );
                                                        
							//$this->loadModel( 'MergeOrder' );							
							//pr( $this->MergeOrder->find('all') ); exit;
							
							// code for save customer detail
							$customerInfo['Customer']['email']		=	$result->CustomerInfo->Address->EmailAddress;
							$customerInfo['Customer']['address1']	=	$result->CustomerInfo->Address->Address1;
							$customerInfo['Customer']['address2']	=	$result->CustomerInfo->Address->Address2;
							$customerInfo['Customer']['address3']	=	$result->CustomerInfo->Address->Address3;
							$customerInfo['Customer']['town']		=	$result->CustomerInfo->Address->Town;
							$customerInfo['Customer']['region']		=	$result->CustomerInfo->Address->Region;
							$customerInfo['Customer']['postcode']	=	$result->CustomerInfo->Address->PostCode;
							$customerInfo['Customer']['country']	=	$result->CustomerInfo->Address->Country;
							$customerInfo['Customer']['name']		=	$result->CustomerInfo->Address->FullName;
							$customerInfo['Customer']['company']	=	$result->CustomerInfo->Address->Company;
							$customerInfo['Customer']['phone']		=	$result->CustomerInfo->Address->PhoneNumber;
							$customerInfo['Customer']['source']		=	$result->GeneralInfo->Source;
							$customerInfo['Customer']['subsource']	=	$result->GeneralInfo->SubSource;
						
							$customerdetails	=	$this->Customer->find('first', array('conditions' => array('Customer.email' => $customerInfo['Customer']['email'])));
							
							if( count($customerdetails) > 0 )
							{
								//$customerdetails['Customer']['count'] = $customerdetails['Customer']['count'] + '1';
								$this->Customer->updateAll(array('Customer.order_id' => $result->NumOrderId),
								array('Customer.email' => $customerdetails['Customer']['email']));
							}
							else
							{
								$customerInfo['Customer']['order_id']		=	$result->NumOrderId;
								$this->Customer->create();
								$this->Customer->saveAll( $customerInfo );
							}
							
						}	
					}
					else
					{
						//Do something you required
					}					
				}
				
				/* update tyose order has 0 quantity */
				//$this->updateMergeOrder();
				
				/* call the function for assign the postal servises */
				//$this->assign_services();
				$this->getBarcode();	
				//$this->setAgainAssignedServiceToAllOrder(); // Euraco Group	
				
				//pr($orders); 
				
				//Delete cancel UNKNOWN orders
				/*$getAllUnprepardId = $this->UnprepareOrder->find('all', array(
							'conditions' => array(
								'UnprepareOrder.order_id NOT IN' => $orders
								),									
							  'fields' => array( 
								'UnprepareOrder.order_id',
								'UnprepareOrder.id',  
								'UnprepareOrder.items',  
							)
						)
					);
				
				foreach( $getAllUnprepardId  as $getAllId )
				{
					$itemsReserve = unserialize($getAllId['UnprepareOrder']['items']);
					$this->reserveInventoryForUnknown( $itemsReserve , 2 );
					$this->UnprepareOrder->delete( $getAllId['UnprepareOrder']['id'] );
				}*/
				 
				//Sync start
				App::import( 'Controller' , 'MyExceptions' );
				$exception = new MyExceptionsController();
				$exception->syncComp( $locationName );
							
				/*App::import('Controller', 'Products');
				$productModel = new ProductsController();
				$productModel->prepareVirtualStock();*/
				
			return "done";	
			}
			
			public function setManifestRecord( $id = null, $manifestName )
			 {
					$this->loadModel( 'OpenOrder' );
					$this->loadModel( 'MergeUpdate' );
					$this->loadModel( 'ManifestEntrie' );
					$this->loadModel( 'ClientMagazine' );
					$getRecord	=	$this->MergeUpdate->find( 'first', array( 'conditions' => array( 'MergeUpdate.id' => $id ) ) );
					$getSku = explode( ',' , $getRecord['MergeUpdate']['sku']);
					$combineTitle = '';
					$totalUnits = 0;
					$massWeight = 0;
					
					
					$j = 0;while( $j <= count($getSku)-1 )
					{
						$newSku = explode( 'XS-' , $getSku[$j] );
						$setNewSku = 'S-'.$newSku[1];
						$this->loadModel( 'Product' );
						$productSku = $this->Product->find(
							'first' ,
							array(
								'conditions' => array(
									'Product.product_sku' => $setNewSku
								)
							)
						);
						
						if( $combineTitle == '' )
						{
							$totalUnits = $totalUnits + $newSku[0];     
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;		
						}
						else
						{
							$totalUnits = $totalUnits + $newSku[0];
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;                                                        
						}
					$j++;	
					}
					
						 		
						$paramsConsignee = array(
						'conditions' => array(
							'OpenOrder.num_order_id' => $getRecord['MergeUpdate']['order_id']
						),
						'fields' => array(
							'OpenOrder.num_order_id',
							'OpenOrder.id',
							'OpenOrder.general_info',
							'OpenOrder.shipping_info',
							'OpenOrder.customer_info',
							'OpenOrder.totals_info'							
						)
					);
					
					$getConsigneeDetailFromLinnworksOrder = json_decode(json_encode($this->OpenOrder->find( 'first', $paramsConsignee )),0);					
					
					$customerDetail	=	unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->customer_info);
					$generalInfo	=	unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->general_info);
					$subsource		=	$generalInfo->SubSource;
					$packageWeight 	= 	$getRecord['MergeUpdate']['envelope_weight'];
					$massWeight 	= 	$packageWeight + $massWeight; 
					
					$data['split_order_id']		=	$getRecord['MergeUpdate']['product_order_id_identify'];
					$data['reference_num']		=	$generalInfo->ReferenceNum;
					$data['sub_source']			=	$subsource;
					$data['quantity']			=	$getRecord['MergeUpdate']['quantity'];
					$data['recipent_name']		=	$customerDetail->Address->FullName;
					
					$data['sku']				=	$getRecord['MergeUpdate']['sku'];
					$data['service_provider']	=	$getRecord['MergeUpdate']['service_provider'];
					$data['service_name']		=	$getRecord['MergeUpdate']['service_name'];
					$data['provider_ref_code']	=	$getRecord['MergeUpdate']['provider_ref_code'];
					$data['packaging_type']		=	$getRecord['MergeUpdate']['packaging_type'];
					$data['weight']				=	$massWeight;
					$data['envelope_cost']		=	$getRecord['MergeUpdate']['envelope_cost'];
					$data['delevery_country']	=	$getRecord['MergeUpdate']['delevery_country'];
					$data['manifest_name']		=	$manifestName;
					$data['manifest_date']		=	date("Y-m-d h:i:s");
					
					$this->ManifestEntrie->saveAll( $data );
			 }
			
	public function fetchOpenOrders( $locationName = null )
	{
		$this->layout = '';
		$this->autoRender = false;
		
		$this->loadModel('SourceList');
		$this->loadModel('OpenOrder');
		$this->loadModel('MergeUpdate');
		$this->loadModel('Product');		
		$this->loadModel('AssignService');
		$this->loadModel('Customer');
		$this->loadModel('OrderItem');			
		$this->loadModel('UnprepareOrder');
		
		
		//$orderData = $this->getOpenOrderByApi( $locationName);
		
		//if( $orderData['results'] == 'error' ){
			$orderData = $this->getOpenOrderByCsv();
		//}
		
		if( $orderData['results'] != 'no-new-order'){
			$this->saveOpenOrder( $orderData['results'] , $orderData['locationName'] , $orderData['orders']);
		}
	}
	
	public function getOpenOrderByApi( $locationName = null )
	{
		$this->layout = '';
		$this->autoRender = false;
				
		if($locationName != ''){
			$param = array(				
				'conditions' => array(					
					'SourceList.LocationName' => $locationName 
				)				
			); 								 
			$getList = json_decode(json_encode($this->SourceList->find( 'all' , $param )),0);
		}else{
			
			$param = array(				
				'conditions' => array(					
					'SourceList.status' => 'active' 
				)				
			); 
						
			$getList = json_decode(json_encode($this->SourceList->find( 'all' , $param )),0);
		}
		
		if( count( $getList ) > 0 )
		{	
		
			App::import('Vendor', 'Linnworks/src/php/Auth');
			App::import('Vendor', 'Linnworks/src/php/Factory');
			App::import('Vendor', 'Linnworks/src/php/Orders');
		
			$username = Configure::read('linnwork_api_username');
			$password = Configure::read('linnwork_api_password');
			
			$token = Configure::read('access_new_token');
			$applicationId = Configure::read('application_id');
			$applicationSecret = Configure::read('application_secret');				
			try{			
				$auth   = AuthMethods::AuthorizeByApplication($applicationId,$applicationSecret,$token);	
				$token  = $auth->Token;	
				$server = $auth->Server;
				
				file_put_contents(WWW_ROOT."logs/cron_".date("dmY").".log", "\n------".date("d-m-Y H:i:s")."-----\n", FILE_APPEND|LOCK_EX);
				foreach( $getList as $source ){
				
					$location_id  = $source->SourceList->StockLocationId;
					//if($source->SourceList->LocationName != ''){
						$locationName = $source->SourceList->LocationName;	
					//}				
					//$location_id = '8844999f-8e27-4155-949b-5b8bb81def86'; 
				
					//$openorder	=	OrdersMethods::GetOpenOrders('3000','1','','',$location_id,'',$token, $server);
					$openorder	=	OrdersMethods::GetAllOpenOrders("","",$location_id,"",$token, $server);	
					
					$orders = array();
					if(count($openorder) > 0){
						//echo $orderids. "otderID <br>";
						//$orders[]	=	$orderids->OrderId;
						 
						foreach ($openorder	 as $orderids)
						{
							$checkOpenOrder 	=	$this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.order_id' => $orderids)));
							//echo $checkOpenOrder."one <br>";
							//pr($checkOpenOrder);
							if( count($checkOpenOrder) == 0 )
							{
								//echo 'Insert >> '.$orderids->OrderId."<br>";
								$orders[]	=	$orderids;
								
							}
							else
							{
								//Clean Orders
								$this->cleanOrders();
								$unPaid[]	=	$orderids;
								//CHECK IF ORDER EXISTS OR NOT
								// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
								$order_status = OrdersMethods::GetOrders($unPaid,$location_id,true,true,$token, $server);
								//print_r(  $order_status);
								$results = $order_status[0];								
								$dataUpdate['OpenOrder']['id'] = $checkOpenOrder['OpenOrder']['id'];
								$dataUpdate['OpenOrder']['linn_fetch_orders'] = $results->GeneralInfo->Status;	
								//exit;						
								//$dataUpdate['OpenOrder']['linn_fetch_orders'] = serialize($result->CustomerInfo);
								$this->OpenOrder->saveAll( $dataUpdate ); 
								//Now update into Merge Section
								$ordStatus  = $results->GeneralInfo->Status;	
								$NumOrderid = $checkOpenOrder['OpenOrder']['num_order_id'];
								//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
								$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $ordStatus), array('MergeUpdate.order_id' => $NumOrderid) );
								unset( $unPaid );
					
							}
						}
						unset( $checkOpenOrder );				
					}
					
					file_put_contents(WWW_ROOT."logs/cron_".date("dmY").".log", $locationName."==".$location_id."\n", FILE_APPEND |LOCK_EX);
								
				}
					
				if(count( $orders ) == 0 )
				{			
					return array('results'=>'no-new-order','msg'=>'no new orders found.');
									
				}else{					
					try{
						$results = OrdersMethods::GetOrders($orders,$location_id,true,true,$token, $server);
						return array('results' => $results, 'locationName' => $locationName, 'orders' => $orders);	
					}catch (Exception $e) {					//echo 'Caught exception: ',  $e->getMessage(), "\n";
						return array('results'=>'error','msg'=>$e->getMessage());
					}				
				}
						
			}catch (Exception $e) {					
				//echo 'Caught exception: ',  $e->getMessage(), "\n";
				return array('results'=>'error','msg'=>$e->getMessage());
					
			}		
				
		}		
			
	}	
	
	public function getOpenOrderByCsv()
	{
 		$configRate = Configure::read('configuration');
		$this->maxValue = $configRate['hiddenConfigBridge']['max_value'];
		$this->minValue  = $configRate['hiddenConfigBridge']['min_value'];;
		$this->conversionRateValue  = $configRate['hiddenConfigBridge']['conversion_rate'];;
		
		$this->autoLayout = 'ajax';
		$this->autoRender = false;
		
		/*$this->loadModel('Product');
		$this->loadModel( 'OpenOrder' );
		$this->loadModel( 'MergeUpdate' );*/
		
		
		
		App::import('Vendor', 'PHPExcel/IOFactory');
		
		$fName   =  WWW_ROOT .'Book1_1.csv'; 
		//$fName    = 'http://techdrive.biz/LinnworksOrdersExport/order.csv'; 
		$f_Name   =  WWW_ROOT .'orders_csv'.DS.'order.csv'; 
		
		file_put_contents( $f_Name, file_get_contents($fName));	
		
		$oderIdInitial = array();
		$file = fopen($f_Name,"r");
		$i = 0;
		while(! feof($file))
		{
			$innerOrderId = fgetcsv($file);
			
			if($i > 0 && $innerOrderId[0]!=''){
				
				$amazon_order_id = $innerOrderId[0];
				$checkOpenOrder = $this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.amazon_order_id' => $amazon_order_id)));
				if( count($checkOpenOrder) == 0 )
				{
					$oderIdInitial[$i][0] = $innerOrderId[0];
					$orderItems[$innerOrderId[0]][] = $innerOrderId[0];
				
				}
				else
				{						
					//Clean Orders
					$this->cleanOrders();
					if($innerOrderId[32] == 'Paid'){
						 $status = 1;
						
						//CHECK IF ORDER EXISTS OR NOT
						// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
						
						$dataUpdate['OpenOrder']['id'] = $checkOpenOrder['OpenOrder']['id'];
						$dataUpdate['OpenOrder']['linn_fetch_orders'] = $status;	
						$this->OpenOrder->saveAll( $dataUpdate ); 
						
						//Now update into Merge Section							
						$amazon_order_id =  $checkOpenOrder['OpenOrder']['amazon_order_id'];
						//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
						$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $status), array('MergeUpdate.amazon_order_id' => $amazon_order_id) );
					}
				
				}
			}
			$i++;							
		}
		fclose($file);
		
		 
		if( count( $oderIdInitial ) > 0 )
		{
			
			$objPHPExcel = new PHPExcel();
			$objReader   = PHPExcel_IOFactory::createReader('CSV');
			$objReader->setReadDataOnly(true);		
			
			$objPHPExcel 	= $objReader->load($f_Name);					
			$objWorksheet	= $objPHPExcel->setActiveSheetIndex(0);
			$lastRow 		= $objPHPExcel->getActiveSheet()->getHighestRow();
			$colString		= $highestColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
			$colNumber 		= PHPExcel_Cell::columnIndexFromString($colString);
			$getClientData  = array();
			//no of different orders
			$inc = 0;
			$orderids = array();
			for( $inId = 1; $inId <= count( $oderIdInitial ); $inId++ )
			{							
				//order id
				$clientOrderId = $oderIdInitial[$inId][0];
				$orderids[]	   = $clientOrderId;
				//outer flags
				$sub = 0; $post = 0;
				//find the order related to order id													
				for($i=2,$innerIndex = 0; $i <= $lastRow;$i++) 
				{
					//now area of storing the order according to 
					$cell = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();								
					
					//seeking
					if( $cell == $clientOrderId )
					{
						
						if( $innerIndex == 0 )
						{
							$file_name = date("dmy_Hi");
							//$orderIdSet = strtotime(date('Y-m-d H:i:s')) ;
							$exchangeRate = $objWorksheet->getCellByColumnAndRow(24,$i)->getValue();
							$orderIdSet = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
							$customOrderId = $orderIdSet;
							$getClientData[$inc]['file_name'] = $file_name.'.csv';
							$getClientData[$inc]['OrderId'] = $customOrderId;
							$et = explode("-", $orderIdSet);
							$getClientData[$inc]['NumOrderId'] = '1'.$et[0].$et[1];
							$getClientData[$inc]['amazon_order_id'] = $orderIdSet;
							
							$ReferenceNum = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();//$orderIdSet;
							$status = 1;
							/*if($objWorksheet->getCellByColumnAndRow(32,$i)->getValue() == "Paid"){
								$status = 1;
							}*/
							$getClientData[$inc]['GeneralInfo']['Status'] = $status;
							$getClientData[$inc]['GeneralInfo']['Notes'] = '';//$objWorksheet->getCellByColumnAndRow(63,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['Marker'] = 0;//$objWorksheet->getCellByColumnAndRow(44,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['ReferenceNum'] = $ReferenceNum;
							$getClientData[$inc]['GeneralInfo']['ExternalReferenceNum'] = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
							
							$purchase_date = str_replace("/","-",$objWorksheet->getCellByColumnAndRow(2,$i)->getValue());				
							
							$getClientData[$inc]['GeneralInfo']['ReceivedDate'] = date("Y-m-d H:i:s",strtotime($purchase_date));
							$getClientData[$inc]['GeneralInfo']['Source'] =  'AMAZON';//$objWorksheet->getCellByColumnAndRow(33,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['SubSource'] = $objWorksheet->getCellByColumnAndRow(25,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['HoldOrCancel'] = 'TRUE';//$objWorksheet->getCellByColumnAndRow(46,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['DespatchByDate'] = date("Y-m-d H:i:s",strtotime($purchase_date + "+5 days"));										
							$getClientData[$inc]['GeneralInfo']['Location'] = '11c4663e-06b8-40ea-8af4-b7b675a55555';
							
							
							$getClientData[$inc]['ShippingInfo']['PostalServiceId'] = 'eba09b4d-cc52-44d6-ad58-'.date('ymdhis'); 									
							$getClientData[$inc]['ShippingInfo']['PostalServiceName'] = $objWorksheet->getCellByColumnAndRow(15,$i)->getValue();									
							$getClientData[$inc]['ShippingInfo']['TotalWeight'] = 0;//$objWorksheet->getCellByColumnAndRow(64,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['ItemWeight'] = 0;
							$getClientData[$inc]['ShippingInfo']['PackageCategoryId'] = '00000000-0000-0000-0000-000000000000';
							$getClientData[$inc]['ShippingInfo']['PackageCategory'] = 'Default';//$objWorksheet->getCellByColumnAndRow(41,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['PackageTypeId'] = '00000000-0000-0000-0000-000000000000';
							$getClientData[$inc]['ShippingInfo']['PackageType'] = 'Default';
							$getClientData[$inc]['ShippingInfo']['PostageCost'] = $objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['PostageCostExTax'] = '';//$objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['TrackingNumber'] ='';// $objWorksheet->getCellByColumnAndRow(42,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['ManualAdjust'] = '';
							
							$getClientData[$inc]['CustomerInfo']['ChannelBuyerName'] = $objWorksheet->getCellByColumnAndRow(8,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['EmailAddress'] = $objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['RecipentName'] = $objWorksheet->getCellByColumnAndRow(16,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Address1'] = $objWorksheet->getCellByColumnAndRow(17,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Address2'] = $objWorksheet->getCellByColumnAndRow(18,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Address3'] = $objWorksheet->getCellByColumnAndRow(19,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Town'] = $objWorksheet->getCellByColumnAndRow(20,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Region'] = $objWorksheet->getCellByColumnAndRow(21,$i)->getValue();
							
							$PostCode = $objWorksheet->getCellByColumnAndRow(22,$i)->getValue();
							$pcountry = array('FR','DE','ES');
							$country_code = $objWorksheet->getCellByColumnAndRow(23,$i)->getValue();
							if((strlen($PostCode) < 5) && in_array($country_code,$pcountry)){
								$PostCode =  '0'.$PostCode;	
							}										 
							$getClientData[$inc]['CustomerInfo']['Address']['PostCode'] = 	$PostCode;								
							
							
							$getClientData[$inc]['CustomerInfo']['Address']['Country'] =  $objWorksheet->getCellByColumnAndRow(23,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['FullName'] = $objWorksheet->getCellByColumnAndRow(16,$i)->getValue();//$objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Company'] = '';//$objWorksheet->getCellByColumnAndRow(6,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['PhoneNumber'] = $objWorksheet->getCellByColumnAndRow(9,$i)->getValue();;
							$getClientData[$inc]['CustomerInfo']['Address']['CountryId'] = '445c01b5-e48c-4002-aef5-888888888888';										
							$getClientData[$inc]['CustomerInfo']['BillingAddress'] = 1;
							
							$getClientData[$inc]['TotalsInfo']['PaymentMethod'] = 'Default';//$objWorksheet->getCellByColumnAndRow(45,$i)->getValue();
							$getClientData[$inc]['TotalsInfo']['PaymentMethodId'] = '00000000-0000-0000-0000-000000000000';
							$getClientData[$inc]['TotalsInfo']['ProfitMargin'] = 0;
							$getClientData[$inc]['TotalsInfo']['TotalDiscount'] = 0;
							$getClientData[$inc]['TotalsInfo']['Currency'] = $objWorksheet->getCellByColumnAndRow(30,$i)->getValue();
							$getClientData[$inc]['TotalsInfo']['CountryTaxRate'] = '';
							
							$getClientData[$inc]['FolderName'] = '';
							
							
						}
						$sub += $objWorksheet->getCellByColumnAndRow(34,$i)->getValue();
						$post += $objWorksheet->getCellByColumnAndRow(30,$i)->getValue();
						
						$getClientData[$inc]['TotalsInfo']['Subtotal'] = $sub;
						$getClientData[$inc]['TotalsInfo']['PostageCost'] =$post;
						$getClientData[$inc]['TotalsInfo']['Tax'] = $objWorksheet->getCellByColumnAndRow(29,$i)->getValue();
						$getClientData[$inc]['TotalsInfo']['TotalCharge'] = $sub;
								
						
						//order ref number customized
						$getClientData[$inc]['Items'][$innerIndex]['external_order_ref'] = $ReferenceNum;
										
						$getClientData[$inc]['Items'][$innerIndex]['OrderId'] = $customOrderId;
						$getClientData[$inc]['Items'][$innerIndex]['ItemId'] = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['StockItemId'] = '00000000-0000-0000-0000-999999999999';
						$getClientData[$inc]['Items'][$innerIndex]['ItemNumber'] = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
								
						$getClientData[$inc]['Items'][$innerIndex]['SKU'] = $objWorksheet->getCellByColumnAndRow(26,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['OuterSKU'] = $objWorksheet->getCellByColumnAndRow(10,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['ItemSource'] = $objWorksheet->getCellByColumnAndRow(25,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['Title'] = $objWorksheet->getCellByColumnAndRow(11,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['Quantity'] = $objWorksheet->getCellByColumnAndRow(12,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['CategoryName'] = 'Default';
						$getClientData[$inc]['Items'][$innerIndex]['CompositeAvailablity'] = '';
						$getClientData[$inc]['Items'][$innerIndex]['RowId'] = 'a020dc1d-4ed1-4fe7-8bfe-333333333333';
						$getClientData[$inc]['Items'][$innerIndex]['StockLevelsSpecified'] = 1;
						$getClientData[$inc]['Items'][$innerIndex]['AvailableStock'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['PricePerUnit'] =  $objWorksheet->getCellByColumnAndRow(32,$i)->getValue();
						
						$getClientData[$inc]['Items'][$innerIndex]['UnitCost'] = $objWorksheet->getCellByColumnAndRow(32,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['Discount'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['Tax'] = $objWorksheet->getCellByColumnAndRow(29,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['TaxRate'] =  0;
						$getClientData[$inc]['Items'][$innerIndex]['Cost'] = $objWorksheet->getCellByColumnAndRow(32,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['CostIncTax'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['CompositeSubItems'] = '';
						$getClientData[$inc]['Items'][$innerIndex]['IsService'] = 'FALSE';//$objWorksheet->getCellByColumnAndRow(60,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['SalesTax'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['TaxCostInclusive'] = 1;
						$getClientData[$inc]['Items'][$innerIndex]['PartShipped'] = '';
						$getClientData[$inc]['Items'][$innerIndex]['Weight'] = 0;
						// fetch from inventery 
						$getClientData[$inc]['Items'][$innerIndex]['BarcodeNumber'] = '';
						$getClientData[$inc]['Items'][$innerIndex]['Market'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['ChannelSKU'] = $objWorksheet->getCellByColumnAndRow(10,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['ChannelTitle'] = $objWorksheet->getCellByColumnAndRow(11,$i)->getValue();
						
						$innerIndex++;	
						}		
					}
				
				$inc++;			
			}
		
			$orderData	= json_decode(json_encode( $getClientData ),0);		
			 pr($orderData);
			//return	array('orders' => $orderData, 'orderids' => $orderids) ;
			return array('results' => $orderData, 'locationName' => 'default', 'orders' => $orderids);	
		}
		else{
			return array('results'=>'no-new-order','msg'=>'No new orders found in csv.');
		}	
	}	
    public function getOpenOrderByCsv_20()
	{
		
		$configRate = Configure::read('configuration');
		$this->maxValue = $configRate['hiddenConfigBridge']['max_value'];
		$this->minValue  = $configRate['hiddenConfigBridge']['min_value'];;
		$this->conversionRateValue  = $configRate['hiddenConfigBridge']['conversion_rate'];;
		
		$this->autoLayout = 'ajax';
		$this->autoRender = false;
		
		/*$this->loadModel('Product');
		$this->loadModel( 'OpenOrder' );
		$this->loadModel( 'MergeUpdate' );*/
		
		
		
		App::import('Vendor', 'PHPExcel/IOFactory');
		
		$fName    = 'http://techdrive.biz/LinnworksOrdersExport/order.csv'; 
		$f_Name   =  WWW_ROOT .'orders_csv'.DS.'order.csv'; 
		
		file_put_contents( $f_Name, file_get_contents($fName));	
		
		$oderIdInitial = array();
		$file = fopen($f_Name,"r");
		$i = 0;
		while(! feof($file))
		{
			$innerOrderId = fgetcsv($file);
			
			if($i > 0 && $innerOrderId[0]!=''){
				
				$order_id = $innerOrderId[0];
				$checkOpenOrder = $this->OpenOrder->find('first', array('conditions'=>array('OpenOrder.num_order_id' => $order_id)));
				if( count($checkOpenOrder) == 0 )
				{
					$oderIdInitial[$i][0] = $innerOrderId[0];
					$orderItems[$innerOrderId[0]][] = $innerOrderId[0];
				
				}
				else
				{						
					//Clean Orders
					$this->cleanOrders();
					if($innerOrderId[32] == 'Paid'){
						 $status = 1;
						
						//CHECK IF ORDER EXISTS OR NOT
						// ORDER STATUS -> PAID / UNPAID / RESEND / PENDING / HELD
						
						$dataUpdate['OpenOrder']['id'] = $checkOpenOrder['OpenOrder']['id'];
						$dataUpdate['OpenOrder']['linn_fetch_orders'] = $status;	
						$this->OpenOrder->saveAll( $dataUpdate ); 
						
						//Now update into Merge Section							
						$NumOrderid =  $checkOpenOrder['OpenOrder']['num_order_id'];
						//Update Query for merge section also for ensure those will present into Open order screen and Unpain etc screen
						$this->MergeUpdate->updateAll( array('MergeUpdate.linn_fetch_orders' => $status), array('MergeUpdate.order_id' => $NumOrderid) );
					}
				
				}
			}
			$i++;							
		}
		fclose($file);
		
		 
		if( count( $oderIdInitial ) > 0 )
		{
			
			$objPHPExcel = new PHPExcel();
			$objReader   = PHPExcel_IOFactory::createReader('CSV');
			$objReader->setReadDataOnly(true);		
			
			$objPHPExcel 	= $objReader->load($f_Name);					
			$objWorksheet	= $objPHPExcel->setActiveSheetIndex(0);
			$lastRow 		= $objPHPExcel->getActiveSheet()->getHighestRow();
			$colString		= $highestColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
			$colNumber 		= PHPExcel_Cell::columnIndexFromString($colString);
			$getClientData  = array();
			//no of different orders
			$inc = 0;
			$orderids = array();
			for( $inId = 1; $inId <= count( $oderIdInitial ); $inId++ )
			{							
				//order id
				$clientOrderId = $oderIdInitial[$inId][0];
				$orderids[]	   = $clientOrderId;
				//outer flags
				
				//find the order related to order id													
				for($i=2,$innerIndex = 0; $i <= $lastRow;$i++) 
				{
					//now area of storing the order according to 
					$cell = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();								
					
					//seeking
					if( $cell == $clientOrderId )
					{
						
						if( $innerIndex == 0 )
						{
							$file_name = date("dmy_Hi");
							//$orderIdSet = strtotime(date('Y-m-d H:i:s')) ;
							$exchangeRate = $objWorksheet->getCellByColumnAndRow(24,$i)->getValue();
							$orderIdSet = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
							$customOrderId = $orderIdSet;
							$getClientData[$inc]['file_name'] = $file_name.'.csv';
							$getClientData[$inc]['OrderId'] = $customOrderId;
							$getClientData[$inc]['NumOrderId'] = $orderIdSet;
							$ReferenceNum = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();//$orderIdSet;
							$status = 0;
							if($objWorksheet->getCellByColumnAndRow(32,$i)->getValue() == "Paid"){
								$status = 1;
							}
							$getClientData[$inc]['GeneralInfo']['Status'] = $status;
							$getClientData[$inc]['GeneralInfo']['Notes'] = $objWorksheet->getCellByColumnAndRow(63,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['Marker'] = $objWorksheet->getCellByColumnAndRow(44,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['ReferenceNum'] = $ReferenceNum;
							$getClientData[$inc]['GeneralInfo']['ExternalReferenceNum'] = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
							
							$purchase_date = str_replace("/","-",$objWorksheet->getCellByColumnAndRow(26,$i)->getValue());				
							
							$getClientData[$inc]['GeneralInfo']['ReceivedDate'] = date("Y-m-d H:i:s",strtotime($purchase_date));
							$getClientData[$inc]['GeneralInfo']['Source'] =  $objWorksheet->getCellByColumnAndRow(33,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['SubSource'] = $objWorksheet->getCellByColumnAndRow(34,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['HoldOrCancel'] = $objWorksheet->getCellByColumnAndRow(46,$i)->getValue();
							$getClientData[$inc]['GeneralInfo']['DespatchByDate'] = date("Y-m-d H:i:s",strtotime($purchase_date + "+3 days"));										
							$getClientData[$inc]['GeneralInfo']['Location'] = '11c4663e-06b8-40ea-8af4-b7b675a55555';
							
							
							$getClientData[$inc]['ShippingInfo']['PostalServiceId'] = 'eba09b4d-cc52-44d6-ad58-cb8df1122222';									
							$getClientData[$inc]['ShippingInfo']['PostalServiceName'] = $objWorksheet->getCellByColumnAndRow(37,$i)->getValue();									
							$getClientData[$inc]['ShippingInfo']['TotalWeight'] = $objWorksheet->getCellByColumnAndRow(64,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['ItemWeight'] = 0;
							$getClientData[$inc]['ShippingInfo']['PackageCategoryId'] = '00000000-0000-0000-0000-000000000000';
							$getClientData[$inc]['ShippingInfo']['PackageCategory'] = $objWorksheet->getCellByColumnAndRow(41,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['PackageTypeId'] = '00000000-0000-0000-0000-000000000000';
							$getClientData[$inc]['ShippingInfo']['PackageType'] = 'Default';
							$getClientData[$inc]['ShippingInfo']['PostageCost'] = $objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['PostageCostExTax'] = '';//$objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['TrackingNumber'] = $objWorksheet->getCellByColumnAndRow(42,$i)->getValue();
							$getClientData[$inc]['ShippingInfo']['ManualAdjust'] = '';
							
							$getClientData[$inc]['CustomerInfo']['ChannelBuyerName'] = $objWorksheet->getCellByColumnAndRow(43,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['EmailAddress'] = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['RecipentName'] = $objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Address1'] = $objWorksheet->getCellByColumnAndRow(8,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Address2'] = $objWorksheet->getCellByColumnAndRow(9,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Address3'] = $objWorksheet->getCellByColumnAndRow(10,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Town'] = $objWorksheet->getCellByColumnAndRow(11,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Region'] = $objWorksheet->getCellByColumnAndRow(12,$i)->getValue();
							
							$PostCode = $objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
							$pcountry = array('FR','DE','ES');
							$country_code = $objWorksheet->getCellByColumnAndRow(15,$i)->getValue();
							if((strlen($PostCode) < 5) && in_array($country_code,$pcountry)){
								$PostCode =  '0'.$PostCode;	
							}										 
							$getClientData[$inc]['CustomerInfo']['Address']['PostCode'] = 	$PostCode;								
							
							
							$getClientData[$inc]['CustomerInfo']['Address']['Country'] =  $objWorksheet->getCellByColumnAndRow(14,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['FullName'] = $objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['Company'] = $objWorksheet->getCellByColumnAndRow(6,$i)->getValue();
							$getClientData[$inc]['CustomerInfo']['Address']['PhoneNumber'] = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();;
							$getClientData[$inc]['CustomerInfo']['Address']['CountryId'] = '445c01b5-e48c-4002-aef5-888888888888';										
							$getClientData[$inc]['CustomerInfo']['BillingAddress'] = 1;
							
							$getClientData[$inc]['TotalsInfo']['PaymentMethod'] = $objWorksheet->getCellByColumnAndRow(45,$i)->getValue();
							$getClientData[$inc]['TotalsInfo']['PaymentMethodId'] = '00000000-0000-0000-0000-000000000000';
							$getClientData[$inc]['TotalsInfo']['ProfitMargin'] = 0;
							$getClientData[$inc]['TotalsInfo']['TotalDiscount'] = 0;
							$getClientData[$inc]['TotalsInfo']['Currency'] = $objWorksheet->getCellByColumnAndRow(30,$i)->getValue();
							$getClientData[$inc]['TotalsInfo']['CountryTaxRate'] = '';
							
							$getClientData[$inc]['FolderName'] = '';
							$getClientData[$inc]['TotalsInfo']['Subtotal'] = $objWorksheet->getCellByColumnAndRow(58,$i)->getValue();
							$getClientData[$inc]['TotalsInfo']['PostageCost'] =$objWorksheet->getCellByColumnAndRow(27,$i)->getValue();
							$getClientData[$inc]['TotalsInfo']['Tax'] = $objWorksheet->getCellByColumnAndRow(28,$i)->getValue();
							$getClientData[$inc]['TotalsInfo']['TotalCharge'] = $objWorksheet->getCellByColumnAndRow(29,$i)->getValue();
							
						}	
						
						//order ref number customized
						$getClientData[$inc]['Items'][$innerIndex]['external_order_ref'] = $ReferenceNum;
										
						$getClientData[$inc]['Items'][$innerIndex]['OrderId'] = $customOrderId;
						$getClientData[$inc]['Items'][$innerIndex]['ItemId'] = '00000000-0000-0000-0000-000000000000';
						$getClientData[$inc]['Items'][$innerIndex]['StockItemId'] = '00000000-0000-0000-0000-999999999999';
						$getClientData[$inc]['Items'][$innerIndex]['ItemNumber'] = $objWorksheet->getCellByColumnAndRow(52,$i)->getValue();
								
						$getClientData[$inc]['Items'][$innerIndex]['SKU'] = $objWorksheet->getCellByColumnAndRow(48,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['OuterSKU'] = $objWorksheet->getCellByColumnAndRow(48,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['ItemSource'] = $objWorksheet->getCellByColumnAndRow(34,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['Title'] = $objWorksheet->getCellByColumnAndRow(50,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['Quantity'] = $objWorksheet->getCellByColumnAndRow(53,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['CategoryName'] = 'Default';
						$getClientData[$inc]['Items'][$innerIndex]['CompositeAvailablity'] = '';
						$getClientData[$inc]['Items'][$innerIndex]['RowId'] = 'a020dc1d-4ed1-4fe7-8bfe-333333333333';
						$getClientData[$inc]['Items'][$innerIndex]['StockLevelsSpecified'] = 1;
						$getClientData[$inc]['Items'][$innerIndex]['AvailableStock'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['PricePerUnit'] =  $objWorksheet->getCellByColumnAndRow(54,$i)->getValue();
						
						$getClientData[$inc]['Items'][$innerIndex]['UnitCost'] = $objWorksheet->getCellByColumnAndRow(54,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['Discount'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['Tax'] = $objWorksheet->getCellByColumnAndRow(57,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['TaxRate'] =  0;
						$getClientData[$inc]['Items'][$innerIndex]['Cost'] = $objWorksheet->getCellByColumnAndRow(54,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['CostIncTax'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['CompositeSubItems'] = '';
						$getClientData[$inc]['Items'][$innerIndex]['IsService'] = $objWorksheet->getCellByColumnAndRow(60,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['SalesTax'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['TaxCostInclusive'] = 1;
						$getClientData[$inc]['Items'][$innerIndex]['PartShipped'] = '';
						$getClientData[$inc]['Items'][$innerIndex]['Weight'] = 0;
						// fetch from inventery 
						$getClientData[$inc]['Items'][$innerIndex]['BarcodeNumber'] = '';
						$getClientData[$inc]['Items'][$innerIndex]['Market'] = 0;
						$getClientData[$inc]['Items'][$innerIndex]['ChannelSKU'] = $objWorksheet->getCellByColumnAndRow(51,$i)->getValue();
						$getClientData[$inc]['Items'][$innerIndex]['ChannelTitle'] = $objWorksheet->getCellByColumnAndRow(49,$i)->getValue();
						
						$innerIndex++;	
						}		
					}
				
				$inc++;			
			}
		
			$orderData	= json_decode(json_encode( $getClientData ),0);		
			//return	array('orders' => $orderData, 'orderids' => $orderids) ;
			return array('results' => $orderData, 'locationName' => '', 'orders' => $orderids);	
		}
		else{
			return array('results'=>'no-new-order','msg'=>'No new orders found in csv.');
		}	
	}
	
	public function InkSkuInventory($data = array(),$split_order_id = null){
	
 		file_put_contents(WWW_ROOT .'logs/ink_sku_function_'.date('dmY').'.log', date('Y-m-d H:i:s')."\n", FILE_APPEND | LOCK_EX);
		 
	
  		if(count($data) > 0){
		
			$this->loadMOdel( 'InkSku' ); 
			$this->loadModel( 'InkOrderLocation');
			$this->loadModel( 'BinLocation' );		
			$this->loadModel( 'CheckIn'); 	
			$this->loadModel( 'Product'); 
					
			//$quantity = $data['quantity'] * 2;		
			$quantity =  2;					
			$sku      = $data['sku'];			 
			$order_id = $data['split_order_id']; 
			$ink_sku  = 'S-INKENVELOPES';
			
 			$po_name = array(); $posIds = array();
			$pos = array();     $binname = array(); 
			$available_qty = 0; $available_qty_bin = 0;	
  			
			$check_ink = $this->InkSku->find( 'first', array( 'conditions' => array( 'sku' => $sku ) ) );
			
			if( count($check_ink) > 0 ){
			
   				$this->loadModel( 'OpenOrder' );
				$ord = $this->OpenOrder->find( 'first', array( 'conditions' => array('num_order_id' =>$order_id),'fields' => array('customer_info') ) ); 
				$customer_info = unserialize( $ord['OpenOrder']['customer_info']);  
				
				if($customer_info->Address->Country == 'United Kingdom'){
					 
					$pro_ink = $this->Product->find( 'first', array( 'conditions' => array( 'product_sku' => $ink_sku ) ) );
					$ink_barcode = $pro_ink['ProductDesc']['barcode'] ; 
					
					/*-------2 Envelop per order By Jake-01-05-2019- If order is already exist------------*/
					$_loc = $this->InkOrderLocation->find('first',array('conditions' => array('order_id' => $order_id, 'status' => 'active')));  
					
					if(count($_loc) > 0){
						
						$quantity = 0;	
						$finalData['available_qty_check_in'] = '0';
						$finalData['available_qty_bin'] = '0';
						$finalData['quantity']      	= $quantity;
						$finalData['order_id']			= $order_id;					
						$finalData['sku']	   			= $sku;	
						$finalData['ink_sku']	   		= $ink_sku;								
						$finalData['barcode']  			= $ink_barcode;	
						$finalData['po_name']  			= '';	
						$finalData['po_id']    			= '';	
						$finalData['bin_location']  	= '';	
						$finalData['split_order_id']  	= $split_order_id;					
						$ord_loc = $this->InkOrderLocation->find('first',array('conditions' => array('order_id' => $order_id,'split_order_id'=>$split_order_id,'ink_sku' => $ink_sku,'sku' => $sku,'quantity' => $quantity, 'status' => 'active')));
						
						if(count($ord_loc) == 0){
							$this->InkOrderLocation->saveAll( $finalData );
							file_put_contents(WWW_ROOT .'logs/ink_sku_'.date('dmY').'.log', print_r($finalData,true), FILE_APPEND | LOCK_EX);							
						}
						
				  }else{ 
						/*-----------If Order is not found in ink location table------------*/
						$local_barcode = $this->Components->load('Common')->getLocalBarcode($ink_barcode);
						
						$poDetail = $this->CheckIn->find( 'all', 
							array( 'conditions' => array( 'CheckIn.barcode IN' => array($ink_barcode,$local_barcode), 'CheckIn.selling_qty != CheckIn.qty_checkIn','po_name !=""'),'order' => 'CheckIn.date  ASC ' ) );
						
						 
						if(count($poDetail) > 0){
							$qt_count = 0; 				
							foreach($poDetail as $po){
								$poQty = $po['CheckIn']['qty_checkIn'] - $po['CheckIn']['selling_qty'];
								if($poQty > 0){						
									 for($i=0; $i <= $quantity ;$i++){						 
										 if($qt_count >= $quantity ){
											 break;
										 }else if(($poQty - $i)  > 0){							
											$po_name[$po['CheckIn']['id']][] = $po['CheckIn']['po_name'];	
											$available_qty++;							
											$qt_count++;
										}												
									 }						
								}
							}
						}
						 
						
						$getStock = $this->BinLocation->find( 'all', array( 'conditions' => array( 'BinLocation.barcode' => $ink_barcode),'order' => 'priority ASC' ) );
						$bin_name = array(); $finalData['bin_location'] = "";
						
						if(count($getStock) > 0){
							
							$qt_count = 0 ;
							foreach($getStock as $val){
								 
									$binData['id'] = $val['BinLocation']['id'];					
									 for($i=0; $i <= $quantity ;$i++){						 
										 if($qt_count >= $quantity ){
											 break;
										 }else if(($val['BinLocation']['stock_by_location'] - $i)  > 0){							
											$bin_name[$val['BinLocation']['id']][] = $val['BinLocation']['bin_location'];	
											$available_qty_bin++;						
											$qt_count++;
										}												
									 }						 
									 
							}				
							
						}
									
						 
						if(count($po_name) > 0){
							foreach(array_keys($po_name) as $k){
								$qts   = count($po_name[$k]);	
								$pos[] = $po_name[$k][0];
								$posIds[] = $k;
								$this->CheckIn->updateAll( array('CheckIn.selling_qty' =>  "CheckIn.selling_qty + $qts"),array('CheckIn.id' => $k));
							}
						}
						
						if(count($bin_name) > 0)
						{
							foreach(array_keys($bin_name) as $k){					
								$qts = count($bin_name[$k]);	
								$binname[] = $bin_name[$k][0];	
								
								$finalData['available_qty_check_in'] = $available_qty;
								$finalData['available_qty_bin'] = $qts;
								$finalData['quantity']      	= $quantity;
								$finalData['order_id']			= $order_id;					
								$finalData['sku']	   			= $sku;				
								$finalData['ink_sku']	   		= $ink_sku;					
								$finalData['barcode']  			= $ink_barcode;	
								$finalData['po_name']  			= implode(",",$pos);	
								$finalData['po_id']    			= implode(",",$posIds);	
								$finalData['bin_location']  	= $bin_name[$k][0];		
								$finalData['split_order_id']  	= $split_order_id;	
								
								
								$ord_loc = $this->InkOrderLocation->find('first',array('conditions' => array('order_id' => $order_id,'split_order_id'=>$split_order_id,'ink_sku' => $ink_sku,'sku' => $sku,'quantity' => $quantity, 'status' => 'active')));
								
								if(count($ord_loc) == 0){
									$this->InkOrderLocation->saveAll( $finalData );
									file_put_contents(WWW_ROOT .'logs/ink_sku_'.date('dmY').'.log', print_r($finalData,true), FILE_APPEND | LOCK_EX);									
									$this->BinLocation->updateAll( array('BinLocation.stock_by_location' =>  "BinLocation.stock_by_location - $qts"),array('BinLocation.id' => $k));
									$this->Product->updateAll( array('Product.current_stock_level' =>  "Product.current_stock_level - $quantity"),array('Product.product_sku' => $ink_sku));
								}
							}
						} else {
							
								$finalData['available_qty_check_in'] = $available_qty;
								$finalData['available_qty_bin'] = '0';
								$finalData['quantity']      	= $quantity;
								$finalData['order_id']			= $order_id;					
								$finalData['sku']	   			= $sku;	
								$finalData['ink_sku']	   		= $ink_sku;								
								$finalData['barcode']  			= $ink_barcode;	
								$finalData['po_name']  			= implode(",",$pos);	
								$finalData['po_id']    			= implode(",",$posIds);	
								$finalData['bin_location']  	= 'No Location';	
								$finalData['split_order_id']  	= $split_order_id;					
								$ord_loc = $this->InkOrderLocation->find('first',array('conditions' => array('order_id' => $order_id,'split_order_id'=>$split_order_id,'ink_sku' => $ink_sku,'sku' => $sku,'quantity' => $quantity, 'status' => 'active')));
								
								if(count($ord_loc) == 0){
								
									$this->InkOrderLocation->saveAll( $finalData );
									file_put_contents(WWW_ROOT .'logs/ink_sku_'.date('dmY').'.log', print_r($finalData,true), FILE_APPEND | LOCK_EX);
									$this->Product->updateAll( array('Product.current_stock_level' =>  "Product.current_stock_level - $quantity"),array('Product.product_sku' => $ink_sku));
								}
					
							}
						}
					}	
				}					
	 		}
	}
	
	public function order_location($data = array(),$split_order_id = null){
		$this->loadModel( 'OrderLocation' );
		$this->loadModel( 'BinLocation' );		
		$this->loadModel('CheckIn');	
		/*---------Manage Ink SKU Qty-----*/
		if($split_order_id !='' ){
			$this->InkSkuInventory($data,$split_order_id);
		}
		/*------------End----------------*/	
		if(count($data) > 0){
				
			$quantity = $data['quantity'];					
			$sku      = $data['sku'];
			$barcode  = $data['barcode'];
			$order_id = $data['split_order_id']; 
			$channel_sku = @$data['channel_sku']; 
			$data_con = 1;
			if(isset($data['data_con'])){
				$data_con = $data['data_con']; 
			}
					
			$po_name = array(); $posIds = array(); $pos = array(); $binname = array(); 
			$available_qty = 0; $available_qty_bin = 0;	
			
			$local_barcode = $this->Components->load('Common')->getLocalBarcode($barcode);
			
			$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $order_id,'sku' => $sku,'quantity' => $quantity, 'channel_sku' => $channel_sku, 'status' => 'active')));
						
			if(count($ord_loc) > 0){							
				$log_file = WWW_ROOT .'logs-ord/order_md_'.date('dmy').".log";
				file_put_contents($log_file, $sku."\t".$quantity."\t".$order_id."\t".$split_order_id."\n", FILE_APPEND|LOCK_EX);
				return 1;
				exit;
			}
			
			$poDetail = $this->CheckIn->find( 'all', 
				array( 'conditions' => array( 'CheckIn.barcode IN' => array($barcode,$local_barcode), 'CheckIn.selling_qty != CheckIn.qty_checkIn','po_name !=""'),
						'order' => 'CheckIn.date  ASC ' ) );
			
			/*$poDetail = $this->CheckIn->find( 'all', 
				array( 'conditions' => array( 'CheckIn.barcode' => $barcode, 'CheckIn.selling_qty != CheckIn.qty_checkIn','po_name !=""'),
						'order' => 'CheckIn.date  ASC ' ) );*/
			
			if(count($poDetail) > 0){
				$qt_count = 0; 				
				foreach($poDetail as $po){
					$poQty = $po['CheckIn']['qty_checkIn'] - $po['CheckIn']['selling_qty'];
					if($poQty > 0){						
						 for($i=0; $i <= $quantity ;$i++){						 
							 if($qt_count >= $quantity ){
								 break;
							 }else if(($poQty - $i)  > 0){							
								$po_name[$po['CheckIn']['id']][] = $po['CheckIn']['po_name'];	
								$available_qty++;							
								$qt_count++;
							}												
						 }						
					}
				}
			}
			 
			
			$getStock = $this->BinLocation->find( 'all', array( 'conditions' => array( 'BinLocation.barcode' => $barcode),'order' => 'priority ASC' ) );
			$bin_name = array(); $finalData['bin_location'] = "";
			if(count($getStock) > 0){
				
				$qt_count = 0 ;
				foreach($getStock as $val){
					//if($val['BinLocation']['stock_by_location'] >= $quantity ) {
						$binData['id'] = $val['BinLocation']['id'];					
						 for($i=0; $i <= $quantity ;$i++){						 
							 if($qt_count >= $quantity ){
								 break;
							 }else if(($val['BinLocation']['stock_by_location'] - $i)  > 0){							
								$bin_name[$val['BinLocation']['id']][] = $val['BinLocation']['bin_location'];	
								$available_qty_bin++;						
								$qt_count++;
							}												
						 }						 
						//break;
					//}
				}				
				
			}
			
			//$ol = $this->OrderLocation->find( 'first', array( 'conditions' => array( 'order_id' => $order_id,'sku' => $sku)));							
						
			//if(count($ol) == 0){
				if(count($po_name) > 0){
					foreach(array_keys($po_name) as $k){
						$qts   = count($po_name[$k]);	
						$pos[] = $po_name[$k][0];
						$posIds[] = $k;
						$this->CheckIn->updateAll( array('CheckIn.selling_qty' =>  "CheckIn.selling_qty + $qts"),array('CheckIn.id' => $k));
					}
				}
				
				if(count($bin_name) > 0)
				{
					foreach(array_keys($bin_name) as $k){					
						$qts = count($bin_name[$k]);	
						$binname[] = $bin_name[$k][0];	
						
						$finalData['available_qty_check_in'] = $available_qty;
						$finalData['available_qty_bin'] = $qts;
						$finalData['quantity']      	= $quantity;
						$finalData['order_id']			= $order_id;	
						$finalData['split_order_id']	= $split_order_id;					
						$finalData['sku']	   			= $sku;	
						$finalData['channel_sku']	   	= $channel_sku;								
						$finalData['barcode']  			= $barcode;	
						$finalData['po_name']  			= implode(",",$pos);	
						$finalData['po_id']    			= implode(",",$posIds);	
						$finalData['bin_location']  	= $bin_name[$k][0];	
						
						$finalData['data_con']  		= $data_con;					
						$this->OrderLocation->saveAll( $finalData );
						
						$this->BinLocation->updateAll( array('BinLocation.stock_by_location' =>  "BinLocation.stock_by_location - $qts"),array('BinLocation.id' => $k));
					}
				} else {
					
						$finalData['available_qty_check_in'] = $available_qty;
						$finalData['available_qty_bin'] = '0';
						$finalData['quantity']      	= $quantity;
						$finalData['order_id']			= $order_id;
						$finalData['split_order_id']	= $split_order_id;						
						$finalData['sku']	   			= $sku;			
						$finalData['channel_sku']	   	= $channel_sku;						
						$finalData['barcode']  			= $barcode;	
						$finalData['po_name']  			= implode(",",$pos);	
						$finalData['po_id']    			= implode(",",$posIds);	
						$finalData['bin_location']  	= 'No Location';
						$finalData['data_con']  		= $data_con;					
						$this->OrderLocation->saveAll( $finalData );
				}	
			//}
			
		}						
	}
	
	public function order_location_delete_unprepared($data = array()){
	
		$this->loadModel( 'OrderLocation' );
		$this->loadModel( 'BinLocation' );		
		$this->loadModel( 'CheckIn'); 	
		$log_file = WWW_ROOT .'logs/order_location_delete_unprepared_'.date('dmy').".log";			
		file_put_contents($log_file,  print_r($data,true)."\n", FILE_APPEND|LOCK_EX);	
		 						
	}
	
	public function order_location_merge11($data = array()){
		$log_file = WWW_ROOT .'logs/order_location_merge_t_'.date('dmy').".log";	
		file_put_contents($log_file,  print_r($data,true)."\n", FILE_APPEND|LOCK_EX);
	}
	
	public function order_location_update($created_batch = null){ 
 	
		$this->loadModel( 'OrderLocation' );		 
		$this->loadModel( 'MergeUpdate' );
		$this->loadModel( 'Product' ); 
		$merge_order_date = date('Y-m-d H:i:s',strtotime("-5 Hours"));
		
		if($created_batch){
			$merge_order = $this->MergeUpdate->find('all',array('conditions' => array('created_batch' => $created_batch),'fields'=>['sku','barcode','order_id','product_order_id_identify']));
			$log_file = WWW_ROOT .'logs/order_location_update_cb_'.date('dmy').".log";	
		}else{	
			$merge_order = $this->MergeUpdate->find('all',array('conditions' => array('merge_order_date >' => $merge_order_date),'fields'=>['sku','barcode','order_id','product_order_id_identify']));
			$log_file = WWW_ROOT .'logs/order_location_update_md_'.date('dmy').".log";
		}
		
  		if(count($merge_order) > 0){
	
			foreach($merge_order as $_order){
			
				$ordloc = $this->OrderLocation->find('count',array('conditions' => array('order_id' => $_order['MergeUpdate']['order_id'],'split_order_id' => $_order['MergeUpdate']['product_order_id_identify'])));
				
				if($ordloc == 0){
					
					$sk = explode(",",$_order['MergeUpdate']['sku']);  
				
					if(count($sk) > 1){
				
					foreach($sk as $s){
						$mdata = [];
						
						$t = explode("XS-",$s);
						$_sku = "S-".trim($t[1]);
					
						$qty = trim($t[0]);
						$barcode = '';
						$_product = $this->Product->find('first',array('conditions' => ['Product.product_sku'=>$_sku],'fields'=>array('ProductDesc.barcode')));
						
						if(count($_product)){
							$barcode = $_product['ProductDesc']['barcode'];
						}
						$mdata = ['sku'=>$_sku, 'quantity' => $qty, 'split_order_id' => $_order['MergeUpdate']['order_id'],'barcode'=>$barcode,'data_con'=>3];
						
						$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $_order['MergeUpdate']['order_id'],'split_order_id'=>$_order['MergeUpdate']['product_order_id_identify'],'sku' => $_sku,'quantity' => $qty, 'status' => 'active')));
						
						if(count($ord_loc) == 0){							
							file_put_contents($log_file, $_sku."\t".$qty."\t".$_order['MergeUpdate']['order_id']."\t".$_order['MergeUpdate']['product_order_id_identify']."\t".$barcode ."\n", FILE_APPEND|LOCK_EX);
							$this->order_location($mdata, $_order['MergeUpdate']['product_order_id_identify']);
						}
					}
				}else{
					$mdata = [];
					$t = explode("XS-",$_order['MergeUpdate']['sku']);
					$_sku = "S-".trim($t[1]);
					$qty = trim($t[0]);
					 
					$barcode = $_order['MergeUpdate']['barcode'];
					 
					if(strlen($barcode) < 10){
						$_product = $this->Product->find('first',array('conditions' => ['Product.product_sku'=>$_sku],'fields'=>['ProductDesc.barcode']));
						if(count($_product)){
							$barcode = $_product['ProductDesc']['barcode'];
						}
					}
					 	 
					$mdata = ['sku'=>$_sku, 'quantity' => $qty, 'split_order_id' => $_order['MergeUpdate']['order_id'],'barcode'=>$barcode,'data_con'=>3];
					
					$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $_order['MergeUpdate']['order_id'],'split_order_id'=>$_order['MergeUpdate']['product_order_id_identify'],'sku' => $_sku,'quantity' => $qty, 'status' => 'active')));
					
					if(count($ord_loc) == 0){						 
						file_put_contents($log_file, $_sku."\t".$qty."\t".$_order['MergeUpdate']['order_id']."\t".$_order['MergeUpdate']['product_order_id_identify']."\t".$barcode ."\n", FILE_APPEND|LOCK_EX);  
						$this->order_location($mdata, $_order['MergeUpdate']['product_order_id_identify']);
					}
				
					}
				}
			}
		} 
		echo 'order_location_update'. date('Y-m-d H:i:s');
		
		App::import('Controller', 'Reports');
		$rObj = new ReportsController(); 
		$rObj->OrderOver100();
		exit;	
	}
	
	public function order_location_up(){ 
	
		$this->loadModel( 'OrderLocation' );
		$this->loadModel( 'BinLocation' );
		$this->loadModel( 'CheckIn' );
		$this->loadModel( 'MergeUpdate' );
		$this->loadModel( 'Product' ); 
		
		$_ord_loc = $this->OrderLocation->find('all',array('conditions' => array('split_order_id IS NULL', 'status' => 'active')));
  		 
 		foreach($_ord_loc as $ol)
		{
			
			$order_id = $ol['OrderLocation']['order_id'];
			//echo "<br>";
			
			$merge_order = $this->MergeUpdate->find('all',array('conditions' => array('order_id' => $order_id),'fields'=>['sku','barcode','order_id','product_order_id_identify']));
			
			//pr($merge_order); 
			
			if(count($merge_order)>0){
				
				$ordloc = $this->OrderLocation->find('all',array('conditions' => array('order_id' => $order_id,'split_order_id IS NULL', 'status' => 'active')));
		
				if(count($ordloc) > 0){
				 
					foreach($ordloc as $val)
					{  					
						$_ol_orderid 	= $order_id;
						$_ol_barcode 	= $val['OrderLocation']['barcode'];
						$_ol_sku		= $val['OrderLocation']['sku'];
						$_ol_location 	= $val['OrderLocation']['bin_location'];					 
						$_ol_qty_bin 	= $val['OrderLocation']['available_qty_bin'];
						$_ol_check_in 	= $val['OrderLocation']['available_qty_check_in'];	 
						$_ol_po_id	 	= explode(",",$val['OrderLocation']['po_id'])[0];	
								
						if($location != 'No Location')
						{
							$this->BinLocation->updateAll(	array('BinLocation.stock_by_location' => "BinLocation.stock_by_location + $_ol_qty_bin"),
														array('BinLocation.bin_location' => $_ol_location, 'BinLocation.barcode' => $_ol_barcode));
						}					
						 
						$this->CheckIn->updateAll( array('CheckIn.selling_qty' => "CheckIn.selling_qty - $_ol_check_in"),array('CheckIn.id' => $_ol_po_id));							  
						$this->OrderLocation->updateAll( array( 'OrderLocation.status' =>"'deleted'") , array( 'OrderLocation.order_id' => $order_id,'split_order_id IS NULL' ) ); 	
					}
				
				
					foreach($merge_order as $_order){
					
						$sk = explode(",",$_order['MergeUpdate']['sku']);  
				
						if(count($sk) > 1){
						
							foreach($sk as $s){
								$mdata = [];
								
								$t = explode("XS-",$s);
								$_sku = "S-".trim($t[1]);
							
								$qty = trim($t[0]);
								$barcode = '';
								$_product = $this->Product->find('first',array('conditions' => ['Product.product_sku'=>$_sku],'fields'=>array('ProductDesc.barcode')));
								
								if(count($_product)){
									$barcode = $_product['ProductDesc']['barcode'];
								}
								$mdata = ['sku'=>$_sku, 'quantity' => $qty, 'split_order_id' => $_order['MergeUpdate']['order_id'],'barcode'=>$barcode,'data_con'=>3];
								$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $_order['MergeUpdate']['order_id'],'split_order_id'=>$_order['MergeUpdate']['product_order_id_identify'],'sku' => $_sku,'quantity' => $qty, 'status' => 'active')));
								
								if(count($ord_loc) == 0){
									$log_file = WWW_ROOT .'logs/order_location_merge_'.date('dmy').".log";	
									file_put_contents($log_file, print_r($mdata,true)."\n", FILE_APPEND|LOCK_EX);
									$this->order_location($mdata, $_order['MergeUpdate']['product_order_id_identify']);
								}
							}
						}else{
							$mdata = [];
							$t = explode("XS-",$_order['MergeUpdate']['sku']);
							$_sku = "S-".trim($t[1]);
							$qty = trim($t[0]);
							 
							$barcode = $_order['MergeUpdate']['barcode'];
							if($barcode == ''){
								$_product = $this->Product->find('first',array('conditions' => ['Product.product_sku'=>$_sku],'fields'=>['ProductDesc.barcode']));
								if(count($_product)){
									$barcode = $_product['ProductDesc']['barcode'];
								}
							}
								 
							$mdata = ['sku'=>$_sku, 'quantity' => $qty, 'split_order_id' => $_order['MergeUpdate']['order_id'],'barcode'=>$barcode,'data_con'=>3];
							
							$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $_order['MergeUpdate']['order_id'],'split_order_id'=>$_order['MergeUpdate']['product_order_id_identify'],'sku' => $_sku,'quantity' => $qty, 'status' => 'active')));
							
							if(count($ord_loc) == 0){
								$log_file = WWW_ROOT .'logs/order_location_up_'.date('dmy').".log";	
								file_put_contents($log_file, print_r($mdata,true)."\n", FILE_APPEND|LOCK_EX);	
								$this->order_location($mdata, $_order['MergeUpdate']['product_order_id_identify']);
							}
						
						}
					}
				}			
			}	
		}
	}
	
	public function order_location_merge($order_id){
		
		$this->loadModel( 'OrderLocation' );
		$this->loadModel( 'BinLocation' );
		$this->loadModel( 'CheckIn' );
		$this->loadModel( 'MergeUpdate' );
		$this->loadModel( 'Product' ); 
		 
  		
		$merge_order = $this->MergeUpdate->find('all',array('conditions' => array('order_id' => $order_id),'fields'=>['sku','barcode','order_id','product_order_id_identify']));
		
		//pr($merge_order); 
		
		if(count($merge_order)>0){
			
			$ordloc = $this->OrderLocation->find('all',array('conditions' => array('order_id' => $order_id,'split_order_id IS NULL', 'status' => 'active')));
	
			if(count($ordloc) > 0){
			 
				foreach($ordloc as $val)
				{  					
					$_ol_orderid 	= $order_id;
					$_ol_barcode 	= $val['OrderLocation']['barcode'];
					$_ol_sku		= $val['OrderLocation']['sku'];
					$_ol_location 	= $val['OrderLocation']['bin_location'];					 
					$_ol_qty_bin 	= $val['OrderLocation']['available_qty_bin'];
					$_ol_check_in 	= $val['OrderLocation']['available_qty_check_in'];	 
					$_ol_po_id	 	= explode(",",$val['OrderLocation']['po_id'])[0];	
					$locationstr = @$location;		
					if($locationstr != 'No Location')
					{
					 	$this->BinLocation->updateAll(	array('BinLocation.stock_by_location' => "BinLocation.stock_by_location + $_ol_qty_bin"),
													array('BinLocation.bin_location' => $_ol_location, 'BinLocation.barcode' => $_ol_barcode));
					}					
					 
					$this->CheckIn->updateAll( array('CheckIn.selling_qty' => "CheckIn.selling_qty - $_ol_check_in"),array('CheckIn.id' => $_ol_po_id));							  
					$this->OrderLocation->updateAll( array( 'OrderLocation.status' =>"'deleted'") , array( 'OrderLocation.order_id' => $order_id,'split_order_id IS NULL' ) ); 	
				}
			
			
 				foreach($merge_order as $_order){
				
					$sk = explode(",",$_order['MergeUpdate']['sku']);  
			
					if(count($sk) > 1){
					
						foreach($sk as $s){
							$mdata = [];
							
 							$t = explode("XS-",$s);
							$_sku = "S-".trim($t[1]);
						
							$qty = trim($t[0]);
							
							$m[$_order['MergeUpdate']['product_order_id_identify']][$_sku][] = $qty;
							
							/*$barcode = '';
							$_product = $this->Product->find('first',array('conditions' => ['Product.product_sku'=>$_sku],'fields'=>array('Product.product_sku','ProductDesc.barcode')));
							
							if(count($_product)){
								$barcode = $_product['ProductDesc']['barcode'];
							}
							$mdata = ['sku'=>$_sku, 'quantity' => $qty, 'split_order_id' => $_order['MergeUpdate']['order_id'],'barcode'=>$barcode,'data_con'=>2];
							$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $_order['MergeUpdate']['order_id'],'split_order_id'=>$_order['MergeUpdate']['product_order_id_identify'],'sku' => $_sku,'quantity' => $qty, 'status' => 'active')));
							
							if(count($ord_loc) == 0){
							
								$log_file = WWW_ROOT .'logs/order_location_merge_a_'.date('dmy').".log";	
								file_put_contents($log_file, print_r($mdata,true)."\n", FILE_APPEND|LOCK_EX);	
								
								$_log_file = WWW_ROOT .'logs/order_location_merge_ord_a_'.date('dmy').".log";	
								file_put_contents($_log_file, $_order['MergeUpdate']['order_id']."\n", FILE_APPEND|LOCK_EX);
								
								$this->order_location($mdata, $_order['MergeUpdate']['product_order_id_identify']);
							}*/
						}
						foreach($m as $split_id =>$ssku){
							 
							foreach(array_keys($ssku) as $skku){
								$mdata = [];
								$qqty = array_sum($ssku[$skku]); 
								$barcode = '';
								$_product = $this->Product->find('first',array('conditions' => ['Product.product_sku'=>$skku],'fields'=>array('Product.product_sku','ProductDesc.barcode')));
								
								if(count($_product)){
									$barcode = $_product['ProductDesc']['barcode'];
								}
														
								$mdata = ['sku'=>$skku, 'quantity' => $qqty, 'split_order_id' => $_order['MergeUpdate']['order_id'],'barcode'=>$barcode,'data_con'=>4];
								$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $_order['MergeUpdate']['order_id'],'split_order_id'=>$split_id,'sku' => $skku,'quantity' => $qqty, 'status' => 'active')));
								
								if(count($ord_loc) == 0){
								
								 	$log_file = WWW_ROOT .'logs/order_location_merge_a_'.date('dmy').".log";	
								 	file_put_contents($log_file, print_r($mdata,true)."\n", FILE_APPEND|LOCK_EX);	
									
									$this->order_location($mdata, $split_id);
								}								
							}					
						 
						}
					}else{
						$mdata = [];
						$t = explode("XS-",$_order['MergeUpdate']['sku']);
						$_sku = "S-".trim($t[1]);
						$qty = trim($t[0]);
						$barcode = $_order['MergeUpdate']['barcode'];
						if($barcode == ''){
							$_product = $this->Product->find('first',array('conditions' => ['Product.product_sku'=>$_sku],'fields'=>['ProductDesc.barcode']));
 							if(count($_product)){
								$barcode = $_product['ProductDesc']['barcode'];
							}
						}
						 
						$mdata = ['sku'=>$_sku, 'quantity' => $qty, 'split_order_id' => $_order['MergeUpdate']['order_id'],'barcode'=>$barcode,'data_con'=>4];
						
						$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $_order['MergeUpdate']['order_id'],'split_order_id'=>$_order['MergeUpdate']['product_order_id_identify'],'sku' => $_sku,'quantity' => $qty, 'status' => 'active')));
						
						if(count($ord_loc) == 0){
						
							$log_file = WWW_ROOT .'logs/order_location_merge_b_'.date('dmy').".log";	
							file_put_contents($log_file, print_r($mdata,true)."\n", FILE_APPEND|LOCK_EX);
							
							$_log_file = WWW_ROOT .'logs/order_location_merge_ord_b_'.date('dmy').".log";	
							file_put_contents($_log_file, $_order['MergeUpdate']['order_id']."\n", FILE_APPEND|LOCK_EX);		
								
							$this->order_location($mdata, $_order['MergeUpdate']['product_order_id_identify']);
						}
					
					}
				}
			}
 		 	
		}else{
			$log_file = WWW_ROOT .'logs/order_location_merge_1_'.date('dmy').".log";	
			file_put_contents($log_file,  $order_id."\n", FILE_APPEND|LOCK_EX); 
		} 						
	}
	
	public function order_location_merge_27FEB($data = array()){
		
		$this->loadModel( 'OrderLocation' );
		$this->loadModel( 'Product' );
		 
		$products = array();		 
		$_product = $this->Product->find('all',array('fields'=>array('product_sku','ProductDesc.barcode')));
		
		foreach($_product as $pro ){
			$products[$pro['Product']['product_sku']] = $pro['ProductDesc']['barcode'];
		}
  		
		foreach($data as $d){
		
			$ordloc = $this->OrderLocation->find('all',array('conditions' => array('order_id' => $d['order_id'],'split_order_id IS NULL', 'status' => 'active')));
			if(count($ordloc) > 0){
			 
				foreach($ordloc as $val)
				{  					
					$_ol_orderid 	= $d['order_id'];
					$_ol_barcode 	= $val['OrderLocation']['barcode'];
					$_ol_sku		= $val['OrderLocation']['sku'];
					$_ol_location 	= $val['OrderLocation']['bin_location'];					 
					$_ol_qty_bin 	= $val['OrderLocation']['available_qty_bin'];
					$_ol_check_in 	= $val['OrderLocation']['available_qty_check_in'];	 
					$_ol_po_id	 	= explode(",",$val['OrderLocation']['po_id'])[0];	
							
 					if($_ol_location != 'No Location')
					{
						$this->BinLocation->updateAll(	array('BinLocation.stock_by_location' => "BinLocation.stock_by_location + $_ol_qty_bin"),
													array('BinLocation.bin_location' => $_ol_location, 'BinLocation.barcode' => $_ol_barcode));
					}					
					 
					$this->CheckIn->updateAll( array('CheckIn.selling_qty' => "CheckIn.selling_qty - $_ol_check_in"),array('CheckIn.id' => $_ol_po_id));							  
					$this->OrderLocation->updateAll( array( 'OrderLocation.status' =>"'deleted'") , array( 'OrderLocation.order_id' => $d['order_id'],'split_order_id IS NULL' ) ); 	
				}
					
				 
				$sk = explode(",",$d['sku']);  
				
				if(count($sk)>1){
				
					foreach($sk as $s){
						$mdata = [];
						$t = explode("XS-",$s);
						$_sku = "S-".trim($t[1]);
						$qty = trim($t[0]);
						$barcode = '';
						if(isset($products[$_sku])){
							$barcode = $products[$_sku];
						}
						$mdata = ['sku'=>$_sku, 'quantity' => $qty, 'split_order_id' => $d['order_id'],'barcode'=>$barcode,'data_con'=>2];
						
						$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $d['order_id'],'split_order_id'=>$d['product_order_id_identify'],'sku' => $_sku,'quantity' => $qty, 'status' => 'active')));
						
						if(count($ord_loc) == 0){
							$this->order_location($mdata, $d['product_order_id_identify']);
						}
					}
				}else{
					$mdata = [];
					$t = explode("XS-",$d['sku']);
					$_sku = "S-".trim($t[1]);
					$qty = trim($t[0]);
					$barcode = '';
					if(isset($products[$_sku])){
						$barcode = $products[$_sku];
					}
					$mdata = ['sku'=>$_sku, 'quantity' => $qty, 'split_order_id' => $d['order_id'],'barcode'=>$barcode,'data_con'=>2];
					
					$ord_loc = $this->OrderLocation->find('first',array('conditions' => array('order_id' => $d['order_id'],'split_order_id'=>$d['product_order_id_identify'],'sku' => $_sku,'quantity' => $qty, 'status' => 'active')));
						
					if(count($ord_loc) == 0){
						$this->order_location($mdata, $d['product_order_id_identify']);
					}
				}
		
 				$log_file = WWW_ROOT .'logs/order_location_merge_'.date('dmy').".log";	
				file_put_contents($log_file,  print_r($data,true)."\n", FILE_APPEND|LOCK_EX);	
			}else{
				$log_file = WWW_ROOT .'logs/order_location_merge_1_'.date('dmy').".log";	
				file_put_contents($log_file,  print_r($data,true)."\n", FILE_APPEND|LOCK_EX);
			}
		
		}
		 						
	}
	
	public function order_location_unprepared(){
	
		$this->loadModel( 'OrderLocation' );
		$this->loadModel( 'MergeUpdate' );	
		$this->loadModel( 'UnprepareOrder' );	
		 
		$start_date   = date('Y-m-d H:i:s',strtotime("-10 days"));
		$end_date = date('Y-m-d H:i:s',strtotime("-5 days"));
		
		$ordloc = $this->OrderLocation->find('all',array('conditions' => array('split_order_id IS NULL', 'status' => 'active','timestamp BETWEEN ? AND ? ' =>[$start_date,$end_date])));
			
		if(count($ordloc) > 0){
			foreach($ordloc as $order){			
				$merge_order = $this->MergeUpdate->find('count',array('conditions' => array('order_id'=>$order['OrderLocation']['order_id'])));
				$unpre_order = $this->UnprepareOrder->find('count',array('conditions' => array('num_order_id'=>$order['OrderLocation']['order_id'])));
				if($unpre_order == 0){
 					 if($merge_order == 0){
						echo $order['OrderLocation']['order_id'];
						echo " == ". $order['OrderLocation']['timestamp'];
						echo "<br>";
					}
				}
			}
		}
		exit;	 						
	}
	
	public function updatePoStock(){
	
		$this->loadModel( 'OrderLocation' );
		$this->loadModel( 'BinLocation' );
		$this->loadModel( 'Product' );
		$this->loadModel( 'ProductDesc' );
		$this->loadModel( 'CheckIn' );
		
		$this->CheckIn->query('UPDATE `check_ins` SET `selling_qty` = `qty_checkIn`');
		
		//$productDetail	= $this->Product->find('all',array('conditions' => array('ProductDesc.barcode=884962894392'),'order'=>'Product.id DESC'));	
		
		$productDetail	= $this->Product->find('all',array('order'=>'Product.id DESC'));	
		//pr($productDetail);		
		
		foreach($productDetail as $p){
			$cQty = $p['Product']['current_stock_level'];		
			$CheckIn	= $this->CheckIn->find('all',array('conditions' => array('barcode' => $p['ProductDesc']['barcode'], 'custam_page_name' => 'Check In', 'qty_checkIn > 0'),'order'=>'date DESC'));
			
			if(count($CheckIn) > 0){
				$qt_count = 0 ;						
				foreach($CheckIn as $chq){											
								 		
					 for($i=0; $i <= $cQty ;$i++){					
					 	 $sty = $i+1;
						 if(($qt_count >= $cQty) || ($chq['CheckIn']['qty_checkIn'] - $sty) < 0 ) {
							  break;
						 }elseif(($chq['CheckIn']['qty_checkIn'] - $sty)  >= 0) {
							
							$this->CheckIn->updateAll( array('CheckIn.selling_qty' =>  "CheckIn.qty_checkIn - $sty"),
							array('CheckIn.id' => $chq['CheckIn']['id']));	
					
							$qt_count++;						
						}													
				 }					 
				// $count++;
				//if($count > 100) exit;
				}				
			}			
		}	
		echo "Done";
	}
	
	public function updateBinPriority(){
		
		$this->loadModel( 'BinLocation' );
		$bins = $this->BinLocation->find( 'all', array('order' => 'id ASC' ) );
		foreach($bins as $val){
			$i = 1;
			$bin = $this->BinLocation->find( 'all', array( 'conditions' => array( 'BinLocation.barcode' => $val['BinLocation']['barcode'])) );
			foreach($bin as $v){
				$this->BinLocation->updateAll( array('BinLocation.priority' =>  $i),array('BinLocation.id' => $v['BinLocation']['id']));
				$i++;
			}
		}	
	}
	
	public function setInfoEshipperCorreos()
	{
		$this->layout = '';
		$this->autoRender = false;          
	
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		
		$serviceProvider = 'Correos';
		$params = array('conditions' => array('ServiceCounter.service_provider' => $serviceProvider,'ServiceCounter.counter >' => 0,'ServiceCounter.original_counter !=' => ''));
		$eShipperDatas = $this->ServiceCounter->find( 'all', $params );
	
		foreach( $eShipperDatas  as $eShipperData)
		{	
			$scanCount			=	$eShipperData['ServiceCounter']['counter'];
			$mergeupdateIds 	= 	explode(',', $eShipperData['ServiceCounter']['order_ids']);
		
			$productWeight 		= 0; 
			$envelopWeight 		= 0;
			$totalOrderWeight 	= 0;
			foreach( $mergeupdateIds as $mergeupdateId )
			{
				$params = array('conditions' => array('MergeUpdate.id' => $mergeupdateId,'MergeUpdate.delevery_country' => 'Spain'),
								'fields' => array('packet_weight','envelope_weight','MergeUpdate.delevery_country','MergeUpdate.provider_ref_code'));
				$orderDetail	= 	$this->MergeUpdate->find( 'first' , $params );
				$serviceCode 	= 	$orderDetail['MergeUpdate']['provider_ref_code'];
				$productWeight	=	$productWeight + $orderDetail['MergeUpdate']['packet_weight'];
				$envelopWeight	=	$envelopWeight + $orderDetail['MergeUpdate']['envelope_weight'];
			}
			$totalOrderWeight	=	$productWeight + $envelopWeight;
			$correosData[]['postel_service_data'] 	= array('totla_weight' => $totalOrderWeight, 'count' => $scanCount,'sevice_code' => $serviceCode);
		}
		echo (json_encode(array('status' => '1', 'data' => $correosData)));
		exit;
	}
	
	
	public function deliveryNoteCorreos()
	{
				$this->layout = '';
				$this->autoRender = false;   
				$this->loadModel( 'ServiceCounter' );
				$this->loadModel( 'MergeUpdate' );
				App::import('Vendor', 'PHPExcel/IOFactory');
				App::import('Vendor', 'PHPExcel');                          
				$imagePath = Router::url('/', true).'img';
				///img/LogoCorreos.jpg
				$logoImg 	= $imagePath.'/LogoCorreos.jpg'; 
				$addImg 	= $imagePath.'/add.jpg';
				$signImg 	= $imagePath.'/sign.jpg';
				$eslImg 	= $imagePath.'/esl.jpg';
				
				$getBase 		= 	Router::url('/', true);
				$uploadUrl 		= 	WWW_ROOT .'img/delivery_note.xlsx';
				$uploadRemote 	= 	$getBase.'img/delivery_note.xlsx';
				$objPHPExcel = new PHPExcel();
// Set properties
				
				       
			
				
				
				$serviceProvider = 'Correos';
				$params = array('conditions' => array('ServiceCounter.service_provider' => $serviceProvider,'ServiceCounter.counter >' => 0,'ServiceCounter.original_counter !=' => ''));
				$eShipperDatas = $this->ServiceCounter->find( 'all', $params );
			
				foreach( $eShipperDatas  as $eShipperData)
				{	
					$scanCount			=	$eShipperData['ServiceCounter']['counter'];
					$mergeupdateIds 	= 	explode(',', $eShipperData['ServiceCounter']['order_ids']);
				
					$productWeight 		= 0; 
					$envelopWeight 		= 0;
					$totalOrderWeight 	= 0;
					foreach( $mergeupdateIds as $mergeupdateId )
					{
						$params = array('conditions' => array('MergeUpdate.id' => $mergeupdateId,'MergeUpdate.delevery_country' => 'Spain'),
										'fields' => array('packet_weight','envelope_weight','MergeUpdate.delevery_country','MergeUpdate.provider_ref_code'));
						$orderDetail	= 	$this->MergeUpdate->find( 'first' , $params );
						$serviceCode 	= 	$orderDetail['MergeUpdate']['provider_ref_code'];
						$productWeight	=	$productWeight + $orderDetail['MergeUpdate']['packet_weight'];
						$envelopWeight	=	$envelopWeight + $orderDetail['MergeUpdate']['envelope_weight'];
					}
					$totalOrderWeight	=	$productWeight + $envelopWeight;
					$correosData[]['postel_service_data'] 	= array('totla_weight' => $totalOrderWeight, 'count' => $scanCount,'sevice_code' => $serviceCode);
					$weight[]		=	$totalOrderWeight;
					$total_qty[]	=	$scanCount;
				}
				
				$totalWeight	=	 array_sum( $weight );
				$totalQuantity	=	 array_sum( $total_qty );
				$totalWeight	=	 $totalWeight * 1000;
				
				$regerenceNumber	=	 'CORREOS'.date('dmy');

				
				
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(4);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(4);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(4);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(4);
				$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('L2', 'REFERENCE');
				$objPHPExcel->getActiveSheet()->mergeCells('L2:M2');
				$objPHPExcel->getActiveSheet()->getStyle('L2:M2')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('L2:M2')->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('O2', $regerenceNumber);
				$objPHPExcel->getActiveSheet()->mergeCells('O2:Q2');
				$objPHPExcel->getActiveSheet()->getStyle('O2:O2')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ), 'font'  => array('bold'  => true,'color' => array('rgb' => 'FF0000'),'size'  => 14) );
				$objPHPExcel->getActiveSheet()->getStyle('O2:Q2')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('O2:Q2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(11);
				$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(11);
				$objPHPExcel->getActiveSheet()->SetCellValue('L4', 'DISPATCH DATE');
				$objPHPExcel->getActiveSheet()->mergeCells('L4:M4');
				$objPHPExcel->getActiveSheet()->getStyle('L4:M4')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('L4:M4')->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('O4', date('d-m-Y'));
				$objPHPExcel->getActiveSheet()->mergeCells('O4:Q4');
				$objPHPExcel->getActiveSheet()->getStyle('O4:O4')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ), 'font'  => array('bold'  => true,'color' => array('rgb' => 'FF0000'),'size'  => 14) );
				$objPHPExcel->getActiveSheet()->getStyle('O4:Q4')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('O4:Q4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				
				$BStyle = array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)));
				$objPHPExcel->getActiveSheet()->getStyle('E10:Q17')->applyFromArray($BStyle);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E7', 'DELIVERY NOTE');
				$objPHPExcel->getActiveSheet()->mergeCells('E7:Q8');
				$objPHPExcel->getActiveSheet()->getStyle('E7:Q8')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('E7:Q8')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('E7:Q8')->getAlignment()->applyFromArray(array(
							 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							 'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
							 'rotation'   => 0,
							 'wrap'       => true
							));
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('F11', 'CUSTOMER NAME');
				$objPHPExcel->getActiveSheet()->mergeCells('F11:G11');
				$objPHPExcel->getActiveSheet()->getStyle('F11:G11')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('F11:G11')->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('F12', 'CONTRACT NUMBER');
				$objPHPExcel->getActiveSheet()->mergeCells('F12:G12');
				$objPHPExcel->getActiveSheet()->getStyle('F12:G12')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('F12:G12')->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('F13', 'CUSTOMER NUMBER');
				$objPHPExcel->getActiveSheet()->mergeCells('F13:G13');
				$objPHPExcel->getActiveSheet()->getStyle('F13:G13')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('F13:G13')->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('F14', 'VAT NUMBER');
				$objPHPExcel->getActiveSheet()->mergeCells('F14:G14');
				$objPHPExcel->getActiveSheet()->getStyle('F14:G14')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('F14:G14')->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('F16', 'PRODUCT');
				$objPHPExcel->getActiveSheet()->mergeCells('F16:G16');
				$objPHPExcel->getActiveSheet()->getStyle('F16:G16')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('F16:G16')->applyFromArray($styleArray);
				
				$centerArray = array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,'rotation'=> 0,'wrap'=> true);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('I11', 'ESL LIMITED');
				$objPHPExcel->getActiveSheet()->mergeCells('I11:P11');
				$objPHPExcel->getActiveSheet()->getStyle('I11:P11')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('I11:P11')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('I11:P11')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('I12', '54024267');
				$objPHPExcel->getActiveSheet()->mergeCells('I12:P12');
				$objPHPExcel->getActiveSheet()->getStyle('I12:P12')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('I12:P12')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('I12:P12')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('I13', '9980958776');
				$objPHPExcel->getActiveSheet()->mergeCells('I13:P13');
				$objPHPExcel->getActiveSheet()->getStyle('I13:P13')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('I13:P13')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('I13:P13')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('I14', '0043148');
				$objPHPExcel->getActiveSheet()->mergeCells('I14:P14');
				$objPHPExcel->getActiveSheet()->getStyle('I14:P14')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('I14:P14')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('I14:P14')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('I15', '');
				$objPHPExcel->getActiveSheet()->mergeCells('I15:P15');
				$objPHPExcel->getActiveSheet()->getStyle('I15:P15')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('I15:P15')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('I15:P15')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
				$objPHPExcel->getActiveSheet()->SetCellValue('I16', utf8_encode('DISTRIBUCIÓN INTERNACIONAL (ORDINARY MAIL)'));
				$objPHPExcel->getActiveSheet()->mergeCells('I16:P16');
				$objPHPExcel->getActiveSheet()->getStyle('I16:P16')->getFont()->setSize(14);	
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('I16:P16')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('I16:P16')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->mergeCells('E20:Q21');
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('E20:Q21')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('E22:Q41')->applyFromArray($BStyle);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('F23', 'PRODUCT');
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('F23')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('F23')->applyFromArray($BStyle);
				
				$objPHPExcel->getActiveSheet()->mergeCells('H23:K23');
				$objPHPExcel->getActiveSheet()->SetCellValue('H23', utf8_encode('DISTRIBUCIÓN INTERNACIONAL (OM)'));
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('H23:K23')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('H23:K23')->applyFromArray($BStyle);
				
				$objPHPExcel->getActiveSheet()->mergeCells('E25:I25');
				$objPHPExcel->getActiveSheet()->SetCellValue('E25', 'COUNTRY');
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('E25:I25')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('E25:I25')->applyFromArray($BStyle);
				$objPHPExcel->getActiveSheet()->getStyle('E25:I25')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('J25', 'CODE');
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('J25')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('J25')->applyFromArray($BStyle);
				$objPHPExcel->getActiveSheet()->getStyle('J25')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->mergeCells('K25:M25');
				$objPHPExcel->getActiveSheet()->SetCellValue('K25', 'TOTAL WEIGHT IN GRAMS');
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('K25:M25')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('K25:M25')->applyFromArray($BStyle);
				$objPHPExcel->getActiveSheet()->getStyle('K25:M25')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->mergeCells('N25:Q25');
				$objPHPExcel->getActiveSheet()->SetCellValue('N25', 'NUMBER OF ITEMS');
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('N25:Q25')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('N25:Q25')->applyFromArray($BStyle);
				$objPHPExcel->getActiveSheet()->getStyle('N25:Q25')->getAlignment()->applyFromArray($centerArray );
				
				/*Dynamic code*/
				$objPHPExcel->getActiveSheet()->mergeCells('E26:I26');
				$objPHPExcel->getActiveSheet()->SetCellValue('E26', 'SPAIN');
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('E26:I26')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('E26:I26')->applyFromArray($BStyle);
				$objPHPExcel->getActiveSheet()->getStyle('E26:I26')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->SetCellValue('J26', 'ES');
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('J26')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('J26')->applyFromArray($BStyle);
				$objPHPExcel->getActiveSheet()->getStyle('J26')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->mergeCells('K26:M26');
				$objPHPExcel->getActiveSheet()->SetCellValue('K26', $totalWeight);
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('K26:M26')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('K26:M26')->applyFromArray($BStyle);
				$objPHPExcel->getActiveSheet()->getStyle('K26:M26')->getAlignment()->applyFromArray($centerArray );
				
				$objPHPExcel->getActiveSheet()->mergeCells('N26:Q26');
				$objPHPExcel->getActiveSheet()->SetCellValue('N26', $totalQuantity);
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('N26:Q26')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('N26:Q26')->applyFromArray($BStyle);
				$objPHPExcel->getActiveSheet()->getStyle('N26:Q26')->getAlignment()->applyFromArray($centerArray );
				/***************************/
				
				/**************************/
				for($j=27; $j < 42 ; $j++)
				{
					$objPHPExcel->getActiveSheet()->mergeCells('E'.$j.':I'.$j.'');
					$objPHPExcel->getActiveSheet()->SetCellValue('N25', 'NUMBER OF ITEMS');
					$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
					$objPHPExcel->getActiveSheet()->getStyle('E'.$j.':I'.$j.'')->applyFromArray($styleArray);
					$objPHPExcel->getActiveSheet()->getStyle('E'.$j.':I'.$j.'')->applyFromArray($BStyle);
					$objPHPExcel->getActiveSheet()->getStyle('E'.$j.':I'.$j.'')->getAlignment()->applyFromArray($centerArray );
					
					$objPHPExcel->getActiveSheet()->SetCellValue('J'.$j.'', '');
					$objPHPExcel->getActiveSheet()->getStyle('J'.$j.'')->applyFromArray($styleArray);
					$objPHPExcel->getActiveSheet()->getStyle('J'.$j.'')->applyFromArray($BStyle);
					$objPHPExcel->getActiveSheet()->getStyle('J'.$j.'')->getAlignment()->applyFromArray($centerArray );
					
					$objPHPExcel->getActiveSheet()->mergeCells('K'.$j.':M'.$j.'');
					$objPHPExcel->getActiveSheet()->SetCellValue('K'.$j.'', '');
					$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
					$objPHPExcel->getActiveSheet()->getStyle('K'.$j.':M'.$j.'')->applyFromArray($styleArray);
					$objPHPExcel->getActiveSheet()->getStyle('K'.$j.':M'.$j.'')->applyFromArray($BStyle);
					$objPHPExcel->getActiveSheet()->getStyle('K'.$j.':M'.$j.'')->getAlignment()->applyFromArray($centerArray );
					
					$objPHPExcel->getActiveSheet()->mergeCells('N'.$j.':Q'.$j.'');
					$objPHPExcel->getActiveSheet()->SetCellValue('N'.$j.'', '');
					$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
					$objPHPExcel->getActiveSheet()->getStyle('N'.$j.':Q'.$j.'')->applyFromArray($styleArray);
					$objPHPExcel->getActiveSheet()->getStyle('N'.$j.':Q'.$j.'')->applyFromArray($BStyle);
					$objPHPExcel->getActiveSheet()->getStyle('N'.$j.':Q'.$j.'')->getAlignment()->applyFromArray($centerArray );
				}
				
				/*************************/
				$objPHPExcel->getActiveSheet()->mergeCells('E43:Q44');
				$objPHPExcel->getActiveSheet()->SetCellValue('E43', utf8_encode('This document will not be valid without the mechanical confirmation or authorised stampo and signature from Correos y Telégrafos. Correos y Telégrafos reserves the right to modify this Delivery Note in case errors are detected at the time revenue protection procedures are carried out or by any other reason that may be considered relevant.'));
				$objPHPExcel->getActiveSheet()->getStyle('E43:Q44')->getFont()->setSize(9);
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('E43:Q44')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->getStyle('E43:Q44')->getAlignment()->applyFromArray($centerArray );
				
				
				$objPHPExcel->getActiveSheet()->mergeCells('E46:M51');
				$objPHPExcel->getActiveSheet()->SetCellValue('E46', utf8_encode("On behalf of  Correos y Telégrafos SAE:\nADMISSION ENTRY:                                                DATE:"));
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('E46:M51')->applyFromArray($styleArray);
				$alignArray = array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,'rotation' => 0,'wrap' => true);
				$objPHPExcel->getActiveSheet()->getStyle('E46:M51')->getAlignment()->applyFromArray($alignArray );
				
				$objPHPExcel->getActiveSheet()->mergeCells('N46:Q51');
				$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM ) ) );
				$objPHPExcel->getActiveSheet()->getStyle('N46:Q51')->applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->setTitle('Delivery Note');
				
				/*
				$objDrawing = new PHPExcel_Worksheet_Drawing();
				$objDrawing->setName('Logo');
				$objDrawing->setDescription('Logo');
				$objDrawing->setPath($logoImg);
				//PHPExcel_Worksheet_Drawing::setPath($logoImg);
				$objDrawing->setOffsetX(8);    // setOffsetX works properly
				$objDrawing->setOffsetY(100);  //setOffsetY has no effect
				$objDrawing->setCoordinates('B1');
				$objDrawing->setHeight(75); // logo height
				$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
				*/
				
				$gdImage = imagecreatefromjpeg($logoImg);
				$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
				$objDrawing->setName('Sample image');
				$objDrawing->setDescription('Sample image');
				$objDrawing->setImageResource($gdImage);
				
				$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
				$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
				$objDrawing->setHeight(80);
				$objDrawing->setCoordinates('A1');
				
				$gdImage1 = imagecreatefromjpeg($logoImg);
				$objDrawing1 = new PHPExcel_Worksheet_MemoryDrawing();
				$objDrawing1->setName('Sample image1');
				$objDrawing1->setDescription('Sample image1');
				$objDrawing1->setImageResource($gdImage1);
				
				$objDrawing1->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
				$objDrawing1->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
				$objDrawing1->setHeight(80);
				$objDrawing1->setCoordinates('L55');
				
				// Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
				$objDrawingAdd = new PHPExcel_Worksheet_MemoryDrawing();
				$objDrawingAdd->setName('Sample image1');
				$objDrawingAdd->setDescription('Sample image1');
				$objDrawingAdd->setImageResource($gdImage1);
				
				$objDrawingAdd->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
				$objDrawingAdd->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
				$objDrawingAdd->setHeight(40);
				$objDrawingAdd->setCoordinates('R1');
				
				//$objDrawingAdd->setCoordinates('H10');
				
				
				$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$objWriter->save($uploadUrl);
				echo $file = Router::url('/', true).'img/delivery_note.xlsx';
				exit;
				/*$contenttype = "application/force-download";
				header('Content-Type: text/html; charset=ISO-8859-1');
				header("Content-Type: " . $contenttype);
				header("Content-Disposition: attachment; filename=\"" . basename($file) . "\";");
				readfile($uploadRemote);
				exit;*/
	}
	
	
	 public function createCutOffListCorreos()
	{
		$this->layout = '';
		$this->autoRender = false;          
		
		// Get All manifest related services
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		
		/* start european country iso code*/
		$isoCode = Configure::read('customIsoCodes');
		  /* end european country iso code*/
		
		//Global Variable
		$glbalSortingCounter = 0;
		
		$serviceProvider = $this->request->data['serviceProvider'];
		date_default_timezone_set('Europe/Jersey');
		$time_in_12_hour_format  = date("g:i a", strtotime(date("H:i",$_SERVER['REQUEST_TIME'])));
			
		$folderName = 'Service Manifest -'. date("d.m.Y");
		$service = str_replace(' ', '', str_replace(':','_',$serviceProvider.'-'. date("d.m.Y") .'_'. $time_in_12_hour_format));
		
		
		// Get Data
		$manifest = json_decode(json_encode($this->ServiceCounter->find( 'all' , 
			array( 
				'conditions' => array( 
						//'ServiceCounter.manifest' => 1 , 
						'ServiceCounter.service_provider' => $serviceProvider , 
						'ServiceCounter.order_ids !=' => '' , 
						'ServiceCounter.counter >' => 0 , 
						'ServiceCounter.original_counter >' => 0, 
						//'ServiceCounter.locking_stage' => 0 
					)                                                                                                           
				)                                                                              
			)),0); 
		
		if( count($manifest) > 0 )
		{
			
			//We got number of sorted rows which had been done through operator and creating manifest 1 for same provider
			$inc = 1;$cnt = 2;$e = 0;foreach( $manifest as $manifestIndex => $manifestValue )
			{
				
				if( $e == 0 )
				{
					// Clean Stream (Input)
					//ob_clean();                                                         
					App::import('Vendor', 'PHPExcel/IOFactory');
					App::import('Vendor', 'PHPExcel');                          
					
					//Set and create Active Sheet for single workbook with singlle sheet
					$objPHPExcel = new PHPExcel();       
					$objPHPExcel->createSheet();
					
					//Column Create                              
					$objPHPExcel->setActiveSheetIndex(0);
					
					$objPHPExcel->getActiveSheet()->setCellValue('A1', 'LineNo');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Option');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('C1', 'LineIdentifier');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('D1', 'GroupageManifestNo');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Consignor');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('F1', 'ConsignorAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('G1', 'ConsignorAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('H1', 'ConsignorPostCode');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('I1', 'ConsigneeName');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('J1', 'ConsigneeAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('K1', 'ConsigneeAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('L1', 'ConsigneeGSTNumber');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('M1', 'ConsigneePostCode');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('N1', 'ConsigneeCountry');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('O1', 'NoOfUnits');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('P1', 'GrossMass');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Description');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('R1', 'Value');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('S1', 'ValueCurr');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('T1', 'ForwardingAgent');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('U1', 'ForwardingAgentAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('V1', 'ForwardingAgentAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('W1', 'ForwardingAgentCountry');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('X1', 'CommunityStatus');   
					$objPHPExcel->getActiveSheet()->setCellValue('Y1', 'PostalProvider');                                                               
						
				}
				
				$orderIds = explode( ',' , $manifestValue->ServiceCounter->order_ids);
				
				//pr($orderIds);
				
				$this->loadModel( 'MergeUpdate' );
				$this->loadModel( 'Product' );
				$this->loadModel( 'ProductDesc' );
				$this->loadModel( 'OpenOrder' );
				$this->loadModel( 'Customer' );
				
				$combineSku = '';				
				$k = 0;while( $k <= count($orderIds)-1 )
				{
					
					$orderIdSpecified = $orderIds[$k];
					$this->updateManifestDate($orderIdSpecified);
					$params = array(
						'conditions' => array(
							'MergeUpdate.id' => $orderIdSpecified
						),
						'fields' => array(
							'MergeUpdate.id',
							'MergeUpdate.order_id',
							'MergeUpdate.product_order_id_identify',
							'MergeUpdate.quantity',
							'MergeUpdate.sku',
							'MergeUpdate.price',
							'MergeUpdate.packet_weight',
                            'MergeUpdate.envelope_weight'
						)
					);
					
					$mergeOrder = json_decode(json_encode($this->MergeUpdate->find(
						'all', $params
					)),0);
					
					$this->setManifestRecord( $mergeOrder[0]->MergeUpdate->id, $service );
					//pr($mergeOrder);
										
					$getSku = explode( ',' , $mergeOrder[0]->MergeUpdate->sku);
					
                    $packageWeight = $mergeOrder[0]->MergeUpdate->envelope_weight;                    
					$totalPriceValue = 0;
					$massWeight = 0;
					$totalUnits = 0;
					$combineTitle = '';
					$combineCategory = '';
                    $calculateWeight = 0;
                    
                    $getSpecifier = explode('-', $mergeOrder[0]->MergeUpdate->product_order_id_identify);
                   
                    $setSpaces = '';
                    $identifier = $getSpecifier[1];
                    $em = 0;while( $em < $identifier )
                    {
						$setSpaces .= $setSpaces.' ';						
					$em++;	
					}
					
					$j = 0;while( $j <= count($getSku)-1 )
					{
						$newSku = explode( 'XS-' , $getSku[$j] );
						
						//Get Title of product
						$setNewSku = 'S-'.$newSku[1];
						
						$this->loadModel( 'Product' );
						$this->loadModel( 'Category' );					
					
						$this->Product->bindModel(
							array(
							 'hasOne' => array(
							  'Category' => array(
							   'foreignKey' => false,
							   'conditions' => array('Category.id = Product.category_id'),
							   'fields' => array('Category.id,Category.category_name')
							  )
							 )
							)
						   );
					
						$productSku = $this->Product->find(
							'first' ,
							array(
								'conditions' => array(
									'Product.product_sku' => $setNewSku
								)
							)
						);
						
						if( $combineTitle == '' )
						{
							$combineTitle = $newSku[0] .'X' .substr($productSku['Product']['product_name'],0,25);	
							$totalUnits = $totalUnits + $newSku[0];     
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;		
							$combineCategory = $productSku['Category']['category_name'];				
						}
						else
						{
							$combineTitle .= ',' .  $newSku[0] . 'X' .substr($productSku['Product']['product_name'],0,25);	
							$totalUnits = $totalUnits + $newSku[0];
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;                                                        
							$combineCategory .= ',' . $productSku['Category']['category_name'];				
						}
						 
						if( $combineSku == '' )
							$combineSku = $setNewSku;	
						else
							$combineSku .= ',' . $setNewSku;	
						
					$j++;	
					}
					
					//package weight + order item weight
					$massWeight = $packageWeight + $massWeight; 
                                        
					//LineNo
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt, $inc );
					
					//Option
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt, 'N' );
					
					//LineIdentifier
					//$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, 'ECGL'.$mergeOrder[0]->MergeUpdate->product_order_id_identify );
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, $mergeOrder[0]->MergeUpdate->product_order_id_identify );
					
					//LineIdentifier
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt, '' );
					
					/*  Consignor */					
					//Consignor
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, 'ESL Limited' );
					
					//ConsignorAddress1
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$cnt, 'Unit 4 Airport Cargo Centre' );
					
					//ConsignorAddress2
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$cnt, 'L\'avenue De La Comune, Jersey' );
					
					//ConsignorPostCode
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$cnt, 'JE3 7BY' );
					
					/* Condignee */
					$paramsConsignee = array(
						'conditions' => array(
							'OpenOrder.num_order_id' => $mergeOrder[0]->MergeUpdate->order_id
						),
						'fields' => array(
							'OpenOrder.num_order_id',
							'OpenOrder.id',
							'OpenOrder.general_info',
							'OpenOrder.shipping_info',
							'OpenOrder.customer_info',
							'OpenOrder.totals_info'							
						)
					);
					
					$getConsigneeDetailFromLinnworksOrder = json_decode(json_encode($this->OpenOrder->find( 'first', $paramsConsignee )),0);					
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->general_info));
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->shipping_info));
					$congineeInfo = unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->customer_info);					
					//pr($congineeInfo);
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->totals_info));
					
					$totalInfo = unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->totals_info);
					//pr($totalInfo); exit;
					//$congineeInfo->Address->FullName;
					
					
					$postcountry =  $congineeInfo->Address->Country;
					$previousDate	=	date('Y-m-d h:i:s', strtotime('-10 days'));
					
						
					$exteraword = '';
					 if($identifier >= 2)
						{
							$getCustomerDetail =	$this->Customer->find('all', 
									array( 
									'conditions' => array( 
														'Customer.country' => $postcountry,
									'and'		=> array('Customer.date >' => '2016-01-01 00:00:00',
														'Customer.date <' => $previousDate
														)),
									 'order' => 'rand()',
									  'limit' => 1,
									)
								);
								
							$customerName		=	$getCustomerDetail[0]['Customer']['name'].' ';
							$customerAddress1	=	$getCustomerDetail[0]['Customer']['address1'].' ';
							$customerAddress2	=	$getCustomerDetail[0]['Customer']['address2'].' ';
						
						}
						else
						{
							$customerName 		= $congineeInfo->Address->FullName;
							$customerAddress1 	= $congineeInfo->Address->Address1;
							$customerAddress2 	= $congineeInfo->Address->Address2;
						}
					
					
					//ConsigneeName
					//$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, str_replace(' ',$setSpaces,$congineeInfo->Address->FullName) );
					$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, $customerName );
					
					//ConsigneeAddress1
					//$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, str_replace(' ',$setSpaces,$congineeInfo->Address->Address1) );
					$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, $customerAddress1 );
					
					//ConsigneeAddress2
					//$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, str_replace(' ',$setSpaces,$congineeInfo->Address->Address2) );
					$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, '' );
					
					//ConsigneeGSTNumber
					$objPHPExcel->getActiveSheet()->setCellValue('L'.$cnt, '' );
					
					//ConsigneePostCode
					$objPHPExcel->getActiveSheet()->setCellValue('M'.$cnt, $congineeInfo->Address->PostCode );
					
					$setSpaces = '';
					
					//ConsigneeCountry
					$country = '';					
					foreach( $isoCode as $index => $value )
					{
						if( $index == $congineeInfo->Address->Country )
						{
							$country = $value;
						}
					}				
					$objPHPExcel->getActiveSheet()->setCellValue('N'.$cnt, 'ES' );
					
					//NoOfUnits
					$objPHPExcel->getActiveSheet()->setCellValue('O'.$cnt, $totalUnits );
					
					//MassWeight
					$objPHPExcel->getActiveSheet()->setCellValue('P'.$cnt, $massWeight );
					
					//Description					
					$objPHPExcel->getActiveSheet()->setCellValue('Q'.$cnt, $combineCategory );
					//$objPHPExcel->getActiveSheet()->setCellValue('Q'.$cnt, 'Printer cartridge & Electronics' );
					
					$currencyMatter = $totalInfo->Currency;
					
					$globalCurrencyConversion = 1;
					
					if( $currencyMatter == "EUR" )
					{
						$globalCurrencyConversion = 1;
					}
					else
					{
						$globalCurrencyConversion = 1.38;
					}
					
					//Value
					$totalValue = 0;
					$setPrice = $mergeOrder[0]->MergeUpdate->price;
				
					
					if( ( $setPrice * $globalCurrencyConversion) > 21.99 )
					{
						$totalValue = number_format($this->getrand(), 2, '.', ''); //sprintf( '%.2f' , $this->getrand() );
					}
					else
					{
						$totalValue = number_format(( $setPrice * $globalCurrencyConversion ), 2, '.', ''); //sprintf( '%.2f' , $setPrice * $globalCurrencyConversion );
					}
					
					$objPHPExcel->getActiveSheet()->setCellValue('R'.$cnt, $totalValue );
					
					//Currency
					$objPHPExcel->getActiveSheet()->setCellValue('S'.$cnt, 'EUR' );
					
					//ForwardingAgent
					$objPHPExcel->getActiveSheet()->setCellValue('T'.$cnt, 'ESL Limited' );
					
					//ForwardingAgentAddress1
					$objPHPExcel->getActiveSheet()->setCellValue('U'.$cnt, 'Unit 4 Airport Cargo Centre' );
					
					//ForwardingAgentAddress2
					$objPHPExcel->getActiveSheet()->setCellValue('V'.$cnt, "L'avenue De La Comune, JE3 7BY" );
					
					//ForwardingAgentCountry
					$objPHPExcel->getActiveSheet()->setCellValue('W'.$cnt, "JE" );
					
					//CommunityStatus
					$objPHPExcel->getActiveSheet()->setCellValue('X'.$cnt, "T2" );
					$objPHPExcel->getActiveSheet()->setCellValue('Y'.$cnt, "Correos" );
					
					$combineSku = '';
					$totalUnits = 0;
					$massWeight = 0;
					$combineCategory = '';
					
				$inc++;	
				$cnt++;	
				$k++;	
				}
				
				$serviceData = json_decode(json_encode($this->ServiceCounter->find( 'first', array( 'conditions' => array( 'ServiceCounter.id' => $manifestValue->ServiceCounter->id ) ) )),0);							
				$originalCounter = $serviceData->ServiceCounter->original_counter - $serviceData->ServiceCounter->counter;
				
				//Update Now at specific id
				$this->request->data['ServiceCounter']['ServiceCounter']['id'] = $manifestValue->ServiceCounter->id;
				$this->request->data['ServiceCounter']['ServiceCounter']['original_counter'] = $originalCounter;
				$this->request->data['ServiceCounter']['ServiceCounter']['counter'] = 0;
				$this->request->data['ServiceCounter']['ServiceCounter']['order_ids'] = '';
				//$this->request->data['ServiceCounter']['ServiceCounter']['locking_stage'] = 1;
				$this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );
				
			$e++;	
			}
			
			//Set First Row  for Amazon FBa Sheet
			$objPHPExcel->setActiveSheetIndex(0);                                                                              
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:D1')->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));                   
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:D1')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet(0)->getStyle("A1:D1")->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet(0)
			->getStyle('A1:D1')
			->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'EBE5DB')

                                )
                            )
			);
			
			/*date_default_timezone_set('Europe/Jersey');
			$time_in_12_hour_format  = date("g:i a", strtotime(date("H:i",$_SERVER['REQUEST_TIME'])));
			
			$folderName = 'Service Manifest -'. date("d.m.Y");
			$service = str_replace(' ', '', str_replace(':','_',$serviceProvider.'-'. date("d.m.Y") .'_'. $time_in_12_hour_format));*/
							  
			// create new folder with date if exists will remain same or else create new one                                                                                                                                                                                                
			$dir = new Folder(WWW_ROOT .'img/cut_off/'.$folderName, true, 0755);
			
			$uploadUrl = WWW_ROOT .'img/cut_off/'. $folderName . '/' .$service.'.csv';                                          
			$uploadUrI = Router::url('/', true) . 'img/cut_off/'. $folderName . '/' .$service.'.csv';                                          
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');  
			$objWriter->save($uploadUrl);
			
                        //flush all blank rows
			$this->delServiceRows();

			//Update Service Counter
			//$this->call_service_counter();			
			echo $uploadUrI; exit;
		}
		else
		{
			echo "blank"; exit;
		}
	}	
	 
	
	 public function createCutOffListDHL()
	{
		$this->layout = '';
		$this->autoRender = false;          
		
		// Get All manifest related services
		$this->loadModel( 'ServiceCounter' );
		$this->loadModel( 'MergeUpdate' );
		
		/* start european country iso code*/
		$isoCode = Configure::read('customIsoCodes');
		  /* end european country iso code*/
		
		//Global Variable
		$glbalSortingCounter = 0;
		
		$serviceProvider = $this->request->data['serviceProvider'];
		date_default_timezone_set('Europe/Jersey');
		$time_in_12_hour_format  = date("g:i a", strtotime(date("H:i",$_SERVER['REQUEST_TIME'])));
			
		$folderName = 'Service Manifest -'. date("d.m.Y");
		$service = str_replace(' ', '', str_replace(':','_',$serviceProvider.'-'. date("d.m.Y") .'_'. $time_in_12_hour_format));
		
		
		// Get Data
		$manifest = json_decode(json_encode($this->ServiceCounter->find( 'all' , 
			array( 
				'conditions' => array( 
						//'ServiceCounter.manifest' => 1 , 
						'ServiceCounter.service_provider' => $serviceProvider , 
						'ServiceCounter.order_ids !=' => '' , 
						'ServiceCounter.counter >' => 0 , 
						'ServiceCounter.original_counter >' => 0, 
						//'ServiceCounter.locking_stage' => 0 
					)                                                                                                           
				)                                                                              
			)),0); 
		
		if( count($manifest) > 0 )
		{
			
			//We got number of sorted rows which had been done through operator and creating manifest 1 for same provider
			$inc = 1;$cnt = 2;$e = 0;foreach( $manifest as $manifestIndex => $manifestValue )
			{
				
				if( $e == 0 )
				{
					// Clean Stream (Input)
					//ob_clean();                                                         
					App::import('Vendor', 'PHPExcel/IOFactory');
					App::import('Vendor', 'PHPExcel');                          
					
					//Set and create Active Sheet for single workbook with singlle sheet
					$objPHPExcel = new PHPExcel();       
					$objPHPExcel->createSheet();
					
					//Column Create                              
					$objPHPExcel->setActiveSheetIndex(0);
					
					$objPHPExcel->getActiveSheet()->setCellValue('A1', 'LineNo');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Option');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('C1', 'LineIdentifier');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('D1', 'GroupageManifestNo');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Consignor');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('F1', 'ConsignorAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('G1', 'ConsignorAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('H1', 'ConsignorPostCode');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('I1', 'ConsigneeName');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('J1', 'ConsigneeAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('K1', 'ConsigneeAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('L1', 'ConsigneeGSTNumber');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('M1', 'ConsigneePostCode');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('N1', 'ConsigneeCountry');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('O1', 'NoOfUnits');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('P1', 'GrossMass');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Description');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('R1', 'Value');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('S1', 'ValueCurr');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('T1', 'ForwardingAgent');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('U1', 'ForwardingAgentAddress1');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('V1', 'ForwardingAgentAddress2');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('W1', 'ForwardingAgentCountry');                                                                  
					$objPHPExcel->getActiveSheet()->setCellValue('X1', 'CommunityStatus');   
					$objPHPExcel->getActiveSheet()->setCellValue('Y1', 'PostalProvider');                                                               
						
				}
				
				$orderIds = explode( ',' , $manifestValue->ServiceCounter->order_ids);
				
				//pr($orderIds);
				
				$this->loadModel( 'MergeUpdate' );
				$this->loadModel( 'Product' );
				$this->loadModel( 'ProductDesc' );
				$this->loadModel( 'OpenOrder' );
				$this->loadModel( 'Customer' );
				
				$combineSku = '';				
				$k = 0;while( $k <= count($orderIds)-1 )
				{
					
					$orderIdSpecified = $orderIds[$k];
					
					$params = array(
						'conditions' => array(
							'MergeUpdate.id' => $orderIdSpecified
						),
						'fields' => array(
							'MergeUpdate.id',
							'MergeUpdate.order_id',
							'MergeUpdate.product_order_id_identify',
							'MergeUpdate.quantity',
							'MergeUpdate.sku',
							'MergeUpdate.price',
							'MergeUpdate.packet_weight',
                            'MergeUpdate.envelope_weight'
						)
					);
					
					$mergeOrder = json_decode(json_encode($this->MergeUpdate->find(
						'all', $params
					)),0);
					
					$this->setManifestRecord( $mergeOrder[0]->MergeUpdate->id, $service );
					//pr($mergeOrder);
										
					$getSku = explode( ',' , $mergeOrder[0]->MergeUpdate->sku);
					
                    $packageWeight = $mergeOrder[0]->MergeUpdate->envelope_weight;                    
					$totalPriceValue = 0;
					$massWeight = 0;
					$totalUnits = 0;
					$combineTitle = '';
					$combineCategory = '';
                    $calculateWeight = 0;
                    
                    $getSpecifier = explode('-', $mergeOrder[0]->MergeUpdate->product_order_id_identify);
                   
                    $setSpaces = '';
                    $identifier = $getSpecifier[1];
                    $em = 0;while( $em < $identifier )
                    {
						$setSpaces .= $setSpaces.' ';						
					$em++;	
					}
					
					$j = 0;while( $j <= count($getSku)-1 )
					{
						$newSku = explode( 'XS-' , $getSku[$j] );
						
						//Get Title of product
						$setNewSku = 'S-'.$newSku[1];
						
						$this->loadModel( 'Product' );
						$this->loadModel( 'Category' );					
					
						$this->Product->bindModel(
							array(
							 'hasOne' => array(
							  'Category' => array(
							   'foreignKey' => false,
							   'conditions' => array('Category.id = Product.category_id'),
							   'fields' => array('Category.id,Category.category_name')
							  )
							 )
							)

						   );
					
						$productSku = $this->Product->find(
							'first' ,
							array(
								'conditions' => array(
									'Product.product_sku' => $setNewSku
								)
							)
						);
						
						if( $combineTitle == '' )
						{
							$combineTitle = $newSku[0] .'X' .substr($productSku['Product']['product_name'],0,25);	
							$totalUnits = $totalUnits + $newSku[0];     
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;		
							$combineCategory = $productSku['Category']['category_name'];				
						}
						else
						{
							$combineTitle .= ',' .  $newSku[0] . 'X' .substr($productSku['Product']['product_name'],0,25);	
							$totalUnits = $totalUnits + $newSku[0];
                                                        $calculateWeight = ($newSku[0] * $productSku['ProductDesc']['weight']);
							$massWeight = $massWeight + $calculateWeight;                                                        
							$combineCategory .= ',' . $productSku['Category']['category_name'];				
						}
						 
						if( $combineSku == '' )
							$combineSku = $setNewSku;	
						else
							$combineSku .= ',' . $setNewSku;	
						
					$j++;	
					}
					
					//package weight + order item weight
					$massWeight = $packageWeight + $massWeight; 
                                        
					//LineNo
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt, $inc );
					
					//Option
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt, 'N' );
					
					//LineIdentifier
					//$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, 'ECGL'.$mergeOrder[0]->MergeUpdate->product_order_id_identify );
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, $mergeOrder[0]->MergeUpdate->product_order_id_identify );
					
					//LineIdentifier
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt, '' );
					
					/*  Consignor */					
					//Consignor
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, 'ESL Limited' );
					
					//ConsignorAddress1
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$cnt, 'Unit 4 Airport Cargo Centre' );
					
					//ConsignorAddress2
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$cnt, 'L\'avenue De La Comune, Jersey' );
					
					//ConsignorPostCode
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$cnt, 'JE3 7BY' );
					
					/* Condignee */
					$paramsConsignee = array(
						'conditions' => array(
							'OpenOrder.num_order_id' => $mergeOrder[0]->MergeUpdate->order_id
						),
						'fields' => array(

							'OpenOrder.num_order_id',
							'OpenOrder.id',
							'OpenOrder.general_info',
							'OpenOrder.shipping_info',
							'OpenOrder.customer_info',
							'OpenOrder.totals_info'							
						)
					);
					
					$getConsigneeDetailFromLinnworksOrder = json_decode(json_encode($this->OpenOrder->find( 'first', $paramsConsignee )),0);					
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->general_info));
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->shipping_info));
					$congineeInfo = unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->customer_info);					
					//pr($congineeInfo);
					//pr(unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->totals_info));
					
					$totalInfo = unserialize($getConsigneeDetailFromLinnworksOrder->OpenOrder->totals_info);
					//pr($totalInfo); exit;
					//$congineeInfo->Address->FullName;
					
					
					$postcountry =  $congineeInfo->Address->Country;
					$previousDate	=	date('Y-m-d h:i:s', strtotime('-10 days'));
					
						
					$exteraword = '';
					 if($identifier >= 2)
						{
							$getCustomerDetail =	$this->Customer->find('all', 
									array( 
									'conditions' => array( 
														'Customer.country' => $postcountry,
									'and'		=> array('Customer.date >' => '2016-01-01 00:00:00',
														'Customer.date <' => $previousDate

														)),
									 'order' => 'rand()',
									  'limit' => 1,
									)
								);
								
							$customerName		=	$getCustomerDetail[0]['Customer']['name'].' ';
							$customerAddress1	=	$getCustomerDetail[0]['Customer']['address1'].' ';
							$customerAddress2	=	$getCustomerDetail[0]['Customer']['address2'].' ';
						
						}
						else
						{
							$customerName 		= $congineeInfo->Address->FullName;
							$customerAddress1 	= $congineeInfo->Address->Address1;
							$customerAddress2 	= $congineeInfo->Address->Address2;
						}
					
					
					//ConsigneeName
					//$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, str_replace(' ',$setSpaces,$congineeInfo->Address->FullName) );
					$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, $customerName );
					
					//ConsigneeAddress1
					//$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, str_replace(' ',$setSpaces,$congineeInfo->Address->Address1) );
					$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, $customerAddress1 );
					
					//ConsigneeAddress2
					//$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, str_replace(' ',$setSpaces,$congineeInfo->Address->Address2) );
					$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, '' );
					
					//ConsigneeGSTNumber
					$objPHPExcel->getActiveSheet()->setCellValue('L'.$cnt, '' );
					
					//ConsigneePostCode
					$objPHPExcel->getActiveSheet()->setCellValue('M'.$cnt, $congineeInfo->Address->PostCode );
					
					$setSpaces = '';
					
					//ConsigneeCountry
					$country = '';					
					foreach( $isoCode as $index => $value )
					{
						if( $index == $congineeInfo->Address->Country )
						{
							$country = $value;
						}
					}				
					$objPHPExcel->getActiveSheet()->setCellValue('N'.$cnt, 'ES' );
					
					//NoOfUnits
					$objPHPExcel->getActiveSheet()->setCellValue('O'.$cnt, $totalUnits );
					
					//MassWeight
					$objPHPExcel->getActiveSheet()->setCellValue('P'.$cnt, $massWeight );
					
					//Description					
					$objPHPExcel->getActiveSheet()->setCellValue('Q'.$cnt, $combineCategory );
					//$objPHPExcel->getActiveSheet()->setCellValue('Q'.$cnt, 'Printer cartridge & Electronics' );
					
					$currencyMatter = $totalInfo->Currency;
					
					$globalCurrencyConversion = 1;
					
					if( $currencyMatter == "EUR" )
					{
						$globalCurrencyConversion = 1;
					}
					else
					{
						$globalCurrencyConversion = 1.38;
					}
					
					//Value
					$totalValue = 0;
					$setPrice = $mergeOrder[0]->MergeUpdate->price;
				
					
					if( ( $setPrice * $globalCurrencyConversion) > 21.99 )
					{
						$totalValue = number_format($this->getrand(), 2, '.', ''); //sprintf( '%.2f' , $this->getrand() );
					}
					else
					{
						$totalValue = number_format(( $setPrice * $globalCurrencyConversion ), 2, '.', ''); //sprintf( '%.2f' , $setPrice * $globalCurrencyConversion );
					}
					
					$objPHPExcel->getActiveSheet()->setCellValue('R'.$cnt, $totalValue );
					
					//Currency
					$objPHPExcel->getActiveSheet()->setCellValue('S'.$cnt, 'EUR' );
					
					//ForwardingAgent
					$objPHPExcel->getActiveSheet()->setCellValue('T'.$cnt, 'ESL Limited' );
					
					//ForwardingAgentAddress1
					$objPHPExcel->getActiveSheet()->setCellValue('U'.$cnt, 'Unit 4 Airport Cargo Centre' );
					
					//ForwardingAgentAddress2
					$objPHPExcel->getActiveSheet()->setCellValue('V'.$cnt, "L'avenue De La Comune, JE3 7BY" );
					
					//ForwardingAgentCountry
					$objPHPExcel->getActiveSheet()->setCellValue('W'.$cnt, "JE" );
					
					//CommunityStatus
					$objPHPExcel->getActiveSheet()->setCellValue('X'.$cnt, "T2" );
					$objPHPExcel->getActiveSheet()->setCellValue('Y'.$cnt, "Correos" );
					
					$combineSku = '';
					$totalUnits = 0;
					$massWeight = 0;
					$combineCategory = '';
					
				$inc++;	
				$cnt++;	
				$k++;	
				}
				
				$serviceData = json_decode(json_encode($this->ServiceCounter->find( 'first', array( 'conditions' => array( 'ServiceCounter.id' => $manifestValue->ServiceCounter->id ) ) )),0);							
				$originalCounter = $serviceData->ServiceCounter->original_counter - $serviceData->ServiceCounter->counter;
				
				//Update Now at specific id
				$this->request->data['ServiceCounter']['ServiceCounter']['id'] = $manifestValue->ServiceCounter->id;
				$this->request->data['ServiceCounter']['ServiceCounter']['original_counter'] = $originalCounter;
				$this->request->data['ServiceCounter']['ServiceCounter']['counter'] = 0;
				$this->request->data['ServiceCounter']['ServiceCounter']['order_ids'] = '';
				//$this->request->data['ServiceCounter']['ServiceCounter']['locking_stage'] = 1;
				$this->ServiceCounter->saveAll( $this->request->data['ServiceCounter'] );
				
			$e++;	
			}
			
			//Set First Row  for Amazon FBa Sheet
			$objPHPExcel->setActiveSheetIndex(0);                                                                              
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:D1')->getAlignment()->applyFromArray(
			array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));                   
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:D1')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet(0)->getStyle("A1:D1")->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet(0)
			->getStyle('A1:D1')
			->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'EBE5DB')
                                )
                            )
			);
			
			/*date_default_timezone_set('Europe/Jersey');
			$time_in_12_hour_format  = date("g:i a", strtotime(date("H:i",$_SERVER['REQUEST_TIME'])));
			
			$folderName = 'Service Manifest -'. date("d.m.Y");
			$service = str_replace(' ', '', str_replace(':','_',$serviceProvider.'-'. date("d.m.Y") .'_'. $time_in_12_hour_format));*/
							  
			// create new folder with date if exists will remain same or else create new one                                                                                                                                                                                                
			$dir = new Folder(WWW_ROOT .'img/cut_off/'.$folderName, true, 0755);
			
			$uploadUrl = WWW_ROOT .'img/cut_off/'. $folderName . '/' .$service.'.csv';                                          
			$uploadUrI = Router::url('/', true) . 'img/cut_off/'. $folderName . '/' .$service.'.csv';                                          
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');  
			$objWriter->save($uploadUrl);
			
                        //flush all blank rows
			$this->delServiceRows();

			//Update Service Counter
			//$this->call_service_counter();			
			echo $uploadUrI; exit;
		}
		else
		{
			echo "blank"; exit;
		}
	}

}
?>

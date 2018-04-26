<?php

public function validcallchek($param)
	    {
	
		$validate=array();
		$validate['valid']=0;
		if(count($param)!=0)
		{
			$secretkey 			= $this->secretkey;
			$status				= urldecode($param["status"]);
			$installmentid		= urldecode($param['installmentid']);
			$ordercode          = urldecode($param['ordercode']);
			$callid       		= urldecode($param['callid']);
			$check      		= urldecode($param['check']);
			
			
			$str = $status.$installmentid.$ordercode.$callid;
			$str .=$secretkey;
			$calculatedCheck = hash('sha256',$str);
			
			if (strcasecmp($check,$calculatedCheck)==0) 
				{
					$validate['valid']			= 1;
					$validate['status']			= $status;
					$validate['installmentid']	= $installmentid;
					$validate['ordercode']		= $ordercode;
					$validate['callid']			= $callid;
				}
		}
		
		return $validate;
	}
	

	public function returnResult($resultcode,$resultdesc,$transactioncode) 
        {
	
	$check = hash('sha256', $resultcode.$resultdesc.$transactioncode.$this->secretkey);
	
$xmlstr =
<<<XML
<result>
<resultcode>$resultcode</resultcode>
<resultdesc>$resultdesc</resultdesc>
<check>$check</check>
<data>$transactioncode</data>
</result>
XML;
	
	
	
	
	header('Content-type: text/xml');
	die($xmlstr);
	
	} // returnResult
	
?>
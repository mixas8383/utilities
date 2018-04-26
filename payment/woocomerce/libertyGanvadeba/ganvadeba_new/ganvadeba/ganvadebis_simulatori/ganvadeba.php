<?php
$merchant='merchant';
$secretkey='key';
$testmode=0;
$ordercode=$_POST['ordercode'];
$callid=$ordercode.time();//unicaluri yoveljerze marto time ara
$shipping_address=$_POST['shipping_address'];
$products=$_POST['products'];
          $str = $secretkey
                . $merchant
                . $ordercode
				. $callid
				. $shipping_address
                . $testmode;
                
    foreach ($products as $product) 
	{
         $str .= $product['id'].$product['title'].$product['amount'].$product['price'].$product['type'].$product['installmenttype']; 
    }         
   $check = strtoupper(hash('sha256', $str));
   $action='http://onlineinstallment.lb.ge/installment/';
?>
<form method="post" action="<?php echo $action;?>" target="_blank">
    <input type="hidden" name="merchant"    value="<?php echo $merchant; ?>" />
    <input type="hidden" name="ordercode"   value="<?php echo htmlentities($ordercode); ?>" />
	<input type="text"   name="callid"        value="<?php echo htmlentities($callid); ?>" />
	<input type="hidden" name="shipping_address"   value="<?php echo htmlentities($shipping_address, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="testmode"    value="<?php echo $testmode; ?>" />
    <input type="hidden" name="check"       value="<?php echo $check; ?>" />
<?php
        foreach ($products as $key => $value) {
?>        <input type="hidden" name="products[<?php echo $key; ?>][id]"      value="<?php echo $value['id']; ?>" />
		  <input type="hidden" name="products[<?php echo $key; ?>][title]"   value="<?php echo htmlentities($value['title'], ENT_QUOTES, 'UTF-8'); ?>" />
		  <input type="hidden" name="products[<?php echo $key; ?>][amount]"  value="<?php echo $value['amount']; ?>" />
		  <input type="hidden" name="products[<?php echo $key; ?>][price]"   value="<?php echo $value['price']; ?>" />
		  <input type="hidden" name="products[<?php echo $key; ?>][cashprice]"   value="<?php echo $value['cashprice']; ?>" />
		  <input type="hidden" name="products[<?php echo $key; ?>][type]"   value="<?php echo $value['type']; ?>" />
		  <input type="hidden" name="products[<?php echo $key; ?>][installmenttype]"   value="<?php echo $value['installmenttype']; ?>" />
		  
		  
<?php          
        }         
?>        
<input type="submit" value="Continue" />
</form>

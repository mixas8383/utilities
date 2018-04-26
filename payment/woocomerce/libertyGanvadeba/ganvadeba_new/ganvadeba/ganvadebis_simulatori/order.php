<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>order</title>
</head>
<body>
<?php
$prefix = 'order_';

?>
<form action="ganvadeba.php" method="post">
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td>ordercode</td>
            <td><input name="ordercode" type="text" value="<?php echo $prefix . time(); ?>"/></td>
        </tr>
        <tr>
            <td>Shipping address</td>
            <td><input name="shipping_address" type="text" value="თბილისი,ჭავჭავაძის 74"/></td>
        </tr>
        <tr>
            <td colspan="2">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>Prod_id<br/> <input name="products[0][id]" type="text" value="1001"/></td>
                        <td>prod_title<br/><input name="products[0][title]" type="text" value="my ტესტ product 1"/></td>
                        <td>prod_amount<br/><input name="products[0][amount]" type="text" value="1"/></td>
                        <td>prod_price<br/><input name="products[0][price]" type="text" value="45"/></td>
                        <td>prod_cashprice<br/><input name="products[0][cashprice]" type="text" value="40"/></td>
                        <td>prod_type<br/><input name="products[0][type]" type="text" value="0"/></td>
                        <td>installment_type<br/><input name="products[0][installmenttype]" type="text" value="0"/></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>Prod_id<br/><input name="products[1][id]" type="text" value="1002"/></td>
                        <td>prod_title<br/><input name="products[1][title]" type="text" value="my test პროდუქტ 2"/></td>
                        <td>prod_amount<br/><input name="products[1][amount]" type="text" value="1"/></td>
                        <td>prod_price<br/><input name="products[1][price]" type="text" value="135"/></td>
                        <td>prod_cashprice<br/><input name="products[0][cashprice]" type="text" value="40"/></td>
                        <td>prod_type<br/><input name="products[1][type]" type="text" value="0"/></td>
                        <td>installment_type<br/><input name="products[1][installmenttype]" type="text" value="0"/></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td></td>
            <td><input name="submit" type="submit" value="submit"/></td>
        </tr>
    </table>
</form>
</body>
</html>
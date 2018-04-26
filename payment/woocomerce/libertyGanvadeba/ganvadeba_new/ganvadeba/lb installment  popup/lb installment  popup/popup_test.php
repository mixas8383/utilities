<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liberty Bank Online Installment</title>
    <link rel="stylesheet" href="http://onlineinstallment.lb.ge/cdn/sources/style.css">
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="//onlineinstallment.lb.ge/cdn/sources/jq_1_11_0.js"></script>
</head>
<body>
<div class="product-container">
    <div class="image-container"><img src="http://onlineinstallment.lb.ge/cdn/images/products/Samsung.jpg"></div>
    <div class="text-container">
        <div class="product-title-row"><span>Samsung ATIV Tab</span></div>
        <div class="product-price-row"><span>ფასი:</span><span>150.00 ლარი</span></div>
        <div class="product-bit-row">

            <!--პოპაპ ფანჯრის კონტეინერი-->
            <div id="lbRoot"></div>
            <!--end-->

            <div class="product-description-row"><span>აღწერილობა:</span><span>Samsung ATIV Smart PC 64GB 11.6" Tablet (XE500T1C) - Mystic Blue w' Windows 8</span>
            </div>
        </div>
    </div>


</body>
</html>


<?php

$merchantName = 'TESTGE'; //სატესტო მერჩანტის სახელი
$secretKey = 'TEST123456'; //სატესტო მერჩანტის კოდი
$testMode = 1;


$ordercode = "order" . uniqid(); //უნიკალური შეკვეთის კოდი
$callid = uniqid(); //უნიკალური კოდი რომელიც სხვადასხვაა ყოველ მიმართვაზე
$shipping_address = "თბილისი ჭავჭავაძის გამზირი";
//პროდუქტების მასივი
$products = array(
    array("id" => 25, "title" => "Samsung ATIV SmartPC 64GB 11.60 Tablet", "amount" => 1, "price" => "1500.25", "cashprice" => '1400.00', "type" => 0, "installmenttype" => 0),
    array("id" => 26, "title" => "Samsung ATIV2 SmartPC 64GB 11.65 Tablet", "amount" => 5, "price" => "500.50", "cashprice" => '1400.00', "type" => 0, "installmenttype" => 0)
);
//...
$str = $secretKey . $merchantName . $ordercode . $callid . $shipping_address . $testMode; //check-ის დასაგენერირებლად საჭირო სტრინგი

$prod_str = '';
foreach ($products as $product) {
    $prod_str .= $product['id'] . $product['title'] . $product['amount'] . $product['price'] . $product['type'] . $product['installmenttype'];

}

$str = $str . $prod_str;
//check-ის გენერირება
function generateCheck($str)
{
    $check = strtoupper(hash('sha256', $str));
    return $check;
}

//...
?>
<script>


    var orderData = {
        merchant: "<?php echo $merchantName;?>",
        ordercode: "<?php echo $ordercode;?>",
        callid: "<?php echo $callid;?>",
        shipping_address: "<?php echo $shipping_address;?>",
        products:         <?php echo json_encode($products);?>,
        testmode: "<?php echo $testMode;?>",
        check: "<?php echo generateCheck($str);?>"
    };


    $.getScript("http://onlineinstallment.lb.ge/cdn/lbrootnew.js", function () {
    });

</script>


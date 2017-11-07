<?php
$time	=date("H");
$day	=date("D");
if($day=="Sat" || $day=="Sun") {
 // It is Saturday or Sunday. Return Tuesday.
 $display=date('l jS F', strtotime(date("Y-m-d").' +2 Weekday'));
echo 1;
}
elseif(date("d/m/Y")=="01/05/2014" && $time>="16") {
 // Thursday before Bank Holiday, delivery on Tuesday.
 $display=date('l jS F', strtotime(date("Y-m-d").' +3 Weekday'));
echo 1.5;
}
elseif(date("d/m/Y")=="02/05/2014" && $time>="17") {
 // May Day Bank Holiday after 5pm. Return Wednesday.
 $display=date('l jS F', strtotime(date("Y-m-d").' +3 Weekday'));
echo 2;
}
elseif(date("d/m/Y")=="02/05/2014" && $time<"17") {
 // May Day Bank Holiday before 5pm. Return Tuesday.
 $display=date('l jS F', strtotime(date("Y-m-d").' +2 Weekday'));
echo 3;
}
elseif($time>="17" && $day=="Fri") {
 // It's a Friday after 17:00. Return Tuesday.
 $display=date('l jS F', strtotime(date("Y-m-d").' +2 Weekday'));
echo 4;
}
elseif($time<"17" && $day=="Fri") {
 // It's a Friday before 17:00. Return Monday.
 $display=date('l jS F', strtotime(date("Y-m-d").' +1 Weekday'));
echo 5;
}
elseif($time>"17") {
 // It is a weekday after 17:00. Return +2 days.
 $display=date('l jS F', strtotime(date("Y-m-d").' +2 Weekday'));
echo 6;
}
else {
 // It is a weekday before 17:00. Return +1 day.
 $display=date('l jS F', strtotime(date("Y-m-d").' +1 Weekday'));
echo 7;
}
?>

<div class="page-head tablet-show" style="text-align: center;"><strong><img src="/skin/frontend/default/theme453/images/free-shipping.png" alt="Free Delivery" width="40" height="24" />&nbsp; <span style="font-size: small;">Free Next Day Delivery <br> Order your inkjets and toners now for delivery on <?=$display;?></span> &nbsp;<strong><img src="/skin/frontend/default/theme453/images/free-shipping.png" alt="Free Delivery" width="40" height="24" /></strong></strong></div>
<div class="page-head tablet-hide" style="text-align: center;"><strong><img src="/skin/frontend/default/theme453/images/free-shipping.png" alt="Free Delivery" width="40" height="24" />&nbsp; <span style="font-size: small;">Free Next Day Delivery - Order your inkjets and toners now for delivery on <?=$display;?></span> &nbsp;<strong><img src="/skin/frontend/default/theme453/images/free-shipping.png" alt="Free Delivery" width="40" height="24" /></strong></strong></div>
<p>&nbsp;</p>

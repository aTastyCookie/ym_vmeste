<?php
date_default_timezone_set("Europe/Moscow");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo ('<checkOrderResponse performedDatetime="'.date("c")."\" \n");
echo ('code="0" invoiceId="'.$_POST['invoiceId'].'" ');
echo ('shopId="'.$_POST['shopId'].'"/>');
?>

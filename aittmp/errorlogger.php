<?php 
require_once('auth_ip_check.php');
require_once('auth.php');
if(!isset($_SESSION)) 
    session_start();
$_SESSION['autoupdate'] = 0;
if (isset($_POST['autoupdate']))
    $_SESSION['autoupdate'] = 1;
else
    $_SESSION['autoupdate'] = 0;

if (isset($_POST['log_path']))
    $sErrorPath = $_POST['log_path'];
else
    $sErrorPath = ini_get('error_log');
?>
<html>
<head>
<script type="text/JavaScript">
<!--
function timedRefresh(timeoutPeriod) {
	setTimeout("document.getElementById('form_error_log').submit();",timeoutPeriod);
}
//   -->
</script>
</head>
<body onload="<?php if ($sErrorPath && $_SESSION['autoupdate']): ?>JavaScript:timedRefresh(1000);<?php endif; ?>">
<form method="POST" id="form_error_log">
Error log path: <input length type="text" size="50" name="log_path" value="<?php echo $sErrorPath; ?>" /> Autoupdate: <input name="autoupdate" type="checkbox" value="1" <?php if ($_SESSION['autoupdate']) echo "checked"; ?> />
<input value="START LOG ERRORS" type="submit" />
</form>
<hr />
<?php

$n = ( isset($_REQUEST['n']) == true )? $_REQUEST['n']:60;

$offset = -$n * 120;

if ($sErrorPath)
{
    $rs = fopen($sErrorPath,'r');
    if ( $rs === false )
    {
        echo "Cannot open file :(";
        die();
    }
    fseek($rs,$offset,SEEK_END);
    
    fgets($rs);
    $buffer = '';
    while(!feof($rs))
    {
        $buffer = fgets($rs)."<hr />".$buffer;
    }
    echo $buffer;
    fclose($rs);
}
?>

</body>
</html>
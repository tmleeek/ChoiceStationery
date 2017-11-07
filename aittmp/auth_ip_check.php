<?php
function getClientIp()
{
    $realip = 'none';
    
    if(isset($HTTP_SERVER_VARS))
    {
        if(isset($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]))
        {
            $realip .= $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
        }
        if(isset($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]))
        {
            $realip .= $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
        }
        if(isset($HTTP_SERVER_VARS["REMOTE_ADDR"]))
        {
            $realip .= $HTTP_SERVER_VARS["REMOTE_ADDR"];
        }
    }
    else
    {
        if(getenv('HTTP_X_FORWARDED_FOR'))
        {
            $realip .= getenv('HTTP_X_FORWARDED_FOR');
        }
        if (getenv('HTTP_CLIENT_IP'))
        {
            $realip .= getenv('HTTP_CLIENT_IP');
        }
        if(getenv('REMOTE_ADDR'))
        {
            $realip .= getenv('REMOTE_ADDR');
        }
    }

    return $realip;
}

$client_ip = getClientIp();

$ini = parse_ini_file('config.php', true);
if (empty($ini['settings']))
    $ini['settings'] = array();

$is_ip_allowed = false;

if (isset($ini['ait_allowed_ips']))
{
    $allowed_ips = $ini['ait_allowed_ips'];
    foreach($allowed_ips as $value)
    {
        if(@strstr($client_ip, $value))
        {
            $is_ip_allowed = true;
        }
    }
}

if(!$is_ip_allowed) die('Your ip is not allowed');
?>
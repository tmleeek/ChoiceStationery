<?php
function logout() {
    /* Empty the session data, except for the 'authenticated' entry which the
     * rest of the code needs to be able to check. */
    $_SESSION = array('authenticated' => false);
    unset($_SESSION['XSS']);
    

    /* Unset the client's cookie, if it has one. */
//    if (isset($_COOKIE[session_name()]))
//        setcookie(session_name(), '', time()-42000, '/');

    /* Destroy the session data on the server.  This prevents the simple
     * replay attach where one uses the back button to re-authenticate using
     * the old POST data since the server wont know the session then.*/
//    session_destroy();
}
 
function stripslashes_deep($value) {
    if (is_array($value))
        return array_map('stripslashes_deep', $value);
    else
        return stripslashes($value);
}

if (get_magic_quotes_gpc())
    $_POST = stripslashes_deep($_POST);
    
/* Initialize some variables we need again and again. */
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$nounce   = isset($_POST['nounce'])   ? $_POST['nounce']   : '';

/* Load the configuration. */
$ini = parse_ini_file('config.php', true);

if (empty($ini['settings']))
    $ini['settings'] = array();

session_start();

/* Delete the session data if the user requested a logout.  This leaves the
 * session cookie at the user, but this is not important since we
 * authenticates on $_SESSION['authenticated']. */
if (isset($_POST['logout']) || isset($_GET['logout']))
    logout();

/* Attempt authentication. */
if (isset($_SESSION['nounce']) && $nounce == $_SESSION['nounce'] && 
    isset($ini['users'][$username]) && isset($ini['ait_allowed_ips'])) 
{
    if (strchr($ini['users'][$username], ':') === false) 
    {
        // No seperator found, assume this is a password in clear text.
        $_SESSION['authenticated'] = ($ini['users'][$username] == $password);
    }
    else 
    {
        list($fkt, $salt, $hash) = explode(':', $ini['users'][$username]);
        $_SESSION['authenticated'] = ($fkt($salt . $password) == $hash);
    }
}


/* Enforce default non-authenticated state if the above code didn't set it
 * already. */
if (!isset($_SESSION['authenticated']))
    $_SESSION['authenticated'] = false;

if (!$_SESSION['authenticated']) 
    {
    /* Genereate a new nounce every time we preent the login page.  This binds
     * each login to a unique hit on the server and prevents the simple replay
     * attack where one uses the back button in the browser to replay the POST
     * data from a login. */
    $_SESSION['nounce'] = mt_rand();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>AITOC Service Tools</title>
    <link href="aitools.css" type="text/css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="title"><span class="aitoc">AITOC</span> <span class="tools">Service Tools</span></div>
    <div class="panel">
        <form name="shell" action="<?php print($_SERVER['PHP_SELF']) ?>" method="post">
        <fieldset>
          <div class="legend shadow-radius">Authentication</div>
          <?php
          if (!empty($username)) {
              echo "  <div class=\"error clear shadow-radius\">Login failed, please try again:</div>\n";
          }
          ?>
        
          <div class="label clear" for="username">Username:</div>
          <input class="input shadow-radius" name="username" id="username" type="text" value="<?php echo $username ?>" />
          <div class="label" for="password">Password:</div>
          <input class="input shadow-radius" name="password" id="password" type="password" />
          <input class="submit shadow-radius" type="submit" value="Login" />
          <input name="nounce" type="hidden" value="<?php echo $_SESSION['nounce']; ?>" />
        </fieldset>
        </form>
    </div>
    <script type="text/javascript">document.getElementById('username').focus();</script>
</body>
</html>
<?php die();
} 
?>
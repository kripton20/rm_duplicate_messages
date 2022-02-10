<?php

    /**
     * This small script makes it possible to test whether the 
     * RoundcubeLogin-class works as expected.
     *
     * Please find a detailed description of the available functions
     * in the original blog post:
     *
     * http://blog.philippheckel.com/2008/05/16/roundcube-login-via-php-script/
     * Updated July 2013
     */

    include "RoundcubeLogin.class.php";

    // Set to TRUE if something doesn't work
    $debug = true;
    //$rcPath = "/roundcubemail-0.9.2/";
    $rcPath = "http://localhost/rc147/";
    
    // Pass the relative Roundcube path to the constructor
    $rcl = new RoundcubeLogin($rcPath, $debug);
    // $rcl->setHostname("example.localhost");
    // $rcl->setPort(443);
    // $rcl->setSSL(true);

    try {
        // Perform login/logout action
        if ($_GET['action'] == "login")
            $rcl->login("your-email-address", "plain-text-password");
            
        else if ($_GET['action'] == "logout")
            $rcl->logout();
            
            
        // Get current status
        if ($rcl->isLoggedIn())
            $status = "We are logged in!";
        else
            $status = "We're NOT logged in.";
    }
    catch (RoundcubeLoginException $ex) {
        // If anything goes wrong, an exception is thrown.        
        $status = "ERROR: ".$ex->getMessage();
    }
        
    // Output / Controls
    echo "Status: $status<br />";
    echo "<a href='rclogin.php'>Status</a> - <a href='rclogin.php?action=login'>Login</a> - ";
    echo "<a href='rclogin.php?action=logout'>Logout</a><br />";
    
    $rcl->dumpDebugStack();
    
?>

<div id="eXTReMe">
    <a href="http://extremetracking.com/open?login=blogphil">
        <img
        src="http://t1.extreme-dm.com/i.gif" style="border: 0; visibility: hidden"
        height="38" width="41" id="EXim" alt="" />
    </a>
    <script type="text/javascript">
        <!--
        var EXlogin='blogphil' // Login
        var EXvsrv='s11' // VServer
        EXs=screen;EXw=EXs.width;navigator.appName!="Netscape"?
        EXb=EXs.colorDepth:EXb=EXs.pixelDepth;
        navigator.javaEnabled()==1?EXjv="y":EXjv="n";
        EXd=document;EXw?"":EXw="na";EXb?"":EXb="na";
        EXd.write("<img src=http://e2.extreme-dm.com",
            "/"+EXvsrv+".g?login="+EXlogin+"&amp;",
            "jv="+EXjv+"&amp;j=y&amp;srw="+EXw+"&amp;srb="+EXb+"&amp;",
            "l="+escape(EXd.referrer)+" height=1 width=1>");//-->
    </script>
</div>

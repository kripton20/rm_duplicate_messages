<?php
$fp = fsockopen("localhost", 80);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    $out = "GET / HTTP/1.1\r\n";
    $out .= "Host: localhost\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
    while (!feof($fp)) {
        $line1 = fgets($fp, 128);
        $line2 = fgets($fp, 4096);
        $line3 = fgets($fp);
        echo $line1;
        echo $line2;
    }
    fclose($fp);
}
?> 
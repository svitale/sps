</body>
</html>
<?php
print "<div>GLOBALS</div>";
print "sps";
print_r($GLOBALS['sps']);
print "id_study";
print_r($GLOBALS['id_study']);
print "<p>";
print "<div>Session Stuff</div>";
foreach ($_SESSION as $key=>$value) {
    print "<div>";
    print_r($key);
    print ":<br>";
    print_r($value);
    print "</div>";
print "<p>";
}
phpinfo();
?>

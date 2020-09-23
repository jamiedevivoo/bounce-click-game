<?php
function OpenCon()
 {
 $dbhost = "localhost";
 $dbuser = "devivoo_bounce";
 $dbpass = "bounceBounce";
 $db = "devivoo_bounce";
 $conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
 
 return $conn;
 }
 
function CloseCon($conn)
 {
 $conn -> close();
 }
   

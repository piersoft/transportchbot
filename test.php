<?php


include("getting.php");



$data=new getdata();

/*
$lat=41.1181;
$lon=16.86953;
$test=$data->get_stations($lat,$lon);
echo $test;
*/

$test1=$data->get_start("Prato Borgonuovo");
echo $test1;


$test2=$data->get_connection("Lecce","Modena","2015-10-30");
echo $test2;



?>

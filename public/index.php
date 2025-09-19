<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require dirname($_SERVER['DOCUMENT_ROOT']).'/system/Connect.php';

$connect = new Connect();

include dirname($_SERVER['DOCUMENT_ROOT']).'/html/page.php';
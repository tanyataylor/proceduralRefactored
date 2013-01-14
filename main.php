<?php

include('functions.php');
include('setters.php');

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors',1);

$xml = createXmlElement();

$dbCredentials = dbCredentials($xml);

$link = dbConnect($dbCredentials);

$dbSelected = dbSelect($dbCredentials,$link);

$header = displayHeader();

$productData = getProductData($dbSelected, $sortoption = 'null', $sortorder = 'null');

$sortData = dataSortForm($productData);

create_log_entry("Test function of create log");


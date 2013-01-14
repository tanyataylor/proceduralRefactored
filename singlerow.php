<?php

include('functions.php');
include('setters.php');

error_reporting();
ini_set('display_errors',1);

$xml = createXmlElement();

$dbCredentials = dbCredentials($xml);

$link = dbConnect($dbCredentials);

$dbSelected = dbSelect($dbCredentials,$link);

$header = displayHeader();

$singleRow = getSingleRow($dbSelected);

goBackToPage();







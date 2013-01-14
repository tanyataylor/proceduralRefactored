<?php

error_reporting(E_ALL|E_STRICT);
ini_set("display_errors",1);


function createXmlElement(){
    $xml = json_decode(json_encode(simpleXML_load_file('/var/www/magento/app/etc/local.xml','SimpleXMLElement', LIBXML_NOCDATA)),true);
    return $xml;
    //var_dump($xml);
}

function dbCredentials($xml){

    $contents = $xml['global']['resources']['default_setup']['connection'];
    $connectAttributes = array_slice($contents,0,4);
    $host = $connectAttributes['host'];
    $username = $connectAttributes['username'];
    $password = $connectAttributes['password'];
    $dbName = $connectAttributes['dbname'];
    $storeCredentials = array();
    $storeCredentials[] = $host;
    $storeCredentials[] = $username;
    $storeCredentials[] = $password;
    $storeCredentials[] = $dbName;
    return $storeCredentials;
}

function dbConnect($storeCredentials){

    $link = mysql_connect($storeCredentials[0], $storeCredentials[1], $storeCredentials[2]);
    if (!$link){
        create_log_entry("Error connecting to database: " . mysql_error());
        die("Could not connect : " . mysql_error());
    }
    /* Uncomment below if needed for the test! */
    //else {echo "Link was established.<br/>";}
    else {create_log_entry("Successfully connected to database");}
    return $link;
}


function dbSelect($storeCredentials, $link){

    $dbSelected = mysql_select_db($storeCredentials[3], $link);
    if(!$dbSelected){
        create_log_entry("Could not select a database: " . mysql_error());
        die('Can\'t use db : ' . mysql_error());
    }
    /* Uncomment below if needed for the test! */
    //else {echo "<br/>Database $storeCredentials[3] was selected.";}
    else {create_log_entry("Successfully selected database $storeCredentials[3]");}
    return $dbSelected;
}

function displayHeader(){
    ?>
    <table>
        <tr><th>SKU</th><th>Value</th><th>Website_Name</th></tr>

    <?php
}

function getProductData($dbSelected,$sortoption = 'null', $sortorder = 'null'){

    if(isset($_POST['sortoption'])){
        $sortoption = $_POST["sortoption"];
    }
    else $sortoption = "sku";

    if(isset($_POST['sortorder'])){
        $sortorder = $_POST["sortorder"];
    }
    else $sortorder = "asc";

    $sql = "SELECT catalog_product_entity.sku, catalog_product_entity_varchar.value, core_website.name
FROM catalog_product_entity
JOIN catalog_product_entity_varchar
ON catalog_product_entity.entity_id = catalog_product_entity_varchar.entity_id
JOIN catalog_product_website
ON catalog_product_website.product_id = catalog_product_entity.entity_id
JOIN core_website
ON catalog_product_website.website_id = core_website.website_id
WHERE catalog_product_entity_varchar.attribute_id = 96
ORDER BY {$sortoption}" . " {$sortorder}";

    if((isset($_POST['limit'])) AND $_POST['limit'] > 0){
        $limit = $_POST['limit'];
        $sql .= " limit 0, {$limit}";
    } else{
        $sql .= " limit 0, 30";
    }

    $result = mysql_query($sql);
    if(!$result){
        die("Invalid query : " . mysql_error());
    }
    /* Uncomment if needed for the test! */
    //else {echo "<br/>Success<br/>";}
    else { }

    while ($row = mysql_fetch_assoc($result)){
        echo $string = "<tr><td>" . '<a href="singlerow.php?sku=' .$row['sku'] . '">' . $row['sku'] . '</a>'.
            "</td><td>"  .$row['value'] .
            "</td><td>" . $row['name'] .
            "</td></tr>";
    }
    return $string;

}

function dataSortForm($string){
    ?>
    <html>
    <head>
        <title>SORT THE RESULTS</title>
    </head>
    <body>
    <form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <br/>
        List Logs: <a href='logs.php'>View Logs</a> &nbsp; &nbsp; &nbsp;
        Enter Limit:<input type="text" size="12" maxlength="3" name="limit"><br /><br />
        Sort Option: SKU<input type="radio" value="sku" name="sortoption">
        Value<input type="radio" value="value" name="sortoption"><br />
        Sort Order: ASC <input type="radio" value="ASC" name="sortorder">
        DSC<input type="radio" value="desc" name="sortorder"><br /><br />
        <input type="submit" value="Submit!" size="12" name="submit"><br /><br/>
    </form>
    </body>
    </html>
    <?php

}

function getSingleRow($dbSelected){

    $sql = "SELECT catalog_product_entity.sku, catalog_product_entity_varchar.value, core_website.name
FROM catalog_product_entity
JOIN catalog_product_entity_varchar
ON catalog_product_entity.entity_id = catalog_product_entity_varchar.entity_id
JOIN catalog_product_website
ON catalog_product_website.product_id = catalog_product_entity.entity_id
JOIN core_website
ON catalog_product_website.website_id = core_website.website_id
WHERE catalog_product_entity_varchar.attribute_id = 96 AND catalog_product_entity.sku = '" .$_GET['sku']. "'";

    $result = mysql_query($sql);
    if(!$result){
        die("Invalid query: " . mysql_error());
        }
    while ($row = mysql_fetch_assoc($result)){
        echo $string = "<tr><td>" .$row['sku'] .
            "</td><td>" .$row['value'] .
            "</td><td>" . $row['name'] .
            "</td></tr>";
    }


    return $string;
    }

function goBackToPage(){
    ?>
    </table>
    <a href="main.php">Go back to previous page</a>
        <?php
    }

function create_log_entry($str){
    $d = date("Y-m-d");
    $file_path = getcwd();
    $file_path .= "/logs/";
    $file_path .= $d;
    $file_path .= ".log";
    $file = fopen($file_path, 'a+') or die("cannot open the file");
    $stringData = date('H-i-s') . " : " . $_SERVER['REMOTE_ADDR'] . " : " . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . " : "
        . 'Session_id: ' . session_id() . "\n\t" . $str . "\n";
    fwrite($file, "$stringData");
    fclose($file);

}

?>




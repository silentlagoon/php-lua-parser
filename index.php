<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');
require_once(dirname(__FILE__) . '/Parser.php');

$parser = new Parser('test.lua');
$data = $parser->toArray();

foreach($data["WRATH_VOTE"] as $key=>$value) {
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}


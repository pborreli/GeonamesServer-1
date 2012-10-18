<?php
require_once __DIR__ . '/vendor/autoload.php';

use \ElasticSearch\Client;

include("vars.php");

function getElasticSearchUrl($url, $db, $collection,
			     $mongoDb, $mongoCollection) {
  if (substr($url, -1) != "/")
    $url .= "/";
  if (isset($db) && !empty($db))
    $url .= $db . "/";
  else
    $url .= $mongoDb . "/";
  if (isset($collection) && !empty($collection))
    $url .= $collection . "/";
  else
    $url .= $mongoCollection . "/";
  return ($url);
}

function getUrl($url, $db, $mongoDb) {
  if (substr($url, -1) != "/")
    $url .= "/";
  if (isset($db) && !empty($db))
    $url .= $db . "/";
  else
    $url .= $mongoDb . "/";
  return ($url);
}


try
{
  $m = new Mongo();
  $db = $m->$varMongoDbName;
}
catch (MongoConnectionException $e)
{
  echo 'Couldn\'t connect to mongodb, is the "mongo" process running?';
  exit();
}

echo "Connected to the database $varMongoDbName.\n";

$collection = $db->$varMongoCollectionName;

$cursor = $collection->find();
$i = 0;

$URL = getElasticSearchUrl($varElasticSearchUrl,
			   $varElasticSearchDB,
			   $varElasticSearchCollection,
			   $varMongoDbName,
			   $varMongoCollectionName);


$rootUrl = getUrl($varElasticSearchUrl,
		  $varElasticSearchDB,
		  $varMongoDbName);



$es = Client::connection($URL);
if (isset($es))
  {

    echo "Dropping current ElasticSearch index.\n";
    exec("sh ./deleteIndex.sh $rootUrl" );
    echo "Creating new index.\n";
    exec("sh ./createIndex.sh $rootUrl");
    echo "Setting geolocation parameters.\n";
    exec("sh ./setGeolocation.sh $URL"."_mapping $varMongoCollectionName");

    echo "\nIndexing...\n";
  
  foreach ($cursor as $obj)
      {
	$i++;
	$mongoid = $obj['_id'];
	
	unset($obj['_id']);
	$es->index($obj, $i);
      }
  
    $es->refresh();

    echo "$i entries proccessed.\n";
    exit();
  }
echo "Couldn\'t connect to the ElasticSearch database.\n";
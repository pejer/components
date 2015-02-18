<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2014-10-12 08:46
 */

ini_set('display_errors', '1');
error_reporting(-1);
date_default_timezone_set("Europe/Stockholm");
use \uppdragshuset\portal\data\directory\Account;
use \uppdragshuset\portal\data\directory\AccountQuery;

header("Access-Control-Allow-Origin: *", true);
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept", true);
header("Access-Control-Allow-Methods: POST, PUT, DELETE, GET", true);
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}
header('Content-type: application/json');

require '../vendor/autoload.php';
require '../propel/generated-conf/config.php';
set_include_path("../propel/generated-classes" . PATH_SEPARATOR . get_include_path());

if (!isset($_SERVER['PATH_INFO'])) {
    $_SERVER['PATH_INFO'] = '/';
}

$dataMap = array(
    "account" => array(
        'Id'       => 'id',
        'Name'     => 'name',
        'Isactive' => 'active',
        'created'  => array(
            'name'   => 'created',
            'access' => 'readonly'
        ),
        'updated'  => array(
            'name'   => 'updated',
            'access' => 'readonly'
        ),
    )
);

function mapColumnsToNames($model, $data)
{
    global $dataMap;
    if (!isset($dataMap[$model])) {
        return false;
    }
    $return = array();
    foreach ($dataMap[$model] as $field => $returnField) {
        $func = "get{$field}";
        $val  = $data->$func();
        switch (true) {
            case is_object($val):
                switch (get_class($val)) {
                    case 'DateTime':
                        $val = $val->format(\DateTime::W3C);
                        break;
                }
                break;
        }
        if( is_string($returnField) ){
            $return[$returnField] = $val;
        } else {
            $return[$returnField['name']] = $val;
        }

    }
    return $return;
}

function mapNamesToColumns($model, $data)
{
    global $dataMap;
    static $reversedFields;
    if (!isset($dataMap[$model])) {
        return false;
    }
    $return = array();
    if (!isset($reversedFields[$model])) {
        foreach($dataMap[$model] as $column => $nameValues){
            if ( is_string($nameValues) || $nameValues['access'] !== "readonly"){
                $reversedFields[$nameValues] = $column;
            }

        }
    }
    foreach ($reversedFields as $name => $column){
        if ( isset($data->{$name})) {
            $return[$column] = $data->{$name};
        }
    }
    return $return;
}

$parameters = explode('/', trim($_SERVER['PATH_INFO'], '/'));
switch ($parameters[0]) {
    default:
        $class       = "\\uppdragshuset\\portal\\data\\directory\\" . ucfirst($parameters[0]) . "Query";
        $objectClass = "\\uppdragshuset\\portal\\data\\directory\\" . ucfirst($parameters[0]);
        $requestBody = (file_get_contents('php://input'));

        if (!class_exists($class)) {
            throw new \RuntimeException("No data point");
        }
        if (isset($parameters[1])) {
            # UPDATE object
            $d       = $class::create();
            $results = $d->findPK($parameters[1]);
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $data = mapNamesToColumns($parameters[0], json_decode($requestBody));
                    $results->fromArray($data);
                    $results->save();
                    break;
                case 'DELETE':
                    $results->delete();
            }
            # $return = $results->toJson();
        } else {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') { # save new post!
                $object = new $objectClass();
                $data   = mapNamesToColumns($parameters[0], json_decode($requestBody));
                $object->fromArray($data);
                $object->save();
                header('Location: /account/' . $object->getId(), true, 201);
                $results = $object;
            } else {
                $results = $class::create()->find();
            }
        }
        if (isset($results)) {
            switch (true) {
                case is_a($results, '\Propel\Runtime\Collection\ObjectCollection'):
                    $return = array();
                    foreach ($results as $result) {
                        $return[] = mapColumnsToNames($parameters[0], $result);
                    }
                    $return = json_encode($return);
                    break;
                default:
                    $return = json_encode(mapColumnsToNames($parameters[0], $results));
                    break;
            }
        }

        die($return);
        break;
}
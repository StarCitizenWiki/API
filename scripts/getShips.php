<?php
/**
 * @Author: Hannes
 * @Date:   2015-03-11 16:32:31
 * @Last Modified by:   Hannes
 * @Last Modified time: 2015-04-25 22:46:55
 */
require_once basedir . '/lib/simple_html_dom.php';
$html = file_get_html( basedir . '/resources/html/ships.html' );
$ships = $html->find('.ship');
$shipsNew = array();
$name2id = array();
foreach( $ships as $id => $ship ){
    $shipsNew[$id] = trim( $ship->find( '.titlecontainer .title p', 0 )->plaintext );
    asort($shipsNew);
    $name2id[strtolower(str_replace(' ', '', trim( $ship->find( '.titlecontainer .title p', 0 )->plaintext )))] = $id;
}

header('Content-Type: application/json');
$id = false;
if (isset($match['params']['id']))
{
    if (is_numeric($match['params']['id']))
    {
        $id = $match['params']['id'];
    }
    else
    {
        if (isset($name2id[strtolower(str_replace(' ', '', urldecode($match['params']['id'])))]))
        {
            $id = $name2id[strtolower(str_replace(' ', '', urldecode($match['params']['id'])))];
        }
    }
}

if (isset($_POST['id']))
{
    if (is_numeric($_POST['id']))
    {
        $id = $_POST['id'];
    }
    else
    {
        if (isset($name2id[strtolower(str_replace(' ', '', urldecode($_POST['id'])))]))
        {
            $id = $name2id[strtolower(str_replace(' ', '', urldecode($_POST['id'])))];
        }
    }
}

$shipArray = array();
foreach( $ships as $ship ){
    $tmpArray = array();
    $tmpArray["ship_name"]  = trim( $ship->find( '.titlecontainer .title p', 0 )->plaintext );
    $tmpArray["ship_thumb"] = trim( $ship->find( '.shipimg img', 0 )->src );
    $tmpArray["ship_manufacturer"]  = trim( $ship->find( '.manufacturer p', 0 )->plaintext );
    $tmpArray["ship_role"]  = trim( $ship->find( '.role p', 0 )->plaintext );
    if( strpos( $tmpArray["ship_role"], '/' ) !== false ){
        $tmpArray["ship_role"] = array_map( 'trim', explode( '/', $tmpArray["ship_role"] ) );
    }else{
        $tmpArray["ship_role"] = array( 1 => $tmpArray["ship_role"] );
    }
    $tmpArray["ship_description"]   = trim( $ship->find( '.description p', 0 )->plaintext );
    $tmpArray["ship_measurements"]  = array(
        "length"  => str_replace( '.', ',' , trim( str_replace( 'm', '', $ship->find( '.length p', 0 )->plaintext ) ) ),
        "height"  => str_replace( '.', ',' , trim( str_replace( 'm', '', $ship->find( '.height p', 0 )->plaintext ) ) ),
        "beam"    => str_replace( '.', ',' , trim( str_replace( 'm', '', $ship->find( '.beam p', 0 )->plaintext ) ) ),
        "mass"    => str_replace( ',', '.' , substr( trim( str_replace( 'Kg', '', $ship->find( '.mass p', 0 )->plaintext ) ), 0, -3 ) )
    );
    $tmpArray["ship_structural_stats"]  = array(
        "cargocapacity"                 => trim( str_replace( ' freight units', '', $ship->find( '.cargocapacity p', 0 )->plaintext ) ),
        "crew"                          => trim( str_replace( array( 'persons', 'person' ), '', $ship->find( '.maxcrew p', 0 )->plaintext ) ),
        "max_powerplant"                => trim( $ship->find( '.maxpowerplant p', 0 )->plaintext ),
        "factory_powerplant"            => trim( $ship->find( '.factorypowerplant p', 0 )->plaintext ),
        "max_primarythruster"           => trim( $ship->find( '.maxprimarythruster p', 0 )->plaintext ),
        "factory_thruster"              => trim( $ship->find( '.factorythruster p', 0 )->plaintext ),
        "maneuveringthrusters"          => trim( $ship->find( '.maneuveringthrusters p', 0 )->plaintext ),
        "max_maneuveringthrusters"      => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.maneuveringthrusters p', 0 )->plaintext ) ), -3 ),
        "factory_maneuveringthrusters"  => trim( $ship->find( '.factorymaneuveringthrusters p', 0 )->plaintext ),
        "max_shield"                    => trim( $ship->find( '.maxshield p', 0 )->plaintext ),
        "factory_shield"                => trim( $ship->find( '.shield p', 0 )->plaintext ),
    );
    $tmpArray["ship_hardpoints"]  = array(
        "class1"        => trim( substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c1', 0 )->plaintext ) ), 3 ) ),
        "class1_count"  => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c1', 0 )->plaintext ) ), 0, 2 ),
        "class2"        => trim( substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c2', 0 )->plaintext ) ), 3 ) ),
        "class2_count"  => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c2', 0 )->plaintext ) ), 0, 2 ),
        "class3"        => trim( substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c3', 0 )->plaintext ) ), 3 ) ),
        "class3_count"  => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c3', 0 )->plaintext ) ), 0, 2 ),
        "class4"        => trim( substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c4', 0 )->plaintext ) ), 3 ) ),
        "class4_count"  => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c4', 0 )->plaintext ) ), 0, 2 ),
        "class5"        => trim( substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c5', 0 )->plaintext ) ), 3 ) ),
        "class5_count"  => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c5', 0 )->plaintext ) ), 0, 2 ),
        "class6"        => trim( substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c6', 0 )->plaintext ) ), 3 ) ),
        "class6_count"  => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c6', 0 )->plaintext ) ), 0, 2 ),
        "class7"        => trim( substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c7', 0 )->plaintext ) ), 3 ) ),
        "class7_count"  => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c7', 0 )->plaintext ) ), 0, 2 ),
        "class8"        => trim( substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c8', 0 )->plaintext ) ), 3 ) ),
        "class8_count"  => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c8', 0 )->plaintext ) ), 0, 2 ),
        "class9"        => trim( substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c9', 0 )->plaintext ) ), 3 ) ),
        "class9_count"  => substr( trim( preg_replace('/\s+/', ' ', $ship->find( '.hp-c9', 0 )->plaintext ) ), 0, 2 ),
    );
    $tmpArray["ship_additions"]  = array(
        "storage"  => trim( preg_replace('/\s+/', ' ', $ship->find( '.modularcontainer .statbox', 3 )->plaintext ) ),
        "other"    => trim( preg_replace('/\s+/', ' ', $ship->find( '.modularcontainer .statbox', 4 )->plaintext ) ),
    );
    $tmpArray["ship_link"] = 'https://robertsspaceindustries.com' . $ship->find( '.actionscontainer .statbox a', 0 )->href;
    $shipArray[] = $tmpArray;
}
if (!isset($shipArray[$id]))
{
    $id = false;
}
if ($id) {
    die(json_encode($shipArray[$id], JSON_PRETTY_PRINT));
}
die(json_encode($shipArray, JSON_PRETTY_PRINT));

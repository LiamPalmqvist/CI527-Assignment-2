<?php
    // an array with data about places (typically from a database)
    $places = array(
        array('name'=>'My Place',
            'description'=>'This is a great place',
            'lat'=> 0.35656765765,
            'lon'=> 0.76576576576),
        array('name'=>'Some Place',
            'description'=>'Not so great but cheap',
            'lat'=> 0.75656765765,
            'lon'=> 0.16576576576),
        array('name'=>'Other Place',
            'description'=>'Been here a lot, like it',
            'lat'=> 0.15656765765,
            'lon'=> 0.96576576576)
    );

    // return as JSON
    header('Content-type: application/json; charset=UTF-8');
    echo '{"places":'.json_encode($places, JSON_PRETTY_PRINT)."}";
?>
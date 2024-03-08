<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<?php

$jsonPlain = file_get_contents("Streaming_History.json");
$json = json_decode($jsonPlain, false);

if ($json === null) {
    echo "Error decoding JSON!";
} else {
    // Extract playtime from JSON data and calculate the sum
    $playtimeSum = array_reduce($json, function($carry, $item) {
        return $carry + $item->ms_played;
    }, 0);
    
    // Output total playtime
    echo "Total playtime: " . ($playtimeSum/3600000) . " hours";
}
?>  
</html>
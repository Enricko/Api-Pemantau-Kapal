<?php

$serverIp = '8.215.38.33';  // Change this to your Laravel server's IP address
$serverPort = 8000;      // Change this to the Laravel server's port

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if (!$socket) {
    die('Unable to create socket');
}

if (!socket_connect($socket, $serverIp, $serverPort)) {
    die('Unable to bind socket');
}

$gps_quality = [
    'Fix not valid','GPS fix',
    'Differential GPS fix (DGNSS), SBAS, OmniSTAR VBS, Beacon, RTX in GVBS mode',
    'Not applicable',
    'RTK Fixed, xFill',
    'RTK Float, OmniSTAR XP/HP, Location RTK, RTX',
    'INS Dead reckoning'
];
$accumulatedData = [];

while ($data = socket_recvfrom($socket, $buffer, 1024, 0, $clientIp, $clientPort)) {
    // $nmea = explode('|',$buffer);
    // $split = explode(',',$nmea[1]);
    // $route = "";

    // if ($split[0] == '$GPGGA') {
    //     $route = "insert_coor_GGA";
    // } elseif ($split[0] == '$GPHDT') {
    //     $route = "insert_coor_HDT";
    // }

    // if ($data === false) {
    //     die('Error receiving data');
    // }

    // $url = "http://$serverIp:$serverPort/api/$route";
    // $dataToSend = ['nmea_data' => $buffer];

    // // Form data
    // $formData = [
    //     'call_sign' => $nmea[0],
    //     'coor' => $nmea[1],
    // ];

    // // Encode form data as URL-encoded string
    // $formDataEncoded = http_build_query($formData);

    // $options = [
    //     'http' => [
    //         'method' => 'POST',
    //         'header' => [
    //             'Content-Type: application/x-www-form-urlencoded',
    //             'Content-Length: ' . strlen($formDataEncoded),
    //         ],
    //         'content' => $formDataEncoded,
    //     ],
    // ];

    // $context = stream_context_create($options);
    // $result = file_get_contents($url, false, $context);

    // if ($result === false) {
    //     die('Error sending data to Laravel');
    // }

    echo "Sent data: $buffer\n";
}

<?php

// PHP Errors
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// ---------- Functions ----------

function chkId($int) {
  if (is_numeric($int) && $int > 0 && $int <= 4294967295) {
    return true;
  } else {
    return false;
  }
}

function nowPlus5Min() {
  return (new DateTimeImmutable())
    ->modify('+5 minutes')
    ->format('Y-m-d H:i');
}

function curlRequest($url) {
  //  Initiate curl
  $ch = curl_init();
  // Will return the response, if false it print the response
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // Set the url
  curl_setopt($ch, CURLOPT_URL,$url);
  // Execute
  $result = curl_exec($ch);
  // Closing
  curl_close($ch);

  // Will dump a json
  return json_decode($result, true);
}

function formatTime($dateTimeString) {
  try {
    $date = new DateTimeImmutable($dateTimeString);
  } catch (Exception) {
    return null;
  }

  return $date->format('H:i');
}

// -------------------------------

// Set header
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

// Get request handler
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Check parameter
  if (!isset($_GET['station']) || !chkId($_GET['station'])) {
    http_response_code(400); // Bad Request
    echo json_encode(array('error' => 'invalid station id given'));
    exit();
  }
  $param_station = $_GET['station'];

  // Query stationboard
  $stationId = $param_station;
  $departure = nowPlus5Min();
  $url = "https://transport.opendata.ch/v1/stationboard?id=".$stationId."&transportations=train&datetime=".urlencode($departure).'&limit=10';
  $result = curlRequest($url);

  if (isset($result["errors"])) {
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => $result["errors"][0]["message"]));
    exit();
  }

  if (!isset($result["stationboard"])) {
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'no departures from this station'));
    exit();
  }

  // Get random train
  $random_train = $result["stationboard"][array_rand($result["stationboard"])];

  // Build pass list
  $passList = array();
  foreach ($random_train["passList"] as $stop) {
    if ($stop["arrival"] != null) {
      array_push($passList, array(
        "id" => $stop["station"]["id"],
        "name" => $stop["station"]["name"],
        "arrival" => formatTime($stop["arrival"]),
      ));
    }
  }

  // Build output
  $output = array(
    "station_id" => $result["station"]["id"],
    "station_name" => $result["station"]["name"],
    "train_category" => $random_train["category"],
    "train_number" => $random_train["number"],
    "departure" => formatTime($random_train["stop"]["departure"]),
    "platform" => $random_train["stop"]["platform"],
    "to" => $random_train["to"],
    "passList" => $passList,
  );

  echo json_encode($output, JSON_UNESCAPED_UNICODE);
}

// EOF
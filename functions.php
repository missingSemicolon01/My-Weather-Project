<?php
function getWeather($city, $apiKey)
{
 $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . $apiKey .
  "&units=metric&lang=el";

 $context = stream_context_create([
  'http' => [
   'ignore_errors' => true
  ]
 ]);
 $response = @file_get_contents($url, false, $context);

 if ($response === false) {
  throw new Exception("Can't connect to the weather service. Please try again later.");
 }

 $data = json_decode($response, true);

 if (isset($data['cod']) && $data['cod'] == '404') {
  throw new Exception("The city '{$city}' was not found. Please try another name.");
 }

 return $data;
}
?>
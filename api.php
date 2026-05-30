<?php
// AJAX endpoint: takes a city, returns weather data as JSON (not HTML)
session_start();

require_once 'config.php';
require_once 'functions.php';

// Tell the browser we're sending JSON
header('Content-Type: application/json');

$city = isset($_GET['city']) ? trim($_GET['city']) : '';

// No city given
if ($city === '') {
 echo json_encode(['error' => 'Please enter a city.']);
 exit;
}

try {
 $weatherData = getWeather($city, $apiKey);

 // Decide the weather type from the description
 $desc = strtolower($weatherData['weather'][0]['description']);
 if (str_contains($desc, "snow")) {
  $weatherType = "snow";
 } elseif (str_contains($desc, "rain") || str_contains($desc, "drizzle")) {
  $weatherType = "rain";
 } elseif (str_contains($desc, "few") || str_contains($desc, "broken") || str_contains($desc, "scattered")) {
  $weatherType = "few";
 } elseif (str_contains($desc, "cloud")) {
  $weatherType = "clouds";
 } else {
  $weatherType = "clear";
 }

 // Local time + day/night
 $localTime = gmdate("H:i", $weatherData['dt'] + $weatherData['timezone']);
 $isDay = ($weatherData['dt'] > $weatherData['sys']['sunrise']
  && $weatherData['dt'] < $weatherData['sys']['sunset']);

 // Photo
 $cityPhoto = getCityPhoto($city, $unsplashKey);
 if ($cityPhoto) {
  $_SESSION['lastPhoto'] = $cityPhoto;
 }

 // Pick emojis depending on day/night
 if ($isDay) {
  $emoji = ['clear' => "☀️", 'clouds' => "☁️", 'rain' => "☔", 'snow' => "❄️", 'few' => "🌤️"];
 } else {
  $emoji = ['clear' => "🌙", 'clouds' => "☁️", 'rain' => "☔", 'snow' => "🌨️", 'few' => "🌙"];
 }
 $weatherIcon = $emoji[$weatherType] ?? "🌤️";

 // Send everything the front-end needs as JSON
 echo json_encode([
  'name' => $weatherData['name'],
  'country' => $weatherData['sys']['country'],
  'temp' => $weatherData['main']['temp'],
  'humidity' => $weatherData['main']['humidity'],
  'description' => $weatherData['weather'][0]['description'],
  'localTime' => $localTime,
  'weatherType' => $weatherType,
  'weatherIcon' => $weatherIcon,
  'emojiClear' => $emoji['clear'],
  'emojiClouds' => $emoji['clouds'],
  'emojiRain' => $emoji['rain'],
  'emojiSnow' => $emoji['snow'],
  'photo' => $cityPhoto
 ]);
} catch (Exception $e) {
 echo json_encode(['error' => $e->getMessage()]);
}
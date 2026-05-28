<?php

/**
 * Fetch current weather data for a city from the OpenWeatherMap API.
 *
 * @param  string $city    City name to look up
 * @param  string $apiKey  OpenWeatherMap API key
 * @return array           Decoded weather data as an associative array
 * @throws Exception       If the connection fails or the city isn't found
 */
function getWeather($city, $apiKey)
{
 $url = "https://api.openweathermap.org/data/2.5/weather"
  . "?q=" . urlencode($city)
  . "&appid=" . $apiKey
  . "&units=metric&lang=en";

 // ignore_errors lets us read the response body even when the API
 // returns an HTTP error status (like 404), instead of just getting false.
 $context = stream_context_create([
  'http' => [
   'ignore_errors' => true
  ]
 ]);

 $response = @file_get_contents($url, false, $context);

 // With ignore_errors on, false now means a real connection problem.
 if ($response === false) {
  throw new Exception("Can't connect to the weather service. Please try again later.");
 }

 $data = json_decode($response, true);

 // OpenWeatherMap reports an unknown city with "cod" = 404.
 if (isset($data['cod']) && $data['cod'] == '404') {
  throw new Exception("The city '{$city}' was not found. Please try another name.");
 }

 return $data;
}

/**
 * Fetch a background photo for a city from the Unsplash API.
 *
 * @param  string      $city         City name to search for
 * @param  string      $unsplashKey  Unsplash access key
 * @return string|null               Photo URL, or null if none was found
 */
function getCityPhoto($city, $unsplashKey)
{
 $url = "https://api.unsplash.com/search/photos"
  . "?query=" . urlencode($city)
  . "&client_id=" . $unsplashKey
  . "&per_page=1";

 $context = stream_context_create([
  'http' => ['ignore_errors' => true]
 ]);

 $response = @file_get_contents($url, false, $context);

 $data = json_decode($response, true);
 if ($data['total'] === 0) {
  return null;
 }

 // Return the first result's full-size image URL if it exists.
 if (isset($data['results'][0]['urls']['full'])) {
  return $data['results'][0]['urls']['full'];
 }

 return null;
}
/**
 * Fetch a background video for a city from the Pexels API.
 *
 * @param  string      $city       City name to search for
 * @param  string      $pexelsKey  Pexels API key
 * @return string|null             Video URL (.mp4), or null if none found
 */
function getCityVideo($city, $pexelsKey)
{
 $url = "https://api.pexels.com/videos/search"
  . "?query=" . urlencode($city)
  . "&per_page=1&orientation=landscape";

 // Pexels needs the API key in the Authorization header,
 // so we add it to the HTTP request via the stream context.
 $context = stream_context_create([
  'http' => [
   'header' => "Authorization: " . $pexelsKey,
   'ignore_errors' => true
  ]
 ]);

 $response = @file_get_contents($url, false, $context);

 if ($response === false) {
  return null;
 }

 $data = json_decode($response, true);

 // Look through the returned video files and pick an HD .mp4 link.
 if (isset($data['videos'][0]['video_files'])) {
  foreach ($data['videos'][0]['video_files'] as $file) {
   if ($file['width'] === 1920 && $file['file_type'] === 'video/mp4') {
    return $file['link'];
   }
  }
  // Fallback: just take the first file if no HD mp4 was found
  return $data['videos'][0]['video_files'][0]['link'] ?? null;
 }

 return null;
}
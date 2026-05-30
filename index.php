<?php
session_start();

// Load configuration (API keys) and helper functions
require_once 'config.php';
require_once 'functions.php';

// Initialize variables to null so they always exist,
// even on first page load before any search happens.
$weatherData = null;
$error = null;
$cityPhoto = null;
$weatherType = null;
$localTime = null;
$isDay = null;

// Emoji defaults (used in the title; overwritten on a real search)
$emojiClear = "☀️";
$emojiFew = "🌤️";
$emojiClouds = "☁️";
$emojiRain = "☔";
$emojiSnow = "❄️";
$weatherIcon = "🌤️";


// Only run a search when the user actually submitted a non-empty city
if (isset($_GET['city']) && !empty($_GET['city'])) {
  $city = trim($_GET['city']);

  try {
    // Fetch weather data and a matching background photo
    $weatherData = getWeather($city, $apiKey);

    // Decide which animation to show based on the description.
    // Order matters: check most "intense" conditions first (snow, rain)
    // then less intense (few clouds, many clouds), and default to clear.
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

    // Local time of the city (UTC time + the city's timezone offset).
    // We use gmdate() so the server's own timezone is never added.
    $localTime = gmdate("H:i", $weatherData['dt'] + $weatherData['timezone']);

    // Day if the current time is between sunrise and sunset.
    $isDay = ($weatherData['dt'] > $weatherData['sys']['sunrise']
      && $weatherData['dt'] < $weatherData['sys']['sunset']);
    $cityPhoto = getCityPhoto($city, $unsplashKey);
    // Pick weather emojis depending on day/night
    if ($isDay) {
      $emojiClear = "☀️";
      $emojiClouds = "☁️";
      $emojiRain = "☔";
      $emojiSnow = "❄️";
      $emojiFew = "🌤️";
    } else {
      $emojiClear = "🌙";
      $emojiClouds = "☁️";
      $emojiRain = "☔";
      $emojiSnow = "🌨️";
      $emojiFew = "🌙";
    }

    // Emoji shown in the title for the current weather type
    $weatherIcon = match ($weatherType) {
      'clear' => $emojiClear,
      'clouds' => $emojiClouds,
      'rain' => $emojiRain,
      'snow' => $emojiSnow,
      'few' => $emojiFew,
      default => "🌤️"
    };

    // Remember the photo and weather type so they persist
    // even if the user visits again without searching.
    if ($cityPhoto != null && $weatherType) {
      $_SESSION['lastPhoto'] = $cityPhoto;
      $_SESSION['lastWeather'] = $weatherType;
    }
  } catch (Exception $e) {
    $error = $e->getMessage();
  }
}

// Fallback: reuse the last searched photo/weather, or default to Athens
if (!$cityPhoto && !$weatherType) {
  $cityPhoto = $_SESSION['lastPhoto'] ?? getCityPhoto('Athens', $unsplashKey);
  $weatherType = $_SESSION['lastWeather'] ?? 'clear';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Weather App</title>
</head>

<body>

  <!-- Animated weather effects: sits above the photo, behind the card -->
  <?php if ($weatherType): ?>
    <div class="weather-effects">

      <?php if ($weatherType === "clear"): ?>
        <!-- Clear sky: moving sun/moon across the screen -->
        <div class="cloud" style="top: 0.5%; animation-duration: 53s;"><?= $emojiClear ?></div>
        <div class="cloud" style="top: 15%; animation-duration: 50s; animation-delay: -40s;"><?= $emojiClear ?></div>
        <div class="cloud" style="top: 55%; animation-duration: 50s; animation-delay: -25s;"><?= $emojiClear ?></div>
        <div class="cloud" style="top: 75%; animation-duration: 50s; animation-delay: -5s;"><?= $emojiClear ?></div>

      <?php elseif ($weatherType === "clouds"): ?>
        <!-- Overcast: multiple clouds drifting -->
        <div class="cloud" style="top: 15%; animation-duration: 30s;"><?= $emojiClouds ?></div>
        <div class="cloud" style="top: 35%; animation-duration: 45s; animation-delay: -15s;"><?= $emojiClouds ?></div>
        <div class="cloud" style="top: 55%; animation-duration: 38s; animation-delay: -25s;"><?= $emojiClouds ?></div>

      <?php elseif ($weatherType === "rain"): ?>
        <!-- Rainy: clouds + falling raindrops -->
        <div class="cloud" style="top: 10%; animation-duration: 35s;"><?= $emojiRain ?></div>
        <div class="cloud" style="top: 25%; animation-duration: 40s; animation-delay: -20s;"><?= $emojiRain ?></div>
        <div class="cloud" style="top: 78%; animation-duration: 58s; animation-delay: -5s;"><?= $emojiRain ?></div>

        <!-- Each raindrop gets a random position, delay, and speed -->
        <?php for ($i = 0; $i < 60; $i++): ?>
          <div class="raindrop"
            style="left: <?= rand(0, 100) ?>%; animation-delay: <?= rand(0, 20) / 10 ?>s; animation-duration: <?= rand(4, 9) / 10 ?>s;">
          </div>
        <?php endfor; ?>

      <?php elseif ($weatherType === "snow"): ?>
        <!-- Snowy: cloud + falling snowflakes -->
        <div class="cloud" style="top: 10%; animation-duration: 35s;"><?= $emojiSnow ?></div>

        <!-- Each snowflake gets a random position, delay, and speed -->
        <?php for ($i = 0; $i < 50; $i++): ?>
          <div class="snowflake"
            style="left: <?= rand(0, 100) ?>%; animation-delay: <?= rand(0, 30) / 10 ?>s; animation-duration: <?= rand(50, 100) / 10 ?>s;">
            ❄️</div>
        <?php endfor; ?>

      <?php elseif ($weatherType === "few"): ?>
        <!-- Few clouds: sun/moon + some clouds drifting -->
        <div class="cloud" style="top: 0.5%; animation-duration: 53s;"><?= $emojiClear ?></div>
        <div class="cloud" style="top: 15%; animation-duration: 50s; animation-delay: -40s;"><?= $emojiClear ?></div>
        <div class="cloud" style="top: 35%; animation-duration: 50s; animation-delay: -20s;"><?= $emojiClouds ?></div>
        <div class="cloud" style="top: 55%; animation-duration: 50s; animation-delay: -25s;"><?= $emojiClear ?></div>
        <div class="cloud" style="top: 75%; animation-duration: 50s; animation-delay: -5s;"><?= $emojiClouds ?></div>

      <?php endif; ?>

    </div>
  <?php endif; ?>

  <!-- Use the last searched photo as the page background -->
  <?php if ($_SESSION['lastPhoto']): ?>
    <style>
      body {
        background-image: url('<?= $_SESSION['lastPhoto'] ?>');
        background-size: cover;
        background-position: center;
      }
    </style>
  <?php endif; ?>

  <div class="container">
    <!-- Title with a weather emoji that changes based on conditions and day/night -->
    <h1 class="app-title"> Weather Now
    </h1>

    <form method="get" action="">
      <input type="text" name="city" placeholder="Enter city..." value="<?= htmlspecialchars($_GET['city'] ?? '') ?>"
        class="search-input">
      <button type="submit" class="search-button">Search</button>
    </form>

    <!-- Show error message if the search failed -->
    <?php if ($error): ?>
      <div class="error-message">
        <strong>Error:</strong> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Show results only when we have weather data -->
    <?php if ($weatherData): ?>
      <div class="weather-result">
        <h2>🏛️
          <?= htmlspecialchars($weatherData['name']) ?>,<?= htmlspecialchars($weatherData['sys']['country']) ?>
        </h2>
        <p class="local-time"><strong>
            🕓 Local Time:
          </strong> <?= htmlspecialchars($localTime) ?></p>
        <p class="temp"><strong>🌡️ Temperature:</strong> <?= htmlspecialchars($weatherData['main']['temp']) ?>°C</p>
        <p class="humidity"><strong>💧 Humidity:</strong> <?= htmlspecialchars($weatherData['main']['humidity']) ?>%</p>
        <p class="description"><strong>
            <?= $weatherIcon ?> Weather Description:
          </strong>
          <?= htmlspecialchars($weatherData['weather'][0]['description']) ?></p>
      </div>
    <?php endif; ?>
  </div>

</body>

</html>
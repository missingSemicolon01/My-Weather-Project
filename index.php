<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// On first load, show the last searched photo or default to Athens
$cityPhoto = $_SESSION['lastPhoto'] ?? getCityPhoto('Athens', $unsplashKey);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Weather App</title>
</head>

<body
  style="<?= $cityPhoto ? "background-image:url('" . $cityPhoto . "');background-size:cover;background-position:center;" : '' ?>">

  <!-- Animated weather effects (filled in by JavaScript) -->
  <div class="weather-effects" id="weatherEffects"></div>

  <div class="container">
    <h1 class="app-title">Weather Now</h1>

    <form id="searchForm" method="get" action="">
      <input type="text" name="city" id="cityInput" placeholder="Enter city..." class="search-input" autocomplete="off">
      <button type="submit" class="search-button">Search</button>
    </form>

    <!-- Error message (shown/hidden by JavaScript) -->
    <div class="error-message" id="errorBox" style="display:none;"></div>

    <!-- Weather results (filled in by JavaScript) -->
    <div class="weather-result" id="resultBox" style="display:none;">
      <h2 id="cityName"></h2>
      <p class="local-time"><strong>🕓 Local Time:</strong> <span id="localTime"></span></p>
      <p class="temp"><strong>🌡️ Temperature:</strong> <span id="temp"></span>°C</p>
      <p class="humidity"><strong>💧 Humidity:</strong> <span id="humidity"></span>%</p>
      <p class="description"><strong><span id="descIcon"></span> Weather Description:</strong> <span
          id="description"></span></p>
    </div>
  </div>

  <script>
    const form = document.getElementById('searchForm');
    const input = document.getElementById('cityInput');
    const errorBox = document.getElementById('errorBox');
    const resultBox = document.getElementById('resultBox');
    const effects = document.getElementById('weatherEffects');

    form.addEventListener('submit', async function (e) {
      e.preventDefault();                       // stop the normal page reload
      const city = input.value.trim();
      if (!city) return;

      try {
        const res = await fetch('api.php?city=' + encodeURIComponent(city));
        const data = await res.json();

        if (data.error) {
          errorBox.innerHTML = '<strong>Error:</strong> ' + data.error;
          errorBox.style.display = 'block';
          resultBox.style.display = 'none';
          return;
        }

        // Hide error, show results
        errorBox.style.display = 'none';
        resultBox.style.display = 'block';

        // Fill in the data
        document.getElementById('cityName').textContent = '🏛️ ' + data.name + ', ' + data.country;
        document.getElementById('localTime').textContent = data.localTime;
        document.getElementById('temp').textContent = data.temp;
        document.getElementById('humidity').textContent = data.humidity;
        document.getElementById('description').textContent = data.description;
        document.getElementById('descIcon').textContent = data.weatherIcon;

        // Update background photo (only if one was found)
        if (data.photo) {
          document.body.style.backgroundImage = "url('" + data.photo + "')";
          document.body.style.backgroundSize = "cover";
          document.body.style.backgroundPosition = "center";
        }

        // Update animated weather effects
        renderEffects(data);

      } catch (err) {
        errorBox.innerHTML = '<strong>Error:</strong> Something went wrong. Please try again.';
        errorBox.style.display = 'block';
      }
    });

    // Build the animated emoji/raindrops/snowflakes based on weather type
    function renderEffects(data) {
      let html = '';
      const type = data.weatherType;

      if (type === 'clear') {
        html += cloud(data.emojiClear, '0.5%', '53s', '0s');
        html += cloud(data.emojiClear, '15%', '50s', '-40s');
        html += cloud(data.emojiClear, '55%', '50s', '-25s');
        html += cloud(data.emojiClear, '75%', '50s', '-5s');

      } else if (type === 'clouds') {
        html += cloud(data.emojiClouds, '15%', '30s', '0s');
        html += cloud(data.emojiClouds, '35%', '45s', '-15s');
        html += cloud(data.emojiClouds, '55%', '38s', '-25s');

      } else if (type === 'rain') {
        html += cloud(data.emojiRain, '10%', '35s', '0s');
        html += cloud(data.emojiRain, '25%', '40s', '-20s');
        html += cloud(data.emojiRain, '78%', '58s', '-5s');
        for (let i = 0; i < 60; i++) {
          const left = Math.floor(Math.random() * 100);
          const delay = (Math.random() * 2).toFixed(1);
          const dur = (0.4 + Math.random() * 0.5).toFixed(1);
          html += '<div class="raindrop" style="left:' + left + '%;animation-delay:' + delay + 's;animation-duration:' + dur + 's;"></div>';
        }

      } else if (type === 'snow') {
        html += cloud(data.emojiSnow, '10%', '35s', '0s');
        for (let i = 0; i < 50; i++) {
          const left = Math.floor(Math.random() * 100);
          const delay = (Math.random() * 3).toFixed(1);
          const dur = (5 + Math.random() * 5).toFixed(1);
          html += '<div class="snowflake" style="left:' + left + '%;animation-delay:' + delay + 's;animation-duration:' + dur + 's;">❄️</div>';
        }

      } else if (type === 'few') {
        html += cloud(data.emojiClear, '0.5%', '53s', '0s');
        html += cloud(data.emojiClear, '15%', '50s', '-40s');
        html += cloud(data.emojiClouds, '35%', '50s', '-20s');
        html += cloud(data.emojiClear, '55%', '50s', '-25s');
        html += cloud(data.emojiClouds, '75%', '50s', '-5s');
      }

      effects.innerHTML = html;
    }

    // Helper to build one drifting emoji div
    function cloud(emoji, top, duration, delay) {
      return '<div class="cloud" style="top:' + top + ';animation-duration:' + duration + ';animation-delay:' + delay + ';">' + emoji + '</div>';
    }
  </script>

</body>

</html>
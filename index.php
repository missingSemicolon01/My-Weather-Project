<?php
require_once 'config.php';
require_once 'functions.php';
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '1');

$weatherData = null;
$error = null;  // 

if (isset($_GET['city']) && !empty($_GET['city'])) {
 $city = $_GET['city'];

 try {
  $weatherData = getWeather($city, $apiKey);
 } catch (Exception $e) {
  $error = $e->getMessage();
 }
} ?>

<!DOCTYPE html>
<html lang="el">

<body>
 <form method="get" action="">
  <input type="text" name="city" placeholder="Enter city">
  <button type="submit">Search</button> <br><br>
 </form>

 <?php if ($error): ?>
  <div style="color: red; border: 1px solid red; padding: 10px; margin: 10px 0;">
   <strong>Error:</strong> <?= htmlspecialchars($error) ?>
  </div>
 <?php endif; ?>

 <?php if ($weatherData): ?>
  <div style="border: 1px solid green; padding: 10px; margin: 10px 0;">
   <h2><?= $weatherData['name'] ?></h2>
   <p><strong>Temperature:</strong> <?= $weatherData['main']['temp'] ?>°C</p>
   <p><strong>Humidity:</strong> <?= $weatherData['main']['humidity'] ?>%</p>
   <p><strong>Description:</strong> <?= $weatherData['weather'][0]['description'] ?></p>
  </div>
 <?php endif; ?>
</body>

</html>
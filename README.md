# Weather App 🌤️

A simple weather application. You enter a city name and it shows you the current temperature, humidity, and weather description using the OpenWeatherMap API.

## Used

- PHP
- OpenWeatherMap API
- Composer (vlucas/phpdotenv)

## Project structure
├── index.php      - Main page with the form
├── config.php     - Loads API key from .env
├── functions.php  - getWeather() function
├── .env           - Your API key
└── vendor/        - Composer packages
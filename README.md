# Weather App 🌤️ (AJAX version)

This is a version of my weather app where the search works without
reloading the whole page. It uses JavaScript to fetch the data in the
background. I made this branch to try out AJAX after finishing the
main PHP version.

## What's different from main

- The page doesn't reload when you search
- JavaScript sends the request and updates only the parts that change
- The background and weather effects update smoothly
- The PHP part now returns JSON instead of a full HTML page (api.php)

## How it works

- `index.php` shows the page and has the JavaScript
- `api.php` takes the city and returns the weather data as JSON
- The JavaScript calls `api.php` with fetch() and fills in the page

## Built with

- PHP (backend / api.php)
- JavaScript (fetch for the live search)
- OpenWeatherMap API + Unsplash API
- Composer (vlucas/phpdotenv)

## How to run it

1. Clone the repo and switch to the `ajax-search` branch
2. Run `composer install`
3. Make a `.env` file: API_KEY=your_openweathermap_key,UNSPLASH_KEY=your_unsplash_key
4. Put it in XAMPP htdocs and open in the browser

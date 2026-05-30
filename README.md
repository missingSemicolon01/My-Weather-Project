# Weather App 🌤️

A weather app I built to learn PHP. You type a city and it shows the
current weather, the local time there, and a background photo of the city.

## What it does

- Search any city and get temperature, humidity and description
- Shows the local time of the city (not my own timezone)
- Background photo changes based on the city you searched
- Animated weather effects (sun, clouds, rain, snow) depending on the weather
- Different emoji for day and night
- Friendly error message when a city isn't found

## Built with

- PHP
- OpenWeatherMap API (weather data)
- Unsplash API (city photos)
- Composer (vlucas/phpdotenv for the API keys)
- Plain HTML/CSS

## How to run it

1. Clone the repo
2. Run `composer install`
3. Make a `.env` file with your own keys: API_KEY=your_openweathermap_key UNSPLASH_KEY=your_unsplash_key
4. Put it in your XAMPP htdocs folder and open it in the browser


## Notes

There's also an `ajax-search` branch where I tried making the search
work without reloading the page, using JavaScript.

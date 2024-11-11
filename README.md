Weather Report Application

This PHP application fetches weather data for top cities from the AccuWeather API, displays it in a table, and allows users to download the data as an Excel-like file. The report includes current weather conditions, temperatures, geographic data, and other details.

Features

- Fetches data for top cities, including current weather conditions, temperatures, and geographic information.
- Displays data in an HTML table format.
- Provides an option to download the data as an Excel-like file.
- Includes error handling for API and network issues.
- Can be scheduled as a weekly cron job for automatic data fetching.


Setup Instructions
1. Clone the Repository
  git clone https://github.com/creativeuidesigner/weatherapp.git
  cd weather-report-app

2. Get an API Key from AccuWeather
   Sign up at the AccuWeather API platform and create a new application to generate an API key.

3. Configure the API Key
   In index.php, replace 'your_api_key_here' with your actual AccuWeather API key:
    $apiKey = 'your_api_key_here'; // Replace with your API key
   
4. Run the Application
    Deploy this code on your web server or local PHP environment. Access index.php in your browser to view the top cities' weather data.
     - Display Data: The weather data is displayed in a table format on the page.
     - Download Excel: Use the "Download Excel" button to download the data in a tab-separated format.


Setting Up a Cron Job
To automate this report on a weekly basis, you can set up a cron job that runs index.php weekly.

 1. Navigate to your server's crontab:
  - crontab -e

 2. Add the following cron job:
    0 7 * * MON /usr/bin/php /path/to/your/project/index.php




Error Handling
- HTTP Errors: If there’s an issue with the AccuWeather API or an invalid response, the application will display an error message.
- cURL and JSON Errors: The application handles cURL and JSON decoding errors to ensure reliable data fetching.

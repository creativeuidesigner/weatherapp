<?php
// Define API Key
$apiKey = '*********************';//Your Api Key


$getNumberOfCities = 2; // Number of cities to fetch

// Custom exception handler
function exception_handler($exception) {
    echo "Error: " . $exception->getMessage();
    exit;
}

// Set the exception handler
set_exception_handler('exception_handler');

// Helper function to make an HTTP request with status code checking
function fetchData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // Get headers as well as body
    
    $response = curl_exec($ch);
    
    // Separate headers and body
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    // Check for cURL error
    if (curl_errno($ch)) {
        throw new Exception("cURL error: " . curl_error($ch));
    }

    // Check HTTP status code
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status_code >= 400) {
        throw new Exception("HTTP error $status_code: Failed to fetch data from $url" ."<BR>".$body);
    }

    // Decode JSON body
    $responseData = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding JSON response.");
    }

    return $responseData;
}

// Fetch top cities data with error handling
function getCities($number) {
    global $apiKey;
    $locationUrl = "http://dataservice.accuweather.com/locations/v1/topcities/$number?apikey=$apiKey";
    return fetchData($locationUrl); // Use fetchData function with status code check
}

// Fetch forecast for cities with error handling
function getCitiesForecast($citiesData) {
    global $apiKey;
    global $getNumberOfCities;
    $citiesData = array_slice($citiesData, 0, $getNumberOfCities); // Limit to first 2 cities

    $finalResultData = [];

    foreach ($citiesData as $city) {
        try {
            $cityId = $city['Key'];
            $conditionUrl = "http://dataservice.accuweather.com/currentconditions/v1/$cityId?apikey=$apiKey";
            
            $condData = fetchData($conditionUrl)[0]; // Use fetchData with status check

            // Store data in final result array
            $finalResultData[] = [
                'name' => $city['EnglishName'],
                'country' => $city['Country']['EnglishName'],
                'region' => $city['Region']['EnglishName'],
                'timezone' => $city['TimeZone']['Name'],
                'rank' => $city['Rank'],
                'latitude' => $city['GeoPosition']['Latitude'],
                'longitude' => $city['GeoPosition']['Longitude'],
                'weather_text' => $condData['WeatherText'],
                'is_day_time' => $condData['IsDayTime'],
                'temperature_celsius' => $condData['Temperature']['Metric']['Value'],
                'temperature_fahrenheit' => $condData['Temperature']['Imperial']['Value']
            ];
        } catch (Exception $e) {
            echo "Error processing city " . $city['EnglishName'] . ": " . $e->getMessage() . "<br>";
        }
    }

    return $finalResultData;
}

// Main logic to get cities and weather data
try {
    $data = getCities(50); // Get top 50 cities
    $citiesData = getCitiesForecast($data); // Get weather data for first 2 cities
} catch (Exception $e) {
    echo "Error fetching data: " . $e->getMessage();
    exit;
}

// Function to generate and download Excel-like file with error handling
function downloadExcel($citiesData) {
    if (empty($citiesData)) {
        throw new Exception("No city data available to generate Excel file.");
    }

    // Create the Excel-like output in tab-separated format
    $output = "Name\tCountry\tRegion\tTimezone\tRank\tLatitude\tLongitude\tWeather\tDaytime\tTemp (°C)\tTemp (°F)\n";

    foreach ($citiesData as $city) {
        $output .= "{$city['name']}\t{$city['country']}\t{$city['region']}\t{$city['timezone']}\t{$city['rank']}\t";
        $output .= "{$city['latitude']}\t{$city['longitude']}\t{$city['weather_text']}\t";
        $output .= ($city['is_day_time'] ? 'Day' : 'Night') . "\t";
        $output .= "{$city['temperature_celsius']}\t{$city['temperature_fahrenheit']}\n";
    }

    // Set headers for download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="weather_report.xls"');
    header('Cache-Control: max-age=0');

    // Output the Excel content
    echo $output;
    exit;
}

// Handle the download request
if (isset($_POST['download_excel'])) {
    try {
        downloadExcel($citiesData); // Download the Excel-like file when the button is clicked
    } catch (Exception $e) {
        echo "Error generating the Excel file: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        .download-container {
            text-align: right;
            margin: 20px 0;
        }

        .download-container button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .download-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <h2>Top <?php echo $getNumberOfCities ?> Cities Weather Report</h2>

    <!-- Display Data in a Table -->
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Country</th>
                <th>Region</th>
                <th>Timezone</th>
                <th>Rank</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Weather</th>
                <th>Daytime</th>
                <th>Temp (°C)</th>
                <th>Temp (°F)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($citiesData as $city): ?>
            <tr>
                <td><?= $city['name'] ?></td>
                <td><?= $city['country'] ?></td>
                <td><?= $city['region'] ?></td>
                <td><?= $city['timezone'] ?></td>
                <td><?= $city['rank'] ?></td>
                <td><?= $city['latitude'] ?></td>
                <td><?= $city['longitude'] ?></td>
                <td><?= $city['weather_text'] ?></td>
                <td><?= $city['is_day_time'] ? 'Day' : 'Night' ?></td>
                <td><?= $city['temperature_celsius'] ?></td>
                <td><?= $city['temperature_fahrenheit'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Download Excel Button -->
    <div class="download-container">
        <form method="POST">
            <button type="submit" name="download_excel">Download Excel</button>
        </form>
    </div>

</body>
</html>

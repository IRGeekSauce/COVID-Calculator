<?php
//Get the current date in month/day/year format
$date = date("m/d/Y");
//Array of top countries and their ISO codes
$isos = array('usa', 'gb', 'it', 'es', 'fr', 'bra', 'be', 'de', 'mx', 'ir');
$countries = array('us', 'uk', 'italy', 'spain', 'france', 'brazil', 'belgium', 'germany', 'mexico', 'iran');
//init populations array
$populations = array();
//get populations from restcountries api
foreach($isos as $iso) {
    $pop_curl = curl_init();
    $pop_url = "https://restcountries.eu/rest/v2/alpha/$iso";
    curl_setopt($pop_curl, CURLOPT_URL, $pop_url);
    curl_setopt($pop_curl, CURLOPT_RETURNTRANSFER, true);
    $pop_resp = curl_exec($pop_curl);
    $pop_resp = json_decode($pop_resp, true);

    switch($iso) {
        case 'usa':
            $iso = 'us';
            break;
        case 'gb':
            $iso = 'uk';
            break;
        case 'it':
            $iso = 'italy';
            break;
        case 'es':
            $iso = 'spain';
            break;
        case 'fr':
            $iso = 'france';
            break;
        case 'bra':
            $iso = 'brazil';
            break;
        case 'be':
            $iso = 'belgium';
            break;
        case 'de':
            $iso = 'germany';
            break;
        case 'mx':
            $iso = 'mexico';
            break;
        case 'ir':
            $iso = 'iran';
            break;
    }

    $populations[$iso] = $pop_resp['population'];
    curl_close($pop_curl);
}

$result_array = array();

//Use API to gather covid death reports
//Add entire response objects to array
foreach($countries as $country) {
    $curl = curl_init();
    $url = "https://covid19api.io/api/v1/ReportsByCountries/$country";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($curl);
    $resp = json_decode($resp, true);
    $result_array[] = $resp;
    curl_close($curl);
}
$deaths = array();

//Grab inner array 'deaths' column and insert into new array
foreach($result_array as $inner_array) {
    foreach($inner_array as $report) {
        //$deaths[] = number_format($report['deaths']);
        $deaths[] = $report['deaths'];
    }
}
//Combine countries and deaths for new associative array (array(country=>deaths))
$combined = array_combine($countries, $deaths);
$merged = array_merge_recursive($combined, $populations);
$result = array();

//Sort array in descending order and maintain index association
arsort($combined, SORT_NUMERIC);
echo "Total COVID-19 Deaths as of $date (Sorted Descending)";
echo "\n-------------------------------------------------------------------------\n";
foreach($combined as $key=>$item) {
    echo $key.": ".number_format($item)."\n";
}
echo "\n\n";
//Get percentages of country populations that are covid
foreach($merged as $key=>$item) {
    $x = ($item[0]/$item[1]) * 100;
    $x = intval($x * 1e4)/1e4;
    $result[] = array('country'=>$key, 'percent'=>$x);
}
//Get the percentage column and sort them
$columns = array_column($result, 'percent');
array_multisort($columns, SORT_DESC, $result);

echo "Percentage of Population Killed by COVID-19 as of $date (Sorted Descending)";
echo "\n-------------------------------------------------------------------------\n";
foreach($result as $item) {
    echo $item['country'].": ".$item['percent']."%\n";
}

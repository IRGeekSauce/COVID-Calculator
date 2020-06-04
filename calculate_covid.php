<?php
//Array of top countries 
$countries = array('us', 'uk', 'italy', 'spain', 'france', 'brazil', 'belgium', 'germany', 'mexico', 'iran');

//Their populations
$populations = array("us"=>332639102, "uk"=>67886011, "italy"=>60461826, "spain"=>46754778, "france"=>65273511,
    "brazil"=>212559417, "belgium"=>11589623, "germany"=>83783942, "mexico"=>128932753, "iran"=>83992949);

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
        $deaths[] = $report['deaths'];
    }
}
//Combine countries and deaths for new associative array (array(country=>deaths))
$combined = array_combine($countries, $deaths);
$merged = array_merge_recursive($combined, $populations);
$result = array();

//Sort array in descending order and maintain index association
arsort($combined, SORT_NUMERIC);
echo "Total Deaths (Sorted Descending)";
echo "\n------------\n";
foreach($combined as $key=>$item) {
    echo $key.": ".$item."\n";
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

echo "Percentage of Population (Sorted Descending)";
echo "\n------------------------\n";
foreach($result as $item) {
    echo $item['country'].": ".$item['percent']."%\n";
}

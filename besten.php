<?php
$year = date("Y");
$curl = curl_init();
$requestType = "GET";
$url = "https://www.imdb.com/chart/moviemeter/";

curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_CUSTOMREQUEST => $requestType,
    CURLOPT_RETURNTRANSFER => true, // Diese Option sorgt dafür, dass die Antwort in $response gespeichert wird
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
));

$response = curl_exec($curl);
curl_close($curl);

if ($response === false) {
    die('Fehler beim Abrufen der URL');
}

// Verarbeiten Sie die Antwort, ohne sie direkt anzuzeigen
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($response);
$xpath = new DOMXPath($dom);

// Extrahieren der Platzierungen
$placementNodes = $xpath->query('//div[contains(@class, "sc-b8b74125-0 ilwIpP meter-const-ranking sc-d5ea4b9d-5 elIWdi cli-meter-title-header")]');
$placements = [];
foreach ($placementNodes as $i => $node) {
    $placements[] = $node->nodeValue;
}

// Extrahieren der Filmtitel
$titleNodes = $xpath->query('//a/h3[@class="ipc-title__text"]');
$movieTitles = [];
foreach ($titleNodes as $i => $node) {
    $movieTitles[] = $node->nodeValue;
}

// Zusammenführen der Platzierungen und Filmtitel
$movies = [];
for ($i = 0; $i < count($movieTitles); $i++) {
    $movies[] = [
        'placement' => $placements[$i] ?? 'N/A',
        'title' => $movieTitles[$i]
    ];
}

// Anzahl der gecrawlten Filme
$movieCount = count($movies);

// Ausgabe in einer Tabelle
$output = "<h1>Meistgesehene Filme</h1>\n";
$output .= "<h3>Wie von IMDb-Nutzern festgelegt</h3>\n";
$output .= "<h4>25 von 100</h4>\n";

$output .= "<table style='border: 1px solid black; border-collapse: collapse; font-family: verdana;'>\n";
$output .= "\t<thead>\n";
$output .= "\t\t<tr>\n";
$output .= "\t\t\t<th style='border: 1px solid black; padding: 4px;'>Platzierung</th>\n";
$output .= "\t\t\t<th style='border: 1px solid black; padding: 4px;'>Filmtitel</th>\n";
$output .= "\t\t</tr>\n";
$output .= "\t</thead>\n";
$output .= "\t<tbody>\n";

if (empty($movies)) {
    $output .= "\t\t<tr>\n";
    $output .= "\t\t\t<td colspan='2' style='border: 1px solid black; padding: 4px;'>Keine Ergebnisse gefunden</td>\n";
    $output .= "\t\t</tr>\n";
} else {
    foreach ($movies as $movie) {
        $output .= "\t\t<tr>\n";
        $output .= "\t\t\t<td style='border: 1px solid black; padding: 4px;'>" . htmlentities($movie['placement'], ENT_QUOTES, 'UTF-8') . "</td>\n";
        $output .= "\t\t\t<td style='border: 1px solid black; padding: 4px;'>" . htmlentities($movie['title'], ENT_QUOTES, 'UTF-8') . "</td>\n";
        $output .= "\t\t</tr>\n";
    }
}
$output .= "\t</tbody>\n";
$output .= "</table>\n";

echo $output;
echo "\n<br>\nHier die URL der Seite, die gecrawlt wurde: <a href='" . $url . "' target='_blank'>" . $url . "</a>";
echo "\n<br>\nAnzahl der gecrawlten Filme: " . $movieCount;

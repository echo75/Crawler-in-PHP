<?php

$curl = curl_init();
$requestType = "GET";
$url = "https://www.imdb.com/chart/boxoffice/";

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

// Extrahieren des Wochenenddatums
$weekendNode = $xpath->query('//div[@class="ipc-title__description"]');
if ($weekendNode->length > 0) {
    $weekendText = $weekendNode->item(0)->nodeValue;
} else {
    $weekendText = null;
    error_log("Wochenende nicht gefunden");
}

$nodes = $xpath->query('//a/h3[@class="ipc-title__text"]');
$output = '';
foreach ($nodes as $i => $node) {
    $movieTitles[] = $node->nodeValue;
}

//print_r($weekendText);
//print_r($movieTitles);

// Ausgabe in einer Tabelle
$tableHeader = "<table style='border: 1px solid black; border-collapse: collapse; font-family: verdana;'>\n";
$tableHeader .= "\t<thead>\n";
$tableHeader .= "\t\t<tr>\n";
$tableHeader .= "\t\t\t<th colspan='2' style='border: 1px solid black; padding: 4px;'>\n";
$tableHeader .= "\t\t\t\t" . htmlentities($weekendText ? $weekendText : 'Kein Wochenende angegeben', ENT_QUOTES, 'UTF-8') . "\n";
$tableHeader .= "\t\t\t</th>\n";
$tableHeader .= "\t\t</tr>\n";
$tableHeader .= "\t</thead>\n";
$tableHeader .= "\t<tbody>\n";

$tableFooter = "\t</tbody>\n";
$tableFooter .= "</table>\n";

$output = $tableHeader;
if (empty($movieTitles)) {
    $output .= "\t\t<tr>\n";
    $output .= "\t\t\t<td colspan='2' style='border: 1px solid black; padding: 4px;'>Keine Ergebnisse gefunden</td>\n";
    $output .= "\t\t</tr>\n";
} else {
    foreach ($movieTitles as $title) {
        $output .= "\t\t<tr>\n";
        $output .= "\t\t\t<td style='border: 1px solid black; padding: 4px;'>" . htmlentities($title, ENT_QUOTES, 'UTF-8') . "</td>\n";
        $output .= "\t\t</tr>\n";
    }
}
$output .= $tableFooter;

echo $output;
echo "\n<br>\nHier die URL der Seite, die gecrawlt wurde: <a href='" . $url . "' target='_blank'>" . $url . "</a>";

<?php
/* new code to parse JSON from tvmaze.com API */
$date = "";
$showtext = "";
$networktext = "";
$airtimes = array();
$networks = array();
$networks2 = array();
$networkshows = array();
$times = array();
$endtimes = array();
$shows = array();
$networkcount = 0;
$id = 0;
$json_url = file_get_contents("http://api.tvmaze.com/schedule");
$json_a = json_decode($json_url, true);

foreach ($json_a as $key => $val) {
    $airtimes[$id] = $val['airtime'];
    $date = $val['airdate'];
    $networks2[$val['show']['network']['name']] = $val['show']['network']['name'];
    $time = strtotime($val['airtime']);
    $endTime = date("H:i", strtotime('+'.$val['runtime'].' minutes', $time));
    $endtimes[$id] = $endTime;
    if ($val['summary'] != "") {
        $summary = $val['summary'];
    } else {
        $summary = $val['show']['summary'];
    }

    $showtext2 = "<event start=\"".$val['airtime']."\" end=\"".$endTime."\">
                    <title>".str_replace('&', '&amp;', $val['show']['name'])."</title>
                    <subtitle>Season ".$val['season']." Episode ".$val['number']."</subtitle>
                    <description><![CDATA[".$summary."]]></description>
                    <link>".$val['url']."</link>
                </event>";
    $shows[$id] = $showtext2;
    $showtext .= $showtext2;
    $networkshows[$val['show']['network']['name']] .= $showtext2;
    $id++;
}

foreach ($networks2 as $network2) {
    $network = "<location name=\"".str_replace('&', '&amp;', $network2)."\" subtext=\"\">
                    ".$networkshows[$network2]."
                </location>";
    $networktext .= $network;
    $networks[$networkcount] = $network;
    $networkcount++;
}

header('Content-Type: text/xml; charset=utf-8');
$finalxml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
<timetable start=\"".$airtimes[0]."\" end=\"".end($endtimes)."\" interval=\"2\" title=\"".$date."\">";
foreach($networks as $network) {
    $finalxml .= $network;
}
$finalxml .= "</timetable>";
echo $finalxml;
$fp = fopen('output.xml', "w");  
fwrite($fp, $finalxml);
fclose($fp);
?>
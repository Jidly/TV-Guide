<?php
/* Globals */
$showtext = "";
$timetext = "";
$networktext = "";
$date = "";
$timesave = null;
$networks = array();
$networks2 = array();
$times = array();
$timelines = array();
$timesindex = array();
$shows = array();
$daycount = 0;
$networkcount = 0;
$timecount = 0;
$startcount = 0;
$id = 0;
$lasthour = 0;
$lasthour2 = 0;

/* Grab RSS schedule feed from TVRage - accessing tvrage.com through an open Heroku CORS proxy since tvrage tends to ban IPs that make the same request too many times */
$xml = simpleXML_load_file("http://fast-fortress-4864.herokuapp.com/tvrage.com/myrss.php?show_network=1");

$date = $xml->channel->pubDate;

/* parse and reformat TVRage XML to channel PEAR custom XML format */
foreach ($xml->channel->item as $items) {
    $origtitle = $items->title;
    if (strpos($items->title,'-') !== false) {
        $fulltitle = $items->title;
        $link = $items->link;
        $description = $items->description;
        $ep = get_string_between($fulltitle, "(", ")");
        $show = get_string_between($fulltitle, "- ", " (");
        $episode = explode("x",$ep);
        $showtext2 = "<title>".str_replace('&', '&amp;', $show)."</title>
                    <subtitle>Season ".$episode[0]." Episode ".$episode[1]."</subtitle>
                    <description><![CDATA[".$description."]]></description>
                    <link>".$link."</link>";
        $shows[$id] = $showtext2;
        $showtext .= $showtext2;
        $id++;
		$fulltitle_2 = $items->title;
    	$network = get_string_between($fulltitle_2, "[", "]");
    	$ep_2 = get_string_between($fulltitle_2, "(", ")");
        if ($network !== "Netflix") {
            $networks2[$networkcount] = $network;
            $network = "<location name=\"".str_replace('&', '&amp;', $network)."\" subtext=\"\">
                            ".$timelines[$timecount-1]."
                            ".$shows[$networkcount]."
                        </event>
                        </location>";
            $networktext .= $network;
            $networks[$networkcount] = $network;
    		$networkcount++;
    	}
    }
     if (strpos($origtitle,'-') === false) {
        $time = $items->title;
        $tmp = explode(" ",$time);
        $selectedTime = $tmp[0].":00";
        $endTime = strtotime($selectedTime) + 3600;
        $time2 = explode(":",$tmp[0]);
        $time3 = $time2[0] + 10+3;
        $time4 = $time2[0] + 10+2;
        if ($time3 >= 23) {
            $time3 = 01;
        }
        $timeline = "<event start=\"".$time4.":".$time2[1]."\" end=\"".$time3.":".$time2[1]."\">";
        $timesindex[$timecount] = $time4.":".$time2[1];
        $times[$timecount] = $tmp[0];
        $timelines[$timecount] = $timeline;
        $timetext .= $timeline;
        $timecount++;
    }
}
header('Content-Type: text/xml; charset=utf-8');
$finalxml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
        <timetable start=\"".$timesindex[0]."\" end=\"\" interval=\"2\" title=\"".$date."\">";
foreach($networks as $network) {
    $finalxml .= $network;
}
$finalxml .= "</timetable>";
echo $finalxml;

/* Write parsed result to XML file for inclusion in main TV schedule page */
$fp = fopen('output.xml', "w");  
fwrite($fp, $finalxml);
fclose($fp);

function get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}
?>
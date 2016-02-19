<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link type="text/css" rel="stylesheet" href="style.css">
<link type="text/css" rel="stylesheet" href="batch.css">
</head>
<body>
<?php
error_reporting(E_ALL);
$servername = "172.25.111.66";
$username = "cmdb";
$password = "123qwe";
$dbname = "cmdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
} 
$conn->set_charset("utf8");
// lista środowisk
$sql = "select nazwa, baza from srodowisko " . ((isset($_GET["srodowisko"]))? " where nazwa = '". $_GET["srodowisko"] ."' and zewnetrzne = 0": " where zewnetrzne = 0");
$srodowiska = $conn->query($sql);
echo "<p>".$srodowisko["baza"]."</p></br>";
echo "<div><table class=\"confluenceTable\"><tbody><tr>";
$k = 0;
while($srodowisko = $srodowiska->fetch_assoc()) 
{
	$sql = "select 	ps.r_produkt_nazwa,
					pr.nazwa_pelna,
					pr.r_rodzic_nazwa,
					(select GROUP_CONCAT(nazwisko separator '<br>') from osoba o
					join produkt_osoba po on po.r_osoba_login = o.login
					where o.r_rola_nazwa = 'cm' and po.r_produkt_nazwa = pr.nazwa) cm,
					(select GROUP_CONCAT(nazwisko separator '<br>') from osoba o
					join produkt_osoba po on po.r_osoba_login = o.login
					where o.r_rola_nazwa = 'tester' and po.r_produkt_nazwa = pr.nazwa) tester,
					(select GROUP_CONCAT(nazwisko separator '<br>') from osoba o
					join produkt_osoba po on po.r_osoba_login = o.login
					where o.r_rola_nazwa = 'programista' and po.r_produkt_nazwa = pr.nazwa) prod,
                    (select GROUP_CONCAT(nazwisko separator '<br>') from osoba o
					join produkt_osoba po on po.r_osoba_login = o.login
					where o.r_rola_nazwa = 'projektant' and po.r_produkt_nazwa = pr.nazwa) proj,
                    (select GROUP_CONCAT(nazwisko separator '<br>') from osoba o
					join produkt_osoba po on po.r_osoba_login = o.login
					where o.r_rola_nazwa = 'analityk' and po.r_produkt_nazwa = pr.nazwa) analityk,
					h.nazwa host,
					s.domena,
					h.ip,
					h.ip_ks,
					w.port,
					srv.port_debug,
					CONVERT(a.adresy USING utf8) adresy,
                    CONVERT(a.polaczenia_do_bazy USING utf8) polaczenia,
                    (select hh.nazwa from host hh, wezel ww
						where
						hh.r_srodowisko_nazwa = h.r_srodowisko_nazwa and
						ww.r_host_nazwa = hh.nazwa
						and ww.menedzer = 1) dmgr
			from 
				produkt_serwer ps
				
				join produkt pr on pr.nazwa = ps.r_produkt_nazwa
				join serwer srv on srv.id = ps.r_serwer_id
				join wezel w on w.id = srv.r_wezel_id
				join host h on h.nazwa = w.r_host_nazwa
				join srodowisko s on s.nazwa = h.r_srodowisko_nazwa
				left join adresy_uslug a on a.r_produkt_serwer_id = ps.id
			where 
				h.r_srodowisko_nazwa = '" . $srodowisko["nazwa"]."'  
			order by 
				h.nazwa, ps.r_produkt_nazwa";
	$lista = $conn->query($sql);

	if ($lista->num_rows > 0)
	{
		if($k == 0)
		{
			$k = 1;
			echo "<tr>".
			"<th class=\"confluenceTh\">Środowisko</th>".
			"<th class=\"confluenceTh\">Produkt</th>".
			//"<th class=\"confluenceTh\">Nazwa</th>".
			"<th class=\"confluenceTh\">Host/IP/IP KS</th>".
			"<th class=\"confluenceTh\">Lista aplikacji</th>".
			//"<th class=\"confluenceTh\">Połączenia do bazy</th>".
			"<th class=\"confluenceTh\">Połączenia do bazy</th>".
			"<th class=\"confluenceTh\">Usługi</th>".
			"<th class=\"confluenceTh\">Logi</th>".
			"<th class=\"confluenceTh\">Port</th>".
			"<th class=\"confluenceTh\">Port debug</th>".
			//"<th class=\"confluenceTh\">Osoby</th>".
			"</tr>";
		}
		 $i=0;
		 while($row = $lista->fetch_assoc()) 
		 {
			 $guid = mt_rand(0, 1000000);
			 $produkt = ((is_null($row["r_rodzic_nazwa"]))?$row["r_produkt_nazwa"] : $row["r_rodzic_nazwa"]."-".$row["r_produkt_nazwa"]);
			 $aplikacje = ((is_null($row["r_rodzic_nazwa"]))?$row["r_produkt_nazwa"] : $row["r_rodzic_nazwa"]);
			 echo 
			 "<tr>".
			 	 (($i == 0)? "<th rowspan=". mysqli_num_rows($lista). " class=\"confluenceTh\">". $srodowisko["nazwa"] ."</th>": "").
				 "<th class=\"confluenceTh\">" . $produkt . "</th>".
				 //"<td class=\"confluenceTh\">". $row["nazwa_pelna"] ."</td>".				 
				 "<td class=\"confluenceTd\"><nobr>" . $row["host"].".".$row["domena"]."</nobr>".
				 "<br>". $row["ip"].
				 "<br>". $row["ip_ks"].
				 "<br><a href=\"https://" . $row["dmgr"] . ".pro.corp:9043/ibm/console/logon.jsp\" target=\"blank\">Konsola WAS</a>".
				 "</td>".
				 "<td class=\"confluenceTd\"><pre>". file_get_contents("http://p1-build/wsp_app_list/". strtolower($srodowisko["nazwa"]). "/" . strtolower($aplikacje). ".txt") ."</pre></td>".
				 //"<td class=\"confluenceTd\">" . $row["polaczenia"] . "</td>".
				 (($i == 0)? "<td rowspan=". mysqli_num_rows($lista). " class=\"confluenceTd\"><pre>" . file_get_contents("http://p1-build/wsp_app_list/". strtolower($srodowisko["nazwa"]). "/baza.html") . "</pre></td>": "").
				 "<td class=\"confluenceTd\">".
"<div id=\"expander-". $guid ."\" class=\"expand-container\">".
"<div id=\"expander-control-". $guid ."\" class=\"expand-control\">".
"<span class=\"expand-control-icon icon\">&nbsp;</span><span class=\"expand-control-text\">Pokaż usługi</span>".
"</div>".
"<div id=\"expander-content-". $guid ."\" class=\"expand-content expand-hidden\" style=\"display: none; opacity: 0;\">".
str_replace("type=''hidden' name=''aplikacja'","type=hidden",$row["adresy"]) .
"</div></div>".
				"</td>".
				 "<td class=\"confluenceTd\"><a href=http://". $row["host"].".pro.corp/logi-p1/SystemOut.log target=_blank>SystemOut.log</a></br>".
				 "<a href=http://". $row["host"].".pro.corp/logi-p1/p1.log target=_blank>P1.log</a></br>".
				 "<a href=http://". $row["host"].".pro.corp/logi-p1/logiUdo.log target=_blank>Udo.log</a></br>".
				 "<a href=http://". $row["host"].".pro.corp/logi-p1/logiAudytu.log target=_blank>Audyt</a></br>".
				 "<a href=http://". $row["host"].".pro.corp/logi-p1/p1_".strtolower($row["r_produkt_nazwa"]).".log target=_blank>Techniczne</a></br>".
				 "<a href=http://". $row["host"].".pro.corp/logi-p1/p1_wsp.log target=_blank>Wsp</a></td>".
				 "<td class=\"confluenceTd\">". $row["port"] ."</td>".
				 "<td class=\"confluenceTd\">". (($row["port_debug"] == "0")? "": $row["port_debug"]) ."</td>".
				 //"<td class=\"confluenceTd\">". "<b>CM:</b> ".$row["cm"]."<br><b>Testy:</b> " . $row["tester"] ."<br><b>Prod:</b> " . $row["prod"] . "</td>".
			"</tr>";
			$i++;
		 }
	}
}
echo "</tbody></table></div>";
$conn->close();
?>
</body>
</html>

<?php
set_time_limit(0);

$servername = "192.168.0.73";
$username = "root";
$password = "root";
$ocsdb = "ocsweb_test";
$glpidb = "glpi_test";
$i = 0;

$ocsconn = new mysqli($servername, $username, $password, $ocsdb);
$glpiconn = new mysqli($servername, $username, $password, $glpidb);

function getCurrentNumbers() {
	global $glpiconn;
	$sql = "SELECT COUNT(id) AS count FROM glpi_computers_softwareversions";
	$query = mysqli_query($glpiconn, $sql);
	$exec = mysqli_fetch_assoc($query);
	$count = round($exec['count'] / 6, 0);
	return $count;
}

$sql = "SELECT id,computers_id,softwareversions_id FROM glpi_computers_softwareversions WHERE date_install='0000-00-00' AND is_deleted_computer='0' LIMIT ".getCurrentNumbers()." OFFSET ".(getCurrentNumbers() * 2);

$q1 = mysqli_query($glpiconn, $sql);
while( $row = mysqli_fetch_assoc($q1)) {
	$computer_name = getComputerName($row['computers_id']);
	$software = getSoftware($row['softwareversions_id']);
	$software_id = $software[0];
	$software_version = $software[1];
	$software_name = getSoftwareName($software_id);
	// start ocs
	$ocs_hardwareId = getOCSHardwareId($computer_name);
	$ocs_softwares = getOCSSoftwares($ocs_hardwareId, $software_name, $software_version);
	//echo '<pre>';
	//print_r($ocs_softwares);
	//echo '</pre>';
	//$ocs_install_date = $ocs_softwares[0]['INSTALLDATE'];
	//echo '<hr>';
}
echo $i;


function getOCSSoftwares($ocs_hardwareId, $software_name, $software_version) {
	global $ocsconn, $i;
	$query = "SELECT * FROM softwares WHERE HARDWARE_ID = '".$ocs_hardwareId."' AND NAME = '".$software_name."' AND VERSION = '".$software_version."' AND INSTALLDATE != '0000-00-00 00:00:00'";
	$exec = mysqli_query($ocsconn, $query);
	$array = [];
	if(mysqli_num_rows($exec) > 0) {
		while($data = mysqli_fetch_assoc($exec)) {
			$array[] = $data;
			$i++;
		}
	}
	return $array;
}



function getOCSHardwareId($computer_name) {
	global $ocsconn;
	$query = "SELECT * FROM `hardware` WHERE `NAME` = '".$computer_name."'";
	$exec = mysqli_query($ocsconn, $query);
	$data = mysqli_fetch_assoc($exec);
	return $data['ID'];
}

function getSoftwareName($software_id) {
	global $glpiconn;
	$query = "SELECT id, name FROM glpi_softwares WHERE id = '".$software_id."'";
	$exec = mysqli_query($glpiconn, $query);
	$data = mysqli_fetch_assoc($exec);
	return $data['name'];
}

function getSoftware($softwareversion_id) {
	global $glpiconn;
	$query = "SELECT softwares_id, name FROM `glpi_softwareversions` WHERE id = '".$softwareversion_id."'";
	$exec = mysqli_query($glpiconn, $query);
	$data = mysqli_fetch_assoc($exec);
	$array = [$data['softwares_id'], $data['name']];
	return $array;
}

function getComputerName($comp_id) {
	global $glpiconn;
	$query = "SELECT id, name FROM glpi_computers WHERE id = '".$comp_id."'";
	$exec = mysqli_query($glpiconn, $query);
	$data = mysqli_fetch_assoc($exec);
	return $data['name'];
}
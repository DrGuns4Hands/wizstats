<?php



require_once 'includes.php';
$link = pg_pconnect("dbname=$psqldb user=$psqluser password='$psqlpass' host=$psqlhost");


if (isset($_GET["lastblockirc"])) {
	$sql = "select username,shares.time,height from shares left join users on user_id=users.id left join stats_blocks on shares.id=stats_blocks.orig_id where shares.server=7 and upstream_result=true and confirmations > 0 order by shares.id desc limit 1;";
	$result = pg_exec($link, $sql);
	$numrows = pg_numrows($result);
	$row = pg_fetch_array($result, 0);
	$username = $row["username"];
	print "ws001: $username\n\n";
	exit;
}

if (isset($_GET["poolhashrate"])) {
	$cppsrbjson = file_get_contents("/var/lib/eligius/$serverid/cppsrb.json");
	$cppsrbjsondec = json_decode($cppsrbjson,true);
	$globalcppsrb = $cppsrbjsondec[''];
	$my_shares = $globalcppsrb["shares"];
	if (!isset($_GET["numeric"])) {
		print "Insta-hashrate for $poolname - ";
		print "64 second: " . prettyHashrate(($my_shares[64] * 4294967296)/64) . " - ";
		print "128 second: " . prettyHashrate(($my_shares[128] * 4294967296)/128) . " - ";
		print "256 second: " . prettyHashrate(($my_shares[256] * 4294967296)/256);
	} else {
		print (($my_shares[256] * 4294967296)/256);
	}
	exit;
}

# TODO: Allow worker sub-names
if (!isset($_SERVER['PATH_INFO'])) {
        print "Error: Specify username in path\n";
        exit;
}


$givenuser = substr($_SERVER['PATH_INFO'],1,strlen($_SERVER['PATH_INFO'])-1);
$bits =  hex2bits(\Bitcoin::addressToHash160($givenuser));

$sql = "select id from public.users where keyhash='$bits' order by id asc limit 1";
$result = pg_exec($link, $sql);
$numrows = pg_numrows($result);
if (!$numrows) {
        print "Error: Username $givenuser not found in database.  Please try again later.";
        exit;
}

$row = pg_fetch_array($result, 0);
$user_id = $row["id"];


$cppsrbjson = file_get_contents("/var/lib/eligius/$serverid/cppsrb.json");
$cppsrbjsondec = json_decode($cppsrbjson,true);
$mycppsrb = $cppsrbjsondec[$givenuser];

$globalccpsrb = $cppsrbjsondec[""];

$latest_chunk = $globalccpsrb['share_log_top_chunk'];

#var_export ($globalccpsrb['share_log_top_chunk']);

#print "<PRE>";

#var_dump($mycppsrb);

$my_shares = $mycppsrb["shares"];
$my_share_log = $mycppsrb["share_log"];

#var_dump($my_shares);
#var_dump($my_share_log);

print "Insta-hashrate for $givenuser - ";
print "64 second: " . prettyHashrate(($my_shares[64] * 4294967296)/64) . " - ";
print "128 second: " . prettyHashrate(($my_shares[128] * 4294967296)/128) . " - ";
print "256 second: " . prettyHashrate(($my_shares[256] * 4294967296)/256);


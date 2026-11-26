<?php
// Import database.sql once if the schema is missing (runs at container start).
mysqli_report(MYSQLI_REPORT_OFF);
$h = getenv("MYSQLHOST") ?: "localhost";
$u = getenv("MYSQLUSER") ?: "root";
$p = getenv("MYSQLPASSWORD") ?: "";
$d = getenv("MYSQLDATABASE") ?: "e_lapor";
$port = (int)(getenv("MYSQLPORT") ?: 3306);
for ($i = 0; $i < 30; $i++) {
    $c = @mysqli_connect($h, $u, $p, null, $port);
    if ($c) break;
    sleep(2);
}
if (!$c) { fwrite(STDERR, "init-db: cannot connect, skipping\n"); exit(0); }
$r = @mysqli_query($c, "SELECT 1 FROM `$d`.users LIMIT 1");
if ($r) { echo "init-db: schema exists\n"; exit(0); }
if (mysqli_multi_query($c, file_get_contents(__DIR__ . "/database.sql"))) {
    do { if ($res = mysqli_store_result($c)) mysqli_free_result($res); } while (mysqli_next_result($c));
}
echo "init-db: imported (" . (mysqli_error($c) ?: "ok") . ")\n";

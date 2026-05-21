<?php
$c = new mysqli('localhost','root','','nbc');
if($c->connect_errno){ echo 'connect error: '.$c->connect_error.PHP_EOL; exit(1);} 
$c->set_charset('utf8');

$dateFr = '2026-05-13 00:00:00';
$dateTo = '2026-05-13 23:59:59';

$sqls = [
  "SELECT product_id, isload, active, COUNT(*) c FROM transactions WHERE dttm BETWEEN '$dateFr' AND '$dateTo' GROUP BY product_id, isload, active ORDER BY c DESC",
  "SELECT COALESCE(rfid_no,'(null)') rfid_no, COALESCE(member_name,'(null)') member_name, COUNT(*) c FROM transactions WHERE dttm BETWEEN '$dateFr' AND '$dateTo' GROUP BY rfid_no, member_name ORDER BY c DESC LIMIT 30",
  "SELECT DATE(dttm) d, COUNT(*) c, COUNT(DISTINCT COALESCE(rfid_no,'')) uniq_rfid FROM transactions WHERE dttm >= '2026-05-01' AND dttm < '2026-06-01' GROUP BY DATE(dttm) ORDER BY d DESC LIMIT 20"
];

foreach($sqls as $idx => $sql){
  echo "\n=== Q" . ($idx+1) . " ===\n";
  $r = $c->query($sql);
  if(!$r){ echo 'query error: '.$c->error.PHP_EOL; continue; }
  while($row = $r->fetch_assoc()){
    echo json_encode($row, JSON_UNESCAPED_SLASHES).PHP_EOL;
  }
}

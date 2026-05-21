<?php
$c = new mysqli('localhost','root','','nbc');
if($c->connect_errno){ echo 'connect error: '.$c->connect_error.PHP_EOL; exit(1);} 
$c->set_charset('utf8');

$dateFr = '2026-05-13 00:00:00';
$dateTo = '2026-05-13 23:59:59';

$queries = [
  'all_non_meal' => "SELECT COUNT(*) c FROM transactions WHERE dttm BETWEEN '$dateFr' AND '$dateTo' AND product_id NOT IN (1,2)",
  'all_non_meal_by_emp' => "SELECT COALESCE(rfid_no,'(null)') rfid_no, COALESCE(member_name,'(null)') member_name, COUNT(*) c FROM transactions WHERE dttm BETWEEN '$dateFr' AND '$dateTo' AND product_id NOT IN (1,2) GROUP BY rfid_no, member_name ORDER BY c DESC LIMIT 20",
  'isload0_active02' => "SELECT COUNT(*) c FROM transactions WHERE dttm BETWEEN '$dateFr' AND '$dateTo' AND product_id NOT IN (1,2) AND isload=0 AND active IN (0,2)",
  'isload0_active02_by_emp' => "SELECT COALESCE(rfid_no,'(null)') rfid_no, COALESCE(member_name,'(null)') member_name, COUNT(*) c FROM transactions WHERE dttm BETWEEN '$dateFr' AND '$dateTo' AND product_id NOT IN (1,2) AND isload=0 AND active IN (0,2) GROUP BY rfid_no, member_name ORDER BY c DESC LIMIT 20",
  'isload0_active_any' => "SELECT active, COUNT(*) c FROM transactions WHERE dttm BETWEEN '$dateFr' AND '$dateTo' AND product_id NOT IN (1,2) AND isload=0 GROUP BY active ORDER BY active",
  'unique_employees_isload0' => "SELECT COUNT(DISTINCT CONCAT(COALESCE(rfid_no,''),'|',COALESCE(member_name,''))) c FROM transactions WHERE dttm BETWEEN '$dateFr' AND '$dateTo' AND product_id NOT IN (1,2) AND isload=0"
];

foreach($queries as $name => $sql){
  echo "\n=== $name ===\n";
  $r = $c->query($sql);
  if(!$r){
    echo 'query error: '.$c->error.PHP_EOL;
    continue;
  }
  if($r->field_count === 1){
    $row = $r->fetch_assoc();
    echo json_encode($row, JSON_UNESCAPED_SLASHES).PHP_EOL;
  } else {
    while($row = $r->fetch_assoc()){
      echo json_encode($row, JSON_UNESCAPED_SLASHES).PHP_EOL;
    }
  }
}

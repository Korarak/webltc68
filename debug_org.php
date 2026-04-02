<?php
require 'www/condb/condb.php';

echo "Database Checking for Dept 10 and Mr. Surasak Rasee\n";
echo "--------------------------------------------------\n";

// 1. Check Department 10
$res = $mysqli3->query("SELECT id, department_name FROM department WHERE id = 10");
if ($row = $res->fetch_assoc()) {
    echo "Department [10] Name: [" . $row['department_name'] . "]\n";
} else {
    echo "Department [10] NOT FOUND\n";
}

// 2. Find Mr. Surasak Rasee
$search = "%สุรศักดิ์ ราษี%";
$query = "SELECT p.id, p.fullname, 
            GROUP_CONCAT(DISTINCT CONCAT(wb.workbranch_name, ' (', wl.work_level_name, ')') SEPARATOR '|||') AS exact_work_roles
          FROM personel_data p
          LEFT JOIN work_detail wd ON p.id = wd.personel_id
          LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id
          LEFT JOIN worklevel wl ON wd.worklevel_id = wl.id
          WHERE p.fullname LIKE ? 
          GROUP BY p.id";

$stmt = $mysqli3->prepare($query);
$stmt->bind_param("s", $search);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    echo "Person Found: " . $row['fullname'] . " (ID: " . $row['id'] . ")\n";
    echo "Exact Work Roles: " . $row['exact_work_roles'] . "\n";
} else {
    echo "Person Surasak Rasee NOT FOUND\n";
}
?>

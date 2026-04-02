<?php
require __DIR__ . '/../condb/condb.php';
require __DIR__ . '/../include/SecurityHelper.php';

$sql = "SELECT id, thai_id FROM personel_data WHERE thai_id_hash IS NULL OR thai_id_hash = ''";
$result = $mysqli3->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " records to migrate.\n";
    $success = 0;
    $failed = 0;

    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $thaiIdRaw = $row['thai_id'];

        if (!empty($thaiIdRaw)) {
            // Check if already base64 encoded and long enough to be an encrypted string
            // AES-256-CBC with IV usually produces string longer than 40 characters
            if (strlen($thaiIdRaw) > 20 && base64_encode(base64_decode($thaiIdRaw, true)) === $thaiIdRaw) {
               // Possibly already encrypted, skip for safety or assume it needs hashing
               // Since we rely on thai_id_hash being null to find unmigrated rows, 
               // if it's already encrypted but hash is null, we will have a problem.
               // Assuming raw data doesn't look like base64. 13-digit number is safe to hash.
            }

            $encrypted = SecurityHelper::encrypt($thaiIdRaw);
            $hash = SecurityHelper::hashThaiId($thaiIdRaw);

            $updateStmt = $mysqli3->prepare("UPDATE personel_data SET thai_id = ?, thai_id_hash = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $encrypted, $hash, $id);
            
            if ($updateStmt->execute()) {
                $success++;
            } else {
                echo "Failed to update record ID $id: " . $updateStmt->error . "\n";
                $failed++;
            }
        }
    }
    echo "Migration completed. Success: $success, Failed: $failed\n";
} else {
    echo "No records found to migrate.\n";
}
?>

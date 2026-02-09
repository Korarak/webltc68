<?php
// filepath: /home/adm1n_ltc/webltc67/www/admin/personel_update.php
include 'middleware.php';
session_start();
ob_start();
include '../condb/condb.php';

// ฟังก์ชันอัพโหลด base64 image
function uploadBase64Image($base64_string, $upload_path, $prefix = "profile_") {
    if (empty($base64_string)) {
        return null;
    }

    if (preg_match('/^data:image\/(\w+);base64,/', $base64_string, $type)) {
        $data = substr($base64_string, strpos($base64_string, ',') + 1);
        $type = strtolower($type[1]);

        if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
            return null;
        }

        $data = base64_decode($data);
        if ($data === false) {
            return null;
        }
    } else {
        return null;
    }

    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }

    $filename = $prefix . uniqid() . '.' . $type;
    $file_path = $upload_path . $filename;

    if (file_put_contents($file_path, $data)) {
        return $file_path;
    }

    return null;
}

// ฟังก์ชันลบไฟล์รูปภาพ
function deleteImageFile($file_path) {
    if (!$file_path) return false;
    
    // Check direct path
    if (file_exists($file_path)) {
        unlink($file_path);
        return true;
    }
    // Check central uploads path (../uploads/...)
    $central_path = "../" . $file_path;
    if (file_exists($central_path)) {
        unlink($central_path);
        return true;
    }
    return false;
}

// ตรวจสอบว่าเป็น AJAX request หรือไม่
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// ตรวจสอบว่า id ได้รับมาจากฟอร์มหรือไม่
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $personel_id = $_POST['id'];
    
    // --- ส่วนที่ปรับปรุง: รับค่า return_query (Query String ทั้งก้อน) ---
    $return_query = $_POST['return_query'] ?? '';
    
    // สร้าง URL กลับไปยังหน้า manage
    $return_url = 'personel_manage.php';
    if (!empty($return_query)) {
        // ถ้ามี Query String ให้ต่อท้าย URL
        $return_url .= '?' . $return_query;
    }

    // เก็บค่าจากฟอร์ม
    $form_data = [
        'fullname' => $_POST['fullname'] ?? '',
        'Tel' => $_POST['Tel'] ?: null,
        'E_mail' => $_POST['E_mail'] ?: null,
        'gender_id' => ($_POST['gender_id'] !== '-' && $_POST['gender_id'] !== '0' && $_POST['gender_id'] !== '') ? (int)$_POST['gender_id'] : null,
        'education_level_id' => ($_POST['education_level_id'] !== '-' && $_POST['education_level_id'] !== '0' && $_POST['education_level_id'] !== '') ? (int)$_POST['education_level_id'] : null,
        'education_detail' => $_POST['education_detail'] ?? null,
        'position_id' => ($_POST['position_id'] !== '-' && $_POST['position_id'] !== '0' && $_POST['position_id'] !== '') ? (int)$_POST['position_id'] : null,
        'position_level_id' => ($_POST['position_level_id'] !== '-' && $_POST['position_level_id'] !== '0' && $_POST['position_level_id'] !== '') ? (int)$_POST['position_level_id'] : null,
        'department_id' => ($_POST['department_id'] !== '-' && $_POST['department_id'] !== '0' && $_POST['department_id'] !== '') ? (int)$_POST['department_id'] : null,
        'workbranch_ids' => $_POST['workbranch_id'] ?? [],
        'worklevel_ids' => $_POST['worklevel_id'] ?? []
    ];

    $errors = [];

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($form_data['fullname'])) {
        $errors[] = "กรุณากรอกชื่อ-นามสกุล";
    }

    // อัพโหลดรูปโปรไฟล์จาก base64
    $profile_image = $_POST['current_profile_image'] ?? null;
    if (!empty($_POST['profile_image_base64'])) {
        // Fix: Upload to central directory
        $new_physical_path = uploadBase64Image($_POST['profile_image_base64'], "../uploads/ltc_personal/", "profile_");
        
        if ($new_physical_path) {
            // Fix: DB should store path relative to root
            $new_profile_image = str_replace('../', '', $new_physical_path);
            
            // ถ้าอัพโหลดใหม่สำเร็จ ให้ลบรูปเดิม (ถ้ามี)
            // Note: If old image path is in DB as "uploads/...", deleting it needs "admin/../uploads/..." 
            // BUT existing files might be mixed.
            // Safe Deletion: Check both relative and ../ path.
            if ($profile_image) {
                 if(file_exists($profile_image)) unlink($profile_image); // Try direct relative (old)
                 elseif(file_exists("../" . $profile_image)) unlink("../" . $profile_image); // Try centralized path
            }
            $profile_image = $new_profile_image;
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัพโหลดรูปภาพใหม่";
        }
    }

    // ถ้าไม่มีข้อผิดพลาด ให้บันทึกข้อมูล
    if (empty($errors)) {
        try {
            $mysqli3->begin_transaction();

            // อัพเดทข้อมูลบุคลากร
            $update_query = "UPDATE personel_data SET 
                             fullname = ?, Tel = ?, E_mail = ?, gender_id = ?, 
                             education_level_id = ?, education_detail = ?, position_id = ?, 
                             position_level_id = ?, department_id = ?, 
                             profile_image = ? 
                             WHERE id = ?";
            
            $stmt = $mysqli3->prepare($update_query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $mysqli3->error);
            }

            $stmt->bind_param("sssiisiisss", 
                $form_data['fullname'], 
                $form_data['Tel'], 
                $form_data['E_mail'], 
                $form_data['gender_id'], 
                $form_data['education_level_id'], 
                $form_data['education_detail'],
                $form_data['position_id'], 
                $form_data['position_level_id'], 
                $form_data['department_id'], 
                $profile_image, 
                $personel_id
            );

            if ($stmt->execute()) {
                // ลบข้อมูลงานเดิม
                $delete_work_query = "DELETE FROM work_detail WHERE personel_id = ?";
                $delete_stmt = $mysqli3->prepare($delete_work_query);
                if (!$delete_stmt) {
                    throw new Exception("Prepare delete failed: " . $mysqli3->error);
                }
                $delete_stmt->bind_param("i", $personel_id);
                if (!$delete_stmt->execute()) {
                    throw new Exception("Delete failed: " . $delete_stmt->error);
                }
                $delete_stmt->close();

                // เพิ่มข้อมูลงานใหม่
                if (!empty($form_data['workbranch_ids']) && !empty($form_data['worklevel_ids'])) {
                    $insert_work_query = "INSERT INTO work_detail (personel_id, workbranch_id, worklevel_id) VALUES (?, ?, ?)";
                    $insert_stmt = $mysqli3->prepare($insert_work_query);
                    if (!$insert_stmt) {
                        throw new Exception("Prepare insert failed: " . $mysqli3->error);
                    }
                    
                    foreach ($form_data['workbranch_ids'] as $workbranch_id) {
                        $workbranch_id = (int)$workbranch_id;
                        foreach ($form_data['worklevel_ids'] as $worklevel_id) {
                            $worklevel_id = (int)$worklevel_id;
                            $insert_stmt->bind_param("iii", $personel_id, $workbranch_id, $worklevel_id);
                            if (!$insert_stmt->execute()) {
                                throw new Exception("Insert failed: " . $insert_stmt->error);
                            }
                        }
                    }
                    $insert_stmt->close();
                }

                $mysqli3->commit();
                $stmt->close();
                
                if ($is_ajax) {
                    // ส่ง JSON response สำหรับ AJAX
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'success',
                        'message' => '✅ อัปเดตข้อมูลบุคลากรสำเร็จ',
                        'redirect_url' => $return_url
                    ]);
                } else {
                    // เก็บข้อความสำเร็จใน session และ redirect
                    $_SESSION['toast_message'] = [
                        'type' => 'success',
                        'message' => '✅ อัปเดตข้อมูลบุคลากรสำเร็จ'
                    ];
                    header("Location: " . $return_url);
                }
                exit();
                
            } else {
                throw new Exception($stmt->error);
            }

        } catch (Exception $e) {
            $mysqli3->rollback();
            
            if (isset($new_profile_image) && $new_profile_image && file_exists($new_profile_image)) {
                deleteImageFile($new_profile_image);
            }
            
            $error_message = '❌ เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $e->getMessage();
            if ($is_ajax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => $error_message
                ]);
            } else {
                // เก็บข้อความ error ใน session และ redirect
                $_SESSION['toast_message'] = [
                    'type' => 'error',
                    'message' => $error_message
                ];
                header("Location: " . $return_url);
            }
            exit();
        }
    } else {
        $error_message = '❌ ' . implode(', ', $errors);
        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => $error_message
            ]);
        } else {
            // เก็บข้อความ error ใน session และ redirect
            $_SESSION['toast_message'] = [
                'type' => 'error',
                'message' => $error_message
            ];
            header("Location: " . $return_url);
        }
        exit();
    }

} else {
    $error_message = '❌ เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ไม่พบ ID';
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => $error_message
        ]);
    } else {
        // เก็บข้อความ error ใน session และ redirect
        $_SESSION['toast_message'] = [
            'type' => 'error',
            'message' => $error_message
        ];
        header("Location: personel_manage.php");
    }
    exit();
}
?>
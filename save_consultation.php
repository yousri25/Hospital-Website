<?php
header('Content-Type: application/json; charset=utf-8');

// 1. Connect to database
$conn = new mysqli("localhost", "root", "", "hospital_db");
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed / فشل الاتصال بقاعدة البيانات'
    ]));
}

// 2. Set charset for Arabic/French support
$conn->set_charset("utf8mb4");

// 3. Get and sanitize data
$data = [
    'first_name' => $conn->real_escape_string($_POST['first_name'] ?? ''),
    'last_name' => $conn->real_escape_string($_POST['last_name'] ?? ''),
    'phone' => $conn->real_escape_string($_POST['phone'] ?? ''),
    'message' => $conn->real_escape_string($_POST['message'] ?? '')
];

// 4. Validate
foreach ($data as $value) {
    if (empty($value)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required / جميع الحقول مطلوبة'
        ]);
        $conn->close();
        exit();
    }
}

// 5. Insert into database
$sql = "INSERT INTO consultations (first_name, last_name, phone, message)
        VALUES ('{$data['first_name']}', '{$data['last_name']}', 
                '{$data['phone']}', '{$data['message']}')";

if ($conn->query($sql)) {
    echo json_encode([
        'success' => true,
        'message' => 'Success! Your consultation was submitted. / تم إرسال استشارتك بنجاح'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error: Could not save your request / خطأ: تعذر حفظ طلبك: ' . $conn->error
    ]);
}

$conn->close();
?>
<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "hospital_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Change these to your secure credentials
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['loggedin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Invalid credentials / بيانات الاعتماد غير صالحة";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle database reset
if (isset($_POST['reset_table'])) {
    $conn->query("TRUNCATE TABLE consultations");
    $_SESSION['reset_success'] = true;
    header("Location: admin.php?lang=" . ($_GET['lang'] ?? 'fr'));
    exit;
}

// If not logged in, show login form
if (!isset($_SESSION['loggedin'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: #f5f7fa;
            }
            .login-container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 400px;
            }
            .login-form h2 {
                color: #2c3e50;
                margin-bottom: 20px;
                text-align: center;
            }
            .login-form input {
                width: 100%;
                padding: 12px;
                margin: 8px 0;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                box-sizing: border-box;
            }
            .login-form button {
                width: 100%;
                padding: 12px;
                background-color: #3498db;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                margin-top: 10px;
            }
            .login-form button:hover {
                background-color: #2980b9;
            }
            .error {
                color: #e74c3c;
                text-align: center;
                margin-bottom: 15px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-form">
                <h2>Admin Login / تسجيل الدخول</h2>
                <?php if (isset($error)): ?>
                    <p class="error"><?= $error ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="username" placeholder="Username / اسم المستخدم" required>
                    <input type="password" name="password" placeholder="Password / كلمة المرور" required>
                    <button type="submit" name="login">Login / تسجيل الدخول</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Rest of the admin panel code (only shown when logged in)

// Language switching
$lang = $_GET['lang'] ?? 'fr'; // Default to French
$_SESSION['lang'] = $lang;

// Translations
$translations = [
    'fr' => [
        'title' => 'Gestion des Consultations',
        'search_placeholder' => 'Rechercher par nom ou téléphone',
        'search' => 'Rechercher',
        'reset' => 'Réinitialiser',
        'id' => 'ID',
        'name' => 'Nom',
        'phone' => 'Téléphone',
        'message' => 'Message',
        'submitted' => 'Soumis le',
        'actions' => 'Actions',
        'delete' => 'Supprimer',
        'delete_confirm' => 'Êtes-vous sûr de vouloir supprimer cette entrée ?',
        'logout' => 'Déconnexion',
        'reset_table' => 'Réinitialiser la Table',
        'reset_confirm' => 'Êtes-vous sûr de vouloir réinitialiser toute la table? Toutes les données seront perdues!',
        'reset_success' => 'Table réinitialisée avec succès!'
    ],
    'ar' => [
        'title' => 'إدارة الاستشارات',
        'search_placeholder' => 'ابحث بالاسم أو الهاتف',
        'search' => 'بحث',
        'reset' => 'إعادة تعيين',
        'id' => 'الرقم',
        'name' => 'الاسم',
        'phone' => 'الهاتف',
        'message' => 'الرسالة',
        'submitted' => 'تاريخ الإرسال',
        'actions' => 'الإجراءات',
        'delete' => 'حذف',
        'delete_confirm' => 'هل أنت متأكد من حذف هذا السجل؟',
        'logout' => 'تسجيل خروج',
        'reset_table' => 'إعادة تعيين الجدول',
        'reset_confirm' => 'هل أنت متأكد أنك تريد إعادة تعيين الجدول بالكامل؟ سيتم فقدان جميع البيانات!',
        'reset_success' => 'تم إعادة تعيين الجدول بنجاح!'
    ]
];

$t = $translations[$lang];

// Handle search
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM consultations WHERE id = $id");
    header("Location: admin.php?lang=$lang");
    exit;
}

// Build query
$sql = "SELECT * FROM consultations";
if (!empty($search)) {
    $sql .= " WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR phone LIKE '%$search%'";
}
$sql .= " ORDER BY submission_time DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang == 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .search-box button, .search-box a {
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }
        .search-box a {
            background-color: #95a5a6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: <?php echo $lang == 'ar' ? 'right' : 'left'; ?>;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .action-btn {
            padding: 5px 10px;
            background-color: #e74c3c;
            color: white;
            border-radius: 3px;
            text-decoration: none;
            font-size: 14px;
        }
        .action-btn:hover {
            background-color: #c0392b;
        }
        .reset-btn {
            background-color: #f39c12 !important;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
        }
        .logout-link {
            color: #e74c3c;
            text-decoration: none;
        }
        .lang-switcher {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
        }
        .lang-btn {
            padding: 5px 10px;
            background: #eee;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }
        .lang-btn.active {
            background: #3498db;
            color: white;
        }
        .reset-success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .search-box {
                flex-direction: column;
            }
            th, td {
                padding: 8px 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['reset_success'])): ?>
            <div class="reset-success">
                <?php echo $t['reset_success']; ?>
            </div>
            <?php unset($_SESSION['reset_success']); ?>
        <?php endif; ?>
        
        <div class="lang-switcher">
            <a href="?lang=fr" class="lang-btn <?php echo $lang == 'fr' ? 'active' : ''; ?>">Français</a>
            <a href="?lang=ar" class="lang-btn <?php echo $lang == 'ar' ? 'active' : ''; ?>">العربية</a>
            <a href="?logout=1" class="logout-link" style="margin-left: auto;"><?php echo $t['logout']; ?></a>
        </div>
        
        <h1><?php echo $t['title']; ?></h1>
        
        <!-- Reset Table Button -->
        <form method="POST" onsubmit="return confirm('<?php echo $t['reset_confirm']; ?>')" style="margin-bottom: 20px;">
            <button type="submit" name="reset_table" class="action-btn reset-btn">
                <?php echo $t['reset_table']; ?>
            </button>
        </form>
        
        <div class="search-box">
            <form method="GET">
                <input type="hidden" name="lang" value="<?php echo $lang; ?>">
                <input type="text" name="search" placeholder="<?php echo $t['search_placeholder']; ?>" 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><?php echo $t['search']; ?></button>
                <a href="admin.php?lang=<?php echo $lang; ?>"><?php echo $t['reset']; ?></a>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th><?php echo $t['id']; ?></th>
                    <th><?php echo $t['name']; ?></th>
                    <th><?php echo $t['phone']; ?></th>
                    <th><?php echo $t['message']; ?></th>
                    <th><?php echo $t['submitted']; ?></th>
                    <th><?php echo $t['actions']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                    <td><?php echo date('M j, Y g:i a', strtotime($row['submission_time'])); ?></td>
                    <td>
                        <a href="admin.php?delete=<?php echo $row['id']; ?>&lang=<?php echo $lang; ?>" 
                           class="action-btn"
                           onclick="return confirm('<?php echo $t['delete_confirm']; ?>')">
                            <?php echo $t['delete']; ?>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
$conn->close();
?>
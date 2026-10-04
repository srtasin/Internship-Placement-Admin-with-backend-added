<?php
// api/setup.php
// Visit this file ONCE in your browser (e.g. http://localhost/interntrack/api/setup.php)
// to create the database, tables, and demo data. Safe to run more than once —
// it checks before inserting duplicate seed data.

define('DB_HOST', 'localhost');
define('DB_NAME', 'interntrack');
define('DB_USER', 'root');
define('DB_PASS', '');

header('Content-Type: text/plain');

try {
    // Step 1: connect WITHOUT a database name, so we can create it if missing
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4");
    echo "✔ Database '" . DB_NAME . "' ready.\n";

    // Step 2: reconnect, this time INTO that database
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Step 3: create tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            role ENUM('student','company','coordinator') NOT NULL,
            status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            internship_count INT DEFAULT 0
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS deadlines (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            date_label VARCHAR(255) NOT NULL,
            description TEXT
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            audience VARCHAR(50) NOT NULL DEFAULT 'All Users',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            `key` VARCHAR(100) PRIMARY KEY,
            value TEXT
        ) ENGINE=InnoDB
    ");

    echo "✔ Tables created.\n";

    // Step 4: seed data (only if each table is currently empty)
    $count = fn($table) => (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();

    if ($count('admins') === 0) {
        $hash = password_hash('password', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO admins (email, password_hash, name) VALUES (?, ?, ?)")
            ->execute(['admin@university.edu.my', $hash, 'Admin Portal']);
        echo "✔ Seeded admin login -> admin@university.edu.my / password\n";
    }

    if ($count('users') === 0) {
        $insert = $pdo->prepare("INSERT INTO users (name, email, role, status) VALUES (?, ?, ?, ?)");
        $rows = [
            ['Liyana Sabri', 'liyana@student.edu.my', 'student', 'pending'],
            ['Danial Harith', 'danial@student.edu.my', 'student', 'pending'],
            ['Siti Khadijah', 'siti@student.edu.my', 'student', 'approved'],
            ['Cyberview Sdn Bhd', 'hr@cyberview.com.my', 'company', 'pending'],
            ['NCB Holdings Bhd', 'intern@ncb.com.my', 'company', 'pending'],
            ['Dr. Rashid Mohd', 'rashid@university.edu.my', 'coordinator', 'pending'],
        ];
        foreach ($rows as $r) $insert->execute($r);
        echo "✔ Seeded demo users.\n";
    }

    if ($count('categories') === 0) {
        $insert = $pdo->prepare("INSERT INTO categories (name, description, internship_count) VALUES (?, ?, ?)");
        $rows = [
            ['IT & Software', 'Programming, web development, mobile apps, AI/ML', 142],
            ['Engineering', 'Civil, mechanical, electrical, network engineering', 86],
            ['Business', 'Marketing, operations, strategy, HR management', 64],
            ['Finance', 'Banking, accounting, audit, financial analysis', 48],
            ['Design', 'UI/UX, graphic design, product design, branding', 35],
            ['Science', 'Chemistry, biology, environmental science, research', 22],
        ];
        foreach ($rows as $r) $insert->execute($r);
        echo "✔ Seeded categories.\n";
    }

    if ($count('deadlines') === 0) {
        $insert = $pdo->prepare("INSERT INTO deadlines (title, date_label, description) VALUES (?, ?, ?)");
        $rows = [
            ['Application Deadline', 'February 1, 2025', 'Students must submit applications by this date'],
            ['Interview Period', 'February 3 – 14, 2025', 'Companies conduct interviews with shortlisted candidates'],
            ['Internship Start Date', 'February 17, 2025', 'All accepted students begin industrial training'],
            ['Mid-term Evaluation', 'April 20, 2025', 'Coordinator visit or online evaluation session'],
            ['Final Logbook Submission', 'July 1, 2025', 'All 24 weekly logbooks must be submitted and approved'],
            ['Final Evaluation Deadline', 'July 10, 2025', 'Industry and academic evaluation submission deadline'],
        ];
        foreach ($rows as $r) $insert->execute($r);
        echo "✔ Seeded deadlines.\n";
    }

    if ($count('announcements') === 0) {
        $insert = $pdo->prepare("INSERT INTO announcements (title, description, audience, created_at) VALUES (?, ?, ?, ?)");
        $rows = [
            ['Logbook Submission Deadline Extended to Jan 31', '', 'Students', '2025-01-20 09:00:00'],
            ['New Partner Company: Cyberview Sdn Bhd', '', 'All Users', '2025-01-18 09:00:00'],
            ['Coordinator Training Session — Feb 5', '', 'Coordinators', '2025-01-15 09:00:00'],
            ['System Maintenance: Jan 26 (12 AM – 4 AM)', '', 'All Users', '2025-01-14 09:00:00'],
            ['Application Portal Opens for Semester 2', '', 'Students', '2025-01-10 09:00:00'],
        ];
        foreach ($rows as $r) $insert->execute($r);
        echo "✔ Seeded announcements.\n";
    }

    if ($count('settings') === 0) {
        $insert = $pdo->prepare("INSERT INTO settings (`key`, value) VALUES (?, ?)");
        $defaults = [
            'system_name' => 'InternTrack — University IT System',
            'university_name' => 'Universiti Teknologi Malaysia',
            'admin_email' => 'admin@university.edu.my',
            'current_semester' => 'Semester 2, 2024/2025',
            'session_timeout' => '30',
            'password_policy' => 'Strong (min 8 chars, mixed case, numbers)',
            'notif_email_applications' => '1',
            'notif_sms_deadlines' => '0',
            'notif_system_announcements' => '1',
            'notif_weekly_digest' => '1',
            'security_2fa' => '0',
        ];
        foreach ($defaults as $k => $v) $insert->execute([$k, $v]);
        echo "✔ Seeded settings.\n";
    }

    echo "\nAll done! Go to http://localhost/interntrack/login.html and log in with:\n";
    echo "  Email:    admin@university.edu.my\n";
    echo "  Password: password\n";

} catch (PDOException $e) {
    http_response_code(500);
    echo "Setup failed: " . $e->getMessage() . "\n";
    echo "\nCheck that MySQL is running in the XAMPP control panel, and that\n";
    echo "DB_USER / DB_PASS in this file match your MySQL root credentials.\n";
}

<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

// Если подтверждено удаление
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $success = deleteStudent($id);
    if ($success) {
        header('Location: index.php');
        exit();
    } else {
        $error = "Не удалось удалить студента. Возможно, у него есть связанные записи об экзаменах.";
    }
}

// Показываем форму подтверждения
$student = getStudent($id);
if (!$student) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление студента</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="confirmation-box">
            <h1>Удаление студента</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <p>Вы действительно хотите удалить студента:</p>
            <div class="student-info">
                <h3><?= htmlspecialchars($student['full_name']) ?></h3>
                <p>Студенческий билет: <?= htmlspecialchars($student['student_card']) ?></p>
                <p>Дата рождения: <?= date('d.m.Y', strtotime($student['birth_date'])) ?></p>
            </div>
            <p class="warning">Это действие невозможно отменить! Все связанные записи об экзаменах также будут удалены.</p>
            
            <div class="confirmation-buttons">
                <a href="student_delete.php?id=<?= $id ?>&confirm=yes" 
                   class="btn btn-danger">Да, удалить</a>
                <a href="index.php" class="btn btn-secondary">Отмена</a>
            </div>
        </div>
    </div>
</body>
</html>
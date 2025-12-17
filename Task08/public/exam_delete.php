<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isset($_GET['id']) || !isset($_GET['student_id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];
$student_id = $_GET['student_id'];
$db = Database::connect();

if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $stmt = $db->prepare("DELETE FROM exams WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        header("Location: exams.php?student_id=$student_id");
        exit();
    } else {
        $error = "Не удалось удалить запись об экзамене!";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление экзамена</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="confirmation-box">
            <h1>Удаление записи об экзамене</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <p>Вы действительно хотите удалить запись об экзамене?</p>
            <p class="warning">Это действие невозможно отменить!</p>
            
            <div class="confirmation-buttons">
                <a href="exam_delete.php?id=<?= $id ?>&student_id=<?= $student_id ?>&confirm=yes" 
                   class="btn btn-danger">Да, удалить</a>
                <a href="exams.php?student_id=<?= $student_id ?>" class="btn btn-secondary">Отмена</a>
            </div>
        </div>
    </div>
</body>
</html>
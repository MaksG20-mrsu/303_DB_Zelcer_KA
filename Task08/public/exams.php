<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isset($_GET['student_id'])) {
    header('Location: index.php');
    exit();
}

$student_id = $_GET['student_id'];
$db = Database::connect();

// Получаем информацию о студенте
$stmt = $db->prepare("SELECT s.*, g.group_number, g.direction 
                      FROM students s 
                      JOIN groups g ON s.group_id = g.id 
                      WHERE s.id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: index.php');
    exit();
}

// Получаем экзамены студента
$stmt = $db->prepare("SELECT e.*, sj.subject_name, sj.course 
                      FROM exams e 
                      JOIN subjects sj ON e.subject_id = sj.id 
                      WHERE e.student_id = ? 
                      ORDER BY e.exam_date DESC");
$stmt->execute([$student_id]);
$exams = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты экзаменов</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Результаты экзаменов</h1>
        
        <!-- Информация о студенте -->
        <div class="student-card">
            <h2>Студент: <?= htmlspecialchars($student['full_name']) ?></h2>
            <p>Группа: <?= htmlspecialchars($student['group_number']) ?></p>
            <p>Направление: <?= htmlspecialchars($student['direction']) ?></p>
        </div>
        
        <!-- Таблица экзаменов -->
        <div class="table-container">
            <div class="table-header">
                <h3>Сданные экзамены</h3>
                <a href="exam_form.php?student_id=<?= $student_id ?>" class="btn btn-success">
                    Добавить экзамен
                </a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Дисциплина</th>
                        <th>Курс</th>
                        <th>Дата экзамена</th>
                        <th>Оценка</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Нет данных об экзаменах</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($exams as $index => $exam): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($exam['subject_name']) ?></td>
                                <td><?= $exam['course'] ?></td>
                                <td><?= date('d.m.Y', strtotime($exam['exam_date'])) ?></td>
                                <td>
                                    <span class="grade grade-<?= $exam['grade'] ?>">
                                        <?= $exam['grade'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="exam_form.php?edit=<?= $exam['id'] ?>&student_id=<?= $student_id ?>" 
                                           class="btn btn-warning" title="Редактировать">
                                            Редакт.
                                        </a>
                                        <a href="exam_delete.php?id=<?= $exam['id'] ?>&student_id=<?= $student_id ?>" 
                                           class="btn btn-danger" title="Удалить"
                                           onclick="return confirm('Удалить запись об экзамене?')">
                                            Удалить
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary">Назад к списку студентов</a>
        </div>
    </div>
</body>
</html>
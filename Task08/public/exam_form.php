<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = Database::connect();
$isEdit = isset($_GET['edit']);
$student_id = $_GET['student_id'] ?? null;

if (!$student_id && !$isEdit) {
    header('Location: index.php');
    exit();
}

// Получаем информацию о студенте
if ($isEdit) {
    // Получаем экзамен
    $stmt = $db->prepare("SELECT e.*, s.group_id, g.direction 
                          FROM exams e 
                          JOIN students s ON e.student_id = s.id 
                          JOIN groups g ON s.group_id = g.id 
                          WHERE e.id = ?");
    $stmt->execute([$_GET['edit']]);
    $exam = $stmt->fetch();
    $student_id = $exam['student_id'];
    $direction = $exam['direction'];
} else {
    // Получаем студента для нового экзамена
    $stmt = $db->prepare("SELECT s.*, g.direction 
                          FROM students s 
                          JOIN groups g ON s.group_id = g.id 
                          WHERE s.id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    $direction = $student['direction'];
}

// Получаем дисциплины для направления студента
$stmt = $db->prepare("SELECT * FROM subjects WHERE direction = ? ORDER BY course, subject_name");
$stmt->execute([$direction]);
$subjects = $stmt->fetchAll();

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $exam_date = $_POST['exam_date'];
    $grade = $_POST['grade'];
    
    // Валидация
    if (empty($subject_id) || empty($exam_date) || empty($grade)) {
        $error = "Все поля должны быть заполнены!";
    } else {
        if ($isEdit) {
            $stmt = $db->prepare("UPDATE exams SET 
                subject_id = ?, exam_date = ?, grade = ? 
                WHERE id = ?");
            $result = $stmt->execute([$subject_id, $exam_date, $grade, $_GET['edit']]);
        } else {
            $stmt = $db->prepare("INSERT INTO exams 
                (student_id, subject_id, exam_date, grade) 
                VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$student_id, $subject_id, $exam_date, $grade]);
        }
        
        if ($result) {
            header("Location: exams.php?student_id=$student_id");
            exit();
        } else {
            $error = "Ошибка при сохранении данных!";
        }
    }
}

// Если редактирование, получаем данные экзамена
if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $examData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Редактирование' : 'Добавление' ?> экзамена</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= $isEdit ? 'Редактирование экзамена' : 'Добавление нового экзамена' ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <input type="hidden" name="student_id" value="<?= $student_id ?>">
            
            <div class="form-group">
                <label for="subject_id">Дисциплина:</label>
                <select id="subject_id" name="subject_id" required>
                    <option value="">Выберите дисциплину</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>"
                            <?= ($examData['subject_id'] ?? '') == $subject['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subject['subject_name']) ?> 
                            (<?= $subject['course'] ?> курс)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="exam_date">Дата экзамена:</label>
                <input type="date" id="exam_date" name="exam_date" required
                       value="<?= $examData['exam_date'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label for="grade">Оценка:</label>
                <select id="grade" name="grade" required>
                    <option value="">Выберите оценку</option>
                    <option value="2" <?= ($examData['grade'] ?? '') == 2 ? 'selected' : '' ?>>2</option>
                    <option value="3" <?= ($examData['grade'] ?? '') == 3 ? 'selected' : '' ?>>3</option>
                    <option value="4" <?= ($examData['grade'] ?? '') == 4 ? 'selected' : '' ?>>4</option>
                    <option value="5" <?= ($examData['grade'] ?? '') == 5 ? 'selected' : '' ?>>5</option>
                </select>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Сохранить изменения' : 'Добавить экзамен' ?>
                </button>
                <a href="exams.php?student_id=<?= $student_id ?>" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>
<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = Database::connect();
$groups = getAllGroups();

// Проверяем, редактирование или добавление
$isEdit = isset($_GET['edit']);
$student = null;

if ($isEdit) {
    $student = getStudent($_GET['edit']);
    if (!$student) {
        header('Location: index.php');
        exit();
    }
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $gender = $_POST['gender'];
    $birth_date = $_POST['birth_date'];
    $student_card = trim($_POST['student_card']);
    $group_id = $_POST['group_id'];
    
    // Валидация
    if (empty($full_name) || empty($student_card) || empty($group_id)) {
        $error = "Все обязательные поля должны быть заполнены!";
    } else {
        if ($isEdit) {
            $stmt = $db->prepare("UPDATE students SET 
                full_name = ?, gender = ?, birth_date = ?, 
                student_card = ?, group_id = ? WHERE id = ?");
            $result = $stmt->execute([$full_name, $gender, $birth_date, 
                           $student_card, $group_id, $_GET['edit']]);
        } else {
            $stmt = $db->prepare("INSERT INTO students 
                (full_name, gender, birth_date, student_card, group_id) 
                VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([$full_name, $gender, $birth_date, 
                           $student_card, $group_id]);
        }
        
        if ($result) {
            header('Location: index.php');
            exit();
        } else {
            $error = "Ошибка при сохранении данных!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Редактирование' : 'Добавление' ?> студента</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= $isEdit ? 'Редактирование студента' : 'Добавление нового студента' ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-group">
                <label for="full_name">ФИО:</label>
                <input type="text" id="full_name" name="full_name" required
                       value="<?= htmlspecialchars($student['full_name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Пол:</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="gender" value="М" required
                               <?= ($student['gender'] ?? '') == 'М' ? 'checked' : '' ?>>
                        Мужской
                    </label>
                    <label>
                        <input type="radio" name="gender" value="Ж" required
                               <?= ($student['gender'] ?? '') == 'Ж' ? 'checked' : '' ?>>
                        Женский
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="birth_date">Дата рождения:</label>
                <input type="date" id="birth_date" name="birth_date" required
                       value="<?= $student['birth_date'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label for="student_card">Номер студенческого билета:</label>
                <input type="text" id="student_card" name="student_card" required
                       value="<?= htmlspecialchars($student['student_card'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="group_id">Группа:</label>
                <select id="group_id" name="group_id" required>
                    <option value="">Выберите группу</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= $group['id'] ?>"
                            <?= ($student['group_id'] ?? '') == $group['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($group['group_number']) ?> 
                            (<?= htmlspecialchars($group['direction']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Сохранить изменения' : 'Добавить студента' ?>
                </button>
                <a href="index.php" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>
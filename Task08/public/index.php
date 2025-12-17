<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Проверка фильтра
$groupFilter = $_GET['group'] ?? '';
$students = getStudents($groupFilter);
$activeGroups = getActiveGroups();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление студентами</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Управление студентами</h1>
        
        <!-- Фильтр по группам -->
        <div class="filter-form">
            <form method="GET" action="">
                <label for="group">Фильтр по группе:</label>
                <select name="group" id="group" onchange="this.form.submit()">
                    <option value="">Все группы</option>
                    <?php foreach ($activeGroups as $group): ?>
                        <option value="<?= htmlspecialchars($group['group_number']) ?>" 
                            <?= $groupFilter == $group['group_number'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($group['group_number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($groupFilter): ?>
                    <a href="?" class="btn btn-secondary">Сбросить</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Таблица студентов -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Группа</th>
                        <th>Направление</th>
                        <th>ФИО</th>
                        <th>Пол</th>
                        <th>Дата рождения</th>
                        <th>Студ. билет</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Нет данных для отображения</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($student['group_number']) ?></td>
                                <td><?= htmlspecialchars($student['direction']) ?></td>
                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                <td><?= htmlspecialchars($student['gender']) ?></td>
                                <td><?= date('d.m.Y', strtotime($student['birth_date'])) ?></td>
                                <td><code><?= htmlspecialchars($student['student_card']) ?></code></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="exams.php?student_id=<?= $student['id'] ?>" 
                                           class="btn btn-info" 
                                           title="Результаты экзаменов">
                                            Экзамены
                                        </a>
                                        <a href="student_form.php?edit=<?= $student['id'] ?>" 
                                           class="btn btn-warning" 
                                           title="Редактировать">
                                            Редакт.
                                        </a>
                                        <a href="student_delete.php?id=<?= $student['id'] ?>" 
                                           class="btn btn-danger" 
                                           title="Удалить"
                                           onclick="return confirm('Вы уверены, что хотите удалить этого студента?')">
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
        
        <!-- Кнопка добавления -->
        <div class="text-center mt-4">
            <a href="student_form.php" class="btn btn-success btn-lg">
                Добавить нового студента
            </a>
        </div>
    </div>
</body>
</html>
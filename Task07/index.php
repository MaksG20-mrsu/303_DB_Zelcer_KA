<?php require_once 'database.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список студентов</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3498db;
        }
        
        .filter-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }
        
        .form-group {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        label {
            font-weight: 600;
            color: #495057;
        }
        
        select {
            padding: 10px 15px;
            border: 2px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
            background: white;
            min-width: 200px;
            transition: border-color 0.3s;
        }
        
        select:focus {
            outline: none;
            border-color: #3498db;
        }
        
        button {
            padding: 10px 25px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        .all-students-btn {
            background: #2ecc71;
        }
        
        .all-students-btn:hover {
            background: #27ae60;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        thead {
            background: #3498db;
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #2980b9;
        }
        
        tbody tr {
            transition: background 0.2s;
        }
        
        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        tbody tr:hover {
            background: #e3f2fd;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .gender-m {
            color: #3498db;
            font-weight: 600;
        }
        
        .gender-f {
            color: #e84393;
            font-weight: 600;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-size: 18px;
        }
        
        .counter {
            text-align: right;
            margin-top: 15px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .form-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            select, button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Список студентов университета</h1>
        
        <div class="filter-form">
            <form method="GET" action="">
                <div class="form-group">
                    <label for="group">Выберите группу:</label>
                    <select name="group" id="group">
                        <option value="">Все группы</option>
                        <?php
                        $db = Database::connect();
                        $stmt = $db->prepare("
                            SELECT DISTINCT group_number, direction 
                            FROM `groups` 
                            WHERE is_active = TRUE 
                            ORDER BY group_number
                        ");
                        $stmt->execute();
                        $groups = $stmt->fetchAll();
                        
                        $selectedGroup = $_GET['group'] ?? '';
                        
                        foreach ($groups as $group):
                        ?>
                            <option value="<?= htmlspecialchars($group['group_number']) ?>" 
                                <?= $selectedGroup === $group['group_number'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($group['group_number']) ?> 
                                (<?= htmlspecialchars($group['direction']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit">Применить фильтр</button>
                    
                    <?php if (!empty($selectedGroup)): ?>
                        <a href="?" class="all-students-btn" style="text-decoration: none;">
                            <button type="button">Показать всех</button>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <?php
        // Получение списка студентов
        $sql = "
            SELECT 
                g.group_number,
                g.direction,
                s.full_name,
                s.gender,
                DATE_FORMAT(s.birth_date, '%d.%m.%Y') as birth_date,
                s.student_card
            FROM students s
            JOIN `groups` g ON s.group_id = g.id
            WHERE g.is_active = TRUE
        ";
        
        $params = [];
        
        if (!empty($selectedGroup)) {
            $sql .= " AND g.group_number = :group_number";
            $params[':group_number'] = $selectedGroup;
        }
        
        $sql .= " ORDER BY g.group_number, s.full_name";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll();
        ?>
        
        <div class="table-container">
            <?php if (empty($students)): ?>
                <div class="no-data">
                    <p>Нет данных для отображения</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Группа</th>
                            <th>Направление подготовки</th>
                            <th>ФИО</th>
                            <th>Пол</th>
                            <th>Дата рождения</th>
                            <th>Студенческий билет</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($student['group_number']) ?></strong></td>
                                <td><?= htmlspecialchars($student['direction']) ?></td>
                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                <td>
                                    <span class="gender-<?= $student['gender'] === 'М' ? 'm' : 'f' ?>">
                                        <?= htmlspecialchars($student['gender']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($student['birth_date']) ?></td>
                                <td><code><?= htmlspecialchars($student['student_card']) ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="counter">
                    Найдено студентов: <?= count($students) ?>
                    <?php if (!empty($selectedGroup)): ?>
                        (фильтр по группе: <?= htmlspecialchars($selectedGroup) ?>)
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
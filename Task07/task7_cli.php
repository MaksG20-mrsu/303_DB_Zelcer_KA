#!/usr/bin/env php
<?php
require_once 'database.php';

class StudentManagerCLI {
    private $db;
    
    public function __construct() {
        $this->db = Database::connect();
    }
    
    public function getActiveGroups() {
        $stmt = $this->db->prepare("
            SELECT DISTINCT g.group_number, g.direction 
            FROM `groups` g
            WHERE g.is_active = TRUE
            ORDER BY g.group_number
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getStudents($groupFilter = null) {
        $sql = "
            SELECT 
                g.group_number,
                g.direction,
                s.full_name,
                s.gender,
                s.birth_date,
                s.student_card
            FROM students s
            JOIN `groups` g ON s.group_id = g.id
            WHERE g.is_active = TRUE
        ";
        
        $params = [];
        
        if ($groupFilter) {
            $sql .= " AND g.group_number = :group_number";
            $params[':group_number'] = $groupFilter;
        }
        
        $sql .= " ORDER BY g.group_number, s.full_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function displayTable($students) {
        if (empty($students)) {
            echo "Нет данных для отображения.\n";
            return;
        }
        
                $widths = [
            'group_number' => mb_strlen('Группа'),
            'direction' => mb_strlen('Направление'),
            'full_name' => mb_strlen('ФИО'),
            'gender' => mb_strlen('Пол'),
            'birth_date' => mb_strlen('Дата рождения'),
            'student_card' => mb_strlen('Студ. билет')
        ];
        
        foreach ($students as $student) {
            $widths['group_number'] = max($widths['group_number'], mb_strlen($student['group_number']));
            $widths['direction'] = max($widths['direction'], mb_strlen($student['direction']));
            $widths['full_name'] = max($widths['full_name'], mb_strlen($student['full_name']));
            $widths['gender'] = max($widths['gender'], mb_strlen($student['gender']));
            $widths['birth_date'] = max($widths['birth_date'], mb_strlen($student['birth_date']));
            $widths['student_card'] = max($widths['student_card'], mb_strlen($student['student_card']));
        }
        
               $createLine = function() use ($widths) {
            $line = '┌';
            foreach ($widths as $index => $width) {
                $line .= str_repeat('─', $width + 2) . '┬';
            }
            $line = rtrim($line, '┬') . '┐';
            return $line;
        };
        
        $line = $createLine();
        $middleLine = str_replace(['┌', '┐', '┬'], ['├', '┤', '┼'], $line);
        $bottomLine = str_replace(['┌', '┐', '┬'], ['└', '┘', '┴'], $line);
        
                echo $line . "\n";
        echo "│ " . str_pad('Группа', $widths['group_number']) . " │ ";
        echo str_pad('Направление', $widths['direction']) . " │ ";
        echo str_pad('ФИО', $widths['full_name']) . " │ ";
        echo str_pad('Пол', $widths['gender']) . " │ ";
        echo str_pad('Дата рождения', $widths['birth_date']) . " │ ";
        echo str_pad('Студ. билет', $widths['student_card']) . " │\n";
        
        echo $middleLine . "\n";
        
               foreach ($students as $student) {
            echo "│ " . str_pad($student['group_number'], $widths['group_number']) . " │ ";
            echo str_pad($student['direction'], $widths['direction']) . " │ ";
            echo str_pad($student['full_name'], $widths['full_name']) . " │ ";
            echo str_pad($student['gender'], $widths['gender']) . " │ ";
            echo str_pad($student['birth_date'], $widths['birth_date']) . " │ ";
            echo str_pad($student['student_card'], $widths['student_card']) . " │\n";
        }
        
        echo $bottomLine . "\n";
        echo "Всего студентов: " . count($students) . "\n";
    }
    
    public function run() {
        echo "=== Список студентов ===\n\n";
        
                $activeGroups = $this->getActiveGroups();
        
        if (empty($activeGroups)) {
            echo "Нет активных групп в базе данных.\n";
            return;
        }
        
               echo "Доступные группы:\n";
        foreach ($activeGroups as $group) {
            echo "- " . $group['group_number'] . " (" . $group['direction'] . ")\n";
        }
        
        echo "\n";
        echo "Введите номер группы для фильтрации (или нажмите Enter для вывода всех): ";
        $input = trim(fgets(STDIN));
        
        $filter = null;
        if (!empty($input)) {
            // Проверяем, существует ли введённая группа
            $validGroups = array_column($activeGroups, 'group_number');
            if (in_array($input, $validGroups)) {
                $filter = $input;
                echo "\nФильтр по группе: $input\n\n";
            } else {
                echo "\nОшибка: Группа '$input' не найдена среди активных групп.\n";
                echo "Будет выведен список всех студентов.\n\n";
            }
        }
        
                $students = $this->getStudents($filter);
        $this->displayTable($students);
    }
}

$app = new StudentManagerCLI();
$app->run();
?>
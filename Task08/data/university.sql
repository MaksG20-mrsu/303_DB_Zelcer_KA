-- Создание таблиц для SQLite
CREATE TABLE IF NOT EXISTS `groups` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `group_number` VARCHAR(10) NOT NULL UNIQUE,
    `direction` VARCHAR(255) NOT NULL,
    `is_active` BOOLEAN DEFAULT 1
);

CREATE TABLE IF NOT EXISTS `students` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `gender` TEXT NOT NULL CHECK(gender IN ('М', 'Ж')),
    `birth_date` DATE NOT NULL,
    `student_card` VARCHAR(20) NOT NULL UNIQUE,
    `group_id` INTEGER,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `subject_name` VARCHAR(100) NOT NULL,
    `direction` VARCHAR(255) NOT NULL,
    `course` INTEGER NOT NULL CHECK (course BETWEEN 1 AND 4)
);

CREATE TABLE IF NOT EXISTS `exams` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `student_id` INTEGER NOT NULL,
    `subject_id` INTEGER NOT NULL,
    `exam_date` DATE NOT NULL,
    `grade` INTEGER NOT NULL CHECK (grade BETWEEN 2 AND 5),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
);

-- Вставка тестовых данных
INSERT INTO `groups` (`group_number`, `direction`, `is_active`) VALUES
('ПИ-101', 'Программная инженерия', 1),
('ПИ-102', 'Программная инженерия', 1),
('ФИ-201', 'Фундаментальная информатика', 1),
('ФИ-202', 'Фундаментальная информатика', 1),
('ПМ-301', 'Прикладная математика', 1);

INSERT INTO `students` (`full_name`, `gender`, `birth_date`, `student_card`, `group_id`) VALUES
('Иванов Иван Иванович', 'М', '2000-05-15', 'ПИ2021001', 1),
('Петрова Анна Сергеевна', 'Ж', '2001-03-22', 'ПИ2021002', 1),
('Сидоров Алексей Петрович', 'М', '2000-11-30', 'ПИ2021003', 2),
('Кузнецова Мария Викторовна', 'Ж', '2001-07-14', 'ФИ2021001', 3),
('Смирнов Дмитрий Алексеевич', 'М', '2000-09-10', 'ФИ2021002', 3),
('Васильева Елена Николаевна', 'Ж', '2001-01-25', 'ФИ2021003', 4),
('Николаев Павел Олегович', 'М', '2000-12-05', 'ПМ2021001', 5);

INSERT INTO `subjects` (`subject_name`, `direction`, `course`) VALUES
('Программирование', 'Программная инженерия', 1),
('Базы данных', 'Программная инженерия', 2),
('Веб-технологии', 'Программная инженерия', 3),
('Алгоритмы и структуры данных', 'Фундаментальная информатика', 1),
('Дискретная математика', 'Фундаментальная информатика', 2),
('Теория вычислений', 'Фундаментальная информатика', 3),
('Математический анализ', 'Прикладная математика', 1),
('Линейная алгебра', 'Прикладная математика', 2);

INSERT INTO `exams` (`student_id`, `subject_id`, `exam_date`, `grade`) VALUES
(1, 1, '2023-01-20', 5),
(1, 2, '2023-06-15', 4),
(2, 1, '2023-01-20', 4),
(3, 3, '2024-01-25', 5),
(4, 4, '2023-01-22', 5),
(4, 5, '2023-06-18', 4),
(5, 4, '2023-01-22', 3),
(6, 4, '2023-01-22', 5),
(7, 7, '2023-01-25', 5);
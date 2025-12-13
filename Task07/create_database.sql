CREATE DATABASE IF NOT EXISTS university;
USE university;

CREATE TABLE IF NOT EXISTS `groups` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `group_number` VARCHAR(10) NOT NULL UNIQUE,
    `direction` VARCHAR(255) NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS `students` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `gender` ENUM('М', 'Ж') NOT NULL,
    `birth_date` DATE NOT NULL,
    `student_card` VARCHAR(20) NOT NULL UNIQUE,
    `group_id` INT,
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE SET NULL
);

INSERT INTO `groups` (`group_number`, `direction`, `is_active`) VALUES
('ФИ-301', 'Фундаментальная информатика', TRUE),
('ПМ-303', 'Прикладная математика', TRUE),
('ПИ-304', 'Программная инженерия', TRUE),
('ИТ-306', 'Информационные технологии', FALSE), -- Неактивная группа

INSERT INTO `students` (`full_name`, `gender`, `birth_date`, `student_card`, `group_id`) VALUES
('Иванов Иван Иванович', 'М', '2000-05-15', 'ИТ2021001', 1),
('Петрова Анна Сергеевна', 'Ж', '2001-03-22', 'ИТ2021002', 1),
('Сидоров Алексей Петрович', 'М', '2000-11-30', 'ПМ2021001', 2),
('Кузнецова Мария Викторовна', 'Ж', '2001-07-14', 'ПМ2021002', 2),
('Смирнов Дмитрий Алексеевич', 'М', '2000-09-10', 'ФИ2021001', 3),
('Васильева Елена Николаевна', 'Ж', '2001-01-25', 'ФИ2021002', 3),
('Николаев Павел Олегович', 'М', '2000-12-05', 'БИ2021001', 4),
('Андреева Ольга Игоревна', 'Ж', '2001-04-18', 'БИ2021002', 4);
-- сотрудники
CREATE TABLE employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    phone TEXT,
    hire_date DATE NOT NULL DEFAULT (DATE('now')),
    dismissal_date DATE,
    salary_percent REAL NOT NULL CHECK (salary_percent BETWEEN 0 AND 100),
    is_active BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- услуги
CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    duration_minutes INTEGER NOT NULL CHECK (duration_minutes > 0),
    price REAL NOT NULL CHECK (price >= 0),
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- запись клиентов
CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_first_name TEXT NOT NULL,
    client_last_name TEXT,
    client_phone TEXT NOT NULL,
    employee_id INTEGER NOT NULL,
    scheduled_start TIMESTAMP NOT NULL,
    scheduled_end TIMESTAMP NOT NULL,
    status TEXT DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'completed', 'cancelled', 'no_show')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT
);

-- связь записей и услуг
CREATE TABLE appointment_services (
    appointment_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    quantity INTEGER DEFAULT 1 CHECK (quantity > 0),
    actual_price REAL, -- Фактическая цена на момент выполнения (может отличаться от прайса)
    PRIMARY KEY (appointment_id, service_id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

-- учет выполненых работ
CREATE TABLE completed_works (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER NOT NULL,
    employee_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    completion_date DATE NOT NULL DEFAULT (DATE('now')),
    actual_duration_minutes INTEGER CHECK (actual_duration_minutes > 0),
    notes TEXT,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE RESTRICT,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

-- рабочий график мастеров
CREATE TABLE work_schedule (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    day_of_week INTEGER NOT NULL CHECK (day_of_week BETWEEN 1 AND 7), -- 1=Понедельник
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    valid_from DATE DEFAULT (DATE('now')),
    valid_until DATE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

CREATE INDEX idx_appointments_employee ON appointments(employee_id);
CREATE INDEX idx_appointments_date ON appointments(scheduled_start);
CREATE INDEX idx_completed_works_employee_date ON completed_works(employee_id, completion_date);
CREATE INDEX idx_work_schedule_employee ON work_schedule(employee_id);


INSERT INTO employees (first_name, last_name, phone, salary_percent, is_active) VALUES
    ('Иван', 'Петров', '+79161234567', 30.0, 1),
    ('Мария', 'Сидорова', '+79167654321', 35.0, 1),
    ('Алексей', 'Кузнецов', '+79162345678', 32.5, 0), -- Уволенный
    ('Елена', 'Васильева', '+79168765432', 33.0, 1);

INSERT INTO services (name, duration_minutes, price) VALUES
    ('Замена масла', 30, 2000.00),
    ('Замена тормозных колодок', 90, 5000.00),
    ('Диагностика двигателя', 60, 3000.00),
    ('Развал-схождение', 120, 4000.00),
    ('Замена шин', 45, 1500.00);

INSERT INTO work_schedule (employee_id, day_of_week, start_time, end_time) VALUES
    (1, 1, '09:00', '18:00'), 
    (1, 2, '09:00', '18:00'),
    (1, 3, '09:00', '18:00'),
    (1, 4, '09:00', '18:00'),
    (1, 5, '09:00', '17:00'),
    (2, 2, '10:00', '19:00'),
    (2, 3, '10:00', '19:00'),
    (2, 4, '10:00', '19:00'),
    (2, 5, '10:00', '19:00'),
    (2, 6, '10:00', '16:00'),
    (4, 1, '08:00', '17:00'),
    (4, 3, '08:00', '17:00'),
    (4, 5, '08:00', '17:00');


INSERT INTO appointments (client_first_name, client_last_name, client_phone, employee_id, scheduled_start, scheduled_end) VALUES
    ('Анна', 'Иванова', '+79031234567', 1, '2024-03-01 10:00:00', '2024-03-01 10:30:00'),
    ('Дмитрий', 'Смирнов', '+79039876543', 2, '2024-03-02 11:00:00', '2024-03-02 12:30:00'),
    ('Ольга', 'Попова', '+79035557788', 1, '2024-03-01 14:00:00', '2024-03-01 15:30:00');


INSERT INTO appointment_services (appointment_id, service_id, actual_price) VALUES
    (1, 1, 2000.00),
    (2, 2, 5000.00),
    (2, 3, 3000.00),
    (3, 4, 4000.00);

INSERT INTO completed_works (appointment_id, employee_id, service_id, completion_date, actual_duration_minutes) VALUES
    (1, 1, 1, '2024-03-01', 35),
    (2, 2, 2, '2024-03-02', 95),
    (2, 2, 3, '2024-03-02', 60);
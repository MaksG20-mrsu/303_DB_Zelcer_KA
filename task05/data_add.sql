INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Зельцер Ксения Александровна', 'zeltser@gmail.com', 'female', date('now'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Зинов Никита Александрович', 'zinov@gmail.com', 'male', date('now'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Игнатьева Татьяна Александровна', 'ignatieva@gmail.com', 'female', date('now'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Калинин Александр Евгеньевич', 'kalinin@gmail.com', 'male', date('now'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Кечемайкин Дмитрий Максимович', 'kechemaikin@gmail.com', 'male', date('now'), 'student');

INSERT INTO movies (title, year)
VALUES ('Minecraft в кино', 2025);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id 
FROM movies m, genres g 
WHERE m.title = 'Minecraft в кино' AND g.name = 'Fantasy';

INSERT INTO movies (title, year)
VALUES ('Как приручить дракона', 2025);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id 
FROM movies m, genres g 
WHERE m.title = 'Как приручить дракона' AND g.name = 'Fantasy';

INSERT INTO movies (title, year)
VALUES ('Алиса в Стране чудес', 2025);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id 
FROM movies m, genres g 
WHERE m.title = 'Алиса в Стране чудес' AND g.name = 'Sci-Fi';

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'zeltser@gmail.com'),
    (SELECT id FROM movies WHERE title = 'Minecraft в кино'),
    5.0,
    strftime('%s', 'now');

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'zeltser@gmail.com'),
    (SELECT id FROM movies WHERE title = 'Как приручить дракона'),
    4.3,
    strftime('%s', 'now');

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'zeltser@gmail.com'),
    (SELECT id FROM movies WHERE title = 'Алиса в Стране чудес'),
    2.0,
    strftime('%s', 'now');
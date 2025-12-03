#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo "1. Составить список фильмов, имеющих хотя бы одну оценку. Список фильмов отсортировать по году выпуска и по названиям. В списке оставить первые 10 фильмов."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT m.title AS title, m.year AS year
FROM movies m
WHERE EXISTS (SELECT 1 FROM ratings r WHERE r.movie_id = m.id)
ORDER BY m.year, m.title
LIMIT 10;"
echo " "

echo "2. Вывести список всех пользователей, фамилии (не имена!) которых начинаются на букву 'A'. Полученный список отсортировать по дате регистрации. В списке оставить первых 5 пользователей."
echo "--------------------------------------------------"
# Выделяем фамилию как часть после первого пробела. В случае отсутствия пробела — фамилия будет пустой.
sqlite3 movies_rating.db -box -echo "SELECT id,
       name,
       substr(name, instr(name,' ')+1) AS last_name,
       register_date
FROM users
WHERE upper(substr(name, instr(name,' ')+1,1)) = 'A'
ORDER BY register_date
LIMIT 5;"
echo " "

echo "3. Информация о рейтингах в читаемом формате: имя и фамилия эксперта, название фильма, год выпуска, оценка и дата оценки (ГГГГ-ММ-ДД). Отсортировать по имени эксперта, затем названию фильма и оценке. Первые 50 записей."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT
    substr(u.name,1, instr(u.name,' ')-1) AS first_name,
    substr(u.name, instr(u.name,' ')+1) AS last_name,
    m.title AS movie_title,
    m.year AS year,
    r.rating AS rating,
    date(r.timestamp, 'unixepoch') AS rating_date
FROM ratings r
JOIN users u ON r.user_id = u.id
JOIN movies m ON r.movie_id = m.id
ORDER BY first_name, movie_title, rating
LIMIT 50;"
echo " "

echo "4. Список фильмов с указанием тегов, которые были им присвоены пользователями. Сортировать по году выпуска, затем по названию фильма, затем по тегу. В списке оставить первые 40 записей."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT m.title AS movie_title, m.year AS year, t.tag AS tag
FROM tags t
JOIN movies m ON t.movie_id = m.id
ORDER BY m.year, m.title, t.tag
LIMIT 40;"
echo " "

echo "5. Список самых свежих фильмов (все фильмы последнего года выпуска, определяемого запросом)."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT id, title, year
FROM movies
WHERE year = (SELECT MAX(year) FROM movies)
ORDER BY title;"
echo " "

echo "6. Найти все драмы, выпущенные после 2005 года, которые понравились женщинам (оценка не ниже 4.5). Для каждого фильма вывести название, год выпуска и количество таких оценок. Отсортировать по году выпуска и названию фильма."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT m.title AS movie_title,
       m.year AS year,
       COUNT(*) AS likes_by_women_count
FROM movies m
JOIN ratings r ON m.id = r.movie_id
JOIN users u ON r.user_id = u.id
WHERE m.genres LIKE '%Drama%'
  AND m.year > 2005
  AND u.gender = 'female'
  AND r.rating >= 4.5
GROUP BY m.id
ORDER BY m.year, m.title;"
echo " "

echo "7. Анализ востребованности: количество пользователей, регистрировавшихся на сайте в каждом году. А также годы с максимальным и минимальным количеством регистраций."
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT strftime('%Y', register_date) AS year, COUNT(*) AS registrations
FROM users
GROUP BY year
ORDER BY year;"
echo " "
echo "Год с наибольшим числом регистраций:"
sqlite3 movies_rating.db -box -echo "SELECT year, registrations FROM (
    SELECT strftime('%Y', register_date) AS year, COUNT(*) AS registrations
    FROM users
    GROUP BY year
) ORDER BY registrations DESC LIMIT 1;"
echo " "
echo "Год с наименьшим числом регистраций:"
sqlite3 movies_rating.db -box -echo "SELECT year, registrations FROM (
    SELECT strftime('%Y', register_date) AS year, COUNT(*) AS registrations
    FROM users
    GROUP BY year
) ORDER BY registrations ASC LIMIT 1;"
echo " "

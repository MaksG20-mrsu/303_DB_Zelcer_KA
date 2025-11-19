#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo "1. Найти все пары пользователей, оценивших один и тот же фильм (первые 100 записей)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT u1.name AS user1, u2.name AS user2, m.title AS movie
FROM ratings r1
JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id
JOIN users u1 ON r1.user_id = u1.id
JOIN users u2 ON r2.user_id = u2.id
JOIN movies m ON r1.movie_id = m.id
LIMIT 100;"
echo " "

echo "2. 10 самых старых оценок от разных пользователей"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT m.title, u.name, r.rating, date(r.timestamp, 'unixepoch') AS date
FROM ratings r
JOIN (
  SELECT user_id, MIN(timestamp) AS ts
  FROM ratings
  GROUP BY user_id
) rmin ON r.user_id = rmin.user_id AND r.timestamp = rmin.ts
JOIN users u ON r.user_id = u.id
JOIN movies m ON r.movie_id = m.id
ORDER BY r.timestamp ASC
LIMIT 10;"
echo " "

echo "3. Фильмы с максимальным и минимальным средним рейтингом (включая пометку Рекомендуем)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "WITH avg_r AS (
  SELECT m.id, m.title, m.year, AVG(r.rating) AS avg_rating
  FROM movies m
  JOIN ratings r ON m.id = r.movie_id
  GROUP BY m.id
), bounds AS (
  SELECT MAX(avg_rating) AS maxr, MIN(avg_rating) AS minr FROM avg_r
)
SELECT ar.title, ar.year, ROUND(ar.avg_rating, 2) AS avg_rating,
       CASE WHEN ar.avg_rating = bounds.maxr THEN 'Да' ELSE 'Нет' END AS Рекомендуем
FROM avg_r ar, bounds
WHERE ar.avg_rating IN (bounds.maxr, bounds.minr)
ORDER BY ar.year, ar.title;"
echo " "

echo "4. Количество оценок и средняя оценка от пользователей-мужчин (2011-01-01 — 2014-12-31)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT COUNT(*) AS ratings_count, ROUND(AVG(r.rating), 2) AS avg_rating
FROM ratings r
JOIN users u ON r.user_id = u.id
WHERE lower(u.gender) = 'male'
  AND date(r.timestamp, 'unixepoch') BETWEEN '2011-01-01' AND '2014-12-31';"
echo " "

echo "5. Список фильмов с средней оценкой и количеством пользователей (первые 20)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT m.title, m.year, ROUND(AVG(r.rating), 2) AS avg_rating, COUNT(DISTINCT r.user_id) AS users_count
FROM movies m
LEFT JOIN ratings r ON m.id = r.movie_id
GROUP BY m.id
ORDER BY m.year, m.title
LIMIT 20;"
echo " "

echo "6. Самый распространенный жанр и количество фильмов в нём"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE
split(id, genre, rest) AS (
  SELECT id,
         trim(substr(genres, 1, CASE WHEN instr(genres, ',')=0 THEN length(genres) ELSE instr(genres, ',')-1 END)),
         CASE WHEN instr(genres, ',')=0 THEN '' ELSE substr(genres, instr(genres, ',')+1) END
  FROM movies
  WHERE genres IS NOT NULL
  UNION ALL
  SELECT id,
         trim(substr(rest, 1, CASE WHEN instr(rest, ',')=0 THEN length(rest) ELSE instr(rest, ',')-1 END)),
         CASE WHEN instr(rest, ',')=0 THEN '' ELSE substr(rest, instr(rest, ',')+1) END
  FROM split
  WHERE rest <> ''
)
SELECT genre AS most_common_genre, COUNT(DISTINCT id) AS movie_count
FROM (
  SELECT id, genre FROM split WHERE genre <> ''
)
GROUP BY genre
ORDER BY movie_count DESC
LIMIT 1;"
echo " "

echo "7. 10 последних зарегистрированных пользователей (Фамилия Имя|Дата регистрации)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT (
  CASE WHEN instr(name, ' ') = 0 THEN name
       ELSE substr(name, instr(name, ' ')+1) || ' ' || substr(name, 1, instr(name, ' ')-1)
  END
) || '|' || register_date AS info
FROM users
ORDER BY register_date DESC
LIMIT 10;"
echo " "

echo "8. На какие дни недели приходился ваш день рождения в каждом году"
echo "--------------------------------------------------"
BIRTH_RAW="05-03"
BIRTH_MD="$BIRTH_RAW"     # <- добавлено
START_YEAR=1970
END_YEAR=2025

sqlite3 movies_rating.db -box -echo <<SQL
WITH RECURSIVE years(y) AS (
SELECT $START_YEAR
UNION ALL
SELECT y+1 FROM years WHERE y < $END_YEAR
)
SELECT y AS year,
date(y || '-$BIRTH_MD') AS birth_date,
CASE strftime('%w', date(y || '-$BIRTH_MD'))
WHEN '0' THEN 'Воскресенье'
WHEN '1' THEN 'Понедельник'
WHEN '2' THEN 'Вторник'
WHEN '3' THEN 'Среда'
WHEN '4' THEN 'Четверг'
WHEN '5' THEN 'Пятница'
WHEN '6' THEN 'Суббота'
END AS weekday
FROM years;
SQL
echo " "
echo "Готово."

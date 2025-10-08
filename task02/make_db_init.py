import csv
import os
import sqlite3

def read_occupations():
    occupations = []
    try:
        with open('dataset/occupation.txt', 'r', encoding='utf-8') as f:
            for line in f:
                occupation = line.strip()
                if occupation:
                    occupations.append(occupation)
    except Exception as e:
        print(f"Error reading occupations: {e}")
    return occupations

def read_genres():
    genres = []
    try:
        with open('dataset/genres.txt', 'r', encoding='utf-8') as f:
            for line in f:
                genre = line.strip()
                if genre:
                    genres.append(genre)
    except Exception as e:
        print(f"Error reading genres: {e}")
    return genres

def read_users():
    users = []
    try:
        with open('dataset/users.txt', 'r', encoding='utf-8') as f:
            for line in f:
                parts = line.strip().split('|')
                if len(parts) == 6:
                    users.append({
                        'id': int(parts[0]),
                        'name': parts[1],
                        'email': parts[2],
                        'gender': parts[3],
                        'register_date': parts[4],
                        'occupation': parts[5]
                    })
    except Exception as e:
        print(f"Error reading users: {e}")
    return users

def read_movies():
    movies = []
    try:
        with open('dataset/movies.csv', 'r', encoding='utf-8') as f:
            reader = csv.DictReader(f)
            for row in reader:
                movies.append({
                    'id': int(row['movieId']),
                    'title': row['title'],
                    'genres': row['genres']
                })
    except Exception as e:
        print(f"Error reading movies: {e}")
    return movies

def read_ratings():
    ratings = []
    try:
        with open('dataset/ratings.csv', 'r', encoding='utf-8') as f:
            reader = csv.DictReader(f)
            for row in reader:
                ratings.append({
                    'user_id': int(row['userId']),
                    'movie_id': int(row['movieId']),
                    'rating': float(row['rating']),
                    'timestamp': int(row['timestamp'])
                })
    except Exception as e:
        print(f"Error reading ratings: {e}")
    return ratings

def read_tags():
    tags = []
    try:
        with open('dataset/tags.csv', 'r', encoding='utf-8') as f:
            reader = csv.DictReader(f)
            for row in reader:
                tags.append({
                    'user_id': int(row['userId']),
                    'movie_id': int(row['movieId']),
                    'tag': row['tag'],
                    'timestamp': int(row['timestamp'])
                })
    except Exception as e:
        print(f"Error reading tags: {e}")
    return tags

def escape_sql_value(value):
    if value is None:
        return 'NULL'
    elif isinstance(value, str):
        escaped = value.replace("'", "''")
        return f"'{escaped}'"
    elif isinstance(value, (int, float)):
        return str(value)
    else:
        return f"'{str(value)}'"

def generate_sql_script():
    sql_commands = []
    
    # Drop tables
    sql_commands.append("DROP TABLE IF EXISTS tags;")
    sql_commands.append("DROP TABLE IF EXISTS ratings;")
    sql_commands.append("DROP TABLE IF EXISTS movies;")
    sql_commands.append("DROP TABLE IF EXISTS users;")
    sql_commands.append("")
    
    # Create tables
    sql_commands.append("CREATE TABLE users (")
    sql_commands.append("    id INTEGER PRIMARY KEY AUTOINCREMENT,")
    sql_commands.append("    name VARCHAR(100) NOT NULL,")
    sql_commands.append("    email VARCHAR(255) NOT NULL,")
    sql_commands.append("    gender VARCHAR(10) NOT NULL,")
    sql_commands.append("    register_date DATE NOT NULL,")
    sql_commands.append("    occupation VARCHAR(50) NOT NULL")
    sql_commands.append(");")
    sql_commands.append("")
    
    sql_commands.append("CREATE TABLE movies (")
    sql_commands.append("    id INTEGER PRIMARY KEY,")
    sql_commands.append("    title VARCHAR(255) NOT NULL,")
    sql_commands.append("    year INTEGER,")
    sql_commands.append("    genres VARCHAR(255) NOT NULL")
    sql_commands.append(");")
    sql_commands.append("")
    
    sql_commands.append("CREATE TABLE ratings (")
    sql_commands.append("    id INTEGER PRIMARY KEY AUTOINCREMENT,")
    sql_commands.append("    user_id INTEGER NOT NULL,")
    sql_commands.append("    movie_id INTEGER NOT NULL,")
    sql_commands.append("    rating REAL NOT NULL,")
    sql_commands.append("    timestamp INTEGER NOT NULL,")
    sql_commands.append("    FOREIGN KEY (user_id) REFERENCES users(id),")
    sql_commands.append("    FOREIGN KEY (movie_id) REFERENCES movies(id)")
    sql_commands.append(");")
    sql_commands.append("")
    
    sql_commands.append("CREATE TABLE tags (")
    sql_commands.append("    id INTEGER PRIMARY KEY AUTOINCREMENT,")
    sql_commands.append("    user_id INTEGER NOT NULL,")
    sql_commands.append("    movie_id INTEGER NOT NULL,")
    sql_commands.append("    tag TEXT NOT NULL,")
    sql_commands.append("    timestamp INTEGER NOT NULL,")
    sql_commands.append("    FOREIGN KEY (user_id) REFERENCES users(id),")
    sql_commands.append("    FOREIGN KEY (movie_id) REFERENCES movies(id)")
    sql_commands.append(");")
    sql_commands.append("")
    
    # Insert data
    print("Reading users...")
    users = read_users()
    for user in users:
        sql = f"INSERT INTO users (id, name, email, gender, register_date, occupation) VALUES ("
        sql += f"{user['id']}, "
        sql += f"{escape_sql_value(user['name'])}, "
        sql += f"{escape_sql_value(user['email'])}, "
        sql += f"{escape_sql_value(user['gender'])}, "
        sql += f"{escape_sql_value(user['register_date'])}, "
        sql += f"{escape_sql_value(user['occupation'])}"
        sql += ");"
        sql_commands.append(sql)
    
    print("Reading movies...")
    movies = read_movies()
    for movie in movies:
        title = movie['title']
        year = None
        if '(' in title and ')' in title:
            try:
                year_start = title.rfind('(') + 1
                year_end = title.rfind(')')
                year_str = title[year_start:year_end]
                if year_str.isdigit():
                    year = int(year_str)
            except:
                year = None
        
        sql = f"INSERT INTO movies (id, title, year, genres) VALUES ("
        sql += f"{movie['id']}, "
        sql += f"{escape_sql_value(title)}, "
        sql += f"{year if year is not None else 'NULL'}, "
        sql += f"{escape_sql_value(movie['genres'])}"
        sql += ");"
        sql_commands.append(sql)
    
    print("Reading ratings...")
    ratings = read_ratings()
    for i, rating in enumerate(ratings, 1):
        sql = f"INSERT INTO ratings (id, user_id, movie_id, rating, timestamp) VALUES ("
        sql += f"{i}, "
        sql += f"{rating['user_id']}, "
        sql += f"{rating['movie_id']}, "
        sql += f"{rating['rating']}, "
        sql += f"{rating['timestamp']}"
        sql += ");"
        sql_commands.append(sql)
    
    print("Reading tags...")
    tags = read_tags()
    for i, tag in enumerate(tags, 1):
        sql = f"INSERT INTO tags (id, user_id, movie_id, tag, timestamp) VALUES ("
        sql += f"{i}, "
        sql += f"{tag['user_id']}, "
        sql += f"{tag['movie_id']}, "
        sql += f"{escape_sql_value(tag['tag'])}, "
        sql += f"{tag['timestamp']}"
        sql += ");"
        sql_commands.append(sql)
    
    return '\n'.join(sql_commands)

# MAIN CODE - без функции main()
print("=== Starting ETL Process ===")

# Check dataset directory
if not os.path.exists('dataset'):
    print("ERROR: dataset directory not found!")
    print("Current directory:", os.getcwd())
    print("Files in current directory:", os.listdir('.'))
    exit(1)

# Check required files
required_files = [
    'dataset/users.txt',
    'dataset/occupation.txt', 
    'dataset/genres.txt',
    'dataset/movies.csv',
    'dataset/ratings.csv',
    'dataset/tags.csv'
]

print("Checking files...")
for file_path in required_files:
    if not os.path.exists(file_path):
        print(f"ERROR: File {file_path} not found!")
        exit(1)
    else:
        print(f"OK: {file_path}")

# Generate SQL
print("Generating SQL script...")
sql_script = generate_sql_script()

# Write to file
with open('db_init.sql', 'w', encoding='utf-8') as f:
    f.write(sql_script)

print("SUCCESS: db_init.sql created!")
print("To load into database run: sqlite3 movies_rating.db < db_init.sql")
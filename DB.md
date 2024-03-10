- User
  CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255),
    email VARCHAR(255),
    password VARCHAR(255),
    role ENUM('user', 'admin')
  );

- Account
  CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    balance INT,
    user INT NULL,
    FOREIGN KEY (user) REFERENCES users(id)
  );

- Category
  CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('income', 'expenses'),
    name VARCHAR(255),
    image VARCHAR(255)
  );

- Operation
  CREATE TABLE operations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('income', 'expenses'),
    amount INT,
    date DATE,
    comment TEXT,
    account INT,
    category INT,
    user INT,
    FOREIGN KEY (account) REFERENCES accounts(id),
    FOREIGN KEY (category) REFERENCES categories(id),
    FOREIGN KEY (user) REFERENCES users(id)
  );
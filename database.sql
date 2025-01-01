CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contestant_id INT NOT NULL,
    contestant_name VARCHAR(255) NOT NULL,
    votes INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    vote_date DATETIME NOT NULL
); 
USE skill_exchange;

CREATE TABLE IF NOT EXISTS assignments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    requestId       INT NOT NULL,
    providerId      INT NOT NULL,
    seekerId        INT NOT NULL,
    title           VARCHAR(150) NOT NULL,
    description     TEXT,
    deadline        DATETIME NOT NULL,
    fileName        VARCHAR(255) NULL,
    filePath        VARCHAR(255) NULL,
    createdAt       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requestId) REFERENCES exchangeRequests(id) ON DELETE CASCADE,
    FOREIGN KEY (providerId) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seekerId) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS assignmentSubmissions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    assignmentId    INT NOT NULL,
    seekerId        INT NOT NULL,
    fileName        VARCHAR(255) NOT NULL,
    filePath        VARCHAR(255) NOT NULL,
    submittedAt     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('On Time','Late') DEFAULT 'On Time',
    FOREIGN KEY (assignmentId) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (seekerId) REFERENCES users(id) ON DELETE CASCADE
);

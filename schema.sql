-- Student Skill Exchange Platform
-- Database schema (matches the ER diagram: users, roles, skills, exchangeRequests, exchangeReviews)

DROP DATABASE IF EXISTS skill_exchange;
CREATE DATABASE skill_exchange;
USE skill_exchange;

-- ROLES ---------------------------------------------------------------
CREATE TABLE roles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)  NOT NULL UNIQUE,   -- 'Skill Provider' | 'Skill Seeker' | 'Admin'
    description VARCHAR(255)
);

INSERT INTO roles (name, description) VALUES
('Skill Provider', 'Offers skills to teach or share with other students'),
('Skill Seeker',   'Looks for skills to learn from other students'),
('Admin',          'Manages users and roles, monitors exchanges, generates reports');

-- USERS -----------------------------------------------------------------
CREATE TABLE users (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    email     VARCHAR(150) NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,             -- stored as a bcrypt hash
    firstName VARCHAR(100) NOT NULL,
    lastName  VARCHAR(100) NOT NULL,
    roleId    INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (roleId) REFERENCES roles(id)
);

-- AVAILABILITY ------------------------------------------------------------
CREATE TABLE userAvailability (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    userId      INT NOT NULL,
    dayOfWeek   TINYINT NOT NULL CHECK (dayOfWeek BETWEEN 0 AND 6),
    startTime   TIME NOT NULL,
    endTime     TIME NOT NULL,
    createdAt   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_day (userId, dayOfWeek)
);

-- SKILLS ------------------------------------------------------------------
CREATE TABLE skills (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    providerId       INT NOT NULL,
    name             VARCHAR(150) NOT NULL,
    proficiencyLevel VARCHAR(50) NOT NULL,        -- e.g. Beginner / Intermediate / Advanced
    createdAt        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (providerId) REFERENCES users(id) ON DELETE CASCADE
);

-- EXCHANGE REQUESTS --------------------------------------------------------
CREATE TABLE exchangeRequests (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    skillId      INT NOT NULL,
    seekerId     INT NOT NULL,
    providerId   INT NOT NULL,
    status       ENUM('Pending','Accepted','Declined','Completed') DEFAULT 'Pending',
    requestedAt  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completedAt  TIMESTAMP NULL,
    FOREIGN KEY (skillId)    REFERENCES skills(id) ON DELETE CASCADE,
    FOREIGN KEY (seekerId)   REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (providerId) REFERENCES users(id)  ON DELETE CASCADE
);

-- EXCHANGE MESSAGES --------------------------------------------------------
CREATE TABLE exchangeMessages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    requestId   INT NOT NULL,
    senderId    INT NOT NULL,
    receiverId  INT NOT NULL,
    message     TEXT NOT NULL,
    sentAt      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requestId) REFERENCES exchangeRequests(id) ON DELETE CASCADE,
    FOREIGN KEY (senderId)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiverId) REFERENCES users(id) ON DELETE CASCADE
);

-- ASSIGNMENTS --------------------------------------------------------------
CREATE TABLE assignments (
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

CREATE TABLE assignmentSubmissions (
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

-- EXCHANGE REVIEWS ----------------------------------------------------------
CREATE TABLE exchangeReviews (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    exchangeRequestId  INT NOT NULL,
    reviewerId         INT NOT NULL,
    revieweeId         INT NOT NULL,
    rating             TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    writtenReview      TEXT,
    createdAt          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exchangeRequestId) REFERENCES exchangeRequests(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewerId)        REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (revieweeId)        REFERENCES users(id) ON DELETE CASCADE
);

-- Handy index for browsing/searching skills
CREATE INDEX idx_skills_name ON skills(name);

-- Create Database
CREATE DATABASE IF NOT EXISTS job_portal;
USE job_portal;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','company','admin') NOT NULL,
    delete_request ENUM('none', 'pending') DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OPPORTUNITIES TABLE
CREATE TABLE IF NOT EXISTS opportunities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    type ENUM('internship','job') NOT NULL,
    category VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    internship_type ENUM('paid', 'unpaid', 'n/a') DEFAULT 'n/a',
    salary_type ENUM('fixed', 'range', 'none') DEFAULT 'none',
    salary_amount DECIMAL(10, 2) DEFAULT 0.00,
    salary_max DECIMAL(10, 2) DEFAULT 0.00,
    applicants_needed INT DEFAULT 1,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    deadline DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (company_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);

-- APPLICATIONS TABLE
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    opportunity_id INT NOT NULL,
    student_id INT NOT NULL,
    message TEXT,
    cv_file VARCHAR(255),
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (opportunity_id)
    REFERENCES opportunities(id)
    ON DELETE CASCADE,

    FOREIGN KEY (student_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);

-- SAVED OPPORTUNITIES TABLE
CREATE TABLE IF NOT EXISTS saved_opportunities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    opportunity_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE
);

-- SAMPLE ACCOUNTS (Plain Text)
INSERT INTO users (full_name, email, password, role) VALUES 
('System Admin', 'admin@gmail.com', 'password123', 'admin'),
('Tech Corp', 'company@gmail.com', 'password123', 'company'),
('John Doe', 'student@gmail.com', 'password123', 'student');

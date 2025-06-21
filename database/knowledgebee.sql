-- Knowledge Bee Database Schema
-- Created for PHP, MySQL, Apache (XAMPP) stack

-- Create database
CREATE DATABASE IF NOT EXISTS knowledgebee;
USE knowledgebee;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255) DEFAULT 'default.jpg',
    coins INT DEFAULT 100,
    bio TEXT,
    skills_teach TEXT,
    skills_learn TEXT,
    is_admin BOOLEAN DEFAULT FALSE,
    is_verified BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'suspended', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Skills table
CREATE TABLE skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    category ENUM('tech', 'non-tech') NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    is_banned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Content table
CREATE TABLE content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    type ENUM('video', 'blog', 'quiz') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    content_data TEXT,
    coin_cost INT DEFAULT 0,
    likes INT DEFAULT 0,
    dislikes INT DEFAULT 0,
    views INT DEFAULT 0,
    status ENUM('pending', 'approved', 'flagged', 'hidden') DEFAULT 'pending',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

-- Quizzes table
CREATE TABLE quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('a', 'b', 'c', 'd') NOT NULL,
    explanation TEXT,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

-- Quiz attempts table
CREATE TABLE quiz_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    passed BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

-- Upvotes table
CREATE TABLE upvotes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    vote_type ENUM('like', 'dislike') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (user_id, content_id)
);

-- Comments table
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reports table
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content_id INT NOT NULL,
    user_id INT NOT NULL,
    reason ENUM('spam', 'plagiarism', 'inappropriate', 'irrelevant', 'other') NOT NULL,
    description TEXT,
    report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Badges table
CREATE TABLE badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    badge_title VARCHAR(100) NOT NULL,
    badge_type ENUM('mentor', 'expert', 'contributor', 'verified') NOT NULL,
    earned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('coin_earned', 'upvote', 'comment', 'badge', 'quiz_passed', 'content_used') NOT NULL,
    related_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Coin transactions table
CREATE TABLE coin_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    transaction_type ENUM('earned', 'spent') NOT NULL,
    amount INT NOT NULL,
    description TEXT,
    related_content_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_content_id) REFERENCES content(id) ON DELETE SET NULL
);

-- Content access table (track who accessed what content)
CREATE TABLE content_access (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    coins_spent INT DEFAULT 0,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

-- Insert sample data

-- Admin user
INSERT INTO users (username, email, password, is_admin, is_verified, coins) VALUES 
('admin', 'admin@knowledgebee.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, TRUE, 1000);

-- Sample skills
INSERT INTO skills (name, category, description) VALUES
('HTML', 'tech', 'HyperText Markup Language for web development'),
('CSS', 'tech', 'Cascading Style Sheets for web styling'),
('JavaScript', 'tech', 'Programming language for web interactivity'),
('PHP', 'tech', 'Server-side scripting language'),
('Python', 'tech', 'General-purpose programming language'),
('Guitar', 'non-tech', 'String instrument playing'),
('Cooking', 'non-tech', 'Culinary arts and food preparation'),
('Yoga', 'non-tech', 'Physical and mental wellness practice'),
('Photography', 'non-tech', 'Art of capturing images'),
('Public Speaking', 'non-tech', 'Communication and presentation skills');

-- Sample regular users
INSERT INTO users (username, email, password, bio, skills_teach, skills_learn, coins) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Web developer passionate about teaching', 'HTML,CSS,JavaScript', 'Python,Guitar', 250),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Yoga instructor and wellness coach', 'Yoga,Cooking', 'Photography,Public Speaking', 180),
('mike_dev', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Full-stack developer', 'PHP,Python', 'Guitar,Cooking', 320);

-- Sample content
INSERT INTO content (user_id, skill_id, type, title, description, content_data, coin_cost, status) VALUES
(2, 1, 'blog', 'HTML Basics for Beginners', 'Learn the fundamentals of HTML markup language', 'HTML is the standard markup language for creating web pages. It describes the structure of a web page semantically and originally included cues for the appearance of the document. HTML elements are the building blocks of HTML pages. With HTML constructs, images and other objects such as interactive forms may be embedded into the rendered page. HTML provides a means to create structured documents by denoting structural semantics for text such as headings, paragraphs, lists, links, quotes and other items. HTML elements are delineated by tags, written using angle brackets.', 5, 'approved'),
(2, 2, 'video', 'CSS Flexbox Complete Guide', 'Master CSS Flexbox layout system', 'https://www.youtube.com/watch?v=JJSoEo8JSnc', 8, 'approved'),
(3, 7, 'blog', 'Easy Pasta Recipes', 'Quick and delicious pasta dishes for beginners', 'Here are some simple pasta recipes that anyone can make at home. Start with basic ingredients like pasta, olive oil, garlic, and herbs. For a classic aglio e olio, cook spaghetti al dente, then sauté minced garlic in olive oil until golden. Add red pepper flakes for heat, then toss with the pasta and fresh parsley. For a creamy carbonara, whisk eggs with grated cheese, then combine with hot pasta and crispy pancetta. The key is to work quickly so the eggs don\'t scramble. These recipes are perfect for beginners and can be customized with your favorite ingredients.', 3, 'approved'),
(4, 8, 'video', 'Morning Yoga Routine', 'Start your day with this energizing yoga sequence', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 6, 'approved'),
(4, 9, 'quiz', 'Photography Fundamentals', 'Test your knowledge of basic photography concepts', 'This quiz covers essential photography concepts including composition, lighting, camera settings, and basic techniques. Test your understanding of the rule of thirds, aperture, shutter speed, ISO, and more. Perfect for beginners and intermediate photographers looking to improve their skills.', 4, 'approved');

-- Sample quiz questions
INSERT INTO quizzes (content_id, question, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES
(5, 'What is the rule of thirds in photography?', 'A composition guideline', 'A camera setting', 'A lighting technique', 'A lens type', 'a', 'The rule of thirds is a composition guideline that divides the frame into nine equal parts.'),
(5, 'What does ISO represent in photography?', 'Image Sensor Output', 'International Standards Organization', 'Image Stabilization Option', 'Internal Storage Option', 'b', 'ISO refers to the International Standards Organization rating for film sensitivity.');

-- Sample badges
INSERT INTO badges (user_id, skill_id, badge_title, badge_type) VALUES
(2, 1, 'HTML Mentor', 'mentor'),
(2, 2, 'CSS Expert', 'expert'),
(3, 7, 'Cooking Contributor', 'contributor'),
(4, 8, 'Yoga Verified', 'verified');

-- Sample notifications
INSERT INTO notifications (user_id, message, type) VALUES
(2, 'You earned 10 Buzz Coins for uploading content!', 'coin_earned'),
(3, 'Your content received 5 upvotes!', 'upvote'),
(4, 'Congratulations! You earned the "Yoga Verified" badge!', 'badge');

-- Sample coin transactions
INSERT INTO coin_transactions (user_id, transaction_type, amount, description, related_content_id) VALUES
(2, 'earned', 10, 'Content upload reward', 1),
(2, 'earned', 10, 'Content upload reward', 2),
(3, 'earned', 10, 'Content upload reward', 3),
(4, 'earned', 10, 'Content upload reward', 4),
(4, 'earned', 10, 'Content upload reward', 5);

-- Create indexes for better performance
CREATE INDEX idx_content_skill ON content(skill_id);
CREATE INDEX idx_content_user ON content(user_id);
CREATE INDEX idx_content_status ON content(status);
CREATE INDEX idx_upvotes_content ON upvotes(content_id);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(is_read);
CREATE INDEX idx_transactions_user ON coin_transactions(user_id); 
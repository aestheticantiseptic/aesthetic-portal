USE pinkspace_db;

CREATE TABLE profile_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_owner_id INT NOT NULL,
    author_user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (author_user_id) REFERENCES users(id) ON DELETE CASCADE
);

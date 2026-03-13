CREATE TABLE IF NOT EXISTS clients (
    id         INT UNSIGNED NOT NULL,
    name       VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id  INT UNSIGNED NOT NULL,
    rating     TINYINT NOT NULL,
    comment    TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_reviews_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO clients (id, name) VALUES (1, 'Иван Иванов');
INSERT IGNORE INTO clients (id, name) VALUES (2, 'Мария Петрова');
INSERT IGNORE INTO clients (id, name) VALUES (3, 'Алексей Смирнов');

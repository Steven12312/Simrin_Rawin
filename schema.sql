DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'bride', 'groom') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS guests;
CREATE TABLE guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_hash VARCHAR(32) NOT NULL UNIQUE,
    guest_side ENUM('bride', 'groom', 'both') NOT NULL DEFAULT 'both',
    salutation_1 VARCHAR(20) DEFAULT NULL,
    first_name_1 VARCHAR(100) NOT NULL,
    last_name_1 VARCHAR(100) NOT NULL,
    salutation_2 VARCHAR(20) DEFAULT NULL,
    first_name_2 VARCHAR(100) DEFAULT NULL,
    last_name_2 VARCHAR(100) DEFAULT NULL,
    phone_number VARCHAR(50) DEFAULT NULL,
    invited_events JSON DEFAULT NULL,
    family_members JSON DEFAULT NULL,
    with_family TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pending', 'accepted', 'declined', 'partial') DEFAULT 'pending',
    rsvp_status_events JSON DEFAULT NULL,
    attending_members JSON DEFAULT NULL,
    attending_members_version TINYINT(1) NOT NULL DEFAULT 1,
    message TEXT DEFAULT NULL,
    dietary_info TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

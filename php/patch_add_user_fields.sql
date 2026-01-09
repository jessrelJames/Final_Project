ALTER TABLE users
ADD COLUMN first_name VARCHAR(50) AFTER id,
ADD COLUMN last_name VARCHAR(50) AFTER first_name,
ADD COLUMN phone VARCHAR(20) AFTER email,
ADD COLUMN date_of_birth DATE AFTER phone,
ADD COLUMN gender ENUM('Male', 'Female', 'Other') AFTER date_of_birth;

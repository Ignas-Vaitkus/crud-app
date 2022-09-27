DROP DATABASE IF EXISTS company;

CREATE DATABASE company DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_lithuanian_ci;

CREATE TABLE company.Projects (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
project_name VARCHAR(30) NOT NULL
) ENGINE=Innodb;

INSERT INTO company.Projects (project_name)
VALUES
	('E-Shop'),
    ('Enterprise Resource Planning'),
    ('CRUD App');

CREATE TABLE company.Employees (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(30) NOT NULL,
    lastname VARCHAR(30) NOT NULL
) ENGINE=Innodb;

INSERT INTO company.Employees (firstname, lastname)
VALUES
	('Jonas', 'Jonaitis'),
    ('Petras', 'Petraitis'),
    ('Zita', 'Zitaitė'),
    ('Rolandas', 'Kazlas'),
    ('Dalia', 'Grybauskaitė'),
    ('Ingrida', 'Šimonytė');

CREATE TABLE company.Project_Employee (
	project_id INT UNSIGNED,
    employee_id INT UNSIGNED,
    FOREIGN KEY (project_id) REFERENCES Projects(id),
    FOREIGN KEY (employee_id) REFERENCES Employees(id)
) ENGINE=Innodb;

INSERT INTO company.Project_Employee (project_id, employee_id)
VALUES
	(1, 1),
    (1, 2),
    (1, 5),
    (2, 1),
    (2, 3),
    (2, 4),
    (3, 4),
    (3, 5),
    (3, 6);
    
DROP USER IF EXISTS  'crud_user'@'localhost';
    
CREATE USER 'crud_user'@'localhost' IDENTIFIED BY 'mysql';

GRANT DELETE, INSERT, SELECT, UPDATE ON company.Projects TO 'crud_user'@'localhost';
GRANT DELETE, INSERT, SELECT, UPDATE ON company.Employees TO 'crud_user'@'localhost';
GRANT DELETE, INSERT, SELECT, UPDATE ON company.Project_Employee TO 'crud_user'@'localhost';

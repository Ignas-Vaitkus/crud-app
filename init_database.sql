DROP DATABASE IF EXISTS company_crud_user;

CREATE DATABASE company_crud_user DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_lithuanian_ci;

CREATE TABLE company_crud_user.Projects (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
project_name VARCHAR(30) NOT NULL
) ENGINE=Innodb;

INSERT INTO company_crud_user.Projects (project_name)
VALUES
	('E-Shop'),
    ('Enterprise Resource Planning'),
    ('CRUD App');

CREATE TABLE company_crud_user.Employees (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(30) NOT NULL,
    lastname VARCHAR(30) NOT NULL
) ENGINE=Innodb;

INSERT INTO company_crud_user.Employees (firstname, lastname)
VALUES
	('Jonas', 'Jonaitis'),
    ('Petras', 'Petraitis'),
    ('Zita', 'Zitaitė'),
    ('Rolandas', 'Kazlas'),
    ('Dalia', 'Grybauskaitė'),
    ('Ingrida', 'Šimonytė');

CREATE TABLE company_crud_user.Project_Employee (
	project_id INT UNSIGNED,
    employee_id INT UNSIGNED,
    FOREIGN KEY (project_id) REFERENCES Projects(id),
    FOREIGN KEY (employee_id) REFERENCES Employees(id)
) ENGINE=Innodb;

INSERT INTO company_crud_user.Project_Employee (project_id, employee_id)
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

GRANT DELETE, INSERT, SELECT, UPDATE ON company_crud_user.Projects TO 'crud_user'@'localhost';
GRANT DELETE, INSERT, SELECT, UPDATE ON company_crud_user.Employees TO 'crud_user'@'localhost';
GRANT DELETE, INSERT, SELECT, UPDATE ON company_crud_user.Project_Employee TO 'crud_user'@'localhost';

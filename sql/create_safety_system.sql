

DROP USER IF EXISTS suser;
CREATE USER suser IDENTIFIED BY 'safe';

DROP DATABASE IF EXISTS safety_system;

CREATE DATABASE safety_system;
GRANT ALL ON safety_system.* TO suser;

USE safety_system;
source newsafesystem_table.sql;

SELECT * FROM employee;
SELECT * FROM safety;
SELECT * FROM department;



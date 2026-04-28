-- 部署情報
CREATE TABLE IF NOT EXISTS department(
    d_id        INT AUTO_INCREMENT,
    dname       VARCHAR(50) NOT NULL,
    create_id   INT NOT NULL,
    create_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(d_id)
);


-- 社員情報
CREATE TABLE IF NOT EXISTS employee(
    emp_id          INT AUTO_INCREMENT,
    ename           VARCHAR(50) NOT NULL,
    birth           DATE NOT NULL,
    tel             VARCHAR(50) NOT NULL,
    department      INT NOT NULL,
    post            VARCHAR(20) NOT NULL,
    administrator   BOOLEAN DEFAULT 0,
    password        VARCHAR(255) NOT NULL,
    create_id       INT NOT NULL,
    create_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(emp_id),
    FOREIGN KEY(department) REFERENCES department(d_id),
    UNIQUE(tel)
);

-- 備考欄
-- 安否情報
CREATE TABLE IF NOT EXISTS safety(
    responce_id INT AUTO_INCREMENT,
    emp_id      INT,
    safe        INT NOT NULL,
    status      VARCHAR(10) NOT NULL DEFAULT '無事',
    go_office   BOOLEAN NOT NULL DEFAULT 0,
    note        VARCHAR(255) DEFAULT NULL,
    create_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    isDelete  BOOLEAN DEFAULT 0,
    PRIMARY KEY(responce_id),
    FOREIGN KEY(emp_id) REFERENCES employee(emp_id)
);



INSERT INTO department (dname, create_id) VALUES
 ('営業', '4'),('人事', '4'),('総務', '4'),('経理', '4');

--  データ挿入
-- 役職序列
-- 専務　＞　部長　＞　主任　＞　一般社員
 INSERT INTO employee (ename, birth, tel, department, post, administrator, password, create_id) VALUES
 ('admin1', '1981-1-1', '000-0000-1111', '1','一般社員', '0', '$2y$12$vnrqCCwvee9qijFDlUDcuOdgTXiaKrHUgiMXy2iwpJ9Ba/RLWKtfy', '4'),
 ('admin2', '1970-2-2', '000-0000-2222', '1','部長',     '0', '$2y$12$m.8RxRTV0osbUGapkQiNOOaPSRAZ5IVhQeybQLLSmLZmiySeohw5q', '4'),
 ('admin3', '2000-3-3', '000-0000-3333', '2','一般社員', '1', '$2y$12$AAsxqtGtM5HK8emvdmNR3OzFzlNLSgOoQgRnC4kxD.qVSwtfZlBqe', '4'),
 ('admin4', '1991-4-4', '000-0000-4444', '3','主任',     '0', '$2y$12$zjUNvKtyHnfLBwlwSxNfA./Tw3BO5Mumpykl2Agp81hrMqLDchtl.', '4'),
 ('admin5', '1890-5-5', '000-0000-5555', '4','専務',     '1', '$2y$12$f0uYCl9Qs9qHo2bY4qf/u.ztcDryefaeot6AUldfLkoodV1Fsh5zW', '3');

 INSERT INTO safety (emp_id, safe, status, go_office) VALUES
 ('1', '0', '無事', '0'),
 ('2', '1', '軽傷', '0'),
 ('3', '0', '無事', '0'),
 ('4', '0', '無事', '0'),
 ('5', '1', '重症', '1');

 -- 役職情報 
CREATE TABLE IF NOT EXISTS post(
    p_id        INT AUTO_INCREMENT,
    pname       VARCHAR(50) NOT NULL,
    create_id   INT NOT NULL, -- 最終編集者のID
    create_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(p_id)
);

-- 役職情報追加
INSERT INTO post (pname, create_id) VALUES
 ('一般社員', '4'),('課長', '4'),('部長', '4'),('専務', '4');

-- 社員の役職情報変更
 UPDATE employee SET post = '1' WHERE emp_id = '1';
 UPDATE employee SET post = '3' WHERE emp_id = '2';
 UPDATE employee SET post = '1' WHERE emp_id = '3';
 UPDATE employee SET post = '2' WHERE emp_id = '4';
 UPDATE employee SET post = '4' WHERE emp_id = '5';


-- 数値型の役職カラムを作成
ALTER TABLE employee ADD epost INT;

-- 元の役職カラムを現カラムに移動
UPDATE employee 
SET epost = CASE
    WHEN post REGEXP '^[0-9]+$'
    THEN CAST(post AS SIGNED)
    ELSE NULL
END;

-- 削除＆名称変更
ALTER TABLE employee DROP COLUMN post;
ALTER TABLE employee RENAME COLUMN epost TO post_id;

-- status削除
ALTER TABLE safety DROP COLUMN status;

-- go_officeからcan_workに変更
ALTER TABLE safety RENAME COLUMN go_office TO can_work;

-- 外部キー追加
ALTER TABLE employee ADD FOREIGN KEY(post_id) REFERENCES post(p_id);

SELECT * FROM employee;
SELECT * FROM safety;
SELECT * FROM department;
SELECT * FROM post;
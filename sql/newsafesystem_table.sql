-- 部署情報
CREATE TABLE IF NOT EXISTS department(
    d_id        INT AUTO_INCREMENT,
    dname       VARCHAR(50) NOT NULL,
    create_id   INT NOT NULL, -- 最終編集者のID
    create_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(d_id)
);

-- 役職情報 
CREATE TABLE IF NOT EXISTS post(
    p_id        INT AUTO_INCREMENT,
    pname       VARCHAR(50) NOT NULL,
    create_id   INT NOT NULL, -- 最終編集者のID
    create_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(p_id)
);

-- 社員情報
CREATE TABLE IF NOT EXISTS employee(
    emp_id          INT AUTO_INCREMENT,
    ename           VARCHAR(50) NOT NULL,
    birth           DATE NOT NULL,
    tel             VARCHAR(50) NOT NULL,
    d_id   INT NOT NULL, -- 部署ID
    p_id         INT NOT NULL, -- 役職ID
    administrator   BOOLEAN DEFAULT 0, -- システム管理部に属しているかどうか、管理者かどうか
    password        VARCHAR(255) NOT NULL,
    create_id       INT NOT NULL, -- 最終編集者のID
    create_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(emp_id),
    FOREIGN KEY(d_id) REFERENCES department(d_id),
    FOREIGN KEY(p_id) REFERENCES post(p_id),
    UNIQUE(tel)
);

-- 備考欄
-- 安否情報
CREATE TABLE IF NOT EXISTS safety(
    responce_id INT AUTO_INCREMENT,
    emp_id      INT,
    safe        BOOLEAN NOT NULL DEFAULT 0,
    can_work   BOOLEAN NOT NULL DEFAULT 0,
    note        VARCHAR(255),-- 備考欄
    create_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    isdelete   BOOLEAN DEFAULT 0,  -- 倫理削除済かどうか
    PRIMARY KEY(responce_id),
    FOREIGN KEY(emp_id) REFERENCES employee(emp_id)
);

--  データ挿入

INSERT INTO department (dname, create_id) VALUES
 ('営業', '4'),('人事', '4'),('総務', '4'),('経理', '4');

-- 役職序列
-- 専務　＞　部長　＞　主任　＞　一般社員
INSERT INTO post (pname, create_id) VALUES
 ('一般社員', '4'),('課長', '4'),('部長', '4'),('専務', '4');

INSERT INTO employee (ename, birth, tel, d_id, p_id, administrator, password, create_id) VALUES
 ('朝居朱梨', '1981-1-1', '000-0000-1111', '1','1', '0', '$2y$12$vnrqCCwvee9qijFDlUDcuOdgTXiaKrHUgiMXy2iwpJ9Ba/RLWKtfy', '4'),
 ('木之下宏人', '1970-2-2', '000-0000-2222', '1','3', '0', '$2y$12$m.8RxRTV0osbUGapkQiNOOaPSRAZ5IVhQeybQLLSmLZmiySeohw5q', '4'),
 ('杉野琉斗', '2000-3-3', '000-0000-3333', '2','1', '1', '$2y$12$AAsxqtGtM5HK8emvdmNR3OzFzlNLSgOoQgRnC4kxD.qVSwtfZlBqe', '4'),
 ('寺田星奈', '1991-4-4', '000-0000-4444', '3','2', '0', '$2y$12$zjUNvKtyHnfLBwlwSxNfA./Tw3BO5Mumpykl2Agp81hrMqLDchtl.', '4'),
 ('能木孝二', '1890-5-5', '000-0000-5555', '4','4', '1', '$2y$12$f0uYCl9Qs9qHo2bY4qf/u.ztcDryefaeot6AUldfLkoodV1Fsh5zW', '3');
-- 上から順番に、admin1,admin2,admin3,admin4,admin5でログインできるハッシュ値です

 INSERT INTO safety (emp_id, safe, can_work) VALUES
 ('1', '0', '0'),
 ('2', '1', '0'),
 ('3', '0', '0'),
 ('4', '0', '0'),
 ('5', '1', '1');

 
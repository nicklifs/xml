create database db;
use db;

CREATE TABLE nodes
(
    id      INT AUTO_INCREMENT,
    name    CHAR(20) NOT NULL,
    value   CHAR(100),
	hasChilds bool,
    id_par  INT,
    PRIMARY KEY (id)
);

CREATE TABLE attrs
(
    id      INT AUTO_INCREMENT,
    name    CHAR(20) NOT NULL,
	value   CHAR(50),
    id_node INT,
    PRIMARY KEY (id),
	FOREIGN KEY (id_node) REFERENCES nodes(id) 
);

CREATE TABLE files
(
    id      INT AUTO_INCREMENT,
    name    CHAR(20) NOT NULL,
    id_node INT,
    PRIMARY KEY (id)
);
CREATE DATABASE infobio CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE infobio;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE
);

INSERT INTO usuarios (nome, email) VALUES
('Fulano de Tal', 'fulano@ufcspa.edu.br'),
('Siclana de Tal', 'siclana@teste.com');

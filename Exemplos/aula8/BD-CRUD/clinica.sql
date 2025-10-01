CREATE DATABASE IF NOT EXISTS clinica
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE clinica;

CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    data_nascimento DATE,
    telefone VARCHAR(20),
    email VARCHAR(100),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    data_consulta DATE NOT NULL,
    especialidade VARCHAR(50) NOT NULL,
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);

-- Pacientes de exemplo
INSERT INTO pacientes (nome, cpf, data_nascimento, telefone, email) VALUES
('Maria Silva', '12345678901', '1985-04-12', '(51) 99999-1111', 'maria.silva@exemplo.com'),
('João Pereira', '98765432100', '1990-08-25', '(51) 98888-2222', 'joao.pereira@exemplo.com'),
('Ana Souza', '45612378900', '1975-12-05', '(51) 97777-3333', 'ana.souza@exemplo.com');

-- Consultas de exemplo
INSERT INTO consultas (paciente_id, data_consulta, especialidade, observacoes) VALUES
(1, '2025-10-01', 'Cardiologia', 'Paciente com histórico de hipertensão.'),
(1, '2025-10-15', 'Dermatologia', 'Avaliação de manchas na pele.'),
(2, '2025-10-10', 'Ortopedia', 'Dor no joelho após corrida.'),
(3, '2025-10-20', 'Clínico Geral', 'Exame de rotina.');


-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 05-Set-2025 às 11:19
-- Versão do servidor: 10.11.13-MariaDB-cll-lve
-- versão do PHP: 8.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `infocul_gestao_socios`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `associacoes`
--

CREATE TABLE `associacoes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `morada` text DEFAULT NULL,
  `contacto` varchar(50) DEFAULT NULL,
  `logotipo` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp(),
  `nif` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `associacoes`
--

INSERT INTO `associacoes` (`id`, `nome`, `morada`, `contacto`, `logotipo`, `criado_em`, `nif`, `email`) VALUES
(1, 'Clube do Diabético de Elvas', 'Avenida de Elvas Nº 1', '268622222', 'assets/img/logotipo_68b9614564536.png', '2025-08-10 13:14:48', '206422654', 'clubediabeticodeelvas195@gmail.com');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` int(11) NOT NULL,
  `associado_id` int(11) NOT NULL,
  `quota_id` int(11) NOT NULL,
  `data_pagamento` date NOT NULL,
  `montante` decimal(10,2) NOT NULL,
  `metodo_pagamento` varchar(50) DEFAULT NULL,
  `referente_ano` int(11) DEFAULT NULL,
  `meses` varchar(50) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `associado_id`, `quota_id`, `data_pagamento`, `montante`, `metodo_pagamento`, `referente_ano`, `meses`, `criado_em`) VALUES
(1, 5, 1, '2025-08-11', 1.00, 'MB', 2025, '1,2', '2025-08-11 17:56:42'),
(4, 5, 1, '2025-08-12', 2.00, 'Numerário', 2024, '1,2', '2025-08-12 09:18:45'),
(9, 2, 2, '2025-08-21', 8.00, 'MB', 2025, '1,2,3,4,5,6,7,8,9,10,11,12', '2025-08-21 17:18:54'),
(40, 3, 1, '2025-01-10', 3.00, 'Numerário', 2025, '1,2,3', '2025-08-21 17:51:43'),
(41, 8, 2, '2025-02-12', 8.00, 'MB', 2025, '2,3', '2025-08-21 17:51:43'),
(42, 12, 9, '2025-03-15', 20.00, 'MBWay', 2025, '3,4,5,6', '2025-08-21 17:51:43'),
(43, 15, 1, '2025-04-18', 1.00, 'Transferência Bancária', 2025, '4', '2025-08-21 17:51:43'),
(44, 18, 2, '2025-05-20', 8.00, 'PayPal', 2025, '5,6,7', '2025-08-21 17:51:43'),
(45, 9, 3, '2025-06-22', 0.00, 'Numerário', 2025, '6,7', '2025-08-21 17:51:43'),
(46, 25, 9, '2025-07-25', 10.00, 'MB', 2025, '7,8', '2025-08-21 17:51:43'),
(47, 28, 1, '2025-08-28', 2.00, 'MBWay', 2025, '8,9', '2025-08-21 17:51:43'),
(48, 32, 2, '2025-05-30', 3.00, 'Transferência Bancária', 2025, '9', '2025-08-21 17:51:43'),
(50, 5, 1, '2025-06-08', 5.00, 'Numerário', 2025, '2,3,5,11,12', '2025-08-21 17:51:43'),
(51, 10, 1, '2025-08-12', 1.00, 'MB', 2025, '12', '2025-08-21 17:51:43'),
(52, 11, 2, '2024-01-10', 8.00, 'MBWay', 2024, '1,2,3', '2025-08-21 17:51:43'),
(53, 14, 9, '2024-02-12', 15.00, 'Transferência Bancária', 2024, '2,3,4', '2025-08-21 17:51:43'),
(54, 16, 1, '2024-03-15', 2.00, 'PayPal', 2024, '3,4', '2025-08-21 17:51:43'),
(55, 17, 2, '2024-04-18', 8.00, 'Numerário', 2024, '4', '2025-08-21 17:51:43'),
(56, 23, 3, '2024-05-20', 0.00, 'MB', 2024, '5', '2025-08-21 17:51:43'),
(57, 26, 9, '2024-06-22', 20.00, 'MBWay', 2024, '6,7,8,9', '2025-08-21 17:51:43'),
(58, 29, 1, '2024-07-25', 3.00, 'Transferência Bancária', 2024, '7,8,9', '2025-08-21 17:51:43'),
(59, 31, 2, '2024-08-28', 8.00, 'PayPal', 2024, '8,9', '2025-08-21 17:51:43'),
(60, 5, 1, '2024-09-30', 0.00, 'Numerário', 2024, '9', '2025-08-21 17:51:43'),
(61, 8, 9, '2024-10-05', 10.00, 'MB', 2024, '10,11', '2025-08-21 17:51:43'),
(62, 10, 1, '2024-11-08', 1.00, 'MBWay', 2024, '11', '2025-08-21 17:51:43'),
(63, 12, 2, '2024-12-12', 8.00, 'Transferência Bancária', 2024, '12', '2025-08-21 17:51:43'),
(64, 13, 3, '2023-01-10', 0.00, 'PayPal', 2023, '1,2,3', '2025-08-21 17:51:43'),
(65, 16, 9, '2023-02-12', 25.00, 'Numerário', 2023, '2,3,4,5,6', '2025-08-21 17:51:43'),
(66, 19, 1, '2023-03-15', 3.00, 'MB', 2023, '3,4,5', '2025-08-21 17:51:43'),
(67, 22, 2, '2023-04-18', 8.00, 'MBWay', 2023, '4', '2025-08-21 17:51:43'),
(68, 24, 3, '2023-05-20', 0.00, 'Transferência Bancária', 2023, '5', '2025-08-21 17:51:43'),
(69, 27, 9, '2023-06-22', 15.00, 'PayPal', 2023, '6,7,8', '2025-08-21 17:51:43'),
(70, 2, 2, '2025-09-01', 9.00, 'Numerário', 2025, '1,2,3', '2025-09-01 16:14:37'),
(71, 706, 2, '2025-09-03', 14.00, 'Numerário', 2025, '1,2,7,8', '2025-09-03 16:59:52'),
(72, 625, 1, '2025-09-04', 3.00, 'MB', 2025, '1,5,6', '2025-09-04 10:32:38');

-- --------------------------------------------------------

--
-- Estrutura da tabela `recibos`
--

CREATE TABLE `recibos` (
  `id` int(11) NOT NULL,
  `pagamento_id` int(11) NOT NULL,
  `data_recibo` date NOT NULL,
  `caminho_arquivo` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `socios`
--

CREATE TABLE `socios` (
  `id` int(11) NOT NULL,
  `numero_socio` varchar(10) NOT NULL,
  `nome_completo` varchar(100) NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `bi_cc` varchar(20) DEFAULT NULL,
  `nif` varchar(20) DEFAULT NULL,
  `morada` text DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `telemovel` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estado` enum('Ativa','Suspensa') DEFAULT 'Ativa',
  `quota_id` int(11) DEFAULT NULL,
  `anotacoes` text DEFAULT NULL,
  `data_registo` date NOT NULL,
  `associacao_id` int(11) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp(),
  `diabetico` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `socios`
--

INSERT INTO `socios` (`id`, `numero_socio`, `nome_completo`, `data_nascimento`, `bi_cc`, `nif`, `morada`, `telefone`, `telemovel`, `email`, `estado`, `quota_id`, `anotacoes`, `data_registo`, `associacao_id`, `criado_em`, `diabetico`) VALUES
(2, '1', 'Joana Silva', '1957-09-04', '125125125', '125125125', 'Rua das Flores', '268628628', '96666663', 'joana.silva@example.com', 'Ativa', 2, '', '2025-08-10', NULL, '2025-08-10 11:03:16', 1),
(3, '2', 'Carlos Mendes', '1954-10-10', '125125125', '255685612', 'Rua c', '268268268', '966666666', 'carlos.mendes@example.com', 'Ativa', 1, '', '2025-08-10', 1, '2025-08-10 11:03:16', 0),
(5, '3', 'Daniel Francisco Oliveira Lérias', '1984-09-04', '12539899', '208425659', 'Rua Alcide Nogueira Vaz Leitão 33, 7350-066 ELVAS', '963448368', '963448636', 'design@daniellerias.ddns.net', 'Ativa', 1, 'Isto é uma anotação', '2024-08-10', NULL, '2025-08-10 11:23:24', 0),
(8, '4', 'João Silva', '1990-05-10', '12345678', '123456789', 'Rua A, 4', '212345678', '912345678', 'joao.silva4@example.com', 'Ativa', 1, 'Primeiro associado', '2025-01-01', 1, '2025-08-21 10:52:27', 0),
(9, '5', 'Maria Costa', '1985-03-15', '23456789', '234567890', 'Rua B, 5', '212345679', '912345679', 'maria.costa5@example.com', 'Ativa', 1, 'Segundo associado', '2025-01-02', 1, '2025-08-21 10:52:27', 0),
(10, '6', 'Pedro Santos', '1978-07-22', '34567890', '345678901', 'Rua C, 6', '212345680', '912345680', 'pedro.santos6@example.com', 'Suspensa', 1, 'Terceiro associado', '2025-01-03', 1, '2025-08-21 10:52:27', 0),
(11, '7', 'Ana Martins', '1992-11-30', '45678901', '456789012', 'Rua D, 7', '212345681', '912345681', 'ana.martins7@example.com', 'Ativa', 1, 'Quarto associado', '2025-01-04', 1, '2025-08-21 10:52:27', 0),
(12, '8', 'Carlos Ribeiro', '1988-09-18', '56789012', '567890123', 'Rua E, 8', '212345682', '912345682', 'carlos.ribeiro8@example.com', 'Ativa', 1, 'Quinto associado', '2025-01-05', 1, '2025-08-21 10:52:27', 0),
(13, '9', 'Sofia Almeida', '1995-02-25', '67890123', '678901234', 'Rua F, 9', '212345683', '912345683', 'sofia.almeida9@example.com', 'Suspensa', 1, 'Sexto associado', '2025-01-06', 1, '2025-08-21 10:52:27', 0),
(14, '10', 'Miguel Pinto', '1983-06-12', '78901234', '789012345', 'Rua G, 10', '212345684', '912345684', 'miguel.pinto10@example.com', 'Ativa', 1, 'Sétimo associado', '2025-01-07', 1, '2025-08-21 10:52:27', 0),
(15, '11', 'Rita Lopes', '1991-08-05', '89012345', '890123456', 'Rua H, 11', '212345685', '912345685', 'rita.lopes11@example.com', 'Ativa', 1, 'Oitavo associado', '2025-01-08', 1, '2025-08-21 10:52:27', 0),
(16, '12', 'Tiago Fernandes', '1986-12-20', '90123456', '901234567', 'Rua I, 12', '212345686', '912345686', 'tiago.fernandes12@example.com', 'Suspensa', 1, 'Nono associado', '2025-01-09', 1, '2025-08-21 10:52:27', 0),
(17, '13', 'Patrícia Sousa', '1993-04-17', '01234567', '012345678', 'Rua J, 13', '212345687', '912345687', 'patricia.sousa13@example.com', 'Ativa', 1, 'Décimo associado', '2025-01-10', 1, '2025-08-21 10:52:27', 0),
(18, '14', 'André Gomes', '1987-10-23', '11234567', '112345678', 'Rua K, 14', '212345688', '912345688', 'andre.gomes14@example.com', 'Ativa', 1, 'Décimo primeiro associado', '2025-01-11', 1, '2025-08-21 10:52:27', 0),
(19, '15', 'Beatriz Faria', '1996-01-29', '12234567', '122345678', 'Rua L, 15', '212345689', '912345689', 'beatriz.faria15@example.com', 'Suspensa', 1, 'Décimo segundo associado', '2025-01-12', 1, '2025-08-21 10:52:27', 0),
(20, '16', 'Ricardo Lima', '1984-05-14', '13234567', '132345678', 'Rua M, 16', '212345690', '912345690', 'ricardo.lima16@example.com', 'Ativa', 1, 'Décimo terceiro associado', '2025-01-13', 1, '2025-08-21 10:52:27', 0),
(21, '17', 'Carla Nunes', '1990-09-09', '14234567', '142345678', 'Rua N, 17', '212345691', '912345691', 'carla.nunes17@example.com', 'Ativa', 1, 'Décimo quarto associado', '2025-01-14', 1, '2025-08-21 10:52:27', 0),
(22, '18', 'Joana Rocha', '1994-12-03', '15234567', '152345678', 'Rua O, 18', '212345692', '912345692', 'joana.rocha18@example.com', 'Suspensa', 1, 'Décimo quinto associado', '2025-01-15', 1, '2025-08-21 10:52:27', 0),
(23, '19', 'Paulo Teixeira', '1982-03-27', '16234567', '162345678', 'Rua P, 19', '212345693', '912345693', 'paulo.teixeira19@example.com', 'Ativa', 1, 'Décimo sexto associado', '2025-01-16', 1, '2025-08-21 10:52:27', 0),
(24, '20', 'Helena Barros', '1997-07-21', '17234567', '172345678', 'Rua Q, 20', '212345694', '912345694', 'helena.barros20@example.com', 'Ativa', 1, 'Décimo sétimo associado', '2025-01-17', 1, '2025-08-21 10:52:27', 0),
(25, '21', 'Vítor Cardoso', '1989-11-11', '18234567', '182345678', 'Rua R, 21', '212345695', '912345695', 'vitor.cardoso21@example.com', 'Suspensa', 1, 'Décimo oitavo associado', '2025-01-18', 1, '2025-08-21 10:52:27', 0),
(26, '22', 'Susana Pires', '1992-02-08', '19234567', '192345678', 'Rua S, 22', '212345696', '912345696', 'susana.pires22@example.com', 'Ativa', 1, 'Décimo nono associado', '2025-01-19', 1, '2025-08-21 10:52:27', 0),
(27, '23', 'Fábio Vieira', '1985-06-16', '20234567', '202345678', 'Rua T, 23', '212345697', '912345697', 'fabio.vieira23@example.com', 'Ativa', 1, 'Vigésimo associado', '2025-01-20', 1, '2025-08-21 10:52:27', 0),
(28, '24', 'Marta Cruz', '1993-10-30', '21234567', '212345678', 'Rua U, 24', '212345698', '912345698', 'marta.cruz24@example.com', 'Suspensa', 1, 'Vigésimo primeiro associado', '2025-01-21', 1, '2025-08-21 10:52:27', 0),
(29, '25', 'Luís Fonseca', '1986-08-19', '22234567', '222345678', 'Rua V, 25', '212345699', '912345699', 'luis.fonseca25@example.com', 'Ativa', 1, 'Vigésimo segundo associado', '2025-01-22', 1, '2025-08-21 10:52:27', 0),
(30, '26', 'Diana Moreira', '1991-12-27', '23234567', '232345678', 'Rua W, 26', '212345700', '912345700', 'diana.moreira26@example.com', 'Ativa', 1, 'Vigésimo terceiro associado', '2025-01-23', 1, '2025-08-21 10:52:27', 0),
(31, '27', 'Bruno Tavares', '1987-04-13', '24234567', '242345678', 'Rua X, 27', '212345701', '912345701', 'bruno.tavares27@example.com', 'Suspensa', 1, 'Vigésimo quarto associado', '2025-01-24', 1, '2025-08-21 10:52:27', 0),
(32, '28', 'Isabel Ramos', '1995-09-05', '25234567', '252345678', 'Rua Y, 28', '212345702', '912345702', 'isabel.ramos28@example.com', 'Ativa', 1, 'Vigésimo quinto associado', '2025-01-25', 1, '2025-08-21 10:52:27', 0),
(33, '29', 'Fernando Simões', '1983-01-18', '26234567', '262345678', 'Rua Z, 29', '212345703', '912345703', 'fernando.simoes29@example.com', 'Ativa', 1, 'Vigésimo sexto associado', '2025-01-26', 1, '2025-08-21 10:52:27', 0),
(34, '30', 'Cátia Oliveira', '1996-05-22', '27234567', '272345678', 'Rua AA, 30', '212345704', '912345704', 'catia.oliveira30@example.com', 'Suspensa', 1, 'Vigésimo sétimo associado', '2025-01-27', 1, '2025-08-21 10:52:27', 0),
(35, '31', 'Eduardo Matos', '1988-07-29', '28234567', '282345678', 'Rua AB, 31', '212345705', '912345705', 'eduardo.matos31@example.com', 'Ativa', 1, 'Vigésimo oitavo associado', '2025-01-28', 1, '2025-08-21 10:52:27', 0),
(36, '32', 'Filipa Sousa', '1994-03-11', '29234567', '292345678', 'Rua AC, 32', '212345706', '912345706', 'filipa.sousa32@example.com', 'Ativa', 1, 'Vigésimo nono associado', '2025-01-29', 1, '2025-08-21 10:52:27', 0),
(623, '33', 'Maria Costa Santos', '1975-08-10', '87654321', '345678901', 'Av. Central, 45', '213456789', '913456789', 'maria.costa.santos33@email.com', 'Suspensa', 2, 'Sócio fictício gerado automaticamente.', '2025-02-03', 1, '2025-09-03 16:12:35', 0),
(624, '34', 'Pedro Alves Rocha', '1992-12-30', '23456789', '456789012', 'Travessa do Sol, 8', '214567890', '914567890', 'pedro.alves.rocha34@email.com', 'Ativa', 3, 'Sócio fictício gerado automaticamente.', '2023-07-21', 1, '2025-09-03 16:12:35', 0),
(625, '35', 'Ana Beatriz Lima', '1990-03-22', '12345678', '987654321', 'Rua das Flores, 12', '212345678', '912345678', 'ana.beatriz.lima35@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-05-15', 1, '2025-09-03 16:12:35', 0),
(626, '36', 'João Pedro Silva', '1985-11-15', '34567890', '123456789', 'Avenida da Liberdade, 100', '213456789', '913456789', 'joao.pedro.silva36@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-06-10', 1, '2025-09-03 16:12:35', 0),
(627, '37', 'Sofia Martins Pereira', '1995-02-18', '45678901', '987654321', 'Rua da Alegria, 25', '212345679', '912345679', 'sofia.martins.pereira37@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-08-05', 1, '2025-09-03 16:12:35', 0),
(628, '38', 'Rui Miguel Gomes', '1980-04-25', '56789012', '123456788', 'Avenida da República, 150', '213456780', '913456780', 'rui.miguel.gomes38@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-09-12', 1, '2025-09-03 16:12:35', 0),
(629, '39', 'Inês Filipa Martins', '1993-07-12', '67890123', '234567890', 'Rua da Liberdade, 30', '212345680', '912345680', 'ines.filipa.martins39@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-10-01', 1, '2025-09-03 16:12:35', 0),
(630, '40', 'Tiago André Ferreira', '1988-11-05', '78901234', '345678901', 'Avenida da Boavista, 200', '213456781', '913456781', 'tiago.andre.ferreira40@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-10-15', 1, '2025-09-03 16:12:35', 0),
(631, '41', 'Cláudia Isabel Gomes', '1990-06-12', '89012345', '456789012', 'Rua da Esperança, 10', '212345681', '912345681', 'claudia.isabel.gomes41@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-10-20', 1, '2025-09-03 16:20:15', 0),
(632, '42', 'Ricardo Jorge Pinto', '1982-09-14', '90123456', '567890123', 'Rua da Liberdade, 50', '212345682', '912345682', 'ricardo.jorge.pinto42@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-10-22', 1, '2025-09-03 16:20:15', 0),
(633, '43', 'Ana Sofia Costa', '1995-01-20', '12345678', '987654321', 'Rua da Alegria, 15', '212345683', '912345683', 'ana.sofia.costa43@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-10-23', 1, '2025-09-03 16:20:15', 0),
(634, '44', 'Bruno Miguel Ferreira', '1988-05-30', '23456789', '876543210', 'Rua da Liberdade, 25', '212345684', '912345684', 'bruno.miguel.ferreira44@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-10-24', 1, '2025-09-03 16:20:15', 0),
(635, '45', 'Joana Filipa Marques', '1992-07-18', '34567890', '765432109', 'Rua das Flores, 5', '212345685', '912345685', 'joana.filipa.marques45@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-10-26', 1, '2025-09-03 16:20:15', 0),
(636, '46', 'Pedro Manuel Lopes', '1987-11-22', '45678901', '654321098', 'Rua do Sol, 8', '212345686', '912345686', 'pedro.manuel.lopes46@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-10-27', 1, '2025-09-03 16:20:15', 0),
(637, '47', 'Sofia Alexandra Dias', '1993-04-10', '56789012', '543210987', 'Rua do Norte, 12', '212345687', '912345687', 'sofia.alexandra.dias47@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-10-28', 1, '2025-09-03 16:20:15', 0),
(638, '48', 'Miguel Ângelo Ramos', '1989-08-05', '67890123', '432109876', 'Rua do Sul, 20', '212345688', '912345688', 'miguel.angelo.ramos48@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-10-29', 1, '2025-09-03 16:20:15', 0),
(639, '49', 'Patrícia Cristina Sousa', '1991-12-17', '78901234', '321098765', 'Rua do Poente, 30', '212345689', '912345689', 'patricia.cristina.sousa49@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-10-30', 1, '2025-09-03 16:20:15', 0),
(640, '50', 'Tiago Filipe Martins', '1986-02-28', '89012345', '210987654', 'Rua do Nascente, 40', '212345690', '912345690', 'tiago.filipe.martins50@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-10-31', 1, '2025-09-03 16:20:15', 0),
(641, '51', 'Rita Isabel Pereira', '1994-09-09', '90123456', '109876543', 'Rua do Centro, 50', '212345691', '912345691', 'rita.isabel.pereira51@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-01', 1, '2025-09-03 16:20:15', 0),
(642, '52', 'André Luís Carvalho', '1983-06-21', '12345678', '987654321', 'Rua da Esperança, 60', '212345692', '912345692', 'andre.luis.carvalho52@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-02', 1, '2025-09-03 16:20:15', 0),
(643, '53', 'Mariana Sofia Rocha', '1996-03-13', '23456789', '876543210', 'Rua da Liberdade, 70', '212345693', '912345693', 'mariana.sofia.rocha53@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-03', 1, '2025-09-03 16:20:15', 0),
(644, '54', 'Fábio Alexandre Mendes', '1984-10-25', '34567890', '765432109', 'Rua da Alegria, 80', '212345694', '912345694', 'fabio.alexandre.mendes54@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-04', 1, '2025-09-03 16:20:15', 0),
(645, '55', 'Cátia Filipa Teixeira', '1997-05-07', '45678901', '654321098', 'Rua das Flores, 90', '212345695', '912345695', 'catia.filipa.teixeira55@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-05', 1, '2025-09-03 16:20:15', 0),
(646, '56', 'Diogo Manuel Almeida', '1985-12-19', '56789012', '543210987', 'Rua do Sol, 100', '212345696', '912345696', 'diogo.manuel.almeida56@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-06', 1, '2025-09-03 16:20:15', 0),
(647, '57', 'Sara Alexandra Pinto', '1998-08-23', '67890123', '432109876', 'Rua do Norte, 110', '212345697', '912345697', 'sara.alexandra.pinto57@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-07', 1, '2025-09-03 16:20:15', 0),
(648, '58', 'João Pedro Santos', '1982-04-15', '78901234', '321098765', 'Rua do Sul, 120', '212345698', '912345698', 'joao.pedro.santos58@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-08', 1, '2025-09-03 16:20:15', 0),
(649, '59', 'Helena Cristina Lopes', '1999-01-29', '89012345', '210987654', 'Rua do Poente, 130', '212345699', '912345699', 'helena.cristina.lopes59@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-09', 1, '2025-09-03 16:20:15', 0),
(650, '60', 'Vítor Manuel Costa', '1987-07-03', '90123456', '109876543', 'Rua do Nascente, 140', '212345700', '912345700', 'vitor.manuel.costa60@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-10', 1, '2025-09-03 16:20:15', 0),
(651, '61', 'Inês Filipa Sousa', '1995-11-16', '12345678', '987654321', 'Rua do Centro, 150', '212345701', '912345701', 'ines.filipa.sousa61@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-11', 1, '2025-09-03 16:20:15', 0),
(652, '62', 'Rui Alexandre Ferreira', '1988-03-28', '23456789', '876543210', 'Rua da Esperança, 160', '212345702', '912345702', 'rui.alexandre.ferreira62@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-12', 1, '2025-09-03 16:20:15', 0),
(653, '63', 'Beatriz Sofia Martins', '1993-09-11', '34567890', '765432109', 'Rua da Liberdade, 170', '212345703', '912345703', 'beatriz.sofia.martins63@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-13', 1, '2025-09-03 16:20:15', 0),
(654, '64', 'Gonçalo Miguel Rocha', '1986-06-02', '45678901', '654321098', 'Rua da Alegria, 180', '212345704', '912345704', 'goncalo.miguel.rocha64@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-14', 1, '2025-09-03 16:20:15', 0),
(655, '65', 'Leonor Filipa Almeida', '1997-02-14', '56789012', '543210987', 'Rua das Flores, 190', '212345705', '912345705', 'leonor.filipa.almeida65@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-15', 1, '2025-09-03 16:20:15', 0),
(656, '66', 'Tiago Manuel Pinto', '1984-08-19', '67890123', '432109876', 'Rua do Sol, 200', '212345706', '912345706', 'tiago.manuel.pinto66@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-16', 1, '2025-09-03 16:20:15', 0),
(657, '67', 'Marta Sofia Costa', '1996-05-22', '78901234', '321098765', 'Rua do Norte, 210', '212345707', '912345707', 'marta.sofia.costa67@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-17', 1, '2025-09-03 16:20:15', 0),
(658, '68', 'João Miguel Santos', '1983-11-30', '89012345', '210987654', 'Rua do Sul, 220', '212345708', '912345708', 'joao.miguel.santos68@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-18', 1, '2025-09-03 16:20:15', 0),
(659, '69', 'Carolina Alexandra Lopes', '1998-07-05', '90123456', '109876543', 'Rua do Poente, 230', '212345709', '912345709', 'carolina.alexandra.lopes69@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-19', 1, '2025-09-03 16:20:15', 0),
(660, '70', 'Miguel Ângelo Martins', '1987-04-18', '12345678', '987654321', 'Rua do Nascente, 240', '212345710', '912345710', 'miguel.angelo.martins70@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-20', 1, '2025-09-03 16:20:15', 0),
(661, '71', 'Sofia Filipa Sousa', '1995-09-27', '23456789', '876543210', 'Rua do Centro, 250', '212345711', '912345711', 'sofia.filipa.sousa71@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-21', 1, '2025-09-03 16:20:15', 0),
(662, '72', 'Rui Alexandre Rocha', '1986-12-13', '34567890', '765432109', 'Rua da Esperança, 260', '212345712', '912345712', 'rui.alexandre.rocha72@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-22', 1, '2025-09-03 16:20:15', 0),
(663, '73', 'Beatriz Sofia Mendes', '1993-03-09', '45678901', '654321098', 'Rua da Liberdade, 270', '212345713', '912345713', 'beatriz.sofia.mendes73@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-23', 1, '2025-09-03 16:20:15', 0),
(664, '74', 'Gonçalo Miguel Teixeira', '1988-10-21', '56789012', '543210987', 'Rua da Alegria, 280', '212345714', '912345714', 'goncalo.miguel.teixeira74@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-24', 1, '2025-09-03 16:20:15', 0),
(665, '75', 'Leonor Filipa Carvalho', '1997-06-16', '67890123', '432109876', 'Rua das Flores, 290', '212345715', '912345715', 'leonor.filipa.carvalho75@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-25', 1, '2025-09-03 16:20:15', 0),
(666, '76', 'Tiago Manuel Almeida', '1984-01-28', '78901234', '321098765', 'Rua do Sol, 300', '212345716', '912345716', 'tiago.manuel.almeida76@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-26', 1, '2025-09-03 16:20:15', 0),
(667, '77', 'Marta Sofia Pinto', '1996-08-11', '89012345', '210987654', 'Rua do Norte, 310', '212345717', '912345717', 'marta.sofia.pinto77@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-27', 1, '2025-09-03 16:20:15', 0),
(668, '78', 'João Miguel Costa', '1983-05-03', '90123456', '109876543', 'Rua do Sul, 320', '212345718', '912345718', 'joao.miguel.costa78@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-28', 1, '2025-09-03 16:20:15', 0),
(669, '79', 'Carolina Alexandra Ferreira', '1998-02-25', '12345678', '987654321', 'Rua do Poente, 330', '212345719', '912345719', 'carolina.alexandra.ferreira79@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-11-29', 1, '2025-09-03 16:20:15', 0),
(670, '80', 'Miguel Ângelo Rocha', '1987-09-17', '23456789', '876543210', 'Rua do Nascente, 340', '212345720', '912345720', 'miguel.angelo.rocha80@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-11-30', 1, '2025-09-03 16:20:15', 0),
(671, '81', 'Sofia Filipa Martins', '1995-04-02', '34567890', '765432109', 'Rua do Centro, 350', '212345721', '912345721', 'sofia.filipa.martins81@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-01', 1, '2025-09-03 16:20:15', 0),
(672, '82', 'Rui Alexandre Mendes', '1986-11-08', '45678901', '654321098', 'Rua da Esperança, 360', '212345722', '912345722', 'rui.alexandre.mendes82@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-02', 1, '2025-09-03 16:20:15', 0),
(673, '83', 'Beatriz Sofia Teixeira', '1993-07-19', '56789012', '543210987', 'Rua da Liberdade, 370', '212345723', '912345723', 'beatriz.sofia.teixeira83@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-03', 1, '2025-09-03 16:20:15', 0),
(674, '84', 'Gonçalo Miguel Carvalho', '1988-03-31', '67890123', '432109876', 'Rua da Alegria, 380', '212345724', '912345724', 'goncalo.miguel.carvalho84@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-04', 1, '2025-09-03 16:20:15', 0),
(675, '85', 'Leonor Filipa Rocha', '1997-10-12', '78901234', '321098765', 'Rua das Flores, 390', '212345725', '912345725', 'leonor.filipa.rocha85@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-05', 1, '2025-09-03 16:20:15', 0),
(676, '86', 'Tiago Manuel Teixeira', '1984-06-23', '89012345', '210987654', 'Rua do Sol, 400', '212345726', '912345726', 'tiago.manuel.teixeira86@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-06', 1, '2025-09-03 16:20:15', 0),
(677, '87', 'Marta Sofia Almeida', '1996-02-28', '90123456', '109876543', 'Rua do Norte, 410', '212345727', '912345727', 'marta.sofia.almeida87@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-07', 1, '2025-09-03 16:20:15', 0),
(678, '88', 'João Miguel Mendes', '1983-09-14', '12345678', '987654321', 'Rua do Sul, 420', '212345728', '912345728', 'joao.miguel.mendes88@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-08', 1, '2025-09-03 16:20:15', 0),
(679, '89', 'Carolina Alexandra Carvalho', '1998-05-21', '23456789', '876543210', 'Rua do Poente, 430', '212345729', '912345729', 'carolina.alexandra.carvalho89@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-09', 1, '2025-09-03 16:20:15', 0),
(680, '90', 'Miguel Ângelo Almeida', '1987-01-30', '34567890', '765432109', 'Rua do Nascente, 440', '212345730', '912345730', 'miguel.angelo.almeida90@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-10', 1, '2025-09-03 16:20:15', 0),
(681, '91', 'Sofia Filipa Rocha', '1995-08-17', '45678901', '654321098', 'Rua do Centro, 450', '212345731', '912345731', 'sofia.filipa.rocha91@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-11', 1, '2025-09-03 16:20:15', 0),
(682, '92', 'Rui Alexandre Teixeira', '1986-03-05', '56789012', '543210987', 'Rua da Esperança, 460', '212345732', '912345732', 'rui.alexandre.teixeira92@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-12', 1, '2025-09-03 16:20:15', 0),
(683, '93', 'Beatriz Sofia Almeida', '1993-12-23', '67890123', '432109876', 'Rua da Liberdade, 470', '212345733', '912345733', 'beatriz.sofia.almeida93@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-13', 1, '2025-09-03 16:20:15', 0),
(684, '94', 'Gonçalo Miguel Mendes', '1988-07-09', '78901234', '321098765', 'Rua da Alegria, 480', '212345734', '912345734', 'goncalo.miguel.mendes94@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-14', 1, '2025-09-03 16:20:15', 0),
(685, '95', 'Leonor Filipa Teixeira', '1997-04-14', '89012345', '210987654', 'Rua das Flores, 490', '212345735', '912345735', 'leonor.filipa.teixeira95@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-15', 1, '2025-09-03 16:20:15', 0),
(686, '96', 'Tiago Manuel Carvalho', '1984-09-27', '90123456', '109876543', 'Rua do Sol, 500', '212345736', '912345736', 'tiago.manuel.carvalho96@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-16', 1, '2025-09-03 16:20:15', 0),
(687, '97', 'Marta Sofia Mendes', '1996-06-19', '12345678', '987654321', 'Rua do Norte, 510', '212345737', '912345737', 'marta.sofia.mendes97@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-17', 1, '2025-09-03 16:20:15', 0),
(688, '98', 'João Miguel Teixeira', '1983-02-11', '23456789', '876543210', 'Rua do Sul, 520', '212345738', '912345738', 'joao.miguel.teixeira98@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-18', 1, '2025-09-03 16:20:15', 0),
(689, '99', 'Carolina Alexandra Mendes', '1998-11-03', '34567890', '765432109', 'Rua do Poente, 530', '212345739', '912345739', 'carolina.alexandra.mendes99@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-19', 1, '2025-09-03 16:20:15', 0),
(690, '100', 'Miguel Ângelo Teixeira', '1987-05-25', '45678901', '654321098', 'Rua do Nascente, 540', '212345740', '912345740', 'miguel.angelo.teixeira100@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-20', 1, '2025-09-03 16:20:15', 0),
(691, '101', 'Sofia Filipa Mendes', '1995-03-15', '56789012', '543210987', 'Rua do Centro, 550', '212345741', '912345741', 'sofia.filipa.mendes101@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-21', 1, '2025-09-03 16:20:15', 0),
(692, '102', 'Rui Alexandre Almeida', '1986-10-08', '67890123', '432109876', 'Rua da Esperança, 560', '212345742', '912345742', 'rui.alexandre.almeida102@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-22', 1, '2025-09-03 16:20:15', 0),
(693, '103', 'Beatriz Sofia Teixeira', '1993-01-29', '78901234', '321098765', 'Rua da Liberdade, 570', '212345743', '912345743', 'beatriz.sofia.teixeira103@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-23', 1, '2025-09-03 16:20:15', 0),
(694, '104', 'Gonçalo Miguel Almeida', '1988-08-16', '89012345', '210987654', 'Rua da Alegria, 580', '212345744', '912345744', 'goncalo.miguel.almeida104@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-24', 1, '2025-09-03 16:20:15', 0),
(695, '105', 'Leonor Filipa Mendes', '1997-12-05', '90123456', '109876543', 'Rua das Flores, 590', '212345745', '912345745', 'leonor.filipa.mendes105@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-25', 1, '2025-09-03 16:20:15', 0),
(696, '106', 'Tiago Manuel Rocha', '1984-04-22', '12345678', '987654321', 'Rua do Sol, 600', '212345746', '912345746', 'tiago.manuel.rocha106@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-26', 1, '2025-09-03 16:20:15', 0),
(697, '107', 'Marta Sofia Teixeira', '1996-09-13', '23456789', '876543210', 'Rua do Norte, 610', '212345747', '912345747', 'marta.sofia.teixeira107@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-27', 1, '2025-09-03 16:20:15', 0),
(698, '108', 'João Miguel Almeida', '1983-06-07', '34567890', '765432109', 'Rua do Sul, 620', '212345748', '912345748', 'joao.miguel.almeida108@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-28', 1, '2025-09-03 16:20:15', 0),
(699, '109', 'Carolina Alexandra Teixeira', '1998-03-22', '45678901', '654321098', 'Rua do Poente, 630', '212345749', '912345749', 'carolina.alexandra.teixeira109@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-29', 1, '2025-09-03 16:20:15', 0),
(700, '110', 'Miguel Ângelo Mendes', '1987-08-11', '56789012', '543210987', 'Rua do Nascente, 640', '212345750', '912345750', 'miguel.angelo.mendes110@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2023-12-30', 1, '2025-09-03 16:20:15', 0),
(701, '111', 'Sofia Filipa Almeida', '1995-06-28', '67890123', '432109876', 'Rua do Centro, 650', '212345751', '912345751', 'sofia.filipa.almeida111@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2023-12-31', 1, '2025-09-03 16:20:15', 0),
(702, '112', 'Rui Alexandre Mendes', '1986-01-19', '78901234', '321098765', 'Rua da Esperança, 660', '212345752', '912345752', 'rui.alexandre.mendes112@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-01', 1, '2025-09-03 16:20:15', 0),
(703, '113', 'Beatriz Sofia Carvalho', '1993-05-15', '89012345', '210987654', 'Rua da Liberdade, 670', '212345753', '912345753', 'beatriz.sofia.carvalho113@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-02', 1, '2025-09-03 16:20:15', 0),
(704, '114', 'Gonçalo Miguel Teixeira', '1988-11-27', '90123456', '109876543', 'Rua da Alegria, 680', '212345754', '912345754', 'goncalo.miguel.teixeira114@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-03', 1, '2025-09-03 16:20:15', 0),
(705, '115', 'Leonor Filipa Almeida', '1997-07-08', '12345678', '987654321', 'Rua das Flores, 690', '212345755', '912345755', 'leonor.filipa.almeida115@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-04', 1, '2025-09-03 16:20:15', 0),
(706, '116', 'Tiago Manuel Mendes', '1984-02-16', '23456789', '876543210', 'Rua do Sol, 700', '212345756', '912345756', 'tiago.manuel.mendes116@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-05', 1, '2025-09-03 16:20:15', 0),
(707, '117', 'Marta Sofia Carvalho', '1996-11-21', '34567890', '765432109', 'Rua do Norte, 710', '212345757', '912345757', 'marta.sofia.carvalho117@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-06', 1, '2025-09-03 16:20:15', 0),
(708, '118', 'João Miguel Rocha', '1983-03-04', '45678901', '654321098', 'Rua do Sul, 720', '212345758', '912345758', 'joao.miguel.rocha118@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-07', 1, '2025-09-03 16:20:15', 0),
(709, '119', 'Carolina Alexandra Almeida', '1998-10-30', '56789012', '543210987', 'Rua do Poente, 730', '212345759', '912345759', 'carolina.alexandra.almeida119@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-08', 1, '2025-09-03 16:20:15', 0),
(710, '120', 'Miguel Ângelo Carvalho', '1987-02-13', '67890123', '432109876', 'Rua do Nascente, 740', '212345760', '912345760', 'miguel.angelo.carvalho120@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-09', 1, '2025-09-03 16:20:15', 0),
(711, '121', 'Sofia Filipa Rocha', '1995-10-05', '78901234', '321098765', 'Rua do Centro, 750', '212345761', '912345761', 'sofia.filipa.rocha121@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-10', 1, '2025-09-03 16:20:15', 0),
(712, '122', 'Rui Alexandre Carvalho', '1986-06-29', '89012345', '210987654', 'Rua da Esperança, 760', '212345762', '912345762', 'rui.alexandre.carvalho122@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-11', 1, '2025-09-03 16:20:15', 0),
(713, '123', 'Beatriz Sofia Rocha', '1993-02-17', '90123456', '109876543', 'Rua da Liberdade, 770', '212345763', '912345763', 'beatriz.sofia.rocha123@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-12', 1, '2025-09-03 16:20:15', 0),
(714, '124', 'Gonçalo Miguel Almeida', '1988-05-23', '12345678', '987654321', 'Rua da Alegria, 780', '212345764', '912345764', 'goncalo.miguel.almeida124@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-13', 1, '2025-09-03 16:20:15', 0),
(715, '125', 'Leonor Filipa Carvalho', '1997-03-12', '23456789', '876543210', 'Rua das Flores, 790', '212345765', '912345765', 'leonor.filipa.carvalho125@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-14', 1, '2025-09-03 16:20:15', 0),
(716, '126', 'Tiago Manuel Almeida', '1984-07-25', '34567890', '765432109', 'Rua do Sol, 800', '212345766', '912345766', 'tiago.manuel.almeida126@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-15', 1, '2025-09-03 16:20:15', 0),
(717, '127', 'Marta Sofia Mendes', '1996-04-09', '45678901', '654321098', 'Rua do Norte, 810', '212345767', '912345767', 'marta.sofia.mendes127@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-16', 1, '2025-09-03 16:20:15', 0),
(718, '128', 'João Miguel Teixeira', '1983-12-01', '56789012', '543210987', 'Rua do Sul, 820', '212345768', '912345768', 'joao.miguel.teixeira128@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-17', 1, '2025-09-03 16:20:15', 0),
(719, '129', 'Carolina Alexandra Rocha', '1998-06-14', '67890123', '432109876', 'Rua do Poente, 830', '212345769', '912345769', 'carolina.alexandra.rocha129@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-18', 1, '2025-09-03 16:20:15', 0),
(720, '130', 'Miguel Ângelo Almeida', '1987-03-27', '78901234', '321098765', 'Rua do Nascente, 840', '212345770', '912345770', 'miguel.angelo.almeida130@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-19', 1, '2025-09-03 16:20:15', 0),
(721, '131', 'Sofia Filipa Carvalho', '1995-07-22', '89012345', '210987654', 'Rua do Centro, 850', '212345771', '912345771', 'sofia.filipa.carvalho131@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-20', 1, '2025-09-03 16:20:15', 0),
(722, '132', 'Rui Alexandre Rocha', '1986-09-16', '90123456', '109876543', 'Rua da Esperança, 860', '212345772', '912345772', 'rui.alexandre.rocha132@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-21', 1, '2025-09-03 16:20:15', 0),
(723, '133', 'Beatriz Sofia Mendes', '1993-06-03', '12345678', '987654321', 'Rua da Liberdade, 870', '212345773', '912345773', 'beatriz.sofia.mendes133@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-22', 1, '2025-09-03 16:20:15', 0),
(724, '134', 'Gonçalo Miguel Rocha', '1988-02-18', '23456789', '876543210', 'Rua da Alegria, 880', '212345774', '912345774', 'goncalo.miguel.rocha134@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-23', 1, '2025-09-03 16:20:15', 0),
(725, '135', 'Leonor Filipa Teixeira', '1997-08-27', '34567890', '765432109', 'Rua das Flores, 890', '212345775', '912345775', 'leonor.filipa.teixeira135@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-24', 1, '2025-09-03 16:20:15', 0),
(726, '136', 'Tiago Manuel Carvalho', '1984-05-14', '45678901', '654321098', 'Rua do Sol, 900', '212345776', '912345776', 'tiago.manuel.carvalho136@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-25', 1, '2025-09-03 16:20:15', 0),
(727, '137', 'Marta Sofia Almeida', '1996-12-30', '56789012', '543210987', 'Rua do Norte, 910', '212345777', '912345777', 'marta.sofia.almeida137@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-26', 1, '2025-09-03 16:20:15', 0),
(728, '138', 'João Miguel Mendes', '1983-08-08', '67890123', '432109876', 'Rua do Sul, 920', '212345778', '912345778', 'joao.miguel.mendes138@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-27', 1, '2025-09-03 16:20:15', 0),
(729, '139', 'Carolina Alexandra Carvalho', '1998-01-19', '78901234', '321098765', 'Rua do Poente, 930', '212345779', '912345779', 'carolina.alexandra.carvalho139@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-28', 1, '2025-09-03 16:20:15', 0),
(730, '140', 'Miguel Ângelo Teixeira', '1987-06-02', '89012345', '210987654', 'Rua do Nascente, 940', '212345780', '912345780', 'miguel.angelo.teixeira140@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-29', 1, '2025-09-03 16:20:15', 0),
(731, '141', 'Sofia Filipa Mendes', '1995-02-11', '90123456', '109876543', 'Rua do Centro, 950', '212345781', '912345781', 'sofia.filipa.mendes141@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-01-30', 1, '2025-09-03 16:20:15', 0),
(732, '142', 'Rui Alexandre Almeida', '1986-12-25', '12345678', '987654321', 'Rua da Esperança, 960', '212345782', '912345782', 'rui.alexandre.almeida142@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-01-31', 1, '2025-09-03 16:20:15', 0),
(733, '143', 'Beatriz Sofia Teixeira', '1993-08-16', '23456789', '876543210', 'Rua da Liberdade, 970', '212345783', '912345783', 'beatriz.sofia.teixeira143@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-02-01', 1, '2025-09-03 16:20:15', 0),
(734, '144', 'Gonçalo Miguel Carvalho', '1988-04-29', '34567890', '765432109', 'Rua da Alegria, 980', '212345784', '912345784', 'goncalo.miguel.carvalho144@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-02-02', 1, '2025-09-03 16:20:15', 0),
(735, '145', 'Leonor Filipa Rocha', '1997-05-03', '45678901', '654321098', 'Rua das Flores, 990', '212345785', '912345785', 'leonor.filipa.rocha145@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-02-03', 1, '2025-09-03 16:20:15', 0),
(736, '146', 'Tiago Manuel Teixeira', '1984-10-17', '56789012', '543210987', 'Rua do Sol, 1000', '212345786', '912345786', 'tiago.manuel.teixeira146@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-02-04', 1, '2025-09-03 16:20:15', 0),
(737, '147', 'Marta Sofia Carvalho', '1996-03-28', '67890123', '432109876', 'Rua do Norte, 1010', '212345787', '912345787', 'marta.sofia.carvalho147@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-02-05', 1, '2025-09-03 16:20:15', 0),
(738, '148', 'João Miguel Rocha', '1983-09-21', '78901234', '321098765', 'Rua do Sul, 1020', '212345788', '912345788', 'joao.miguel.rocha148@email.com', 'Ativa', 2, 'Sócio fictício gerado automaticamente.', '2024-02-06', 1, '2025-09-03 16:20:15', 0),
(739, '149', 'Carolina Alexandra Mendes', '1998-12-12', '89012345', '210987654', 'Rua do Poente, 1030', '212345789', '912345789', 'carolina.alexandra.mendes149@email.com', 'Ativa', 1, 'Sócio fictício gerado automaticamente.', '2024-02-07', 1, '2025-09-03 16:20:15', 0),
(741, '150', 'Josué Lérias', '1957-09-04', '125125125', '125125125', 'Rua 1', '268621906', '96969696', 'josue.lerias@gmail.com', 'Ativa', 1, '', '2025-09-04', NULL, '2025-09-04 17:03:09', 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipos_quotas`
--

CREATE TABLE `tipos_quotas` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `criado_em` timestamp NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL,
  `atualizado_por` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `tipos_quotas`
--

INSERT INTO `tipos_quotas` (`id`, `nome`, `valor`, `criado_em`, `atualizado_em`, `atualizado_por`) VALUES
(1, 'Normal', 1.00, '2025-08-10 11:03:10', '2025-08-21 15:58:34', 'clubediabetico'),
(2, 'Extra', 3.50, '2025-08-10 11:03:10', '2025-09-01 17:25:57', 'clubediabetico'),
(3, 'Isento', 0.00, '2025-08-10 11:03:10', '2025-08-21 10:55:51', 'clubediabetico'),
(9, 'Mecenas', 5.00, '2025-08-21 15:00:53', '2025-08-21 16:00:53', 'clubediabetico');

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `nome`, `email`, `criado_em`) VALUES
(1, 'clubediabetico', '$2y$10$Uf0yFOwGMWZy0PRrIv43BO.MT3Ly8HNtj05L2yz3LYO.YhOJjnsHC', 'Administrador', 'daniel.lerias@gmail.com', '2025-08-11 09:27:54'),
(2, 'Administrador', '$2y$10$OH9zZBuKQQL1Nkuk63U6XeOE/60pNwYi1cOuvJgJtiVa90oGOKhXe', 'Administrador1', 'admin@mail.com', '2025-08-11 14:51:38'),
(3, 'josuelerias', '$2y$10$YZzI0Cx.2a3DVqtVLlHzxuWp0XUal9hxxD1evqzMoacAwKxAOdOwS', 'Josué', 'josue.lerias@gmail.com', '2025-08-21 18:58:55');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `associacoes`
--
ALTER TABLE `associacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `associado_id` (`associado_id`),
  ADD KEY `quota_id` (`quota_id`);

--
-- Índices para tabela `recibos`
--
ALTER TABLE `recibos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pagamento_id` (`pagamento_id`);

--
-- Índices para tabela `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_socio` (`numero_socio`),
  ADD KEY `quota_id` (`quota_id`),
  ADD KEY `associacao_id` (`associacao_id`);

--
-- Índices para tabela `tipos_quotas`
--
ALTER TABLE `tipos_quotas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `associacoes`
--
ALTER TABLE `associacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de tabela `recibos`
--
ALTER TABLE `recibos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `socios`
--
ALTER TABLE `socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=742;

--
-- AUTO_INCREMENT de tabela `tipos_quotas`
--
ALTER TABLE `tipos_quotas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD CONSTRAINT `pagamentos_ibfk_1` FOREIGN KEY (`associado_id`) REFERENCES `socios` (`id`),
  ADD CONSTRAINT `pagamentos_ibfk_2` FOREIGN KEY (`quota_id`) REFERENCES `tipos_quotas` (`id`);

--
-- Limitadores para a tabela `recibos`
--
ALTER TABLE `recibos`
  ADD CONSTRAINT `recibos_ibfk_1` FOREIGN KEY (`pagamento_id`) REFERENCES `pagamentos` (`id`);

--
-- Limitadores para a tabela `socios`
--
ALTER TABLE `socios`
  ADD CONSTRAINT `socios_ibfk_1` FOREIGN KEY (`quota_id`) REFERENCES `tipos_quotas` (`id`),
  ADD CONSTRAINT `socios_ibfk_2` FOREIGN KEY (`associacao_id`) REFERENCES `associacoes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

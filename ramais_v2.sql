-- phpMyAdmin SQL Dump
-- version 5.2.2deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 05/01/2026 às 16:52
-- Versão do servidor: 8.4.7-0ubuntu0.25.04.1
-- Versão do PHP: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ramais_v2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `acao_social`
--

CREATE TABLE `acao_social` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `acao_social`
--

INSERT INTO `acao_social` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '2254', 0, 0, 0),
(2, NULL, 'RECEPÇÃO', '', '1877/1920', 0, 0, 0),
(3, '', 'CONSELHO MUNICIPAL DA PESSOA IDOSA, CRIANÇAS E ADOLESCENTES E DA ASSISTÊNCIA SOCIAL', '', '2252', 0, 0, 0),
(4, '', 'ADMINISTRATIVO', '', '1878', 0, 0, 0),
(5, '', 'ADMINISTRATIVO', '', '2256', 0, 0, 0),
(6, '', 'BOLSA FAMILIA', '', '1877/1879', 0, 0, 0),
(7, NULL, 'PASSE OU LEITE', '', '2253', 0, 0, 0),
(8, '', 'ASSISTENTE SOCIAL', '', '2254/2255', 0, 0, 0),
(9, 'Habitação', 'RECEPÇÃO', '', '1880', 0, 0, 0),
(10, NULL, 'HABITAÇÃO', '', '2260', 0, 0, 0),
(11, 'Habitação', 'ADMINISTRATIVO', '', '2266', 0, 0, 0),
(12, 'Habitação', 'MARCINHO', '', '2262', 0, 0, 0),
(13, 'Habitação', 'ASSISTENTE SOCIAL', '', '2264', 0, 0, 0),
(14, 'Habitação', 'ENGENHARIA', '', '2268', 0, 0, 0),
(15, 'Habitação', 'GALPÃO DA HABITAÇÃO', '', '1934', 0, 0, 0),
(16, 'CRAS', 'RECEPÇÃO', '', '1884', 0, 0, 0),
(17, 'CRAS', 'EQUIPE TÉCNICA', '', '1888/2285', 0, 0, 0),
(18, 'CRAS', 'ORIENTAÇÃO SOCIAL', '', '2287/2289', 0, 0, 0),
(19, 'CRAS', 'EQUIPE VOLANTE', '', '2290', 0, 0, 0),
(20, 'CRAS', 'COORDENAÇÃO', '', '2282/2283', 0, 0, 0),
(21, 'CRAS', 'GALPÃO  / QUALIFICAR', 'COSTURA', '1933', 0, 0, 0),
(22, 'CREAS', 'RECEPÇÃO', '', '1887', 0, 0, 0),
(23, 'CREAS', 'EQUIPE TÉCNICA', '', '2292/2302', 0, 0, 0),
(24, 'CREAS', 'COORDENAÇÃO', '', '2301', 0, 0, 0),
(25, 'CREAS', 'MIGRANTES', '', '2294', 0, 0, 0),
(26, 'Conselho Tutelar', 'RECEPÇÃO', '', '1882', 0, 0, 0),
(27, 'Conselho Tutelar', 'ATENDIMENTO', '', '2280', 0, 0, 0),
(28, NULL, 'SALA DOS CONSELHOS', '', '1988', 0, 0, 0),
(29, 'Assistência Jurídica (Defensoria Pública)', 'RECEPÇÃO', '', '1956', 0, 0, 0),
(30, 'Assistência Jurídica (Defensoria Pública)', 'NESTOR', '', '2091', 0, 0, 0),
(31, 'Assistência Jurídica (Defensoria Pública)', 'CAROL', '', '2092', 0, 0, 0),
(32, 'Posto de Identificação', 'VIVINHA', '', '1923', 0, 0, 0),
(33, 'Defesa Civil', 'RECEPÇÃO', '', '1881', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `administracao`
--

CREATE TABLE `administracao` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `administracao`
--

INSERT INTO `administracao` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '2037', 0, 0, 0),
(2, 'Patrimônio', 'RECEPÇÃO', 'SIMONE', '1893', 0, 0, 0),
(3, 'Patrimônio', 'HENDRIGO', '', '2321', 0, 0, 0),
(4, 'Segurança do Trabalho', 'RECEPÇÃO', '', '1909', 0, 0, 0),
(5, 'Segurança do Trabalho', 'ALMOXARIFADO', '', '2161', 0, 0, 0),
(6, 'Segurança do Trabalho', 'ADMINISTRATIVO', '', '2174', 0, 0, 0),
(7, 'Almoxarifado Central', 'RECEPÇÃO', '', '1908', 0, 0, 1),
(8, NULL, 'CENTRO DE TREINAMENTO - GESTÃO DE PESSOAS', '', '1875', 0, 0, 0),
(9, NULL, 'GESTÃO DE CONTRATOS', '', '2133', 0, 0, 0),
(10, NULL, 'COMPRAS', '', '1801', 0, 0, 0),
(11, NULL, 'CONSÓRCIO E CONTRATOS', '', '1921 ; 2004 ; 2011', 0, 0, 0),
(12, NULL, 'LICITAÇÃO', '', '2032', 0, 0, 0),
(13, NULL, 'ZELADORIA', '', '2233', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `agricultura`
--

CREATE TABLE `agricultura` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agricultura`
--

INSERT INTO `agricultura` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1905', 0, 0, 0),
(2, NULL, 'RECEPÇÃO', '', '1905', 0, 0, 0),
(3, 'Administrativo', 'NATHALIA', '', '2358', 0, 0, 0),
(4, 'Veterinária', 'RITA', '', '2357', 0, 0, 0),
(5, 'Emater', 'RECEPÇÃO', '', '1865', 0, 0, 0),
(6, 'Emater', NULL, 'GLEIDSON/GABRIELA', '2316', 0, 0, 0),
(7, NULL, 'PARQUE EXPOSIÇÃO ÁREA DE LEILÃO', '', '2314', 0, 0, 0),
(8, 'Administrativo', 'BRUNA', '', '2059', 0, 0, 0),
(9, 'Administrativo', 'INALDES', '', '2050', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ciencia_e_tecnologia`
--

CREATE TABLE `ciencia_e_tecnologia` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ciencia_e_tecnologia`
--

INSERT INTO `ciencia_e_tecnologia` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, '', 'RECEPÇÃO', '', '1819', 0, 0, 1),
(2, '', 'RECEPÇÃO', '', '1819', 0, 0, 0),
(3, 'Administrativo', NULL, 'GISLAINE/INGRID', '2390', 0, 0, 0),
(4, 'Administrativo', 'JÚLIA', '', '2080', 0, 0, 0),
(5, '', 'RECEPÇÃO 1800', '', '2371', 0, 0, 0),
(6, '', 'RECEPÇÃO 1800', '', '2387', 0, 0, 0),
(7, '', 'RECEPÇÃO 1800', '', '2398', 0, 0, 0),
(8, 'Laboratório', '', 'Isaac', '(31) 997539201', 0, 1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `controladoria_geral`
--

CREATE TABLE `controladoria_geral` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `controladoria_geral`
--

INSERT INTO `controladoria_geral` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, 'Ouvidoria', 'CINTHIA', '', '2271', 0, 0, 0),
(2, 'Ouvidoria', 'GABI', '', '2200', 0, 0, 0),
(3, 'Ouvidoria', 'JAQUELINE', '', '1951', 0, 0, 0),
(4, 'Controle Interno', NULL, 'ULISSES/ANDRE LUIZ/NAPOLIANA', '2019', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cultura`
--

CREATE TABLE `cultura` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cultura`
--

INSERT INTO `cultura` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO CENTRO CULTURAL', '', '1901', 0, 0, 0),
(2, NULL, 'ADMINISTRATIVO', '', '1918', 0, 0, 0),
(3, NULL, 'ADMINISTRATIVO', '', '2384', 0, 0, 0),
(4, NULL, 'CASA DO ARTESÃO', '', '1994', 0, 0, 0),
(5, NULL, 'BIBLIOTECA MUNICIPAL', '', '1902', 0, 0, 0),
(6, 'Centro Cultural', 'BIBLIOTECA', '', '2384', 0, 0, 0),
(7, NULL, NULL, 'PATRIMÔNIO HISTÓRICO/TURISMO', '1957', 0, 0, 0),
(8, NULL, 'CASA DAS ARTES', '', '1992', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `desenvolvimento_economico`
--

CREATE TABLE `desenvolvimento_economico` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `desenvolvimento_economico`
--

INSERT INTO `desenvolvimento_economico` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1928', 0, 0, 0),
(2, NULL, 'RECEPÇÃO', '', '1928', 0, 0, 0),
(3, NULL, 'ADMINISTRATIVO', '', '1910', 0, 0, 0),
(4, 'Administrativo', 'MARCIANA', '', '2372', 0, 0, 0),
(5, NULL, 'JUCEMG', '', '1929', 0, 0, 0),
(6, 'SINE', 'PAV', 'RECEITA FEDERAL', '1944', 0, 0, 0),
(7, 'SINE', 'COORDENAÇÃO SINE', '', '1915', 0, 0, 0),
(8, NULL, 'SINE', '', '1885', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `educacao`
--

CREATE TABLE `educacao` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `educacao`
--

INSERT INTO `educacao` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1860', 0, 0, 0),
(2, 'Secretaria', 'RECEPÇÃO', '', '1860', 0, 0, 0),
(3, 'Secretaria', NULL, 'ANIQUELI/CAMILA/LUCIANA', '2182', 0, 0, 0),
(4, 'Secretaria', NULL, 'FRANCISLAINE/JULIANA', '2183', 0, 0, 0),
(5, 'Secretaria', 'NAIARA', '', '2178', 0, 0, 0),
(6, 'Secretaria', NULL, 'ANGELA/ROSANGELA', '1839', 0, 0, 0),
(7, 'Secretaria', 'COORDENAÇÃO PEDAGÓGICA', '', '2184', 0, 0, 0),
(8, 'Secretaria', 'COMPRAS', 'LÚCIA', '2181', 0, 0, 0),
(9, 'Secretaria', 'ALMOXARIFADO EDUCAÇÃO', '', '2125/2126', 0, 0, 0),
(10, 'Escola Municipal Manoel Gonçalves dos Santos', 'SECRETARIA', '', '1924', 0, 0, 0),
(11, 'Escola Municipal Manoel Gonçalves dos Santos', 'DIRETORIA', '', '2385', 0, 0, 0),
(12, 'CEI - Centro de Educação Infantil', 'SECRETARIA', '', '1872', 0, 0, 0),
(13, 'CEI - Centro de Educação Infantil', 'COORDENAÇÃO', '', '1873', 0, 0, 0),
(14, 'CEI - Centro de Educação Infantil', 'DIREÇÃO', '', '2176', 0, 0, 0),
(15, 'CESGRA', 'SECRETARIA', '', '1870', 0, 0, 0),
(16, 'CESGRA', 'PEDAGÓGICO', '', '2397', 0, 0, 0),
(17, 'CESGRA', 'DIRETOR DIEGO', '', '2220', 0, 0, 0),
(18, 'Escola Integral Maria de Lourdes', 'SECRETARIA', '', '1932', 0, 0, 0),
(19, 'Escola Integral Maria de Lourdes', 'SUPERVISÃO', '', '2203', 0, 0, 0),
(20, 'Escola Integral Maria de Lourdes', 'DIREÇÃO', '', '2392', 0, 0, 0),
(21, 'Escola Integral Maria de Lourdes', 'VICE DIREÇÃO', '', '2202', 0, 0, 0),
(22, NULL, 'ESCOLA MUNICIPAL DE PACAS', '', '1846', 0, 0, 0),
(23, 'Escola Integral Ioleide', 'SECRETARIA', '', '1980', 0, 0, 0),
(24, 'Escola Integral Ioleide', 'DIREÇÃO', '', '2088', 0, 0, 0),
(25, 'Escola Integral Ioleide', 'COORDENAÇÃO', '', '2101/2095', 0, 0, 0),
(26, 'Escola Integral Ioleide', 'EDUCAÇÃO INFANTIL', '', '2087', 0, 0, 0),
(27, 'Escola Municipal de Vargem Alegre', 'SECRETARIA', '', '1868', 0, 0, 0),
(28, 'Escola Municipal de Vargem Alegre', 'DIRETORIA', '', '2214', 0, 0, 0),
(29, NULL, 'ESCOLA MUNICIPAL DE JURUBEBA', '', '1869', 0, 0, 0),
(30, NULL, 'ESCOLA DO UNA', '', '1867', 0, 0, 0),
(31, NULL, 'CRECHE BORGES', '', '1954', 0, 0, 0),
(32, NULL, 'ESCOLA SÃO JOSÉ', '', '1939', 0, 0, 0),
(33, 'Transporte Escolar', 'RECEPÇÃO', '', '1950', 0, 0, 0),
(34, 'Transporte Escolar', 'ADMINISTRATIVO', '', '1830', 0, 0, 0),
(35, 'Almoxarifado', 'RECEPÇÃO', '', '2125', 0, 0, 0),
(36, 'NAIÊ', 'RECEPÇÃO', '', '1930', 0, 0, 0),
(37, 'NAIÊ', 'COORDENAÇÃO', '', '2391', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `emails`
--

CREATE TABLE `emails` (
  `id` int NOT NULL,
  `setor` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `emails`
--

INSERT INTO `emails` (`id`, `setor`, `email`, `created_at`, `updated_at`) VALUES
(1, 'fazenda', 'protocolointernosg@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(2, 'fazenda', 'fazenda2124@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(3, 'fazenda', 'tesouraria@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(4, 'fazenda', 'tributos@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(5, 'fazenda', 'contabilidade@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(6, 'fazenda', 'fiscalizacao@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(7, 'saude', 'ces_@yahoo.com.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(8, 'saude', 'visa@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(9, 'saude', 'transportesaudesgra@yahoo.com.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(10, 'saude', 'tfdsgra@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(11, 'saude', 'psf.urbano@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(12, 'saude', 'psf2vargemalegre@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(13, 'saude', 'psf5sgra@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(14, 'saude', 'psfguanabara@yahoo.com.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(15, 'saude', 'psfrecreio3@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(16, 'saude', 'servicosocialdasaude@yahoo.com.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(17, 'saude', 'saudebucalsaogoncalo@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(18, 'saude', 'gestor.saude@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(19, 'saude', 'almoxarifadorequisicaosaude@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(20, 'saude', 'medicamentosalmoxarifado@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(21, 'saude', 'centrodereabilitacaosgra@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(22, 'saude', 'nupisipeixevivo@yahoo.com.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(23, 'saude', 'campssra@outlook.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(24, 'serviços_urbanos', 'servicosurbanos2@bol.com.bR', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(25, 'serviços_urbanos', 'servicosurbanos@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(26, 'serviços_urbanos', 'setoreletricopm2@yahoo.com.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(27, 'serviços_urbanos', 'eta@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(28, 'serviços_urbanos', 'dae@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(29, 'serviços_urbanos', 'luzimarsouza75@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(30, 'serviços_urbanos', 'almoxarifado@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(31, 'serviços_urbanos', 'cemiteriossgra@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(32, 'serviços_urbanos', 'c.apoiopontecoronel@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(33, 'educacao', 'transporteescolar20242025@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(34, 'educacao', 'naiesgeducacao@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(35, 'educacao', 'integralmariadelourdes@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(36, 'educacao', 'integralvargemalegre@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(37, 'educacao', 'expedicaoalmoxarifadosme@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(38, 'educacao', 'enmanoelgmoreira@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(39, 'educacao', 'emtiioleideaparecida@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(40, 'educacao', 'educacao@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(41, 'educacao', 'educacaoinfantildouna@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(42, 'educacao', 'ceijosealencar@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(43, 'educacao', 'ceijurubeba@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(44, 'educacao', 'cesgradigital@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(45, 'governo', 'sgradefesacivil@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(46, 'governo', 'sao.goncalo.rio.abaixo@emater.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(47, 'governo', 'postoavancadosgra@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(48, 'governo', 'governo@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(49, 'governo', 'depor.sgrioabaixo@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(50, 'governo', 'comunicacao@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(51, 'administracao', 'administracao.secretario@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(52, 'administracao', 'administracao@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(53, 'administracao', 'equipe.almoxarifado@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(54, 'administracao', 'gestaodecontratos@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(55, 'administracao', 'licitacoes@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(56, 'administracao', 'hotelariapa@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(57, 'administracao', 'compras@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(58, 'administracao', 'segtrabalhotmsg@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(59, 'administracao', 'patrimoniosgra@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(60, 'trabalho', 'sinesgra@gmail.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(61, 'trabalho', 'saladosconselhos@yahoo.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(62, 'trabalho', 'habitacao@saogoncalo.gov.mg.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(63, 'trabalho', 'desenvolvimentoeconomico@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(64, 'trabalho', 'cras@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(65, 'trabalho', 'creas@saogoncalo.gov.mg.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(66, 'trabalho', 'conselhotutelarsgra@yahoo.com.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(67, 'trabalho', 'ajudiciariasgra@yahoo.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(68, 'trabalho', 'acaosocial@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(69, 'obras', 'obras@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(70, 'cultura', 'cultura@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(71, 'cultura', 'casadoartesaosgra@outlook.com', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(72, 'esportes', 'esportes@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(73, 'gestao_pessoas', 'rh@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(74, 'gestao_pessoas', 'gestaodepessoas@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(75, 'gestao_pessoas', 'centrodetreinamento@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(76, 'juridico', 'procurador@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(77, 'juridico', 'juridico@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(78, 'agricultura', 'agricultura@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(79, 'meio_ambiente', 'paisagismo@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(80, 'meio_ambiente', 'meioambiente@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(81, 'controladoria', 'ouvidoria@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(82, 'controladoria', 'faleconosco@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(83, 'planejamento', 'planejamento@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(84, 'tecnologia', 'ti@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29'),
(85, 'transportes', 'transportes@saogoncalo.mg.gov.br', '2025-11-12 17:12:29', '2025-11-12 17:12:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `esporte`
--

CREATE TABLE `esporte` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `esporte`
--

INSERT INTO `esporte` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1925', 0, 0, 0),
(2, NULL, 'RECEPÇÃO', '', '1925', 0, 0, 0),
(3, 'Administrativo', 'GISLAINE', '', '2361', 0, 0, 0),
(4, NULL, 'LUAN', '', '2384', 0, 0, 0),
(5, NULL, 'GABRIEL', '', '2273', 0, 0, 0),
(6, NULL, 'DOUGLAS', '', '2274', 0, 0, 0),
(7, NULL, 'JULIEMERSSON', '', '2275', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `externos`
--

CREATE TABLE `externos` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `externos`
--

INSERT INTO `externos` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'ACIASGRA', '', '3833 5291', 0, 0, 0),
(2, NULL, 'APAE', '', '3833 5231', 0, 0, 0),
(3, NULL, 'CAF TRANSPORTES', '', '3380 1751', 0, 0, 0),
(4, NULL, 'CAIXA', '', '3833 6150', 0, 0, 0),
(5, NULL, 'CÂMARA MUNICIPAL', '', '3833 5202', 0, 0, 0),
(6, NULL, 'CATES', '', '3833 5195', 0, 0, 0),
(7, NULL, 'CORREIOS', '', '3833 5217', 0, 0, 0),
(8, NULL, 'ESCOLA DESEMBARGADOR', '', '3833 5150', 0, 0, 0),
(9, NULL, 'RÁDIO SÃO GONÇALO', '', '3833 5482 Whatsapp 9 7219 9633', 0, 0, 0),
(10, NULL, 'SERGAME', '', '3833 5883', 0, 0, 0),
(11, NULL, 'INTS', '', '9 9871 1688', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `fazenda`
--

CREATE TABLE `fazenda` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fazenda`
--

INSERT INTO `fazenda` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '2003', 0, 0, 0),
(2, 'Contabilidade', 'SOLANGE', '', '2012', 0, 0, 0),
(3, 'Contabilidade', NULL, 'FRANCIELE/JÚLIO', '2020', 0, 0, 0),
(4, NULL, 'CONTABILIDADE', '', '1800', 0, 0, 0),
(5, 'Fiscalização', 'ALMIR', '', '2030', 0, 0, 0),
(6, 'Protocolo Interno', NULL, 'JULIANA/KAROL/ANA PAULA', '2035', 0, 0, 0),
(7, 'Tesouraria', 'ADRIANA/ISAMARA/FRANCIELE/MICHELE', '', '2013', 0, 0, 0),
(8, NULL, 'TESOURARIA', '', '1800', 0, 0, 0),
(9, 'Tributos', 'ELAINE', '', '2034', 0, 0, 0),
(10, 'Tributos', 'HISANARA', '', '2038', 0, 0, 0),
(11, 'Tributos', NULL, 'THAIS/JHENIFER/LUCAS', '2025', 0, 0, 0),
(12, 'Tributos', 'PATRÍCIA', '', '2040', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `frotas`
--

CREATE TABLE `frotas` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `frotas`
--

INSERT INTO `frotas` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1969', 0, 0, 0),
(2, 'Transporte', 'RECEPÇÃO', '', '1907', 0, 0, 0),
(3, 'Transporte', 'TONI', '', '2093', 0, 0, 0),
(4, 'Transporte', 'CINTHIA', '', '2367', 0, 0, 0),
(5, 'Coletivos', 'RECEPÇÃO', '', '1969', 0, 0, 0),
(6, 'Coletivos', 'GISELE', '', '2094', 0, 0, 0),
(7, 'Coletivos', 'JAQUELINE', '', '2337', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `gestao_pessoas`
--

CREATE TABLE `gestao_pessoas` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `gestao_pessoas`
--

INSERT INTO `gestao_pessoas` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '2039', 0, 0, 0),
(2, 'Gestão de Pessoas', 'HENDRIGO', '', '2039', 0, 0, 0),
(3, NULL, 'CENTRO DE TREINAMENTO', '', '1875', 0, 0, 0),
(4, 'RH', NULL, 'ALINE/SILVANA/ISABELA', '1817', 0, 0, 0),
(5, NULL, 'RH', '', '1937', 0, 0, 0),
(6, 'RH', 'VALESCA', '', '2021', 0, 0, 0),
(7, 'RH', NULL, 'GERALDA/MÁRCIA', '2031', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `governo`
--

CREATE TABLE `governo` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `governo`
--

INSERT INTO `governo` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '2006', 0, 0, 0),
(2, 'Governo', 'ANA BEATRIZ', '', '2005', 0, 0, 0),
(3, 'Governo', 'ANANDA', '', '2006', 0, 0, 0),
(4, 'Comunicação', 'FERNANDA', '', '1926', 0, 0, 0),
(5, 'Comunicação', 'GERAL', '', '1922', 0, 0, 0),
(6, 'Convênios', NULL, 'RAQUEL/YASMIN/LUIZ', '2014', 0, 0, 0),
(7, 'Convênio', 'CORPO DE BOMBEIROS', 'RECEPÇÃO', '1949', 0, 0, 0),
(8, 'Convênio', 'CORPO DE BOMBEIROS', 'PONTO DE TÁXI', '1859', 0, 0, 0),
(9, 'Convênio', 'EMATER', '', '1865', 0, 0, 0),
(10, 'Convênio', 'POLÍCIA CIVIL', 'RECEPÇÃO', '1967', 0, 0, 0),
(11, 'Convênio', 'POLÍCIA CIVIL', 'CHARDESOM', '2126', 0, 0, 0),
(12, 'Convênio', 'POLÍCIA CIVIL', 'DIEGO', '2127', 0, 0, 0),
(13, 'Convênio', 'POLÍCIA CIVIL', 'ANA CLÁUDIA', '2128', 0, 0, 0),
(14, 'Convênio', 'POLÍCIA MILITAR', '', '1876', 0, 0, 0),
(15, NULL, 'DEFESA CIVIL', '', '1801', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `meio_ambiente`
--

CREATE TABLE `meio_ambiente` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `meio_ambiente`
--

INSERT INTO `meio_ambiente` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1911', 0, 0, 0),
(2, 'Secretaria', 'RECEPÇÃO', '', '1911', 0, 0, 0),
(3, 'Secretaria', 'ADMINISTRATIVO', '', '1912', 0, 0, 0),
(4, 'Secretaria', 'ADMINISTRATIVO', '', '2375', 0, 0, 0),
(5, 'Secretaria', 'ADMINISTRATIVO', '', '2380', 0, 0, 0),
(6, 'Secretaria', 'CORTE DE ÁRVORES', '', '2378', 0, 0, 0),
(7, 'Secretaria', 'LICENCIAMENTO AMBIENTAL', '', '2376', 0, 0, 0),
(8, 'Aterro Sanitário', 'PORTARIA', 'ATERRO', '1914', 0, 0, 0),
(9, 'Aterro Sanitário', 'BALANÇA', 'ATERRO', '2270', 0, 0, 0),
(10, NULL, 'VIVEIRO DE MUDAS', '', '1943', 0, 0, 0),
(11, 'Parque Ecológico', 'PETI', '', '1989', 0, 0, 0),
(12, 'Parque Ecológico', 'EUZÉBIO', '', '2053', 0, 0, 0),
(13, NULL, 'JARDINAGEM E PAISAGISMO', '', '1864', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `obras`
--

CREATE TABLE `obras` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `obras`
--

INSERT INTO `obras` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1889', 0, 0, 0),
(2, NULL, 'RECEPÇÃO', '', '1889', 0, 0, 0),
(3, NULL, 'JOHNNY', '', '2286', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `planejamento`
--

CREATE TABLE `planejamento` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `planejamento`
--

INSERT INTO `planejamento` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1802', 0, 0, 0),
(2, 'Planejamento', 'SARAH', '', '1802', 0, 0, 0),
(3, 'Planejamento', 'JESQUELINE', '', '2028', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `predio_prefeitura`
--

CREATE TABLE `predio_prefeitura` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `predio_prefeitura`
--

INSERT INTO `predio_prefeitura` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1800', 0, 0, 0),
(2, 'Administração', 'MANUELA', '', '2004', 0, 0, 0),
(3, 'Administração/Licitação', 'IRIS', '', '1921', 0, 0, 0),
(4, 'Compras', NULL, 'HAUYTA/MARIA EDUARDA', '1801', 0, 0, 0),
(5, 'Compras', 'GABRIELA', '', '2007', 0, 0, 0),
(6, 'Compras', 'NORA', '', '1803', 0, 0, 0),
(7, 'Licitação', NULL, 'ELIANE/DIRCILENE', '2009', 0, 0, 0),
(8, 'Licitação', 'NONATO', '', '2008', 0, 0, 0),
(9, 'Licitação', 'JANETE', '', '1805', 0, 0, 0),
(10, 'Licitação', 'NINHA', '', '2036', 0, 0, 0),
(11, 'Licitação', 'FABIANE', '', '2010', 0, 0, 0),
(12, 'Licitação', NULL, 'FERNANDA/IGOR', '2032', 0, 0, 0),
(13, 'Encarregada Serv. Gerais', 'FLAVIANA', '', '2033', 0, 0, 0),
(14, 'Recepção 1º Andar', 'MIRELA', '', '1813', 0, 0, 0),
(15, NULL, 'RECEPÇÃO 2º ANDAR', '', '1820', 0, 0, 0),
(16, 'Recepção 3º Andar', 'JEISIANE', '', '2002', 0, 0, 0),
(17, NULL, 'SIAT', '', '1955', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `procuradoria_juridica`
--

CREATE TABLE `procuradoria_juridica` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `procuradoria_juridica`
--

INSERT INTO `procuradoria_juridica` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '2299 ; 2015 ; 2017', 0, 0, 0),
(2, 'Jurídico', 'SABRINA', '', '2015', 0, 0, 0),
(3, 'Procuradoria', 'JEAN', '', '2017', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `saude`
--

CREATE TABLE `saude` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `saude`
--

INSERT INTO `saude` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1827', 0, 0, 0),
(2, 'Secretaria', 'RECEPÇÃO', '', '1827', 0, 0, 0),
(3, 'Secretaria', 'CPD', '', '2159', 0, 0, 0),
(4, 'Secretaria', 'COMPRAS', '', '2041', 0, 0, 0),
(5, 'Secretaria', 'ADMINISTRATIVO', '', '2042', 0, 0, 0),
(6, 'Secretaria', 'ADMINISTRATIVO', '', '2124', 0, 0, 0),
(7, 'Secretaria', 'ADMINISTRATIVO', 'THACIANA', '2049', 0, 0, 0),
(8, 'Secretaria', 'ADMINISTRATIVO', 'RAFAELA', '2043', 0, 0, 0),
(9, 'Secretaria', 'ADMINISTRATIVO', '', '2046', 0, 0, 0),
(10, 'Secretaria', 'LICITAÇÃO', 'JULIANA', '2201', 0, 0, 0),
(11, 'Secretaria', 'COORDENAÇÃO PSF', '', '2044', 0, 0, 0),
(12, 'Secretaria', 'EQUIPE APOIO PSF', '', '2045', 0, 0, 0),
(13, 'Secretaria', 'APOIO APS', '', '2160', 0, 0, 0),
(14, 'Secretaria', 'ALMOXARIFADO', '', '2081', 0, 0, 0),
(15, 'Secretaria', 'ALMOXARIFADO MATERIAL MÉDICO', '', '2069', 0, 0, 0),
(16, 'Secretaria', 'ALMOXARIFADO DE LIMPEZA', '', '2139', 0, 0, 0),
(17, 'Pronto Atendimento (PA)', 'RECEPÇÃO', '', '1840/2063', 0, 0, 0),
(18, 'Pronto Atendimento (PA)', 'COORDENAÇÃO PA', '', '1852', 0, 0, 0),
(19, 'Pronto Atendimento (PA)', 'LABORATÓRIO RECEPÇÃO', '', '2075', 0, 0, 0),
(20, 'Pronto Atendimento (PA)', 'COORDENAÇÃO ENFERMAGEM', '', '2077', 0, 0, 0),
(21, 'Pronto Atendimento (PA)', 'COORDENADOR DOS MOTORISTAS', '', '2100', 0, 0, 0),
(22, 'Pronto Atendimento (PA)', 'ASSISTÊNCIA SOCIAL', '', '2067', 0, 0, 0),
(23, 'Peixe-Vivo', 'RECEPÇÃO', '', '1836', 0, 0, 0),
(24, 'Peixe-Vivo', 'COORDENAÇÃO', '', '2104', 0, 0, 0),
(25, 'Peixe-Vivo', 'ADMINISTRATIVO', '', '2110', 0, 0, 0),
(26, 'Peixe-Vivo', 'ESCAPE', '', '1983', 0, 0, 0),
(27, 'PSF Bom Sucesso', 'RECEPÇÃO', '', '1906', 0, 0, 0),
(28, 'PSF Borges', 'RECEPÇÃO', '', '1862', 0, 0, 0),
(29, 'PSF Fernandes', 'RECEPÇÃO', '', '1861', 0, 0, 0),
(30, 'PSF Guanabara', 'RECEPÇÃO', '', '1854', 0, 0, 0),
(31, 'PSF Guanabara', 'SALA DOS AGENTES DE SAÚDE', '', '2196', 0, 0, 0),
(32, NULL, 'PSF JURUBEBA', '', '1981', 0, 0, 0),
(33, 'PSF Pacas', 'RECEPÇÃO', '', '1853', 0, 0, 0),
(34, NULL, 'PSF PEDRAS', '', '1858', 0, 0, 0),
(35, 'PSF Ponte Coronel 1', 'RECEPÇÃO', '', '1941', 0, 0, 0),
(36, 'PSF Ribeirão (Ponte Coronel 2)', 'RECEPÇÃO', '', '1942', 0, 0, 0),
(37, 'PSF Recreio', 'RECEPÇÃO', '', '1850', 0, 0, 0),
(38, 'PSF Recreio', 'VACINA', '', '2162', 0, 0, 0),
(39, 'PSF São José', 'RECEPÇÃO', '', '1847', 0, 0, 0),
(40, NULL, 'PSF TIMIRIM', '', '1913', 0, 0, 0),
(41, 'PSF Una', 'RECEPÇÃO', '', '1856', 0, 0, 0),
(42, 'PSF Una', 'SALA DOS AGENTES DE SAÚDE', '', '2103', 0, 0, 0),
(43, 'PSF Urbano-Patrimônio', 'RECEPÇÃO', '', '1844', 0, 0, 0),
(44, 'PSF Urbano', 'SALA DOS AGENTES DE SAÚDE', '', '2192', 0, 0, 0),
(45, NULL, 'PSF VARGEM DA LUA', '', '1883', 0, 0, 0),
(46, 'PSF Vargem Alegre', 'RECEPÇÃO', '', '1845', 0, 0, 0),
(47, 'CES (Centro de Especialização em Saúde)', 'RECEPÇÃO', '', '1829', 0, 0, 0),
(48, 'CES (Centro de Especialização em Saúde)', 'COORDENAÇÃO', '', '2051', 0, 0, 0),
(49, 'CES (Centro de Especialização em Saúde)', 'SALA DE MARCAÇÃO', '', '2052', 0, 0, 0),
(50, 'CES (Centro de Especialização em Saúde)', 'PRONTUÁRIO', '', '2140', 0, 0, 0),
(51, 'Transporte Saúde', 'RECEPÇÃO', '', '1833', 0, 0, 0),
(52, 'Transporte Saúde', 'RECEPÇÃO', '', '1935', 0, 0, 0),
(53, 'Transporte Saúde', 'COORDENAÇÃO', '', '2121', 0, 0, 0),
(54, 'TFD', 'RECEPÇÃO', '', '1834', 0, 0, 0),
(55, 'TFD', 'MARCAÇÃO', '', '2083', 0, 0, 0),
(56, 'TFD', 'MARCAÇÃO', '', '2084', 0, 0, 0),
(57, 'TFD', 'COORDENAÇÃO LETÍCIA', '', '2085', 0, 0, 0),
(58, 'TFD', 'COORDENAÇÃO', '', '2313', 0, 0, 0),
(59, 'Centro Reabilitação em Saúde', 'RECEPÇÃO', '', '1857', 0, 0, 0),
(60, 'Centro Reabilitação em Saúde', 'GINECOLOGIA', '', '1843', 0, 0, 0),
(61, 'Centro Reabilitação em Saúde', 'TERAPEUTA', '', '1817', 0, 0, 0),
(62, 'Centro Odontológico', 'RECEPÇÃO', '', '1832', 0, 0, 0),
(63, 'Centro Odontológico', 'COORDENAÇÃO', '', '2048', 0, 0, 0),
(64, 'Farmácia Municipal', 'RECEPÇÃO', '', '1837', 0, 0, 0),
(65, 'Farmácia Especializada', 'RECEPÇÃO', '', '1848', 0, 0, 0),
(66, 'Vigilância Sanitária', 'RECEPÇÃO', '', '1835', 0, 0, 0),
(67, 'Vigilância Sanitária', 'COORDENAÇÃO SANITÁRIA', '', '2097', 0, 0, 0),
(68, 'Vigilância Sanitária', 'COORDENAÇÃO EPIDEMIOLÓGICA', '', '2098', 0, 0, 0),
(69, 'Vigilância Sanitária', 'PCE', '', '2099', 0, 0, 0),
(70, 'Vigilância Sanitária', 'SALA DOS FISCAIS', '', '2130', 0, 0, 0),
(71, 'Serviço Social', 'RECEPÇÃO', '', '1838', 0, 0, 0),
(72, 'CAMPS', 'RECEPÇÃO', '', '1984', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_urbanos`
--

CREATE TABLE `servicos_urbanos` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergencia` tinyint(1) NOT NULL DEFAULT '0',
  `oculto` tinyint(1) NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `servicos_urbanos`
--

INSERT INTO `servicos_urbanos` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`, `emergencia`, `oculto`, `principal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1895', 0, 0, 0),
(2, NULL, 'RECEPÇÃO', '', '1895', 0, 0, 0),
(3, NULL, 'ADMINISTRATIVO', '', '2137', 0, 0, 0),
(4, 'Coleta', 'RODRIGO', '', '2395', 0, 0, 0),
(5, 'Limpeza Urbana', 'RECEPÇÃO', '', '1896', 0, 0, 0),
(6, 'Limpeza Urbana', 'ADMINISTRATIVO', '', '2328', 0, 0, 0),
(7, 'Elétrica', 'SETOR ELÉTRICA', '', '1892', 0, 0, 0),
(8, 'Manutenção de Vias Urbanas', 'RECEPÇÃO', '', '1894', 0, 0, 0),
(9, 'Manutenção de Vias Urbanas', 'ADMINISTRATIVO', '', '2322', 0, 0, 0),
(10, 'Manutenção de Estradas Rurais', 'RECEPÇÃO', '', '1968', 0, 0, 0),
(11, 'Manutenção de Estradas Rurais', 'ADMINISTRATIVO', '', '2090', 0, 0, 0),
(12, 'DAE', 'RECEPÇÃO', '', '1831', 0, 0, 0),
(13, 'DAE', 'RECEPÇÃO', '', '1966', 0, 0, 0),
(14, 'DAE', 'ADMINISTRATIVO', '', '2394', 0, 0, 0),
(15, 'DAE', 'COORDENAÇÃO', 'EDIO', '2096', 0, 0, 0),
(16, 'ETA', 'ETA', '', '1898', 0, 0, 0),
(17, 'SAAE', 'ADMINISTRATIVO', '', '2058', 0, 0, 0),
(18, 'Velório', 'RECEPÇÃO', '', '1904', 0, 0, 0),
(19, 'Pátio de Obras', 'RECEPÇÃO', '', '1891', 0, 0, 0),
(20, 'Pátio de Obras', 'ADMINISTRATIVO', '', '2319', 0, 0, 0),
(21, 'Ponto de Apoio Ponte Coronel', 'RECEPÇÃO', '', '1952', 0, 0, 0),
(22, 'Ponto de Apoio Vargem Alegre', 'RECEPÇÃO', '', '1979', 0, 0, 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `acao_social`
--
ALTER TABLE `acao_social`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `acao_social` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `administracao`
--
ALTER TABLE `administracao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `administracao` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `agricultura`
--
ALTER TABLE `agricultura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `agricultura` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `ciencia_e_tecnologia`
--
ALTER TABLE `ciencia_e_tecnologia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `ciencia_e_tecnologia` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `controladoria_geral`
--
ALTER TABLE `controladoria_geral`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `controladoria_geral` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `cultura`
--
ALTER TABLE `cultura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `cultura` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `desenvolvimento_economico`
--
ALTER TABLE `desenvolvimento_economico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `desenvolvimento_economico` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `educacao`
--
ALTER TABLE `educacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `educacao` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email_setor` (`setor`,`email`),
  ADD KEY `idx_setor` (`setor`);

--
-- Índices de tabela `esporte`
--
ALTER TABLE `esporte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `esporte` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `externos`
--
ALTER TABLE `externos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `externos` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `fazenda`
--
ALTER TABLE `fazenda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `fazenda` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `frotas`
--
ALTER TABLE `frotas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `frotas` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `gestao_pessoas`
--
ALTER TABLE `gestao_pessoas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `gestao_pessoas` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `governo`
--
ALTER TABLE `governo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `governo` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `meio_ambiente`
--
ALTER TABLE `meio_ambiente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `meio_ambiente` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `obras`
--
ALTER TABLE `obras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `obras` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `planejamento`
--
ALTER TABLE `planejamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `planejamento` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `predio_prefeitura`
--
ALTER TABLE `predio_prefeitura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `predio_prefeitura` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `procuradoria_juridica`
--
ALTER TABLE `procuradoria_juridica`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `procuradoria_juridica` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `saude`
--
ALTER TABLE `saude`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `saude` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- Índices de tabela `servicos_urbanos`
--
ALTER TABLE `servicos_urbanos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `servicos_urbanos` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `acao_social`
--
ALTER TABLE `acao_social`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de tabela `administracao`
--
ALTER TABLE `administracao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `agricultura`
--
ALTER TABLE `agricultura`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `ciencia_e_tecnologia`
--
ALTER TABLE `ciencia_e_tecnologia`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `controladoria_geral`
--
ALTER TABLE `controladoria_geral`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `cultura`
--
ALTER TABLE `cultura`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `desenvolvimento_economico`
--
ALTER TABLE `desenvolvimento_economico`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `educacao`
--
ALTER TABLE `educacao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `emails`
--
ALTER TABLE `emails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT de tabela `esporte`
--
ALTER TABLE `esporte`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `externos`
--
ALTER TABLE `externos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `fazenda`
--
ALTER TABLE `fazenda`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `frotas`
--
ALTER TABLE `frotas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `gestao_pessoas`
--
ALTER TABLE `gestao_pessoas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `governo`
--
ALTER TABLE `governo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `meio_ambiente`
--
ALTER TABLE `meio_ambiente`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `obras`
--
ALTER TABLE `obras`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `planejamento`
--
ALTER TABLE `planejamento`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `predio_prefeitura`
--
ALTER TABLE `predio_prefeitura`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `procuradoria_juridica`
--
ALTER TABLE `procuradoria_juridica`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `saude`
--
ALTER TABLE `saude`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de tabela `servicos_urbanos`
--
ALTER TABLE `servicos_urbanos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

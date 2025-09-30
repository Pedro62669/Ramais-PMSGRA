-- phpMyAdmin SQL Dump
-- version 5.2.2deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 30/09/2025 às 16:16
-- Versão do servidor: 8.4.6-0ubuntu0.25.04.3
-- Versão do PHP: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ramais`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `acao_social`
--

CREATE TABLE `acao_social` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `acao_social`
--

INSERT INTO `acao_social` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1877/1920'),
(2, NULL, 'CONSELHO MUNICIPAL DA PESSOA IDOSA, CRIANÇAS E ADOLESCENTES E DA ASSISTÊNCIA SOCIAL', '', '2252'),
(3, NULL, 'ADMINISTRATIVO', '', '1878'),
(4, NULL, 'ADMINISTRATIVO', '', '2256'),
(5, NULL, 'BOLSA FAMILIA', '', '1879'),
(6, NULL, 'PASSE OU LEITE', '', '2253'),
(7, NULL, 'ASSISTENTE SOCIAL', '', '2254'),
(8, NULL, 'ASSISTENTE SOCIAL', '', '2255'),
(9, 'HABITAÇÃO', 'RECEPÇÃO', '', '1880'),
(10, 'HABITAÇÃO', 'HABITAÇÃO', '', '2260'),
(11, 'HABITAÇÃO', 'ADMINISTRATIVO', '', '2266'),
(12, 'HABITAÇÃO', '', 'MARCINHO', '2262'),
(13, 'HABITAÇÃO', 'ASSISTENTE SOCIAL', '', '2264'),
(14, 'HABITAÇÃO', 'ENGENHARIA', '', '2268'),
(15, 'HABITAÇÃO', 'GALPÃO DA HABITAÇÃO', '', '1934'),
(16, 'CRAS', 'RECEPÇÃO', '', '1884'),
(17, 'CRAS', 'EQUIPE TÉCNICA', '', '1888/2285'),
(18, 'CRAS', 'ORIENTAÇÃO SOCIAL', '', '2287/2289'),
(19, 'CRAS', 'EQUIPE VOLANTE', '', '2290'),
(20, 'CRAS', 'COORDENAÇÃO', '', '2282/2283'),
(21, 'CRAS', 'QUALIFICAR', '', '1933'),
(22, 'CREAS', 'RECEPÇÃO', '', '1887'),
(23, 'CREAS', 'EQUIPE TÉCNICA', '', '2292/2302'),
(24, 'CREAS', 'COORDENAÇÃO', '', '2301'),
(25, 'CREAS', 'MIGRANTES', '', '2294'),
(26, 'DEFESA CIVIL', 'RECEPÇÃO', '', '1881'),
(27, 'DEFESA CIVIL', 'SINE', '', '1885'),
(28, 'DEFESA CIVIL', 'COORDENAÇÃO SINE', '', '1915'),
(30, 'CONSELHO TUTELAR', 'RECEPÇÃO', '', '1882'),
(31, 'CONSELHO TUTELAR', 'ATENDIMENTO', '', '2280'),
(32, 'SALA DOS CONSELHOS', 'RECEPCÃO', '', '1988'),
(33, 'ASSISTÊNCIA JURÍDICA (DEFENSORIA PUBLICA)', 'RECEPÇÃO', '', '1956'),
(34, 'ASSISTÊNCIA JURÍDICA (DEFENSORIA PUBLICA)', '', 'NESTOR', '2091'),
(35, 'ASSISTÊNCIA JURÍDICA (DEFENSORIA PUBLICA)', '', 'CAROL', '2092'),
(36, 'EXPEDIÇÃO DE DOCUMENTOS', '', 'VIVINHA', '1923');

-- --------------------------------------------------------

--
-- Estrutura para tabela `administracao`
--

CREATE TABLE `administracao` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `administracao`
--

INSERT INTO `administracao` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, 'PATRIMÔNIO', 'RECEPÇÃO', 'SIMONE', '1893'),
(2, 'PATRIMÔNIO', '', 'HENDRIGO', '2321'),
(3, 'SEGURANÇA DO TRABALHO', 'RECEPÇÃO', '', '1909'),
(4, 'SEGURANÇA DO TRABALHO', 'ALMOXARIFADO', '', '2161'),
(5, 'SEGURANÇA DO TRABALHO', 'ADMINISTRATIVO', '', '2174'),
(6, 'ALMOXARIFADO CENTRAL', 'RECEPÇÃO', '', '1908'),
(7, 'CENTRO DE TREINAMENTO - GESTÃO DE PESSOAS', 'RECEPÇÃO', '', '1875'),
(8, 'GESTÃO DE CONTRATOS', '', '', '2133');

-- --------------------------------------------------------

--
-- Estrutura para tabela `agricultura`
--

CREATE TABLE `agricultura` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agricultura`
--

INSERT INTO `agricultura` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1905'),
(2, NULL, 'ADMINISTRATIVO', 'NATHALIA', '2358'),
(3, NULL, 'VETERINÁRIA', 'RITA', '2357'),
(4, 'EMATER', 'RECEPÇÃO', '', '1865'),
(5, 'EMATER', '', 'GLEIDSON/GABRIELA', '2316'),
(6, 'PARQUE ESPOSICAO AREA DE LEILÃO', 'RECEPÇÃO', '', '2314'),
(7, '', 'ADMINISTRATIVO', 'BRUNA', '2059'),
(9, NULL, 'ADMINISTRATIVO', 'INALDES', '2050');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ciencia_e_tecnologia`
--

CREATE TABLE `ciencia_e_tecnologia` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ciencia_e_tecnologia`
--

INSERT INTO `ciencia_e_tecnologia` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1819'),
(2, NULL, 'ADMINISTRATIVO', 'GISLAINE/INGRID', '2390'),
(3, NULL, 'ADMINISTRATIVO', 'JÚLIA', '2080'),
(4, NULL, 'RECEPÇÃO 1800', '', '2371'),
(5, NULL, 'RECEPÇÃO 1800', '', '2387'),
(6, NULL, 'RECEPÇÃO 1800', '', '2398');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cultura`
--

CREATE TABLE `cultura` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cultura`
--

INSERT INTO `cultura` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'RECEPÇÃO CENTRO CULTURAL', '', '1901'),
(2, NULL, 'ADMINISTRATIVO', '', '1918'),
(3, NULL, 'ADMINISTRATIVO', '', '2384'),
(4, NULL, 'CASA DO ARTESÃO', '', '1994'),
(5, NULL, 'BIBLIOTECA MUNICIPAL', '', '1902'),
(6, NULL, 'PATRIMÔNIO HISTÓRICO/TURISMO', '', '1957'),
(7, NULL, 'CASA DAS ARTES', '', '1992');

-- --------------------------------------------------------

--
-- Estrutura para tabela `desenvolvimento_economico`
--

CREATE TABLE `desenvolvimento_economico` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `desenvolvimento_economico`
--

INSERT INTO `desenvolvimento_economico` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1928'),
(2, NULL, 'ADMINISTRATIVO', '', '1910'),
(3, NULL, 'ADMINISTRATIVO', 'MARCIANA', '2372'),
(4, NULL, 'JUCEMG', '', '1929'),
(5, NULL, 'PAV (RECEITA FEDERAL)', '', '1944');

-- --------------------------------------------------------

--
-- Estrutura para tabela `educacao`
--

CREATE TABLE `educacao` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `educacao`
--

INSERT INTO `educacao` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, 'SECRETARIA', 'RECEPÇÃO', '', '1860'),
(2, 'SECRETARIA', '', 'ANIQUELI/CAMILA/LUCIANA', '2182'),
(3, 'SECRETARIA', '', 'FRANCISLAINE/JULIANA', '2183'),
(4, 'SECRETARIA', '', 'NAIARA', '2178'),
(5, 'SECRETARIA', '', 'ANGELA/ROSANGELA', '1839'),
(6, 'SECRETARIA', 'COORDENAÇÃO PEDAGÓGICA', '', '2184'),
(7, 'SECRETARIA', 'COMPRAS', 'LUCIA', '2181'),
(8, 'SECRETARIA', 'ALMOXARIFADO EDUCAÇÃO', '', '2125'),
(9, 'ESCOLAS', 'ESCOLA MUNICIPAL MANOEL GONÇALVES DOS SANTOS - SECRETARIA', '', '1924'),
(10, 'ESCOLAS', 'ESCOLA MUNICIPAL MANOEL GONÇALVES DOS SANTOS - DIRETORIA', '', '2385'),
(11, 'ESCOLAS', 'CEI-CENTRO DE EDUCAÇÃO INFANTIL - SECRETARIA', '', '1872'),
(12, 'ESCOLAS', 'CEI-CENTRO DE EDUCAÇÃO INFANTIL - COORDENAÇÃO', '', '1873'),
(13, 'ESCOLAS', 'CEI-CENTRO DE EDUCAÇÃO INFANTIL - DIREÇÃO', '', '2176'),
(14, 'ESCOLAS', 'CESGRA - SECRETARIA', '', '1870'),
(15, 'ESCOLAS', 'CESGRA - PEDAGÓGICO', '', '2397'),
(16, 'ESCOLAS', 'CESGRA', 'DIRETOR DIEGO', '2220'),
(17, 'ESCOLAS', 'ESCOLA INTEGRAL MARIA DE LOURDES - SECRETARIA', '', '1932'),
(18, 'ESCOLAS', 'ESCOLA INTEGRAL MARIA DE LOURDES - SUPERVISÃO', '', '2203'),
(19, 'ESCOLAS', 'ESCOLA INTEGRAL MARIA DE LOURDES - DIREÇÃO', '', '2392'),
(20, 'ESCOLAS', 'ESCOLA INTEGRAL MARIA DE LOURDES - VICE DIREÇÃO', '', '2202'),
(21, 'ESCOLAS', 'ESCOLA MUNICIPAL DE PACAS', '', '1846'),
(22, 'ESCOLAS', 'ESCOLA INTEGRAL IOLEIDE - SECRETARIA', '', '1980'),
(23, 'ESCOLAS', 'ESCOLA INTEGRAL IOLEIDE - DIREÇÃO', '', '2088'),
(24, 'ESCOLAS', 'ESCOLA INTEGRAL IOLEIDE - COORDENAÇÃO', '', '2101/2095'),
(25, 'ESCOLAS', 'ESCOLA INTEGRAL IOLEIDE - EDUCAÇÃO INFANTIL', '', '2087'),
(26, 'ESCOLAS', 'ESCOLA MUNICIPAL DE VARGEM ALEGRE - SECRETARIA', '', '1868'),
(27, 'ESCOLAS', 'ESCOLA MUNICIPAL DE VARGEM ALEGRE - DIRETORIA', '', '2214'),
(28, 'ESCOLAS', 'ESCOLA MUNICIPAL DE JURUBEBA', '', '1869'),
(29, 'ESCOLAS', 'ESCOLA DO UNA', '', '1867'),
(30, 'ESCOLAS', 'CRECHE BORGES', '', '1954'),
(31, 'ESCOLAS', 'ESCOLA SÃO JOSÉ', '', '1939'),
(32, 'TRANSPORTE ESCOLAR', 'RECEPÇÃO', '', '1950'),
(33, 'TRANSPORTE ESCOLAR', 'ADMINISTRATIVO', '', '1830'),
(34, 'ALMOXARIFADO', 'RECEPÇÃO', '', '2125'),
(35, 'NAIÊ', 'RECEPÇÃO', '', '1930'),
(36, 'NAIÊ', 'COORDENAÇÃO', '', '2391');

-- --------------------------------------------------------

--
-- Estrutura para tabela `emergencia`
--

CREATE TABLE `emergencia` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `emergencia`
--

INSERT INTO `emergencia` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'POLÍCIA MILITAR', '', '1876'),
(2, 'POLÍCIA CIVIL', 'RECEPÇÂO', '', '1967'),
(3, 'POLÍCIA CIVIL', '', 'CHARDESOM', '2126'),
(4, 'POLÍCIA CIVIL', '', 'DIEGO', '2127'),
(5, 'POLÍCIA CIVIL', '', 'ANA CLAUDIA', '2128'),
(6, 'CORPO DE BOMBEIROS', 'RECEPÇÃO', '', '1949'),
(7, 'CORPO DE BOMBEIROS', 'PONTO DE TÁXI', '', '1859');

-- --------------------------------------------------------

--
-- Estrutura para tabela `esporte`
--

CREATE TABLE `esporte` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `esporte`
--

INSERT INTO `esporte` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1925'),
(2, NULL, 'ADMINISTRATIVO', 'GISLAINE', '2361'),
(3, NULL, '', 'LUAN', '2384'),
(4, NULL, '', 'GABRIEL', '2273'),
(5, NULL, '', 'DOUGLAS', '2274'),
(6, NULL, '', 'JULIEMERSSON', '2275');

-- --------------------------------------------------------

--
-- Estrutura para tabela `externos`
--

CREATE TABLE `externos` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `externos`
--

INSERT INTO `externos` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'ACIASGRA', '', '3833 5291'),
(2, NULL, 'APAE', '', '3833 5231'),
(3, NULL, 'CAF TRANSPORTES', '', '3380 1751'),
(4, NULL, 'CAIXA', '', '3833 6150'),
(5, NULL, 'CAMARA MUNICIPAL', '', '3833 5202'),
(6, NULL, 'CATES', '', '3833 5195'),
(7, NULL, 'CORREIOS', '', '3833 5217'),
(8, NULL, 'ESCOLA DESEMBARGADOR', '', '3833 5150'),
(9, NULL, 'RÁDIO SÃO GONÇALO', '', '3833 5482 Whatsapp 9 7219 9633'),
(10, NULL, 'SERGAME', '', '3833 5883'),
(11, NULL, 'INTS', '', '9 9871 1688');

-- --------------------------------------------------------

--
-- Estrutura para tabela `frotas`
--

CREATE TABLE `frotas` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `frotas`
--

INSERT INTO `frotas` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1907'),
(2, NULL, '', 'TONI', '2093'),
(3, NULL, '', 'CINTHIA', '2367'),
(4, 'COLETIVOS', 'RECEPÇÃO', '', '1969'),
(5, 'COLETIVOS', '', 'GISELE', '2094'),
(6, 'COLETIVOS', '', 'JAQUELINE', '2337');

-- --------------------------------------------------------

--
-- Estrutura para tabela `meio_ambiente`
--

CREATE TABLE `meio_ambiente` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `meio_ambiente`
--

INSERT INTO `meio_ambiente` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, 'SECRETARIA', 'RECEPÇÃO', '', '1911'),
(2, 'SECRETARIA', 'ADMINISTRATIVO', '', '1912'),
(3, 'SECRETARIA', 'ADMINISTRATIVO', '', '2375'),
(4, 'SECRETARIA', 'ADMINISTRATIVO', '', '2380'),
(5, 'SECRETARIA', 'CORTE DE ÁRVORES', '', '2378'),
(6, 'SECRETARIA', 'LICENCIAMENTO AMBIENTAL', '', '2376'),
(7, 'ATERRO SANITÁRIO', 'RECEPÇÃO', '', '1914'),
(8, 'ATERRO SANITÁRIO', 'BALANÇA', '', '2270'),
(9, 'VIVEIRO', 'RECEPÇÃO', '', '1943'),
(10, 'PARQUE ECOLÓGICO', 'PETI', '', '1989'),
(11, 'PARQUE ECOLÓGICO', '', 'EUZÉBIO', '2053'),
(12, 'JARDINAGEM', 'RECEPÇÃO', '', '1864');

-- --------------------------------------------------------

--
-- Estrutura para tabela `obras`
--

CREATE TABLE `obras` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `obras`
--

INSERT INTO `obras` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1889'),
(2, NULL, '', 'JOHNNY', '2286');

-- --------------------------------------------------------

--
-- Estrutura para tabela `predio_prefeitura`
--

CREATE TABLE `predio_prefeitura` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `predio_prefeitura`
--

INSERT INTO `predio_prefeitura` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'ADMINISTRAÇÃO', 'MANUELA', '2004'),
(2, NULL, 'ADMINISTRAÇÃO/LICITAÇÃO', 'IRIS', '1921'),
(3, NULL, 'COMPRAS', 'HAUYTA/MARIA EDUARDA', '1801'),
(4, NULL, 'COMPRAS', 'GABRIELA', '2007'),
(5, NULL, 'COMPRAS', 'NORA', '1803'),
(6, NULL, 'COMUNICAÇÃO', 'FERNANDA', '1926'),
(7, NULL, 'COMUNICAÇÃO', 'GERAL', '1922'),
(8, NULL, 'CONTABILIDADE', 'SOLANGE', '2012'),
(9, NULL, 'CONTABILIDADE', 'FRANCIELE/JULIO', '2020'),
(10, NULL, 'CONTROLE INTERNO', 'ULISSES/ANDRE LUIZ/NAPOLIANA', '2019'),
(11, NULL, 'CONVÊNIOS', 'RAQUEL/YASMIN/LUIZ', '2014'),
(12, NULL, 'ENCARREGADA SERV. GERAIS', 'FLAVIANA', '2033'),
(13, NULL, 'GOVERNO', 'ANA BEATRIZ', '2005'),
(14, NULL, 'GOVERNO', 'ANANDA', '2006'),
(15, NULL, 'LICITAÇÃO', 'ELIANE/DIRCILENE', '2009'),
(16, NULL, 'LICITAÇÃO', 'NONATO', '2008'),
(17, NULL, 'LICITAÇÃO', 'JANETE', '1805'),
(18, NULL, 'LICITAÇÃO', 'NINHA', '2036'),
(19, NULL, 'LICITAÇÃO', 'FABIANE', '2010'),
(20, NULL, 'LICITAÇÃO', 'FERNANDA/IGOR', '2032'),
(21, NULL, 'OUVIDORIA', 'CINTHIA', '2271'),
(22, NULL, 'OUVIDORIA', 'GABI', '2200'),
(23, NULL, 'OUVIDORIA', 'JAQUELINE', '1951'),
(24, NULL, 'PLANEJAMENTO', 'SARAH', '1802'),
(25, NULL, 'PLANEJAMENTO', 'JESQUELINE', '2028'),
(26, NULL, 'JURIDICO', 'SABRINA', '2015'),
(27, NULL, 'PROCURADORIA', 'JEAN', '2017'),
(28, NULL, 'PROTOCOLO INTERNO', 'JULIANA/KAROL/ANA PAULA', '2035'),
(29, NULL, 'RECEPÇÃO 1º ANDAR', 'MIRELA', '1813'),
(30, NULL, 'RECEPÇÃO 2º ANDAR', '', '1820'),
(31, NULL, 'RECEPÇÃO 3º ANDAR', 'JEISIANE', '2002'),
(32, NULL, 'GESTÃO DE PESSOAS', 'HENDRIGO', '2039'),
(33, NULL, 'RH', 'ALINE/SILVANA/ISABELA', '1817'),
(34, NULL, 'RH', '', '1937'),
(35, NULL, 'RH', 'VALESCA', '2021'),
(36, NULL, 'RH', 'GERALDA/MARCIA', '2031'),
(37, NULL, 'SIAT', '', '1955'),
(38, NULL, 'TESOURARIA', 'ADRIANA/ISAMARA/FRANCIELE/MICHELE', '2013'),
(39, NULL, 'TRIBUTOS - FISCALIZAÇÃO', 'ALMIR', '2030'),
(40, NULL, 'TRIBUTOS', 'ELAINE', '2034'),
(41, NULL, 'TRIBUTOS', 'HISANARA', '2038'),
(42, NULL, 'TRIBUTOS', 'THAIS/JHENIFER/LUCAS', '2025'),
(43, NULL, 'TRIBUTOS', 'PATRICIA', '2040');

-- --------------------------------------------------------

--
-- Estrutura para tabela `saude`
--

CREATE TABLE `saude` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `saude`
--

INSERT INTO `saude` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, 'SECRETARIA', 'RECEPÇÃO', '', '1827'),
(2, 'SECRETARIA', 'CPD', '', '2159'),
(3, 'SECRETARIA', 'COMPRAS', '', '2041'),
(4, 'SECRETARIA', 'ADMINISTRATIVO', '', '2042'),
(5, 'SECRETARIA', 'ADMINISTRATIVO', '', '2124'),
(6, 'SECRETARIA', 'ADMINISTRATIVO', 'THACIANA', '2049'),
(7, 'SECRETARIA', 'ADMINISTRATIVO', 'RAFAELA', '2043'),
(8, 'SECRETARIA', 'ADMINISTRATIVO', '', '2046'),
(9, 'SECRETARIA', 'LICITAÇÃO', 'JULIANA', '2201'),
(10, 'SECRETARIA', 'COORDENÇÃO PSF', '', '2044'),
(11, 'SECRETARIA', 'EQUIPE APOIO PSF', '', '2045'),
(12, 'SECRETARIA', 'APOIO APS', '', '2160'),
(13, 'SECRETARIA', 'ALMOXARIFADO', '', '2081'),
(14, 'SECRETARIA', 'ALMOXARIFADO MATERIAL MÉDICO', '', '2069'),
(15, 'SECRETARIA', 'ALMOXARIFADO DE LIMPEZA', '', '2139'),
(16, 'PRONTO ATENDIMENTO (PA)', 'RECEPÇÃO', '', '1840/2063'),
(17, 'PRONTO ATENDIMENTO (PA)', 'COORDENAÇÃO PA', '', '1852'),
(18, 'PRONTO ATENDIMENTO (PA)', 'LABORATÓRIO RECEPÇÃO', '', '2075'),
(19, 'PRONTO ATENDIMENTO (PA)', 'COORDENAÇÃO ENFERMAGEM', '', '2077'),
(20, 'PRONTO ATENDIMENTO (PA)', 'COORDENADOR DOS MOTORISTAS', '', '2100'),
(21, 'PRONTO ATENDIMENTO (PA)', 'ASSISTÊNCIA SOCIAL', '', '2067'),
(22, 'PEIXE-VIVO', 'RECEPÇÃO', '', '1836'),
(23, 'PEIXE-VIVO', 'COORDENAÇÃO', '', '2104'),
(24, 'PEIXE-VIVO', 'ADMINITRATIVO', '', '2110'),
(25, 'PEIXE-VIVO', 'ESCAPE', '', '1983'),
(26, 'PSF\'s', 'PSF BOM SUCESSO RECEPÇÃO', '', '1906'),
(27, 'PSF\'s', 'PSF BORGES RECEPÇÃO', '', '1862'),
(28, 'PSF\'s', 'PSF FERNANDES RECEPÇÃO', '', '1861'),
(29, 'PSF\'s', 'PSF GUANABARA RECEPÇÃO', '', '1854'),
(30, 'PSF\'s', 'PSF GUANABARA SALA DOS AGENTES DE SAÚDE', '', '2196'),
(31, 'PSF\'s', 'PSF JURUBEBA', '', '1981'),
(32, 'PSF\'s', 'PSF PACAS RECEPÇÃO', '', '1853'),
(33, 'PSF\'s', 'PSF PEDRAS', '', '1858'),
(34, 'PSF\'s', 'PSF PONTE CORONEL 1 RECEPÇÃO', '', '1941'),
(35, 'PSF\'s', 'PSF RIBEIRÃO (PONTE CORONEL 2) RECEPÇÃO', '', '1942'),
(36, 'PSF\'s', 'PSF RECREIO RECEPÇÃO', '', '1850'),
(37, 'PSF\'s', 'PSF RECREIO VACINA', '', '2162'),
(38, 'PSF\'s', 'PSF SÃO JOSÉ RECEPÇÃO', '', '1847'),
(39, 'PSF\'s', 'PSF TIMIRIM', '', '1913'),
(40, 'PSF\'s', 'PSF UNA RECEPÇÃO', '', '1856'),
(41, 'PSF\'s', 'PSF UNA SALA DOS AGENTES DE SAÚDE', '', '2103'),
(42, 'PSF\'s', 'PSF URBANO-PATRIMÔNIO RECEPÇÃO', '', '1844'),
(43, 'PSF\'s', 'PSF URBANO SALA DOS AGENTES DE SAÚDE', '', '2192'),
(44, 'PSF\'s', 'PSF VARGEM DA LUA', '', '1883'),
(45, 'PSF\'s', 'PSF VARGEM ALEGRE RECEPÇÃO', '', '1845'),
(46, 'CES (CENTRO DE ESPECIALIZAÇÃO EM SAÚDE)', 'RECEPÇÃO', '', '1829'),
(47, 'CES (CENTRO DE ESPECIALIZAÇÃO EM SAÚDE)', 'COORDENAÇÃO', '', '2051'),
(48, 'CES (CENTRO DE ESPECIALIZAÇÃO EM SAÚDE)', 'SALA DE MARCAÇÃO', '', '2052'),
(49, 'CES (CENTRO DE ESPECIALIZAÇÃO EM SAÚDE)', 'PRONTUÁRIO', '', '2140'),
(50, 'TRANSPORTE SAÚDE', 'RECEPÇÃO', '', '1833'),
(51, 'TRANSPORTE SAÚDE', 'RECEPÇÃO', '', '1935'),
(52, 'TRANSPORTE SAÚDE', 'COORDENAÇÃO', '', '2121'),
(53, 'TFD', 'RECEPÇÃO', '', '1834'),
(54, 'TFD', 'MARCAÇÃO', '', '2083'),
(55, 'TFD', 'MARCAÇÃO', '', '2084'),
(56, 'TFD', 'COORDENAÇÃO LETÍCIA', '', '2085'),
(57, 'TFD', 'COORDENAÇÃO', '', '2313'),
(58, 'CENTRO REABILITAÇÃO EM SAÚDE', 'RECEPÇÃO', '', '1857'),
(59, 'CENTRO REABILITAÇÃO EM SAÚDE', 'GINECOLOGIA', '', '1843'),
(60, 'CENTRO REABILITAÇÃO EM SAÚDE', 'TERAPEUTA', '', '1817'),
(61, 'CENTRO ODONTOLOGICO', 'RECEPÇÃO', '', '1832'),
(62, 'CENTRO ODONTOLOGICO', 'COORDENAÇÃO', '', '2048'),
(63, 'FARMÁCIA MUNICIPAL', 'RECEPÇÃO', '', '1837'),
(64, 'FARMÁCIA ESPECIALIZADA', 'RECEPÇÃO', '', '1848'),
(65, 'VIGILÂNCIA SANITÁRIA', 'RECEPÇÃO', '', '1835'),
(66, 'VIGILÂNCIA SANITÁRIA', 'COORDENAÇÃO SANITÁRIA', '', '2097'),
(67, 'VIGILÂNCIA SANITÁRIA', 'COORDENAÇÃO EPIDEMIOLÓGICA', '', '2098'),
(68, 'VIGILÂNCIA SANITÁRIA', 'PCE', '', '2099'),
(69, 'VIGILÂNCIA SANITÁRIA', 'SALA DOS FISCAIS', '', '2130'),
(70, 'SERVIÇO SOCIAL', 'RECEPÇÃO', '', '1838'),
(71, 'CAMPS', 'RECEPÇÃO', '', '1984');

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_urbanos`
--

CREATE TABLE `servicos_urbanos` (
  `id` int NOT NULL,
  `sub_setor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `falar_com` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `servicos_urbanos`
--

INSERT INTO `servicos_urbanos` (`id`, `sub_setor`, `descricao`, `falar_com`, `ramal`) VALUES
(1, NULL, 'RECEPÇÃO', '', '1895'),
(2, NULL, 'ADMINISTRATIVO', '', '2137'),
(3, NULL, 'COLETA', 'RODRIGO', '2395'),
(4, 'LIMPEZA URBANA', 'RECEPÇÃO', '', '1896'),
(5, 'LIMPEZA URBANA', 'ADMINISTRATIVO', '', '2328'),
(6, 'ELÉTRICA', 'SETOR ELÉTRICA', '', '1892'),
(7, 'MANUTENÇÃO DE VIAS URBANAS', 'RECEPÇÃO', '', '1894'),
(8, 'MANUTENÇÃO DE VIAS URBANAS', 'ADMINISTRATIVO', '', '2322'),
(9, 'MANUTENÇÃO DE VIAS RURAIS', 'RECEPÇÃO', '', '1968'),
(10, 'MANUTENÇÃO DE VIAS RURAIS', 'ADMINISTRATIVO', '', '2090'),
(11, 'DAE', 'RECEPÇÃO', '', '1831'),
(12, 'DAE', 'RECEPÇÃO', '', '1966'),
(13, 'DAE', 'ADMINISTRATIVO', '', '2394'),
(14, 'DAE', 'COORDENAÇÃO', 'EDIO', '2096'),
(15, 'ETA', 'ETA', '', '1898'),
(16, 'SAAE', 'ADMINISTRATIVO', '', '2058'),
(17, 'VELÓRIO', 'RECEPÇÃO', '', '1904'),
(18, 'PÁTIO DE OBRAS', 'PÁTIO DE OBRAS - RECEPÇÃO', '', '1891'),
(19, 'PÁTIO DE OBRAS', 'PÁTIO DE OBRAS - ADMINISTRATIVO', '', '2319'),
(20, 'PONTO DE APOIO PONTE CORONEL', 'RECEPÇÃO', '', '1952'),
(21, 'PONTO DE APOIO VARGEM ALEGRE', 'RECEPÇÃO', '', '1979');

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
-- Índices de tabela `emergencia`
--
ALTER TABLE `emergencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `emergencia` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

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
-- Índices de tabela `frotas`
--
ALTER TABLE `frotas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `frotas` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

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
-- Índices de tabela `predio_prefeitura`
--
ALTER TABLE `predio_prefeitura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sub_setor` (`sub_setor`),
  ADD KEY `idx_descricao` (`descricao`);
ALTER TABLE `predio_prefeitura` ADD FULLTEXT KEY `ft_busca` (`descricao`,`falar_com`,`ramal`,`sub_setor`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `administracao`
--
ALTER TABLE `administracao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `agricultura`
--
ALTER TABLE `agricultura`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `ciencia_e_tecnologia`
--
ALTER TABLE `ciencia_e_tecnologia`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `cultura`
--
ALTER TABLE `cultura`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `desenvolvimento_economico`
--
ALTER TABLE `desenvolvimento_economico`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `educacao`
--
ALTER TABLE `educacao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `emergencia`
--
ALTER TABLE `emergencia`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `esporte`
--
ALTER TABLE `esporte`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `externos`
--
ALTER TABLE `externos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `frotas`
--
ALTER TABLE `frotas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `meio_ambiente`
--
ALTER TABLE `meio_ambiente`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `obras`
--
ALTER TABLE `obras`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `predio_prefeitura`
--
ALTER TABLE `predio_prefeitura`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de tabela `saude`
--
ALTER TABLE `saude`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de tabela `servicos_urbanos`
--
ALTER TABLE `servicos_urbanos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Índices recomendados para acelerar buscas por descricao/falar_com/ramal/sub_setor
-- OBS: Para maior eficácia com buscas por termo (LIKE '%termo%'), considere
-- migrar para FULLTEXT + MATCH ... AGAINST em modo natural language.
-- Este script cria FULLTEXT onde possível (MySQL 5.6+ InnoDB), e índices BTREE
-- de apoio para ordenações e filtros exatos.

-- Aplique em produção fora do horário de pico. Teste antes em homologação.

SET NAMES utf8mb4;

-- Função auxiliar: cria FULLTEXT se não existir (execução idempotente)
-- MySQL não tem CREATE INDEX IF NOT EXISTS para FULLTEXT; rode manualmente conforme necessário.

-- Lista de tabelas-alvo
-- Ajuste conforme seu banco: remova/adicione conforme necessário
-- acao_social, administracao, agricultura, ciencia_e_tecnologia, cultura,
-- desenvolvimento_economico, educacao, emergencia, esporte, externos, frotas,
-- meio_ambiente, obras, predio_prefeitura, saude, servicos_urbanos

-- Modelo: FULLTEXT(descricao, falar_com, ramal, sub_setor)

ALTER TABLE `acao_social` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `administracao` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `agricultura` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `ciencia_e_tecnologia` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `cultura` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `desenvolvimento_economico` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `educacao` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `emergencia` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `esporte` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `externos` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `frotas` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `meio_ambiente` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `obras` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `predio_prefeitura` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `saude` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);
ALTER TABLE `servicos_urbanos` ADD FULLTEXT `ft_busca` (`descricao`, `falar_com`, `ramal`, `sub_setor`);

-- Índices BTREE auxiliares (úteis para filtros exatos e ordenações por sub_setor, descricao)
ALTER TABLE `acao_social` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `administracao` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `agricultura` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `ciencia_e_tecnologia` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `cultura` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `desenvolvimento_economico` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `educacao` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `emergencia` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `esporte` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `externos` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `frotas` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `meio_ambiente` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `obras` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `predio_prefeitura` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `saude` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);
ALTER TABLE `servicos_urbanos` ADD INDEX `idx_sub_setor` (`sub_setor`), ADD INDEX `idx_descricao` (`descricao`);

-- Dica: após criar FULLTEXT, ajuste a aplicação para usar:
--   WHERE MATCH(descricao, falar_com, ramal, sub_setor) AGAINST (? IN NATURAL LANGUAGE MODE)
-- Isso substituirá os múltiplos LIKE com melhor performance e ranking de relevância.



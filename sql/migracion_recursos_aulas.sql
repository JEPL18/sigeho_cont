ALTER TABLE aulas ADD COLUMN recursos TEXT DEFAULT NULL;

UPDATE aulas a SET a.recursos = (SELECT GROUP_CONCAT(r.nombre ORDER BY r.nombre SEPARATOR ', ') FROM aula_recurso ar INNER JOIN recursos r ON ar.recurso_id = r.id WHERE ar.aula_id = a.id) WHERE EXISTS (SELECT 1 FROM aula_recurso ar2 WHERE ar2.aula_id = a.id);

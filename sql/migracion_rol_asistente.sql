-- migracion_rol_asistente.sql — SIGEHO-CONT (Bootstrap)
-- Agrega el rol 'asistente' al ENUM de usuarios. 'asistente' va AL FINAL
-- para no cambiar los índices internos de los valores existentes
-- (administrador=1, coordinador=2, profesor=3): los datos actuales quedan intactos.
-- Ejecutar UNA vez en phpMyAdmin (pestaña SQL) sobre la base de datos en uso.
ALTER TABLE `usuarios`
  MODIFY COLUMN `rol` ENUM('administrador','coordinador','profesor','asistente') NOT NULL;

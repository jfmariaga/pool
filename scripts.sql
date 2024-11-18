
-- Inserta la cuenta sin especificar `id` (deja que MySQL asigne un ID automáticamente)
INSERT INTO `cuentas` (`nombre`, `numero_de_cuenta`, `status`, `saldo`)
VALUES ('Crédito', NULL, 1, 0);

-- Luego, cambia el ID a 0
UPDATE `cuentas` SET `id` = 0 WHERE `nombre` = 'Crédito' AND `id` = LAST_INSERT_ID();

Exacto, el UPDATE atómico no afecta otros registros en absoluto. Solo toca la fila de `maquinas` y su `configbills` asociada vía trigger.

**Ahora el flujo de eliminación.**

Primero analicemos qué tablas tienen FK hacia `maquinas`:

```sql
config        → FK KEY_CONFIG    (ON DELETE CASCADE)
configbills   → FK KEY_BILLS     (ON DELETE CASCADE)
tickets       → FK fk_ticket_maquina (ON DELETE RESTRICT)
```

Las primeras dos son CASCADE, se borran solas. `tickets` es RESTRICT, bloquea el delete si la máquina tiene tickets asociados.

Las tablas de producción (`produccionchile`, `produccionperu`, `produccioncolombia`, `produccionaustralia`) y `contadores` **no tienen FK hacia `maquinas`**. Referencian por `idMaquina` sin constraint, por lo que no bloquean el delete pero quedan huérfanas.

**El flujo correcto no es DELETE directo. Es baja lógica:**

```
Estado operativo  → EsVisible=1, EsActivo=1
Estado inactivo   → EsVisible=0, EsActivo=1  (staging, recién creada)
Estado dado de baja → EsVisible=0, EsActivo=0  (baja lógica)
```

El SP de baja lógica es suficiente para el 99% de los casos:

```sql
DROP PROCEDURE IF EXISTS `usp_dar_baja_maquina`;
DELIMITER ;;
CREATE PROCEDURE `usp_dar_baja_maquina`(
    IN _idMaquina INT
)
BEGIN
    DECLARE v_tiene_tickets INT DEFAULT 0;

    SELECT COUNT(1) INTO v_tiene_tickets
    FROM tickets
    WHERE maquina_id = _idMaquina
      AND estado NOT IN ('Cerrado', 'Cancelado');

    IF v_tiene_tickets > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La máquina tiene tickets abiertos pendientes';
    END IF;

    UPDATE maquinas
    SET
        EsVisible = 0,
        EsActivo  = 0,
        idCentro  = 1,
        country   = NULL
    WHERE IdMaquina = _idMaquina
      AND EsActivo = 1;

    SELECT ROW_COUNT() AS filas_afectadas;
END;;
DELIMITER ;
```

Resetear `idCentro = 1` y `country = NULL` es crítico porque desconecta la máquina del trigger `insertProd`. Si la máquina física sigue reportando (firmware activo), los contadores se insertan pero ninguna rama de producción se activa. Producción queda protegida.

**El flujo completo de ciclo de vida queda:**

```
usp_crear_maquina_inactiva
    EsActivo=1, EsVisible=0, idCentro=1, country=NULL
    trigger INSERT_CONFIG → config + configbills en ceros

usp_activar_maquina
    EsActivo=1, EsVisible=1, idCentro=X, country=X
    trigger UPDAT_CONFIG → corrige configbills si country=2

usp_dar_baja_maquina
    EsActivo=0, EsVisible=0, idCentro=1, country=NULL
    producción histórica intacta, firmware ignorado
```

El DELETE físico de `maquinas` solo tendría sentido para máquinas creadas por error en staging (EsActivo=1, EsVisible=0, sin producción histórica). Para ese caso el SP sería:

```sql
DROP PROCEDURE IF EXISTS `usp_eliminar_maquina_staging`;
DELIMITER ;;
CREATE PROCEDURE `usp_eliminar_maquina_staging`(
    IN _idMaquina INT
)
BEGIN
    DECLARE v_tiene_produccion INT DEFAULT 0;
    DECLARE v_es_staging       INT DEFAULT 0;

    SELECT COUNT(1) INTO v_es_staging
    FROM maquinas
    WHERE IdMaquina = _idMaquina
      AND EsVisible = 0
      AND EsActivo  = 1
      AND idCentro  = 1
      AND country   IS NULL;

    IF v_es_staging = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Solo se pueden eliminar máquinas en staging';
    END IF;

    SELECT
        (SELECT COUNT(1) FROM produccionperu     WHERE idMaquina = _idMaquina) +
        (SELECT COUNT(1) FROM produccionchile    WHERE idMaquina = _idMaquina) +
        (SELECT COUNT(1) FROM produccioncolombia WHERE idMaquina = _idMaquina) +
        (SELECT COUNT(1) FROM produccionaustralia WHERE idMaquina = _idMaquina)
    INTO v_tiene_produccion;

    IF v_tiene_produccion > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La máquina tiene producción registrada, use baja lógica';
    END IF;

    DELETE FROM maquinas WHERE IdMaquina = _idMaquina;

    SELECT ROW_COUNT() AS filas_afectadas;
END;;
DELIMITER ;
```

El CASCADE en `config` y `configbills` se dispara automáticamente con el DELETE. Los triggers `DLT_CONF` y `DLT_CONF_BILL` intentarán bloquear pero como el DELETE en `maquinas` ocurre primero, la fila ya no existe cuando el trigger de config evalúa el COUNT, por lo que pasa sin problema. Eso es un comportamiento esperado del CASCADE en MariaDB.
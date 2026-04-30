### el flujo completo es:

```
Crear máquina (staging)  →  Activar máquina  →  Máquina física reporta  →  Producción visible
```

La producción sale del centro asignado. La tabla donde cae depende del `country` que envía el firmware, que debe coincidir con el país del centro. Si asignas `idCentro` de Chile pero el firmware envía `country=3` (Peru), el trigger `insertProd` genera producción en `ProduccionPeru` pero el centro es Chile — inconsistencia. Por eso el `country` en `maquinas` y el `country` que envía el firmware deben ser el mismo.

Aquí los SP finales listos para ejecutar:

```sql
DROP PROCEDURE IF EXISTS `usp_crear_maquina_inactiva`;
DELIMITER ;;
CREATE PROCEDURE `usp_crear_maquina_inactiva`(
    IN _serie  VARCHAR(30),
    IN _modelo VARCHAR(50)
)
BEGIN
    IF EXISTS (SELECT 1 FROM maquinas WHERE CodigoMaquina = _serie) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La serie ya existe';
    END IF;

    INSERT INTO maquinas (
        CodigoMaquina,
        Modelo,
        EsVisible,
        EsActivo,
        idCentro,
        country,
        onOff,
        uSRV
    ) VALUES (
        _serie,
        _modelo,
        0,
        1,
        1,
        NULL,
        1,
        '0x0007c8'
    );

    SELECT
        IdMaquina,
        CodigoMaquina,
        Modelo,
        EsVisible,
        EsActivo,
        idCentro,
        country
    FROM maquinas
    WHERE CodigoMaquina = _serie;
END;;
DELIMITER ;
```

```sql
DROP PROCEDURE IF EXISTS `usp_activar_maquina`;
DELIMITER ;;
CREATE PROCEDURE `usp_activar_maquina`(
    IN _idMaquina INT,
    IN _idCentro  INT,
    IN _country   INT
)
BEGIN
    DECLARE v_existe_centro  INT DEFAULT 0;
    DECLARE v_es_staging     INT DEFAULT 0;
    DECLARE v_pais_centro    VARCHAR(20) DEFAULT '';

    SELECT COUNT(1) INTO v_existe_centro
    FROM centros
    WHERE IdCentro = _idCentro AND EsActivo = 1;

    IF v_existe_centro = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Centro no existe o está inactivo';
    END IF;

    SELECT COUNT(1) INTO v_es_staging
    FROM maquinas
    WHERE IdMaquina = _idMaquina
      AND EsVisible = 0
      AND EsActivo  = 1
      AND country   IS NULL;

    IF v_es_staging = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La máquina no está en staging o ya fue activada';
    END IF;

    SELECT
        CASE
            WHEN EsChile     = 1 THEN 'chile'
            WHEN EsColombia  = 1 THEN 'colombia'
            WHEN EsAustralia = 1 THEN 'australia'
            ELSE 'peru'
        END
    INTO v_pais_centro
    FROM centros
    WHERE IdCentro = _idCentro;

    IF (v_pais_centro = 'chile'     AND _country != 1) OR
       (v_pais_centro = 'colombia'  AND _country != 2) OR
       (v_pais_centro = 'australia' AND _country != 7) OR
       (v_pais_centro = 'peru'      AND _country NOT IN (3,4,5,6,9,10,11)) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El country no corresponde al país del centro asignado';
    END IF;

    UPDATE maquinas
    SET
        idCentro  = _idCentro,
        country   = _country,
        EsVisible = 1
    WHERE IdMaquina = _idMaquina;

    SELECT
        m.IdMaquina,
        m.CodigoMaquina,
        m.Modelo,
        m.EsVisible,
        m.country,
        c.NombreCentro,
        c.EsChile,
        c.EsColombia,
        c.EsAustralia
    FROM maquinas m
    INNER JOIN centros c ON c.IdCentro = m.idCentro
    WHERE m.IdMaquina = _idMaquina;
END;;
DELIMITER ;
```

```sql
DROP TRIGGER IF EXISTS `UPDAT_CONFIG`;
DELIMITER ;;
CREATE TRIGGER `UPDAT_CONFIG` AFTER UPDATE ON `maquinas` FOR EACH ROW BEGIN
    IF OLD.country IS NULL AND NEW.country = 2 THEN
        UPDATE configbills
        SET
            PulXJugBill1  = 1000,
            PulXJugBill2  = 2000,
            PulXJugBill5  = 5000,
            PulXJugBill10 = 12000,
            PulXJugBill20 = 28000
        WHERE IdMaquina = NEW.IdMaquina;
    END IF;
END;;
DELIMITER ;
```

La validación de coherencia `country vs centro` en `usp_activar_maquina` es la pieza más importante. Sin ella puedes tener una máquina con `idCentro` de Chile pero `country=2`, y el trigger `insertProd` genera en `ProduccionColombia` mientras los SP de reportes Chile buscan por `c.EsChile=1` — la producción existe pero nunca aparece en ningún reporte.

Una vez ejecutado `usp_activar_maquina`, el firmware envía su primer reporte → `INSERTAR_CONTADOR_RED` inserta en `contadores` → `insertProd` dispara → producción cae en la tabla correcta → los SP de reportes la muestran porque `EsVisible=1` y el centro tiene el flag correcto.
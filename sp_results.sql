

DROP PROCEDURE IF EXISTS rpt_centro_produccion_peru;
DELIMITER $$
CREATE PROCEDURE rpt_centro_produccion_peru(IN p_fecha DATE)
BEGIN
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_prom_latest (
        idCentro INT PRIMARY KEY,
        promV    DECIMAL(10,2)
    );
    TRUNCATE TABLE tmp_prom_latest;

    INSERT INTO tmp_prom_latest (idCentro, promV)
    SELECT pr.idCentro, pr.promV
    FROM promedios pr
    INNER JOIN (
        SELECT idCentro, MAX(timestamp) AS ts
        FROM promedios
        GROUP BY idCentro
    ) latest ON latest.idCentro = pr.idCentro AND latest.ts = pr.timestamp;

    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_produccion_dia (
        idMaquina       INT,
        idCentro        INT,
        hora            TINYINT,
        ProduccionFinal DECIMAL(12,2),
        INDEX idx_maq (idMaquina),
        INDEX idx_centro (idCentro)
    );
    TRUNCATE TABLE tmp_produccion_dia;

    INSERT INTO tmp_produccion_dia (idMaquina, idCentro, hora, ProduccionFinal)
    SELECT p.idMaquina, p.idCentro, HOUR(p.FechaProduccion), p.ProduccionFinal
    FROM produccionperu p
    INNER JOIN maquinas m ON m.IdMaquina = p.idMaquina
    WHERE p.FechaProduccion >= p_fecha
      AND p.FechaProduccion <  p_fecha + INTERVAL 1 DAY
      AND m.EsVisible = 1;

    SELECT
        t.idMaquina,
        m.Modelo                                                            AS modelo,
        c.NombreCentro                                                      AS centro,
        m.CodigoMaquina                                                     AS serie,
        SUM(CASE WHEN t.hora <= 8  THEN t.ProduccionFinal ELSE 0 END)      AS h8,
        SUM(CASE WHEN t.hora = 9   THEN t.ProduccionFinal ELSE 0 END)      AS h9,
        SUM(CASE WHEN t.hora = 10  THEN t.ProduccionFinal ELSE 0 END)      AS h10,
        SUM(CASE WHEN t.hora = 11  THEN t.ProduccionFinal ELSE 0 END)      AS h11,
        SUM(CASE WHEN t.hora = 12  THEN t.ProduccionFinal ELSE 0 END)      AS h12,
        SUM(CASE WHEN t.hora = 13  THEN t.ProduccionFinal ELSE 0 END)      AS h13,
        SUM(CASE WHEN t.hora = 14  THEN t.ProduccionFinal ELSE 0 END)      AS h14,
        SUM(CASE WHEN t.hora = 15  THEN t.ProduccionFinal ELSE 0 END)      AS h15,
        SUM(CASE WHEN t.hora = 16  THEN t.ProduccionFinal ELSE 0 END)      AS h16,
        SUM(CASE WHEN t.hora = 17  THEN t.ProduccionFinal ELSE 0 END)      AS h17,
        SUM(CASE WHEN t.hora = 18  THEN t.ProduccionFinal ELSE 0 END)      AS h18,
        SUM(CASE WHEN t.hora = 19  THEN t.ProduccionFinal ELSE 0 END)      AS h19,
        SUM(CASE WHEN t.hora = 20  THEN t.ProduccionFinal ELSE 0 END)      AS h20,
        SUM(CASE WHEN t.hora = 21  THEN t.ProduccionFinal ELSE 0 END)      AS h21,
        SUM(CASE WHEN t.hora = 22  THEN t.ProduccionFinal ELSE 0 END)      AS h22,
        SUM(CASE WHEN t.hora = 23  THEN t.ProduccionFinal ELSE 0 END)      AS h23,
        SUM(t.ProduccionFinal)                                              AS total,
        ROUND(MAX(COALESCE(pl.promV, 0)), 2)                               AS promedioCentro
    FROM tmp_produccion_dia t
    INNER JOIN maquinas         m  ON m.IdMaquina  = t.idMaquina
    INNER JOIN centros          c  ON c.IdCentro   = t.idCentro
    LEFT  JOIN tmp_prom_latest  pl ON pl.idCentro  = t.idCentro
    GROUP BY t.idMaquina, m.Modelo, c.NombreCentro, m.CodigoMaquina
    ORDER BY total DESC;

    DROP TEMPORARY TABLE IF EXISTS tmp_produccion_dia;
    DROP TEMPORARY TABLE IF EXISTS tmp_prom_latest;
END$$
DELIMITER ;

CALL rpt_centro_produccion_peru('2026-02-01'); -- 3 segundos la sp 


DROP PROCEDURE IF EXISTS rpt_centro_produccion_chile;
DELIMITER $$
CREATE PROCEDURE rpt_centro_produccion_chile(IN p_fecha DATE)
BEGIN
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_prom_latest (
        idCentro INT PRIMARY KEY,
        promT    DECIMAL(10,2)
    );
    TRUNCATE TABLE tmp_prom_latest;

    INSERT INTO tmp_prom_latest (idCentro, promT)
    SELECT pr.idCentro, pr.promT
    FROM promedios pr
    INNER JOIN (
        SELECT idCentro, MAX(timestamp) AS ts
        FROM promedios
        GROUP BY idCentro
    ) latest ON latest.idCentro = pr.idCentro AND latest.ts = pr.timestamp;

    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_produccion_dia (
        idMaquina       INT,
        idCentro        INT,
        hora            TINYINT,
        ProduccionFinal DECIMAL(12,2),
        INDEX idx_maq (idMaquina),
        INDEX idx_centro (idCentro)
    );
    TRUNCATE TABLE tmp_produccion_dia;

    INSERT INTO tmp_produccion_dia (idMaquina, idCentro, hora, ProduccionFinal)
    SELECT p.idMaquina, p.idCentro, HOUR(p.FechaProduccion), p.ProduccionFinal
    FROM produccionchile p
    INNER JOIN maquinas m ON m.IdMaquina = p.idMaquina
    INNER JOIN centros  c ON c.IdCentro  = p.idCentro
    WHERE p.FechaProduccion >= p_fecha
      AND p.FechaProduccion <  p_fecha + INTERVAL 1 DAY
      AND m.EsVisible = 1
      AND c.IdCentro != 46;

    SELECT
        t.idMaquina,
        m.Modelo                                                            AS modelo,
        c.NombreCentro                                                      AS centro,
        m.CodigoMaquina                                                     AS serie,
        SUM(CASE WHEN t.hora <= 8  THEN t.ProduccionFinal ELSE 0 END)      AS h8,
        SUM(CASE WHEN t.hora = 9   THEN t.ProduccionFinal ELSE 0 END)      AS h9,
        SUM(CASE WHEN t.hora = 10  THEN t.ProduccionFinal ELSE 0 END)      AS h10,
        SUM(CASE WHEN t.hora = 11  THEN t.ProduccionFinal ELSE 0 END)      AS h11,
        SUM(CASE WHEN t.hora = 12  THEN t.ProduccionFinal ELSE 0 END)      AS h12,
        SUM(CASE WHEN t.hora = 13  THEN t.ProduccionFinal ELSE 0 END)      AS h13,
        SUM(CASE WHEN t.hora = 14  THEN t.ProduccionFinal ELSE 0 END)      AS h14,
        SUM(CASE WHEN t.hora = 15  THEN t.ProduccionFinal ELSE 0 END)      AS h15,
        SUM(CASE WHEN t.hora = 16  THEN t.ProduccionFinal ELSE 0 END)      AS h16,
        SUM(CASE WHEN t.hora = 17  THEN t.ProduccionFinal ELSE 0 END)      AS h17,
        SUM(CASE WHEN t.hora = 18  THEN t.ProduccionFinal ELSE 0 END)      AS h18,
        SUM(CASE WHEN t.hora = 19  THEN t.ProduccionFinal ELSE 0 END)      AS h19,
        SUM(CASE WHEN t.hora = 20  THEN t.ProduccionFinal ELSE 0 END)      AS h20,
        SUM(CASE WHEN t.hora = 21  THEN t.ProduccionFinal ELSE 0 END)      AS h21,
        SUM(CASE WHEN t.hora = 22  THEN t.ProduccionFinal ELSE 0 END)      AS h22,
        SUM(CASE WHEN t.hora = 23  THEN t.ProduccionFinal ELSE 0 END)      AS h23,
        SUM(t.ProduccionFinal)                                              AS total,
        ROUND(MAX(COALESCE(pl.promT, 0)), 2)                               AS promedioCentro
    FROM tmp_produccion_dia t
    INNER JOIN maquinas         m  ON m.IdMaquina = t.idMaquina
    INNER JOIN centros          c  ON c.IdCentro  = t.idCentro
    LEFT  JOIN tmp_prom_latest  pl ON pl.idCentro = t.idCentro
    GROUP BY t.idMaquina, m.Modelo, c.NombreCentro, m.CodigoMaquina
    ORDER BY total DESC;

    DROP TEMPORARY TABLE IF EXISTS tmp_produccion_dia;
    DROP TEMPORARY TABLE IF EXISTS tmp_prom_latest;
END$$
DELIMITER ;
CALL rpt_centro_produccion_chile('2026-02-01'); -- 3 segundos la sp 

DROP PROCEDURE IF EXISTS rpt_centro_produccion_colombia;
DELIMITER $$
CREATE PROCEDURE rpt_centro_produccion_colombia(IN p_fecha DATE)
BEGIN
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_prom_latest (
        idCentro INT PRIMARY KEY,
        promT    DECIMAL(10,2)
    );
    TRUNCATE TABLE tmp_prom_latest;

    INSERT INTO tmp_prom_latest (idCentro, promT)
    SELECT pr.idCentro, pr.promT
    FROM promedios pr
    INNER JOIN (
        SELECT idCentro, MAX(timestamp) AS ts
        FROM promedios
        GROUP BY idCentro
    ) latest ON latest.idCentro = pr.idCentro AND latest.ts = pr.timestamp;

    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_produccion_dia (
        idMaquina       INT,
        idCentro        INT,
        hora            TINYINT,
        ProduccionFinal DECIMAL(12,2),
        INDEX idx_maq (idMaquina),
        INDEX idx_centro (idCentro)
    );
    TRUNCATE TABLE tmp_produccion_dia;

    INSERT INTO tmp_produccion_dia (idMaquina, idCentro, hora, ProduccionFinal)
    SELECT p.idMaquina, p.idCentro, HOUR(p.FechaProduccion), p.ProduccionFinal
    FROM produccioncolombia p
    INNER JOIN maquinas m ON m.IdMaquina = p.idMaquina
    WHERE p.FechaProduccion >= p_fecha
      AND p.FechaProduccion <  p_fecha + INTERVAL 1 DAY
      AND m.EsVisible = 1;

    SELECT
        t.idMaquina,
        m.Modelo                                                            AS modelo,
        c.NombreCentro                                                      AS centro,
        m.CodigoMaquina                                                     AS serie,
        SUM(CASE WHEN t.hora <= 8  THEN t.ProduccionFinal ELSE 0 END)      AS h8,
        SUM(CASE WHEN t.hora = 9   THEN t.ProduccionFinal ELSE 0 END)      AS h9,
        SUM(CASE WHEN t.hora = 10  THEN t.ProduccionFinal ELSE 0 END)      AS h10,
        SUM(CASE WHEN t.hora = 11  THEN t.ProduccionFinal ELSE 0 END)      AS h11,
        SUM(CASE WHEN t.hora = 12  THEN t.ProduccionFinal ELSE 0 END)      AS h12,
        SUM(CASE WHEN t.hora = 13  THEN t.ProduccionFinal ELSE 0 END)      AS h13,
        SUM(CASE WHEN t.hora = 14  THEN t.ProduccionFinal ELSE 0 END)      AS h14,
        SUM(CASE WHEN t.hora = 15  THEN t.ProduccionFinal ELSE 0 END)      AS h15,
        SUM(CASE WHEN t.hora = 16  THEN t.ProduccionFinal ELSE 0 END)      AS h16,
        SUM(CASE WHEN t.hora = 17  THEN t.ProduccionFinal ELSE 0 END)      AS h17,
        SUM(CASE WHEN t.hora = 18  THEN t.ProduccionFinal ELSE 0 END)      AS h18,
        SUM(CASE WHEN t.hora = 19  THEN t.ProduccionFinal ELSE 0 END)      AS h19,
        SUM(CASE WHEN t.hora = 20  THEN t.ProduccionFinal ELSE 0 END)      AS h20,
        SUM(CASE WHEN t.hora = 21  THEN t.ProduccionFinal ELSE 0 END)      AS h21,
        SUM(CASE WHEN t.hora = 22  THEN t.ProduccionFinal ELSE 0 END)      AS h22,
        SUM(CASE WHEN t.hora = 23  THEN t.ProduccionFinal ELSE 0 END)      AS h23,
        SUM(t.ProduccionFinal)                                              AS total,
        ROUND(MAX(COALESCE(pl.promT, 0)), 2)                               AS promedioCentro
    FROM tmp_produccion_dia t
    INNER JOIN maquinas         m  ON m.IdMaquina = t.idMaquina
    INNER JOIN centros          c  ON c.IdCentro  = t.idCentro
    LEFT  JOIN tmp_prom_latest  pl ON pl.idCentro = t.idCentro
    GROUP BY t.idMaquina, m.Modelo, c.NombreCentro, m.CodigoMaquina
    ORDER BY total DESC;

    DROP TEMPORARY TABLE IF EXISTS tmp_produccion_dia;
    DROP TEMPORARY TABLE IF EXISTS tmp_prom_latest;
END$$
DELIMITER ;


CALL rpt_centro_produccion_colombia('2026-02-01'); -- 3 segundos la sp 


DROP PROCEDURE IF EXISTS rpt_centro_produccion_australia;
DELIMITER $$
CREATE PROCEDURE rpt_centro_produccion_australia(IN p_fecha DATE)
BEGIN
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_produccion_dia (
        idMaquina       INT,
        idCentro        INT,
        hora            TINYINT,
        ProduccionFinal DECIMAL(12,2),
        INDEX idx_maq (idMaquina),
        INDEX idx_centro (idCentro)
    );
    TRUNCATE TABLE tmp_produccion_dia;

    INSERT INTO tmp_produccion_dia (idMaquina, idCentro, hora, ProduccionFinal)
    SELECT p.idMaquina, p.idCentro, HOUR(p.FechaProduccion), p.ProduccionFinal
    FROM produccionaustralia p
    INNER JOIN maquinas m ON m.IdMaquina = p.idMaquina
    WHERE p.FechaProduccion >= p_fecha
      AND p.FechaProduccion <  p_fecha + INTERVAL 1 DAY
      AND m.EsVisible = 1;

    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_prom_maquina (
        idMaquina    INT PRIMARY KEY,
        promedioCentro DECIMAL(12,2)
    );
    TRUNCATE TABLE tmp_prom_maquina;

    INSERT INTO tmp_prom_maquina (idMaquina, promedioCentro)
    SELECT
        t.idMaquina,
        ROUND(
            COALESCE(
                SUM(ph.ProduccionFinal) / NULLIF(COUNT(DISTINCT DATE(ph.FechaProduccion)), 0)
            , 0)
        , 2)
    FROM (SELECT DISTINCT idMaquina FROM tmp_produccion_dia) t
    LEFT JOIN produccionaustralia ph ON ph.idMaquina = t.idMaquina
    INNER JOIN maquinas mh ON mh.IdMaquina = ph.idMaquina
    WHERE ph.FechaProduccion >= DATE_SUB(p_fecha, INTERVAL 4 WEEK)
      AND ph.FechaProduccion <  p_fecha
      AND DAYOFWEEK(ph.FechaProduccion) = DAYOFWEEK(p_fecha)
      AND ph.ProduccionFinal > 0
      AND mh.EsVisible = 1
    GROUP BY t.idMaquina;

    SELECT
        t.idMaquina,
        m.Modelo                                                            AS modelo,
        c.NombreCentro                                                      AS centro,
        m.CodigoMaquina                                                     AS serie,
        SUM(CASE WHEN t.hora <= 8  THEN t.ProduccionFinal ELSE 0 END)      AS h8,
        SUM(CASE WHEN t.hora = 9   THEN t.ProduccionFinal ELSE 0 END)      AS h9,
        SUM(CASE WHEN t.hora = 10  THEN t.ProduccionFinal ELSE 0 END)      AS h10,
        SUM(CASE WHEN t.hora = 11  THEN t.ProduccionFinal ELSE 0 END)      AS h11,
        SUM(CASE WHEN t.hora = 12  THEN t.ProduccionFinal ELSE 0 END)      AS h12,
        SUM(CASE WHEN t.hora = 13  THEN t.ProduccionFinal ELSE 0 END)      AS h13,
        SUM(CASE WHEN t.hora = 14  THEN t.ProduccionFinal ELSE 0 END)      AS h14,
        SUM(CASE WHEN t.hora = 15  THEN t.ProduccionFinal ELSE 0 END)      AS h15,
        SUM(CASE WHEN t.hora = 16  THEN t.ProduccionFinal ELSE 0 END)      AS h16,
        SUM(CASE WHEN t.hora = 17  THEN t.ProduccionFinal ELSE 0 END)      AS h17,
        SUM(CASE WHEN t.hora = 18  THEN t.ProduccionFinal ELSE 0 END)      AS h18,
        SUM(CASE WHEN t.hora = 19  THEN t.ProduccionFinal ELSE 0 END)      AS h19,
        SUM(CASE WHEN t.hora = 20  THEN t.ProduccionFinal ELSE 0 END)      AS h20,
        SUM(CASE WHEN t.hora = 21  THEN t.ProduccionFinal ELSE 0 END)      AS h21,
        SUM(CASE WHEN t.hora = 22  THEN t.ProduccionFinal ELSE 0 END)      AS h22,
        SUM(CASE WHEN t.hora = 23  THEN t.ProduccionFinal ELSE 0 END)      AS h23,
        SUM(t.ProduccionFinal)                                              AS total,
        COALESCE(pm.promedioCentro, 0)                                      AS promedioCentro
    FROM tmp_produccion_dia t
    INNER JOIN maquinas         m  ON m.IdMaquina = t.idMaquina
    INNER JOIN centros          c  ON c.IdCentro  = t.idCentro
    LEFT  JOIN tmp_prom_maquina pm ON pm.idMaquina = t.idMaquina
    GROUP BY t.idMaquina, m.Modelo, c.NombreCentro, m.CodigoMaquina, pm.promedioCentro
    ORDER BY total DESC;

    DROP TEMPORARY TABLE IF EXISTS tmp_produccion_dia;
    DROP TEMPORARY TABLE IF EXISTS tmp_prom_maquina;
END$$
DELIMITER ;


CALL rpt_centro_produccion_australia('2026-02-01'); -- 3 segundos la sp 






DROP PROCEDURE IF EXISTS rpt_tareo_centros_latam;
DROP PROCEDURE IF EXISTS rpt_tareo_centros_latam;
DELIMITER $$
CREATE PROCEDURE rpt_tareo_centros_latam(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin    DATE,
    IN p_pais         VARCHAR(20)
)
BEGIN
    DECLARE v_tabla VARCHAR(40);
    SET v_tabla = CASE p_pais
        WHEN 'chile'     THEN 'produccionchile'
        WHEN 'colombia'  THEN 'produccioncolombia'
        WHEN 'australia' THEN 'produccionaustralia'
        ELSE                  'produccionperu'
    END;

    DROP TEMPORARY TABLE IF EXISTS tmp_tareo_produccion_rango;
    CREATE TEMPORARY TABLE tmp_tareo_produccion_rango (
        idMaquina       INT,
        idCentro        INT,
        ProduccionFinal DECIMAL(12,2),
        hora            TINYINT,
        dia             DATE,
        dow             TINYINT,
        INDEX idx_maq_dia_hora (idMaquina, dia, hora),
        INDEX idx_centro       (idCentro)
    );

    SET @sql = CONCAT('
        INSERT INTO tmp_tareo_produccion_rango
               (idMaquina, idCentro, ProduccionFinal, hora, dia, dow)
        SELECT
            p.idMaquina,
            p.idCentro,
            p.ProduccionFinal,
            HOUR(p.FechaProduccion),
            DATE(p.FechaProduccion),
            DAYOFWEEK(p.FechaProduccion)
        FROM ', v_tabla, ' p
        INNER JOIN maquinas m ON m.IdMaquina = p.idMaquina AND m.EsVisible = 1
        WHERE p.FechaProduccion >= ?
          AND p.FechaProduccion <  ? + INTERVAL 1 DAY
    ');
    PREPARE stmt FROM @sql;
    SET @fi = p_fecha_inicio;
    SET @ff = p_fecha_fin;
    EXECUTE stmt USING @fi, @ff;
    DEALLOCATE PREPARE stmt;

    DROP TEMPORARY TABLE IF EXISTS tmp_tareo_horas_turno;
    CREATE TEMPORARY TABLE tmp_tareo_horas_turno (
        idMaquina             INT PRIMARY KEY,
        horas_con_produccion  SMALLINT,
        horas_sin_transmitir  SMALLINT
    );

    INSERT INTO tmp_tareo_horas_turno (idMaquina, horas_con_produccion, horas_sin_transmitir)
    SELECT
        idMaquina,
        COUNT(DISTINCT CASE WHEN max_prod > 0 THEN (dia_int * 100 + hora) END),
        (16 * COUNT(DISTINCT dia)) -
        COUNT(DISTINCT CASE WHEN max_prod > 0 THEN (dia_int * 100 + hora) END)
    FROM (
        SELECT
            idMaquina,
            dia,
            TO_DAYS(dia)            AS dia_int,
            hora,
            MAX(ProduccionFinal)    AS max_prod
        FROM tmp_tareo_produccion_rango
        WHERE hora BETWEEN 8 AND 23
        GROUP BY idMaquina, dia, hora
    ) horas_dedup
    GROUP BY idMaquina;

    DROP TEMPORARY TABLE IF EXISTS tmp_tareo_promedio_historico;
    CREATE TEMPORARY TABLE tmp_tareo_promedio_historico (
        idMaquina      INT PRIMARY KEY,
        promedioCentro DECIMAL(12,2)
    );

    SET @sql = CONCAT('
        INSERT INTO tmp_tareo_promedio_historico (idMaquina, promedioCentro)
        SELECT
            r.idMaquina,
            ROUND(COALESCE(AVG(dias.diario), 0), 2)
        FROM (SELECT DISTINCT idMaquina, dow FROM tmp_tareo_produccion_rango) r
        LEFT JOIN (
            SELECT
                ph.idMaquina,
                DATE(ph.FechaProduccion)    AS dia,
                DAYOFWEEK(ph.FechaProduccion) AS dow_hist,
                SUM(ph.ProduccionFinal)     AS diario
            FROM ', v_tabla, ' ph
            WHERE ph.FechaProduccion >= DATE_SUB(?, INTERVAL 4 WEEK)
              AND ph.FechaProduccion  <  ?
              AND ph.ProduccionFinal  >  0
            GROUP BY ph.idMaquina, DATE(ph.FechaProduccion)
        ) dias ON dias.idMaquina = r.idMaquina
              AND dias.dow_hist   = r.dow
        GROUP BY r.idMaquina
    ');
    PREPARE stmt FROM @sql;
    EXECUTE stmt USING @fi, @fi;
    DEALLOCATE PREPARE stmt;

    SELECT
        m.IdMaquina,
        m.Modelo,
        c.NombreCentro,
        m.CodigoMaquina                             AS Serie,
        SUM(t.ProduccionFinal)                      AS total,
        COUNT(DISTINCT t.dia)                       AS dias_con_datos,
        ROUND(SUM(t.ProduccionFinal)
            / NULLIF(COUNT(DISTINCT t.dia), 0), 2)  AS total_promedio_diario,
        COALESCE(pr.promedioCentro, 0)              AS promedioCentro,
        COALESCE(h.horas_con_produccion, 0)         AS horas_con_produccion,
        COALESCE(h.horas_sin_transmitir, 0)         AS horas_sin_transmitir
    FROM tmp_tareo_produccion_rango t
    INNER JOIN maquinas                     m  ON m.IdMaquina = t.idMaquina
    INNER JOIN centros                      c  ON c.IdCentro  = t.idCentro
    LEFT  JOIN tmp_tareo_promedio_historico pr ON pr.idMaquina = t.idMaquina
    LEFT  JOIN tmp_tareo_horas_turno        h  ON h.idMaquina  = t.idMaquina
    GROUP BY
        m.IdMaquina, m.Modelo, c.NombreCentro, m.CodigoMaquina,
        pr.promedioCentro, h.horas_con_produccion, h.horas_sin_transmitir
    ORDER BY total DESC;

    DROP TEMPORARY TABLE IF EXISTS tmp_tareo_produccion_rango;
    DROP TEMPORARY TABLE IF EXISTS tmp_tareo_horas_turno;
    DROP TEMPORARY TABLE IF EXISTS tmp_tareo_promedio_historico;
END$$
DELIMITER ;


CALL rpt_tareo_centros_latam('2026-02-01','2026-02-01','chile'); -- 27 segundos la sp 
CALL rpt_tareo_centros_latam('2026-02-01','2026-02-01','peru'); -- 27 segundos la sp 
CALL rpt_tareo_centros_latam('2026-02-01','2026-02-01','chile'); -- 27 segundos la sp 
CALL rpt_tareo_centros_latam('2026-02-01','2026-02-01','colombia'); -- 27 segundos la sp 
CALL rpt_tareo_centros_latam('2026-02-01','2026-02-01','australia'); -- 27 segundos la sp 

-- ACTUALIZACION DE SP PERU
CREATE DEFINER=`root`@`%` PROCEDURE `rpt_centro_produccion_peru`(IN p_fecha DATE)
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
        ROUND(MAX(COALESCE(pl.promV, 0)), 2)                               AS promedioCentro,
        MAX(CASE WHEN t.hora <= 8  THEN 1 ELSE 0 END)                      AS trans_h8,
        MAX(CASE WHEN t.hora = 9   THEN 1 ELSE 0 END)                      AS trans_h9,
        MAX(CASE WHEN t.hora = 10  THEN 1 ELSE 0 END)                      AS trans_h10,
        MAX(CASE WHEN t.hora = 11  THEN 1 ELSE 0 END)                      AS trans_h11,
        MAX(CASE WHEN t.hora = 12  THEN 1 ELSE 0 END)                      AS trans_h12,
        MAX(CASE WHEN t.hora = 13  THEN 1 ELSE 0 END)                      AS trans_h13,
        MAX(CASE WHEN t.hora = 14  THEN 1 ELSE 0 END)                      AS trans_h14,
        MAX(CASE WHEN t.hora = 15  THEN 1 ELSE 0 END)                      AS trans_h15,
        MAX(CASE WHEN t.hora = 16  THEN 1 ELSE 0 END)                      AS trans_h16,
        MAX(CASE WHEN t.hora = 17  THEN 1 ELSE 0 END)                      AS trans_h17,
        MAX(CASE WHEN t.hora = 18  THEN 1 ELSE 0 END)                      AS trans_h18,
        MAX(CASE WHEN t.hora = 19  THEN 1 ELSE 0 END)                      AS trans_h19,
        MAX(CASE WHEN t.hora = 20  THEN 1 ELSE 0 END)                      AS trans_h20,
        MAX(CASE WHEN t.hora = 21  THEN 1 ELSE 0 END)                      AS trans_h21,
        MAX(CASE WHEN t.hora = 22  THEN 1 ELSE 0 END)                      AS trans_h22,
        MAX(CASE WHEN t.hora = 23  THEN 1 ELSE 0 END)                      AS trans_h23
    FROM tmp_produccion_dia t
    INNER JOIN maquinas         m  ON m.IdMaquina  = t.idMaquina
    INNER JOIN centros          c  ON c.IdCentro   = t.idCentro
    LEFT  JOIN tmp_prom_latest  pl ON pl.idCentro  = t.idCentro
    GROUP BY t.idMaquina, m.Modelo, c.NombreCentro, m.CodigoMaquina
    ORDER BY total DESC;

    DROP TEMPORARY TABLE IF EXISTS tmp_produccion_dia;
    DROP TEMPORARY TABLE IF EXISTS tmp_prom_latest;
END

-- ACTUALIZACION DE SP COLOMBIA 
CREATE DEFINER=`root`@`%` PROCEDURE `rpt_centro_produccion_colombia`(IN p_fecha DATE)
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
        ROUND(MAX(COALESCE(pl.promT, 0)), 2)                               AS promedioCentro,
        MAX(CASE WHEN t.hora <= 8  THEN 1 ELSE 0 END)                      AS trans_h8,
        MAX(CASE WHEN t.hora = 9   THEN 1 ELSE 0 END)                      AS trans_h9,
        MAX(CASE WHEN t.hora = 10  THEN 1 ELSE 0 END)                      AS trans_h10,
        MAX(CASE WHEN t.hora = 11  THEN 1 ELSE 0 END)                      AS trans_h11,
        MAX(CASE WHEN t.hora = 12  THEN 1 ELSE 0 END)                      AS trans_h12,
        MAX(CASE WHEN t.hora = 13  THEN 1 ELSE 0 END)                      AS trans_h13,
        MAX(CASE WHEN t.hora = 14  THEN 1 ELSE 0 END)                      AS trans_h14,
        MAX(CASE WHEN t.hora = 15  THEN 1 ELSE 0 END)                      AS trans_h15,
        MAX(CASE WHEN t.hora = 16  THEN 1 ELSE 0 END)                      AS trans_h16,
        MAX(CASE WHEN t.hora = 17  THEN 1 ELSE 0 END)                      AS trans_h17,
        MAX(CASE WHEN t.hora = 18  THEN 1 ELSE 0 END)                      AS trans_h18,
        MAX(CASE WHEN t.hora = 19  THEN 1 ELSE 0 END)                      AS trans_h19,
        MAX(CASE WHEN t.hora = 20  THEN 1 ELSE 0 END)                      AS trans_h20,
        MAX(CASE WHEN t.hora = 21  THEN 1 ELSE 0 END)                      AS trans_h21,
        MAX(CASE WHEN t.hora = 22  THEN 1 ELSE 0 END)                      AS trans_h22,
        MAX(CASE WHEN t.hora = 23  THEN 1 ELSE 0 END)                      AS trans_h23
    FROM tmp_produccion_dia t
    INNER JOIN maquinas         m  ON m.IdMaquina = t.idMaquina
    INNER JOIN centros          c  ON c.IdCentro  = t.idCentro
    LEFT  JOIN tmp_prom_latest  pl ON pl.idCentro = t.idCentro
    GROUP BY t.idMaquina, m.Modelo, c.NombreCentro, m.CodigoMaquina
    ORDER BY total DESC;

    DROP TEMPORARY TABLE IF EXISTS tmp_produccion_dia;
    DROP TEMPORARY TABLE IF EXISTS tmp_prom_latest;
END

-- ACTUAALIZACION DE SP AUSTRALIA

CREATE DEFINER=`root`@`%` PROCEDURE `rpt_centro_produccion_australia`(IN p_fecha DATE)
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
        idMaquina      INT PRIMARY KEY,
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
        COALESCE(pm.promedioCentro, 0)                                      AS promedioCentro,
        MAX(CASE WHEN t.hora <= 8  THEN 1 ELSE 0 END)                      AS trans_h8,
        MAX(CASE WHEN t.hora = 9   THEN 1 ELSE 0 END)                      AS trans_h9,
        MAX(CASE WHEN t.hora = 10  THEN 1 ELSE 0 END)                      AS trans_h10,
        MAX(CASE WHEN t.hora = 11  THEN 1 ELSE 0 END)                      AS trans_h11,
        MAX(CASE WHEN t.hora = 12  THEN 1 ELSE 0 END)                      AS trans_h12,
        MAX(CASE WHEN t.hora = 13  THEN 1 ELSE 0 END)                      AS trans_h13,
        MAX(CASE WHEN t.hora = 14  THEN 1 ELSE 0 END)                      AS trans_h14,
        MAX(CASE WHEN t.hora = 15  THEN 1 ELSE 0 END)                      AS trans_h15,
        MAX(CASE WHEN t.hora = 16  THEN 1 ELSE 0 END)                      AS trans_h16,
        MAX(CASE WHEN t.hora = 17  THEN 1 ELSE 0 END)                      AS trans_h17,
        MAX(CASE WHEN t.hora = 18  THEN 1 ELSE 0 END)                      AS trans_h18,
        MAX(CASE WHEN t.hora = 19  THEN 1 ELSE 0 END)                      AS trans_h19,
        MAX(CASE WHEN t.hora = 20  THEN 1 ELSE 0 END)                      AS trans_h20,
        MAX(CASE WHEN t.hora = 21  THEN 1 ELSE 0 END)                      AS trans_h21,
        MAX(CASE WHEN t.hora = 22  THEN 1 ELSE 0 END)                      AS trans_h22,
        MAX(CASE WHEN t.hora = 23  THEN 1 ELSE 0 END)                      AS trans_h23
    FROM tmp_produccion_dia t
    INNER JOIN maquinas         m  ON m.IdMaquina = t.idMaquina
    INNER JOIN centros          c  ON c.IdCentro  = t.idCentro
    LEFT  JOIN tmp_prom_maquina pm ON pm.idMaquina = t.idMaquina
    GROUP BY t.idMaquina, m.Modelo, c.NombreCentro, m.CodigoMaquina, pm.promedioCentro
    ORDER BY total DESC;

    DROP TEMPORARY TABLE IF EXISTS tmp_produccion_dia;
    DROP TEMPORARY TABLE IF EXISTS tmp_prom_maquina;
END


-- ACTUALIZACION DE SP CHILE
CREATE DEFINER=`root`@`%` PROCEDURE `rpt_centro_produccion_chile`(IN p_fecha DATE)
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
        ROUND(MAX(COALESCE(pl.promT, 0)), 2)                               AS promedioCentro,
        MAX(CASE WHEN t.hora <= 8  THEN 1 ELSE 0 END)                      AS trans_h8,
        MAX(CASE WHEN t.hora = 9   THEN 1 ELSE 0 END)                      AS trans_h9,
        MAX(CASE WHEN t.hora = 10  THEN 1 ELSE 0 END)                      AS trans_h10,
        MAX(CASE WHEN t.hora = 11  THEN 1 ELSE 0 END)                      AS trans_h11,
        MAX(CASE WHEN t.hora = 12  THEN 1 ELSE 0 END)                      AS trans_h12,
        MAX(CASE WHEN t.hora = 13  THEN 1 ELSE 0 END)                      AS trans_h13,
        MAX(CASE WHEN t.hora = 14  THEN 1 ELSE 0 END)                      AS trans_h14,
        MAX(CASE WHEN t.hora = 15  THEN 1 ELSE 0 END)                      AS trans_h15,
        MAX(CASE WHEN t.hora = 16  THEN 1 ELSE 0 END)                      AS trans_h16,
        MAX(CASE WHEN t.hora = 17  THEN 1 ELSE 0 END)                      AS trans_h17,
        MAX(CASE WHEN t.hora = 18  THEN 1 ELSE 0 END)                      AS trans_h18,
        MAX(CASE WHEN t.hora = 19  THEN 1 ELSE 0 END)                      AS trans_h19,
        MAX(CASE WHEN t.hora = 20  THEN 1 ELSE 0 END)                      AS trans_h20,
        MAX(CASE WHEN t.hora = 21  THEN 1 ELSE 0 END)                      AS trans_h21,
        MAX(CASE WHEN t.hora = 22  THEN 1 ELSE 0 END)                      AS trans_h22,
        MAX(CASE WHEN t.hora = 23  THEN 1 ELSE 0 END)                      AS trans_h23
    FROM tmp_produccion_dia t
    INNER JOIN maquinas         m  ON m.IdMaquina = t.idMaquina
    INNER JOIN centros          c  ON c.IdCentro  = t.idCentro
    LEFT  JOIN tmp_prom_latest  pl ON pl.idCentro = t.idCentro
    GROUP BY t.idMaquina, m.Modelo, c.NombreCentro, m.CodigoMaquina
    ORDER BY total DESC;

    DROP TEMPORARY TABLE IF EXISTS tmp_produccion_dia;
    DROP TEMPORARY TABLE IF EXISTS tmp_prom_latest;
END
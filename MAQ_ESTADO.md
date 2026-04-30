### Maquinas Baja Fisica (http://127.0.0.1:8082/maquinas/baja-fisica)



```
SET @pais = 'peru';
SET @sql = CONCAT(
    'SELECT c.IdCentro, m.IdMaquina, m.Modelo AS modelo, c.NombreCentro AS centro, ',
    'co.Descripcion AS descripcionPais, m.version, m.lastReport, m.CodigoMaquina AS serie, ',
    'm.EsVisible AS esVisible, m.relay AS Relay, c.tOn AS tON, c.tOff AS tOff, cfg.isRMT AS isRMT ',
    'FROM maquinas m ',
    'INNER JOIN centros c ON c.IdCentro = m.idCentro ',
    'LEFT JOIN config cfg ON cfg.idMaquina = m.IdMaquina ',
    'LEFT JOIN country co ON co.idCountry = m.country ',
    'WHERE m.EsVisible = 0 AND m.EsActivo = 0 AND ( ',
    '   (''', @pais, ''' = ''chile''     AND c.EsChile = 1     AND c.EsProvincia = 0) ',
    'OR (''', @pais, ''' = ''colombia''  AND c.EsColombia = 1  AND c.EsProvincia = 0) ',
    'OR (''', @pais, ''' = ''australia'' AND c.EsAustralia = 1 AND c.EsProvincia = 0) ',
    'OR (''', @pais, ''' = ''provincia'' AND c.EsProvincia = 1) ',
    'OR (''', @pais, ''' NOT IN (''chile'',''colombia'',''australia'',''provincia'') ',
    '     AND c.EsChile = 0 AND c.EsColombia = 0 AND c.EsAustralia = 0 AND c.EsProvincia = 0) ',
    ') ORDER BY c.NombreCentro, m.CodigoMaquina;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

```


### Maquinas Listado Visibles   (http://127.0.0.1:8082/maquinas/visibles)

```
SET @pais = 'chile';

SET @sql = CONCAT(
    'SELECT c.IdCentro, m.IdMaquina, m.Modelo AS modelo, c.NombreCentro AS centro, co.Descripcion AS descripcionPais, m.version, m.lastReport, m.CodigoMaquina AS serie, m.EsVisible AS esVisible, m.relay AS Relay,  c.tOn AS tON, c.tOff AS tOff, cfg.isRMT AS isRMT ',
    'FROM maquinas m ',
    'INNER JOIN centros c ON c.IdCentro = m.idCentro ',
    'LEFT JOIN config cfg ON cfg.idMaquina = m.IdMaquina ',
    'LEFT JOIN country co ON co.idCountry = m.country ',
    'WHERE m.EsVisible = 1 AND ( ',
    '   (''', @pais, ''' = ''chile''     AND c.EsChile = 1) ',
    'OR (''', @pais, ''' = ''colombia''  AND c.EsColombia = 1) ',
    'OR (''', @pais, ''' = ''australia'' AND c.EsAustralia = 1) ',
    'OR (''', @pais, ''' = ''provincia'' AND c.EsProvincia = 1) ',
    'OR (''', @pais, ''' NOT IN (''chile'',''colombia'',''australia'',''provincia'') ',
    '     AND c.EsChile = 0 AND c.EsColombia = 0 AND c.EsAustralia = 0 AND c.EsProvincia = 0) ',
    ') ORDER BY c.NombreCentro, m.CodigoMaquina;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

```

### Maquinas Listado No Visibles  (http://127.0.0.1:8082/maquinas/novisibles)

```
SET @pais = 'chile';

SET @sql = CONCAT(
    'SELECT c.IdCentro, m.IdMaquina, m.Modelo AS modelo, c.NombreCentro AS centro, co.Descripcion AS descripcionPais, m.version, m.lastReport, m.CodigoMaquina AS serie, m.EsVisible AS esVisible, m.relay AS Relay,  c.tOn AS tON, c.tOff AS tOff, cfg.isRMT AS isRMT ',
    'FROM maquinas m ',
    'INNER JOIN centros c ON c.IdCentro = m.idCentro ',
    'LEFT JOIN config cfg ON cfg.idMaquina = m.IdMaquina ',
    'LEFT JOIN country co ON co.idCountry = m.country ',
    'WHERE m.EsVisible = 0 AND ( ',
    '   (''', @pais, ''' = ''chile''     AND c.EsChile = 1) ',
    'OR (''', @pais, ''' = ''colombia''  AND c.EsColombia = 1) ',
    'OR (''', @pais, ''' = ''australia'' AND c.EsAustralia = 1) ',
    'OR (''', @pais, ''' = ''provincia'' AND c.EsProvincia = 1) ',
    'OR (''', @pais, ''' NOT IN (''chile'',''colombia'',''australia'',''provincia'') ',
    '     AND c.EsChile = 0 AND c.EsColombia = 0 AND c.EsAustralia = 0 AND c.EsProvincia = 0) ',
    ') ORDER BY c.NombreCentro, m.CodigoMaquina;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

```
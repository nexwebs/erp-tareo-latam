DESCRIBE centros; -- idCentro

/*
Field	Type	Null	Key	Default	Extra
IdCentro	int(11)	NO	PRI		auto_increment
NombreCentro	varchar(50)	NO	MUL		
EsActivo	bit(1)	NO		b'1'	
FechaAlta	datetime	NO		current_timestamp()	
EsProvincia	bit(1)	YES		b'0'	
EsChile	bit(1)	YES		b'0'	
EsColombia	bit(1)	YES		b'0'	
EsAustralia	bit(1)	YES		b'0'	
Color1	varchar(15)	YES		#0000CC	
Color2	varchar(15)	YES		#0000FF	
idEncargada	int(11)	YES	MUL		
token	varchar(100)	YES			
zona	int(11)	YES			
owner	varchar(4)	YES			
tOn	time	YES		07:00:00	
tOff	time	YES		23:00:00	
promedio	int(11)	YES		0	
lvl	int(11)	YES		0	
idZonaProvincia	int(11)	YES			
idUsuario	int(11)	YES			

*/
select * from centros limit 25; 

/*
IdCentro	NombreCentro	EsActivo	FechaAlta	EsProvincia	EsChile	EsColombia	EsAustralia	Color1	Color2	idEncargada	token	zona	owner	tOn	tOff	promedio	lvl	idZonaProvincia	idUsuario
1	Almacen	1	2023-09-15 16:09:30	0	0	0	0	#0000CC	#0000FF			1		05:00:00	23:00:00	0	0		
2	Mega Plaza Jose Galvez 1	1	2023-09-19 15:11:51	0	0	0	0	#0000CC	#0000FF	2	6404433823:AAGe8beUjnzkIYQk8vkRyPNY9dAHyE4T2rE	2	JL	08:00:00	23:00:00	18	1	3	
3	M Aventura Sta Anita 3	1	2023-09-19 15:12:02	0	0	0	0	#0000CC	#0000FF	2	6834858440:AAGJtL-vf1zH4OT6-1Vwz8UrtV4bpGON-3Q	3	JL	08:00:00	23:00:00	45	3	2	
4	CC Jockey Pza - Shopping C	1	2023-09-19 15:12:45	0	0	0	0	#0000CC	#0000FF	2	6716314619:AAG2oUY5HNJZBeMjakqTZ2t89LET-wyLbz4	3	JL	08:00:00	23:00:00	59	3	3	
5	Metro Pascana 1	1	2023-09-19 15:12:55	0	0	0	0	#0000CC	#0000FF	1	6759439768:AAE7zYKRdrqMUdmw6EkIUbnZgTnsJDvfMak	1	SMS	08:00:00	23:00:00	33	3	1	
6	CC Pza Lima Sur 2 - SSHH	1	2023-09-19 15:13:03	0	0	0	0	#0000CC	#0000FF	2	6104968314:AAE5F7j1T9V2LneKIyvY9Nikok4wjiQ4bFE	2	JL	08:00:00	23:00:00	33	3	3	
7	CC Jockey Pza - Cinemark	1	2023-09-19 15:13:11	0	0	0	0	#0000CC	#0000FF	2	6720559974:AAEUXrGUD3u0uEsmyQwMK_kOEN7v4wOqP_E	3	JL	08:00:00	23:00:00	96	3	3	
8	CC Jockey Pza - SSHH	1	2023-09-19 15:16:30	0	0	0	0	#0000CC	#0000FF	2	6926148449:AAGgo-ZNiQuJCeQ8ScYmBU_XiaRSNZnyvI0	3	JL	08:00:00	23:00:00	206	3	3	5
9	Pza Vea Lurin 2	1	2023-10-05 14:52:49	0	0	0	0	#0000CC	#0000FF		6738288176:AAGQn8z9UWV7HSiAB1zdJfWrWfJnwkodSIU	2	JL	08:00:00	23:00:00	42	3	3	
10	O Pza Atocongo Dino 1	1	2023-10-05 14:54:07	0	0	0	0	#0000CC	#0000FF		6868524424:AAEQJplvEEUHgyMyQzh7LEjfv49drZXhxdk	2	JL	08:00:00	23:00:00	13	1	3	
11	CC Pza Lima Sur 4 - Metro	1	2023-10-05 14:56:28	0	0	0	0	#0000CC	#0000FF		6775956023:AAE-QEvxgZoYIIsXla0FVXOjPzPvirHkdvM	2	JL	08:00:00	23:00:00	49	2	3	5
12	TOTTUS - La Fontana 1	1	2023-10-05 15:00:17	0	0	0	0	#0000CC	#0000FF		6786596372:AAEOTEkb-DFlT1kLBHEZDOg7QGHlepmwvag	3	JL	08:00:00	23:00:00	10	1	3	
13	CC Agustino Pza 2	1	2023-10-05 15:01:38	0	0	0	0	#0000CC	#0000FF		6978245448:AAFxyLhxnprMjmu2AEi9RcmyvJf2QLC4bYI	3	JL	08:00:00	23:00:00	24	2	2	
14	M Pza Bellavista 1	1	2023-10-05 15:04:44	0	0	0	0	#0000CC	#0000FF	1	6495765506:AAESYjH3oxPB3C8Nw7_pbR4wn29SCHKHJ14	1	AC	08:00:00	23:00:00	22	2	1	
15	R Pza Centro Civico	1	2023-10-05 15:05:27	0	0	0	0	#0000CC	#0000FF		6652075442:AAEzBzyRt7IGq_-OO58qfRkaATCfFlykDkI	4		08:00:00	23:00:00	48	3	2	
16	M Pza Comas 2do Piso 	1	2023-10-05 15:06:58	0	0	0	0	#0000CC	#0000FF	1	6615739237:AAFejmSDawOEpx7tiw949fO-0IQ7_UsB_Ig	1	AC	08:00:00	23:00:00	18	1	1	
17	M Pza Comas 3er Piso	1	2023-10-05 15:07:04	0	0	0	0	#0000CC	#0000FF	1	6829093442:AAEviIW8rzQELybzgR58rM-LjDb-SO62t1c	1	AC	08:00:00	23:00:00	137	3	1	3
18	Multi Center Izaguirre - 1	1	2023-10-05 15:07:41	0	0	0	0	#0000CC	#0000FF	1	6413391066:AAFUE3UYI0emotRlglpBvOMZXSNmQSTmOCU	1	AC	08:00:00	23:00:00	47	2	1	3
19	CC Malvitec 1	1	2023-10-05 15:08:22	0	0	0	0	#0000CC	#0000FF	1	6775917742:AAFYGFGxFgMlU1qS-t-eP46wd4ffHgfcuCI	1	JL	09:00:00	20:00:00	10	1	1	
20	R Pza VMT 1	1	2023-10-05 15:09:20	0	0	0	0	#0000CC	#0000FF		6593615453:AAEI2UtpzktrlvOzV44Hsza9KkMMUXrEIUs	2	JL	08:00:00	23:00:00	38	3	3	
21	O Pza Atocongo Dino 2	1	2023-10-10 12:34:28	0	0	0	0	#0000CC	#0000FF		6797748441:AAHKCO0W0Ii5EVgma4eP-qOZB3nteX_4NIc	2	SMS	08:00:00	23:00:00	28	1	3	5
22	TOTTUS - Crillon 1	1	2023-10-12 11:15:08	0	0	0	0	#0000CC	#0000FF	1	6521137712:AAHQ9pfCEqcnoFj5fZvOss3Dc_ckafapGxk	4	JL	08:00:00	23:00:00	10	1	1	
23	TOTTUS - Crillon 2	1	2023-10-12 11:57:00	0	0	0	0	#0000CC	#0000FF	1	7140989936:AAFCfS56OH962OSo2TZh3Vc0c2TOsnaQG-c	4		08:00:00	23:00:00	16	1	1	3
24	CC Malvitec 2	1	2023-10-12 16:56:50	0	0	0	0	#0000CC	#0000FF	1	6910724627:AAFBUXtg1Ri-cPaHMssxu2Txcv4LasgrqSo	1	SMS	09:00:00	20:00:00	32	1	1	3
25	TOTTUS - La Fontana 2	1	2023-10-13 15:51:29	0	0	0	0	#0000CC	#0000FF		6824040537:AAHC4RbHqYke2IvMKTngNXyZGv_cUBEYABU	3	JL	08:00:00	23:00:00	42	2	3	5
*/
describe zonas
/*
Field	Type	Null	Key	Default	Extra
id	int(11)	NO	PRI		auto_increment
descripcion	varchar(255)	YES			
idGroup	varchar(255)	YES			
*/

select * from  zonas;
/*
id	descripcion	idGroup
1	Perú Lima Norte	-1002093112583
2	Perú Lima Sur	-1001993461897
3	Perú Lima Este	-1002045552118
4	Perú Lima Centro	-1002145287965
5	Perú Provincias Norte	-1002091064842
6	Perú Provincias Sur	
7	Perú Provincias Este	
8	Perú Provincias Centro	
9	Chile Santiago 1	-1002031610618
10	Chile Santiago 2	
11	Chile Provincias Norte	-1002065440445
12	Chile Provincias Sur	-1001971992169
13	Colombia Bogotá	-1001990973798
14	Colombia Medellin	
15	Colombia Barranquilla	
16	Australia	

*/
## los centros estan relacionados con las zonas entonce el en el sp cuando insert     IN _idZona        INT, tambien tienen que evaluar sin ahi la logica en la app seria o crea una nueva zona que no existe el spconforme si no exite  depsues de implemntar tambine crud para zona insertar delete y actualizar 

Analizando el paralelismo con el ciclo de vida de `maquinas`, el SP `usp_dar_baja_centro` que generamos **le falta exactamente lo que `maquinas` tiene resuelto**.

**Lo que `maquinas` hace bien y `centros` no tiene:**

`maquinas` tiene dos paths diferenciados:
- `usp_dar_baja_maquina` → baja lógica, resetea FK peligrosas (`idCentro=1, country=NULL`)
- `usp_eliminar_maquina_staging` → delete físico, solo si nunca tuvo producción y está en staging

`usp_dar_baja_centro` actual solo hace `EsActivo=0` y un UPDATE en cascada sobre máquinas inactivas. Le faltan:

1. **Reset defensivo de FKs** — no desconecta el centro de `zonas` ni de `idEncargada`. Si esos registros se borran después, el centro baja rompe integridad referencial silenciosamente.

2. **No existe path de eliminación física para centros en staging** — un centro creado por error (sin máquinas, sin producción) no tiene SP de delete equivalente a `usp_eliminar_maquina_staging`.

3. **No valida `promedios`** — `promedios` no tiene FK con CASCADE hacia `centros`. Si das de baja y luego alguien hace DELETE del centro, `promedios` queda huérfana igual que las tablas de producción en máquinas.

**El flujo completo que le falta a centros, espejando el de máquinas:**

```
usp_crear_centro          → EsActivo=1, zona válida, trigger INSERT_PROM
usp_dar_baja_centro       → EsActivo=0, bloquea si tiene maquinas EsVisible=1
usp_eliminar_centro_staging → DELETE físico, solo si sin máquinas y sin promedios con data
```
```sql
DROP PROCEDURE IF EXISTS `usp_crear_centro`;
DELIMITER ;;
CREATE PROCEDURE `usp_crear_centro`(
    IN _nombre        VARCHAR(50),
    IN _pais          VARCHAR(20),
    IN _idZona        INT,
    IN _idEncargada   INT,
    IN _tOn           TIME,
    IN _tOff          TIME,
    IN _idZonaProv    INT
)
BEGIN
    DECLARE v_zona_existe      INT DEFAULT 0;
    DECLARE v_encargada_existe INT DEFAULT 0;
    DECLARE v_nombre_dup       INT DEFAULT 0;
    DECLARE v_zona_prov_existe INT DEFAULT 0;

    SELECT COUNT(1) INTO v_nombre_dup
    FROM centros WHERE NombreCentro = _nombre;

    IF v_nombre_dup > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ya existe un centro con ese nombre';
    END IF;

    SELECT COUNT(1) INTO v_zona_existe
    FROM zonas WHERE id = _idZona;

    IF v_zona_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La zona no existe';
    END IF;

    IF _idEncargada IS NOT NULL THEN
        SELECT COUNT(1) INTO v_encargada_existe
        FROM cn_encargada WHERE idEncargada = _idEncargada;

        IF v_encargada_existe = 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'La encargada no existe';
        END IF;
    END IF;

    IF _pais NOT IN ('chile', 'colombia', 'australia', 'peru', 'provincia') THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'País inválido: chile|colombia|australia|peru|provincia';
    END IF;

    IF _pais = 'provincia' AND _idZonaProv IS NOT NULL THEN
        SELECT COUNT(1) INTO v_zona_prov_existe
        FROM zonasprovincia WHERE idZonaProvincia = _idZonaProv AND esActivo = 1;

        IF v_zona_prov_existe = 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'La zona de provincia no existe o está inactiva';
        END IF;
    END IF;

    INSERT INTO centros (
        NombreCentro,
        EsActivo,
        EsProvincia,
        EsChile,
        EsColombia,
        EsAustralia,
        zona,
        idEncargada,
        tOn,
        tOff,
        idZonaProvincia
    ) VALUES (
        _nombre,
        1,
        IF(_pais = 'provincia', 1, 0),
        IF(_pais = 'chile',     1, 0),
        IF(_pais = 'colombia',  1, 0),
        IF(_pais = 'australia', 1, 0),
        _idZona,
        _idEncargada,
        COALESCE(_tOn,  '08:00:00'),
        COALESCE(_tOff, '23:00:00'),
        IF(_pais = 'provincia', _idZonaProv, NULL)
    );

    SELECT
        c.IdCentro,
        c.NombreCentro,
        c.EsChile,
        c.EsColombia,
        c.EsAustralia,
        c.EsProvincia,
        c.zona,
        z.descripcion AS zona_desc,
        c.tOn,
        c.tOff,
        c.idEncargada,
        p.idProm
    FROM centros c
    INNER JOIN zonas z ON z.id = c.zona
    LEFT  JOIN promedios p ON p.idCentro = c.IdCentro
    WHERE c.IdCentro = LAST_INSERT_ID();
END;;
DELIMITER ;
```

```sql
DROP PROCEDURE IF EXISTS `usp_dar_baja_centro`;
DELIMITER ;;
CREATE PROCEDURE `usp_dar_baja_centro`(
    IN _idCentro INT
)
BEGIN
    DECLARE v_centro_existe    INT DEFAULT 0;
    DECLARE v_centro_activo    INT DEFAULT 0;
    DECLARE v_maquinas_activas INT DEFAULT 0;

    SELECT COUNT(1) INTO v_centro_existe
    FROM centros WHERE IdCentro = _idCentro;

    IF v_centro_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El centro no existe';
    END IF;

    SELECT COUNT(1) INTO v_centro_activo
    FROM centros WHERE IdCentro = _idCentro AND EsActivo = 1;

    IF v_centro_activo = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El centro ya está inactivo';
    END IF;

    SELECT COUNT(1) INTO v_maquinas_activas
    FROM maquinas
    WHERE idCentro = _idCentro AND EsVisible = 1 AND EsActivo = 1;

    IF v_maquinas_activas > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede dar de baja: el centro tiene máquinas activas (EsVisible=1)';
    END IF;

    UPDATE centros
    SET EsActivo = 0
    WHERE IdCentro = _idCentro;

    UPDATE maquinas
    SET EsActivo = 0
    WHERE idCentro = _idCentro AND EsVisible = 0 AND EsActivo = 1;

    SELECT
        c.IdCentro,
        c.NombreCentro,
        c.EsActivo,
        c.EsChile,
        c.EsColombia,
        c.EsAustralia,
        c.EsProvincia,
        c.zona,
        z.descripcion AS zona_desc,
        COUNT(m.IdMaquina) AS total_maquinas_en_centro
    FROM centros c
    INNER JOIN zonas z ON z.id = c.zona
    LEFT  JOIN maquinas m ON m.idCentro = c.IdCentro
    WHERE c.IdCentro = _idCentro
    GROUP BY
        c.IdCentro, c.NombreCentro, c.EsActivo,
        c.EsChile, c.EsColombia, c.EsAustralia, c.EsProvincia,
        c.zona, z.descripcion;
END;;
DELIMITER ;
```

```sql
DROP PROCEDURE IF EXISTS `usp_eliminar_centro_staging`;
DELIMITER ;;
CREATE PROCEDURE `usp_eliminar_centro_staging`(
    IN _idCentro INT
)
BEGIN
    DECLARE v_es_staging      INT DEFAULT 0;
    DECLARE v_tiene_maquinas  INT DEFAULT 0;
    DECLARE v_tiene_promedios INT DEFAULT 0;

    SELECT COUNT(1) INTO v_es_staging
    FROM centros
    WHERE IdCentro = _idCentro
      AND EsActivo = 0;

    IF v_es_staging = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Solo se pueden eliminar centros previamente dados de baja';
    END IF;

    SELECT COUNT(1) INTO v_tiene_maquinas
    FROM maquinas
    WHERE idCentro = _idCentro;

    IF v_tiene_maquinas > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El centro tiene máquinas asociadas, no se puede eliminar';
    END IF;

    SELECT COUNT(1) INTO v_tiene_promedios
    FROM promedios
    WHERE idCentro = _idCentro
      AND (promedio IS NOT NULL AND promedio > 0);

    IF v_tiene_promedios > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El centro tiene historial de promedios, use baja lógica';
    END IF;

    DELETE FROM promedios WHERE idCentro = _idCentro;
    DELETE FROM centros   WHERE IdCentro = _idCentro;

    SELECT ROW_COUNT() AS filas_afectadas;
END;;
DELIMITER ;
```

**Una diferencia clave respecto a `maquinas`:** el staging de un centro recién creado **no es** `EsActivo=0` sino `EsActivo=1` sin máquinas. El prerequisito del delete físico es haber pasado primero por `usp_dar_baja_centro`. Eso fuerza el flujo correcto y evita eliminar centros activos saltando la validación de máquinas visibles.

**El ciclo de vida de centros queda simétrico al de máquinas:**

```
usp_crear_centro
    EsActivo=1, zona válida
    trigger INSERT_PROM → promedios vacío

usp_dar_baja_centro
    EsActivo=0
    bloquea si maquinas EsVisible=1
    baja en cascada maquinas EsVisible=0 y EsActivo=1

usp_eliminar_centro_staging
    prerequisito: EsActivo=0 (pasó por baja)
    bloquea si tiene maquinas o promedios con data
    DELETE promedios → DELETE centros
```
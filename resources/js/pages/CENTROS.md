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
--

### el ciclo de vida de un centro tiene implicancias directas en varias tablas y el trigger `INSERT_PROM` ya cubre una parte. Vamos a mapear todo:

**Lo que ya existe automáticamente al insertar en `centros`:**

```sql
TRIGGER INSERT_PROM → INSERT INTO promedios (IdCentro)
```

**Dependencias que un centro activo necesita para funcionar:**

Mirando los SP de producción y reportes, un centro para ser operativo requiere:
- `centros.zona` → referencia a `zonas` (requerido por `listar_maquinas`, `GET_CONFIG`, todos los SP de producción)
- `centros.idEncargada` → referencia a `cn_encargada` (opcional pero usado en `usp_ProduccionPeruEncargadas_sel`)
- `centros.EsChile/EsColombia/EsAustralia/EsProvincia` → exactamente uno en 1
- `promedios` → el trigger ya lo crea, pero vacío

**Lo que falta y es crítico:** ningún SP existe para crear centros de forma controlada. El riesgo es insertar un centro sin `zona` válida y romper `GET_CONFIG` y todos los SP de producción que hacen `JOIN zonas z ON z.id = c.zona`.

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

**El flujo completo de ciclo de vida de un centro queda:**

```
usp_crear_centro(_nombre, _pais, _idZona, ...)
    INSERT centros  → EsActivo=1, flags de país correctos
    TRIGGER INSERT_PROM → INSERT promedios (vacío, listo para el job de promedios)

    usp_crear_maquina_inactiva(_serie, _modelo)   [del documento anterior]
        INSERT maquinas → staging

    usp_activar_maquina(_idMaquina, _idCentro, _country)
        UPDATE maquinas → EsVisible=1, vincula al centro
        Firmware reporta → insertProd → producción visible en SP de reportes
```

**Una advertencia importante que surge del schema:** la columna `centros.EsActivo` no tiene un SP de baja para centros, y si das de baja un centro con máquinas activas (`EsVisible=1`), esas máquinas quedan visibles en reportes pero el centro aparece inactivo en selects filtrados por `EsActivo=1`. Necesitarías un `usp_dar_baja_centro` que valide que no existan máquinas con `EsVisible=1` antes de desactivar. ¿Lo incluimos?
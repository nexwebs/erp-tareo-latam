### el flujo completo de creacion 


describe country;

/*
Field	Type	Null	Key	Default	Extra
idCountry	int(11)	NO	PRI		
Descripcion	varchar(255)	YES			

*/
select * from country;

/*
idCountry	Descripcion
1	Chile
2	Colombia
3	Peru Comp. Weiya
4	Peru Comp. Generico
5	Peluchera Weiya
6	Peluchera Chino
7	Australia
8	Modulo Venta
9	Peluchera 2S Patron
10	Peluchera 3S Chino
11	MiniPeluchera
12	Arcade

*/

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

```
Crear máquina (staging)  →  Máquina física reporta  →  Producción visible
```

La producción sale del centro asignado. La tabla donde cae depende del `country` que envía el firmware, que debe coincidir con el país del centro. Si asignas `idCentro` de Chile pero el firmware envía `country=3` (Peru), el trigger `insertProd` genera producción en `ProduccionPeru` pero el centro es Chile — inconsistencia. Por eso el `country` en `maquinas` y el `country` que envía el firmware deben ser el mismo.

Aquí los SP finales listos para ejecutar:

```sql
DROP PROCEDURE IF EXISTS `usp_crear_maquina_visible`;
DELIMITER ;;
CREATE PROCEDURE `usp_crear_maquina_visible`(
    IN _serie      VARCHAR(30),
    IN _modelo     VARCHAR(50),
    IN _idCentro   INT,
    IN _country    INT,
    IN _relay      INT
)
BEGIN
    DECLARE v_existe_centro INT DEFAULT 0;
    DECLARE v_pais_centro   VARCHAR(20) DEFAULT '';

    IF EXISTS (SELECT 1 FROM maquinas WHERE CodigoMaquina = _serie) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La serie ya existe';
    END IF;

    SELECT COUNT(1) INTO v_existe_centro
    FROM centros
    WHERE IdCentro = _idCentro AND EsActivo = 1;

    IF v_existe_centro = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Centro no existe o está inactivo';
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

    INSERT INTO maquinas (
        CodigoMaquina,
        Modelo,
        EsVisible,
        EsActivo,
        idCentro,
        country,
        relay,
        onOff,
        uSRV
    ) VALUES (
        _serie,
        _modelo,
        1,
        1,
        _idCentro,
        _country,
        _relay,
        0,
        '0x0007c8'
    );

    SELECT
        m.IdMaquina,
        m.CodigoMaquina,
        m.Modelo,
        m.EsVisible,
        m.EsActivo,
        m.idCentro,
        m.country,
        m.relay,
        c.NombreCentro,
        c.EsChile,
        c.EsColombia,
        c.EsAustralia
    FROM maquinas m
    INNER JOIN centros c ON c.IdCentro = m.idCentro
    WHERE m.CodigoMaquina = _serie;
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
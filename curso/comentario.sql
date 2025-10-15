`a_curso_forum` 
  `idusuarioCF` int(11) DEFAULT NULL,
  `textoCF` text COLLATE utf8mb4_unicode_ci,
  `permissaoCF` int(11) DEFAULT NULL,
  `dataCF` date DEFAULT NULL,
  `horaCF` time DEFAULT NULL

  
`a_curso_forum_comentarios` 
  `codigoforumcomentario` int(11) NOT NULL,
  `idforumfc` int(11) DEFAULT NULL,
  `idusuariode` int(11) DEFAULT NULL,
  `idusuariopara` int(11) DEFAULT NULL,
  `textofc` text COLLATE utf8mb4_unicode_ci,
  `datafc` date DEFAULT NULL,
  `horafc` time DEFAULT NULL
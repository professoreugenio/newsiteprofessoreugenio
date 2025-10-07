`a_site_afiliados_chave` (
  `codigochaveafiliados` int(11) NOT NULL,
  `idusuarioSA` int(11) DEFAULT NULL,
  `chaveafiliadoSA` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataSA` date DEFAULT NULL,
  `horaSA` time DEFAULT NULL



`a_site_vendas` 
  `codigovendas` int(11) NOT NULL,
  `idcursosv` int(11) DEFAULT NULL,
  `chaveturmasv` int(11) DEFAULT NULL,
  `idalunosv` int(11) DEFAULT NULL,
  `chaveafiliadosv` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valorvendasv` float(9,2) DEFAULT NULL,
  `datacomprasv` date DEFAULT NULL,
  `horacomprasv` time DEFAULT NULL,
  `statussv` int(11) DEFAULT NULL

  
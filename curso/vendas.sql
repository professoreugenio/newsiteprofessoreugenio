`a_site_afiliados_chave` 
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


  `new_sistema_cadastro` 
  `codigocadastro` int(11) NOT NULL,
  `pastasc` varchar(30) DEFAULT '10000000000',
  `nome` varchar(100) DEFAULT NULL,
  `possuipc` int(1) DEFAULT NULL,
  `imagem200` varchar(300) DEFAULT 'usuario.jpg',
  `imagem50` varchar(300) DEFAULT 'usuario.jpg',
  `textoapresentacao` text,
  `email` varchar(200) DEFAULT NULL,
  `datanascimento_sc` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `senha` varchar(200) DEFAULT NULL,
  `chave` varchar(100) DEFAULT NULL,
  `data_sc` date DEFAULT NULL,
  `hora_sc` time DEFAULT NULL,


`new_sistema_cursos` 
  `codigocursos` int(11) NOT NULL,
  `bgcolor` varchar(10) DEFAULT '#cccccc',
  `nomecurso` varchar(50) DEFAULT NULL,
  `pasta` varchar(30) DEFAULT NULL,

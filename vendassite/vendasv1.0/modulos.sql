seleciona nome d módulo quando new_sistema_modulos_PJA.codcursos = $idCursoVenda

`new_sistema_modulos_PJA` 
  `codigomodulos` int(11) NOT NULL,
  `codcursos` int(11) DEFAULT NULL,
  `modulo` varchar(50) DEFAULT NULL,
  `nomemodulo` varchar(50) DEFAULT NULL,
  `visivelm` varchar(1) DEFAULT '0',

Para a lista (li) de conteúdo consulta em a_aluno_publicacoes_cursos.idmodulopc = new_sistema_modulos_PJA.codigomodulos para encontrar o idpublicacaopc

`a_aluno_publicacoes_cursos` 
  `codigopublicacoescursos` int(11) NOT NULL,
  `idpublicacaopc` int(11) DEFAULT NULL,
  `idcursopc` int(11) DEFAULT NULL,
  `idturmapc` int(11) DEFAULT NULL,
  `idmodulopc` int(11) DEFAULT NULL,
  `publicopc` int(11) DEFAULT NULL,
  `visivelpc` int(11) DEFAULT NULL,
  `ordempc` int(11) DEFAULT NULL,
  `bloqueadopc` int(11) DEFAULT NULL,


  busca o nome do conteúdo do módulo em new_sistema_publicacoes_PJA quando  new_sistema_publicacoes_PJA.codigopublicacoes = idpublicacaopc

  `new_sistema_publicacoes_PJA` 
  `codigopublicacoes` int(11) NOT NULL,
  `titulo` varchar(250) DEFAULT NULL,
  


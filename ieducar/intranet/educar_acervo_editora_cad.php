<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	*																	     *
	*	@author Prefeitura Municipal de Itaja�								 *
	*	@updated 29/03/2007													 *
	*   Pacote: i-PLB Software P�blico Livre e Brasileiro					 *
	*																		 *
	*	Copyright (C) 2006	PMI - Prefeitura Municipal de Itaja�			 *
	*						ctima@itajai.sc.gov.br					    	 *
	*																		 *
	*	Este  programa  �  software livre, voc� pode redistribu�-lo e/ou	 *
	*	modific�-lo sob os termos da Licen�a P�blica Geral GNU, conforme	 *
	*	publicada pela Free  Software  Foundation,  tanto  a vers�o 2 da	 *
	*	Licen�a   como  (a  seu  crit�rio)  qualquer  vers�o  mais  nova.	 *
	*																		 *
	*	Este programa  � distribu�do na expectativa de ser �til, mas SEM	 *
	*	QUALQUER GARANTIA. Sem mesmo a garantia impl�cita de COMERCIALI-	 *
	*	ZA��O  ou  de ADEQUA��O A QUALQUER PROP�SITO EM PARTICULAR. Con-	 *
	*	sulte  a  Licen�a  P�blica  Geral  GNU para obter mais detalhes.	 *
	*																		 *
	*	Voc�  deve  ter  recebido uma c�pia da Licen�a P�blica Geral GNU	 *
	*	junto  com  este  programa. Se n�o, escreva para a Free Software	 *
	*	Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA	 *
	*	02111-1307, USA.													 *
	*																		 *
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
require_once ("include/clsBase.inc.php");
require_once ("include/clsCadastro.inc.php");
require_once ("include/clsBanco.inc.php");
require_once( "include/pmieducar/geral.inc.php" );

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "{$this->_instituicao} i-Educar - Editora" );
		$this->processoAp = "595";
	}
}

class indice extends clsCadastro
{
	/**
	 * Referencia pega da session para o idpes do usuario atual
	 *
	 * @var int
	 */
	var $pessoa_logada;

	var $cod_acervo_editora;
	var $ref_usuario_cad;
	var $ref_usuario_exc;
	var $ref_idtlog;
	var $ref_sigla_uf;
	var $nm_editora;
	var $cep;
	var $cidade;
	var $bairro;
	var $logradouro;
	var $numero;
	var $telefone;
	var $ddd_telefone;
	var $data_cadastro;
	var $data_exclusao;
	var $ativo;
	var $ref_cod_biblioteca;

	function Inicializar()
	{
		$retorno = "Novo";
		@session_start();
		$this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$this->cod_acervo_editora=$_GET["cod_acervo_editora"];

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 595, $this->pessoa_logada, 11,  "educar_acervo_editora_lst.php" );

		if( is_numeric( $this->cod_acervo_editora ) )
		{

			$obj = new clsPmieducarAcervoEditora( $this->cod_acervo_editora );
			$registro  = $obj->detalhe();
			if( $registro )
			{
				foreach( $registro AS $campo => $val )	// passa todos os valores obtidos no registro para atributos do objeto
					$this->$campo = $val;

			if( $obj_permissoes->permissao_excluir( 595, $this->pessoa_logada, 11 ) )
			{
				$this->fexcluir = true;
			}

				$retorno = "Editar";
			}
		}
		$this->url_cancelar = ($retorno == "Editar") ? "educar_acervo_editora_det.php?cod_acervo_editora={$registro["cod_acervo_editora"]}" : "educar_acervo_editora_lst.php";
		$this->nome_url_cancelar = "Cancelar";
		return $retorno;
	}

	function Gerar()
	{
		// primary keys
		$this->campoOculto( "cod_acervo_editora", $this->cod_acervo_editora );

		/*$obj_pessoa_bib = new clsPmieducarBibliotecaUsuario();
		$lst_pessoa_bib = $obj_pessoa_bib->lista(null, $this->pessoa_logada);

		$opcoes = array("" => "Selecione");
		if(is_array($lst_pessoa_bib))
		{
			foreach ($lst_pessoa_bib as $bib)
			{
				$obj_biblioteca = new clsPmieducarBiblioteca($bib['ref_cod_biblioteca']);
				$det_biblioteca = $obj_biblioteca->detalhe();
				
				$opcoes[$det_biblioteca['cod_biblioteca']] = $det_biblioteca['nm_biblioteca'];
			}
		}
		$this->campoLista("ref_cod_biblioteca", "Biblioteca", $opcoes, $this->ref_cod_biblioteca);
		*/
		
		$get_escola     = 1;
		$escola_obrigatorio = false;
		$get_biblioteca = 1;
		$instituicao_obrigatorio = true;
		$biblioteca_obrigatorio = true;
		include("include/pmieducar/educar_campo_lista.php");
		
		//text
		$this->campoTexto( "nm_editora", "Editora", $this->nm_editora, 30, 255, true );

		// foreign keys
		if ($this->cod_acervo_editora)
		{
			$this->cep = int2CEP($this->cep);
		}

		$this->campoCep( "cep", "CEP", $this->cep, true );

		$opcoes = array( "" => "Selecione" );
		if( class_exists( "clsUf" ) )
		{
			$objTemp = new clsUf();
			$lista = $objTemp->lista();
			if ( is_array( $lista ) && count( $lista ) )
			{
				foreach ( $lista as $registro )
				{
					$opcoes["{$registro['sigla_uf']}"] = "{$registro['nome']}";
				}
			}
		}
		else
		{
			echo "<!--\nErro\nClasse clsUf nao encontrada\n-->";
			$opcoes = array( "" => "Erro na geracao" );
		}
		$this->campoLista( "ref_sigla_uf", "Estado", $opcoes, $this->ref_sigla_uf );

		$this->campoTexto( "cidade", "Cidade", $this->cidade, 30, 60, true );
		$this->campoTexto( "bairro", "Bairro", $this->bairro, 30, 60, true );

		$opcoes = array( "" => "Selecione" );
		if( class_exists( "clsTipoLogradouro" ) )
		{
			$objTemp = new clsTipoLogradouro();
			$lista = $objTemp->lista();
			if ( is_array( $lista ) && count( $lista ) )
			{
				foreach ( $lista as $registro )
				{
					$opcoes["{$registro['idtlog']}"] = "{$registro['descricao']}";
				}
			}
		}
		else
		{
			echo "<!--\nErro\nClasse clsUrbanoTipoLogradouro nao encontrada\n-->";
			$opcoes = array( "" => "Erro na geracao" );
		}
		$this->campoLista( "ref_idtlog", "Tipo Logradouro", $opcoes, $this->ref_idtlog );

		$this->campoTexto( "logradouro", "Logradouro", $this->logradouro, 30, 255, true );

		$this->campoNumero( "numero", "N&uacute;mero", $this->numero, 6, 6 );

		$this->campoNumero( "ddd_telefone", "DDD Telefone", $this->ddd_telefone, 2, 2, false );
		$this->campoNumero( "telefone", "Telefone", $this->telefone, 10, 15, false );

		// data

	}

	function Novo()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 595, $this->pessoa_logada, 11,  "educar_acervo_editora_lst.php" );

		$this->cep = idFederal2int($this->cep);

		$obj = new clsPmieducarAcervoEditora( null, $this->pessoa_logada, null, $this->ref_idtlog, $this->ref_sigla_uf, $this->nm_editora, $this->cep, $this->cidade, $this->bairro, $this->logradouro, $this->numero, $this->telefone, $this->ddd_telefone, null, null, 1, $this->ref_cod_biblioteca );
		$cadastrou = $obj->cadastra();
		if( $cadastrou )
		{
			$this->mensagem .= "Cadastro efetuado com sucesso.<br>";
			header( "Location: educar_acervo_editora_lst.php" );
			die();
			return true;
		}

		$this->mensagem = "Cadastro n&atilde;o realizado.<br>";
		echo "<!--\nErro ao cadastrar clsPmieducarAcervoEditora\nvalores obrigat&oacute;rios\nis_numeric( $this->pessoa_logada ) && is_string( $this->ref_idtlog ) && is_string( $this->ref_sigla_uf ) && is_string( $this->nm_editora ) && is_numeric( $this->cep ) && is_string( $this->cidade ) && is_string( $this->bairro ) && is_string( $this->logradouro ) && is_numeric( $this->numero )\n-->";
		return false;
	}

	function Editar()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_cadastra( 595, $this->pessoa_logada, 11,  "educar_acervo_editora_lst.php" );

		$this->cep = idFederal2int($this->cep);

		$obj = new clsPmieducarAcervoEditora($this->cod_acervo_editora, null, $this->pessoa_logada, $this->ref_idtlog, $this->ref_sigla_uf, $this->nm_editora, $this->cep, $this->cidade, $this->bairro, $this->logradouro, $this->numero, $this->telefone, $this->ddd_telefone, null, null, 1, $this->ref_cod_biblioteca);
		$editou = $obj->edita();
		if( $editou )
		{
			$this->mensagem .= "Edi&ccedil;&atilde;o efetuada com sucesso.<br>";
			header( "Location: educar_acervo_editora_lst.php" );
			die();
			return true;
		}

		$this->mensagem = "Edi&ccedil;&atilde;o n&atilde;o realizada.<br>";
		echo "<!--\nErro ao editar clsPmieducarAcervoEditora\nvalores obrigat&oacute;rios\nif( is_numeric( $this->cod_acervo_editora ) && is_numeric( $this->pessoa_logada ) )\n-->";
		return false;
	}

	function Excluir()
	{
		@session_start();
		 $this->pessoa_logada = $_SESSION['id_pessoa'];
		@session_write_close();

		$obj_permissoes = new clsPermissoes();
		$obj_permissoes->permissao_excluir( 595, $this->pessoa_logada, 11,  "educar_acervo_editora_lst.php" );


		$obj = new clsPmieducarAcervoEditora($this->cod_acervo_editora, null, $this->pessoa_logada, null,null,null,null,null,null,null,null,null,null,null,null, 0);
		$excluiu = $obj->excluir();
		if( $excluiu )
		{
			$this->mensagem .= "Exclus&atilde;o efetuada com sucesso.<br>";
			header( "Location: educar_acervo_editora_lst.php" );
			die();
			return true;
		}

		$this->mensagem = "Exclus&atilde;o n&atilde;o realizada.<br>";
		echo "<!--\nErro ao excluir clsPmieducarAcervoEditora\nvalores obrigat&oacute;rios\nif( is_numeric( $this->cod_acervo_editora ) && is_numeric( $this->pessoa_logada ) )\n-->";
		return false;
	}
}

// cria uma extensao da classe base
$pagina = new clsIndexBase();
// cria o conteudo
$miolo = new indice();
// adiciona o conteudo na clsBase
$pagina->addForm( $miolo );
// gera o html
$pagina->MakeAll();
?>
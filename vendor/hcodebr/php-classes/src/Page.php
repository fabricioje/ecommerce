<?php 

namespace Jsilva;

use Rain\Tpl;

class Page{

	private $tpl;
	private $options = [];
	private $defaults = [
		"data"=>[]
	];

	public function __construct($opts = array(), $tpl_dir = "/views/"){

		$this->options = array_merge($this->defaults, $opts);

		$config = array(
			//$_SERVER["DOCUMENT_ROOT"] - devolve a pasta root do servidor, a pasta raiz
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => false, // set to false to improve the speed
		);

		Tpl::configure( $config );

		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);

		$this->tpl->draw("header");

	}

	public function setData($data = array()){

		foreach ($data as $key => $value) {
			$this->tpl->assign($key, $value);
		}

	}

	public function setTpl($nome, $data = array(), $returnHtml = false){

		$this->setData($data);

		return $this->tpl->draw($nome, $returnHtml);
	}

	public function __destruct(){

		$this->tpl->draw("footer");

	}
}
 ?>
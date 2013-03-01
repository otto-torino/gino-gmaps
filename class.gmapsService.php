<?php
/**
 * @file class.gmapsService.php
 * @brief Contiene la definizione ed implementazione della classe gmapsService.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione dei servizi
 *
 * Campi:
 *
 * - **id**: identificativo punto di interesse
 * - **instance**: identificativo @ref gmaps di appartenenza 
 * - **name**: nome servizio
 * - **description**: descrizione
 * - **icon**: icona
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsService extends propertyObject {

	private static $_tbl_service = 'gmaps_service';

	private $_controller, $_data_www, $_data_dir, $_extension_media, $_prefix_img, $_prefix_thumb;

	/**
	 * @brief larghezza di ridimensionamento dell'immagine uploadata 
	 */
	private $_img_width = 200;

	/**
	 * @brief larghezza di ridimensionamento della thumb generata dall'immagine uploadata 
	 */
	private $_thumb_width = 80;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsService 
	 * 
	 * @param int $id id del servizio
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsService un oggetto gmapsService
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$_tbl_service;
		parent::__construct($id);

		$this->_extension_media = array('jpg', 'jpeg', 'png');
		$this->_prefix_img = 'img_';
		$this->_prefix_thumb = 'thumb_';
		$this->_data_www = CONTENT_WWW.'/gmaps/'.$this->_controller->instanceName().'/service';
		$this->_data_dir = CONTENT_DIR.OS.'gmaps'.OS.$this->_controller->instanceName().OS.'service';

		$this->_model_label = $this->id ? $this->name : '';
	}

	/**
	 * @brief Metodo setter per la proprietà instance 
	 * 
	 * @param int $value identificativo dell'istanza @ref gmaps 
	 * @access public
	 * @return bool true
	 */
	public function setInstance($value) {

		if($this->_p['instance']!=$value && !in_array('instance', $this->_chgP)) $this->_chgP[] = 'instance';
		$this->_p['instance'] = $value;
		return true;

	}

	/**
	 * @brief Deletes all the records tied to the given gmaps instance 
	 * 
	 * @param int $instance the @ref gmaps instance id
	 * @static
	 * @access public
	 * @return bool The result of the deletion
	 */
	public static function deleteInstance($instance) {

		$db = db::instance();
		$query = "DELETE FROM ".self::$_tbl_service." WHERE instance='$instance'";
		$res = $db->actionquery($query);

		return $res;

	}

	/**
	 * @brief Seleziona id di categorie sul database 
	 * 
	 * @param int $instance id dell'istanza del controller @ref gmaps
	 * @param array $opts array associativo di opzioni:
	 *                          - where: where clauses addizionali 
	 *                          - order: campo di ordinamento dei risultati
	 *                          - limit: limitazione dei risultati
	 * @static
	 * @access public
	 * @return array un array di id delle categorie ottenute dalla query
	 */
	public static function get($instance, $opts=null) {

		$where = "instance='".$instance."'".(isset($opts['where']) ? " AND ".$opts['where'] : '');
		$order = isset($opts['order']) ? $opts['order'] : 'name';
		$limit = isset($opts['limit']) ? "LIMIT ".$opts['limit'] : '';

		$res = array();

		$db = db::instance();
		$query = "SELECT id FROM ".self::$_tbl_service." WHERE $where ORDER BY $order $limit";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[] = $b['id'];
		}

		return $res;

	} 

	/**
	 * @brief Ritorna un array associativo di categorie da utilizzare per popolare un input select 
	 * 
	 * @param int $instance id dell'istanza del controller @ref gmaps
	 * @param array $opts array associativo di opzioni:
	 *                          - where: where clauses addizionali 
	 *                          - order: campo di ordinamento dei risultati
	 * @static
	 * @access public
	 * @return array array associativo id=>name delle categorie selezionate
	 */
	public static function getForSelect($instance, $opts=null) {

		$where = "instance='".$instance."'".(isset($opts['where']) ? " AND ".$opts['where'] : '');
		$order = isset($opts['order']) ? $opts['order'] : 'name';

		$res = array();

		$db = db::instance();
		$query = "SELECT id, name FROM ".self::$_tbl_service." WHERE $where ORDER BY $order";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[$b['id']] = htmlChars($b['name']);
		}

		return $res;

	}

	/**
	 * @brief Form di inserimento e modifica dati categoria 
	 * 
	 * @param string $redirect url di reindirizzamento se il salvataggio va a buon fine
	 * @access public
	 * @return string form di inserimento e modifica dati categoria
	 */
	public function form($redirect) {

		if(isset($_POST['submit'])) {
			$gform = new Form('form_service', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				$this->name = cleanVar($_POST, 'name', 'string', '');
				$this->description = cleanVar($_POST, 'description', '');
				$this->updateDbData();

				$old_icon = cleanVar($_POST, 'old_icon', 'string', '');

				$res = $gform->manageFile('icon', $old_icon, true, $this->_extension_media, $this->_data_dir.OS, preg_replace("#action=insert#", 'action=modify&id='.$this->id, $_SERVER['QUERY_STRING']), $this->_tbl_data, 'icon', 'id', $this->id, array('prefix_file'=>$this->_prefix_img, 'prefix_thumb'=>$this->_prefix_thumb, 'width'=>$this->_img_width, 'thumb_width'=>$this->_thumb_width));

				header("Location: ".$redirect);
				exit();
			}
			else {
				error::errorMessage(array('error'=>1), $_SERVER['QUERY_STRING']);
				exit();
			}
		}

		if($this->id) {
			$title = _("Modifica servizio");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuovo servizio");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_service', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$buffer = $gform->form('', true, 'name', array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('name', 'text', $gform->retvar('name', htmlInput($this->name)), _("Nome"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'name'));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array('cols'=>40, 'rows'=>4, 'trnsl'=>true, 'field'=>'description'));

		$buffer .= $gform->cfile('icon', $this->icon, _("Icona"), array("extensions"=>$this->_extension_media, "del_check"=>true, "preview"=>true, "previewSrc"=>$this->_data_www.'/'.$this->_prefix_img.$this->icon));
		$buffer .= $gform->cinput('submit', 'submit', $submit, '', array('classField'=>'submit'));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Elimina il servizio 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 * 
	 */
	public function delete() {

		$points = gmapsPoint::getByService($this->id);

		if(count($points)) {
			return 'points';
		}
		else {
			if($this->icon) {
				@unlink($this->_data_dir.OS.$this->_prefix_img.$this->icon);	
				@unlink($this->_data_dir.OS.$this->_prefix_thumb.$this->icon);	
			}
	
			return $this->deleteDbData();
		}

	}

}

?>

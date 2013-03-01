<?php
/**
 * @file class.gmapsMarker.php
 * @brief Contiene la definizione ed implementazione della classe gmapsMarker.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione dei markers
 *
 * Campi:
 *
 * - **id**: identificativo punto di interesse
 * - **instance**: identificativo @ref gmaps di appartenenza 
 * - **label**: etichetta
 * - **icon**: icona
 * - **shadow**: icona ombreggiatura
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsMarker extends propertyObject {

	private static $_tbl_marker = 'gmaps_marker';

	private $_controller, $_data_www, $_data_dir, $_extension_media;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsMarker 
	 * 
	 * @param int $id id del marker
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsService un oggetto gmapsMarker
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$_tbl_marker;
		parent::__construct($id);

		$this->_extension_media = array('png');
		$this->_data_www = CONTENT_WWW.'/gmaps/'.$this->_controller->instanceName().'/marker';
		$this->_data_dir = CONTENT_DIR.OS.'gmaps'.OS.$this->_controller->instanceName().OS.'marker';
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
		$query = "DELETE FROM ".self::$_tbl_marker." WHERE instance='$instance'";
		$res = $db->actionquery($query);

		return $res;

	}

	/**
	 * @brief Seleziona id di categorie sul database 
	 * 
	 * @param int $instance identificativo dell'istanza di @ref gmaps
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
		$order = isset($opts['order']) ? $opts['order'] : 'label';
		$limit = isset($opts['limit']) ? "LIMIT ".$opts['limit'] : '';

		$res = array();

		$db = db::instance();
		$query = "SELECT id FROM ".self::$_tbl_marker." WHERE $where ORDER BY $order $limit";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[] = $b['id'];
		}

		return $res;

	} 

	/**
	 * @brief Ritorna un array associativo di categorie da utilizzare per popolare un input select 
	 * 
	 * @param int $instance identificativo dell'istanza di @ref gmaps
	 * @param array $opts array associativo di opzioni:
	 *                          - where: where clauses addizionali 
	 *                          - order: campo di ordinamento dei risultati
	 * @static
	 * @access public
	 * @return array array associativo id=>name delle categorie selezionate
	 */
	public static function getForSelect($instance, $opts=null) {

		$where = "instance='".$instance."'".(isset($opts['where']) ? " AND ".$opts['where'] : '');
		$order = isset($opts['order']) ? $opts['order'] : 'label';

		$res = array();

		$db = db::instance();
		$query = "SELECT id, label FROM ".self::$_tbl_marker." WHERE $where ORDER BY $order";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[$b['id']] = htmlChars($b['label']);
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
			$gform = new Form('form_marker', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				$this->label = cleanVar($_POST, 'label', 'string', '');
				$this->description = cleanVar($_POST, 'description', 'string', '');
				$this->updateDbData();

				$old_icon = cleanVar($_POST, 'old_icon', 'string', '');
				$old_shadow = cleanVar($_POST, 'old_shadow', 'string', '');

				$res = $gform->manageFile('icon', $old_icon, false, $this->_extension_media, $this->_data_dir.OS, preg_replace("#action=insert#", 'action=modify&id='.$this->id, $_SERVER['QUERY_STRING']), $this->_tbl_data, 'icon', 'id', $this->id, array());
				$res = $gform->manageFile('shadow', $old_shadow, false, $this->_extension_media, $this->_data_dir.OS, preg_replace("#action=insert#", 'action=modify&id='.$this->id, $_SERVER['QUERY_STRING']), $this->_tbl_data, 'shadow', 'id', $this->id, array());

				header("Location: ".$redirect);
				exit();
			}
			else {
				error::errorMessage(array('error'=>1), $_SERVER['QUERY_STRING']);
				exit();
			}
		}

		if($this->id) {
			$title = _("Modifica marker");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuovo marker");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_marker', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$buffer = $gform->form('', true, 'label', array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'label'));

		$buffer .= $gform->cfile('icon', $this->icon, _("Icona"), array("extensions"=>$this->_extension_media, "del_check"=>true, "preview"=>true, "previewSrc"=>$this->_data_www.'/'.$this->icon));
		$buffer .= $gform->cfile('shadow', $this->shadow, _("Ombra"), array("extensions"=>$this->_extension_media, "del_check"=>true, "preview"=>true, "previewSrc"=>$this->_data_www.'/'.$this->shadow));
		$buffer .= $gform->cinput('submit', 'submit', $submit, '', array('classField'=>'submit'));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief relative url of the marker's icon 
	 * 
	 * @access public
	 * @return mixed icon url o null
	 */
	public function iconUrl() {
	
		return $this->icon ? $this->_data_www.'/'.$this->icon : null;

	}

	/**
	 * @brief relative url of the shadow's icon 
	 * 
	 * @access public
	 * @return mixed shadow url o null
	 */
	public function shadowUrl() {
	
		return $this->shadow ? $this->_data_www.'/'.$this->shadow : null;

	}

	/**
	 * @brief Elimina il servizio 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 * 
	 */
	public function delete() {

		$points = gmapsPoint::getByMarker($this->id);

		if(count($points)) {
			return 'points';
		}
		else {
			if($this->icon) {
				@unlink($this->_data_dir.OS.$this->icon);	
			}
			if($this->shadow) {
				@unlink($this->_data_dir.OS.$this->shadow);	
			}

			return $this->deleteDbData();
		}

	}

}

?>

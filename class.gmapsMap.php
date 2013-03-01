<?php
/**
 * @file class.gmapsMap.php
 * @brief Contiene la definizione ed implementazione della classe gmapsMap.
 *
 * @version1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione di mappe
 *
 * Le mappe sono oggetti contenitori che raggruppano punti e/o percorsi e/o aree tematicamente collegati
 *
 * Campi:
 * - **id**: identificativo mappa 
 * - **instance**: identificativo @ref gmaps di appartenenza 
 * - **name**: nome mappa 
 * - **description**: descrizione
 * - **width**: larghezza
 * - **height**: altezza
 *
 * Proprietà relazionali:
 *
 *- _points_id: array di id di punti associati al percorso   
 *- _points: array di punti associati alla mappa   
 *- _polylines_id: array di id di percorsi associati alla mappa   
 *- _polylines: array di percorsi associati alla mappa   
 *- _polygons_id: array di id di aree associate alla mappa   
 *- _polygons: array di aree associate alla mappa   
 *
 * @version 1.0
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 * @date 2012
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 *
 */
class gmapsMap extends propertyObject {

    public static $_tbl_map = "gmaps_map";
    public static $_tbl_map_point = "gmaps_map_point";
    public static $_tbl_map_polyline = "gmaps_map_polyline";
    public static $_tbl_map_polygon = "gmaps_map_polygon";

	private $_controller;

	/**
	 * @brief Array di punti (@ref gmapPoint) associati alla mappa
	 */
	private $_points, $_points_id;

	/**
	 * @brief Array di percorsi (@ref gmapPolyline) associati alla mappa
	 */
	private $_polylines, $_polylines_id;

	/**
	 * @brief Array di aree (@ref gmapPolygon) associate alla mappa
	 */
	private $_polygons, $_polygons_id;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsMap 
	 * 
	 * @param int $id id della mappa 
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsMap un oggetto gmapsMap
	 */
	function __construct($id, $instance) {
	
		$this->_controller = $instance;

		$this->_tbl_data = self::$_tbl_map;
		parent::__construct($id);
		$this->_points = array();
		$this->_points_id = array();
		$this->_polylines = array();
		$this->_polylines_id = array();
		$this->_polygons = array();
		$this->_polygons_id = array();
		$query = "SELECT point_id FROM ".self::$_tbl_map_point." WHERE map_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_points[] = new gmapsPoint($b['point_id'], $this->_controller);	
				$this->_points_id[] = $b['point_id'];	
			}
		}
		$query = "SELECT polyline_id FROM ".self::$_tbl_map_polyline." WHERE map_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_polylines[] = new gmapsPolyline($b['polyline_id'], $this->_controller);	
				$this->_polylines_id[] = $b['polyline_id'];	
			}
		}
		$query = "SELECT polygon_id FROM ".self::$_tbl_map_polygon." WHERE map_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_polygons[] = new gmapsPolygon($b['polygon_id'], $this->_controller);	
				$this->_polygons_id[] = $b['polygon_id'];	
			}
		}
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
		$query = "DELETE FROM ".self::$_tbl_map." WHERE instance='$instance'";
		$res = $db->actionquery($query);

		return $res;

	}

	/**
	 * @brief Restituisce gli id dei punti di intresse associati alla mappa 
	 * 
	 * @access public
	 * @return array array di id di punti di interesse
	 */
	public function points_id() {

		return $this->_points_id;

	}

	/**
	 * @brief Restituisce i punti di intresse associati alla mappa 
	 * 
	 * @access public
	 * @return array array di oggetti @reg gmapsPoint
	 */
	public function points() {

		return $this->_points;

	}

	/**
	 * @brief Restituisce gli id dei percorsi associati alla mappa 
	 * 
	 * @access public
	 * @return array array di id di percorsi
	 */
	public function polylines_id() {

		return $this->_polylines_id;

	}

	/**
	 * @brief Restituisce i percorsi associati alla mappa 
	 * 
	 * @access public
	 * @return array array di oggetti @reg gmapsPolyline
	 */
	public function polylines() {

		return $this->_polylines;

	}

	/**
	 * @brief Restituisce gli id delle aree associate alla mappa 
	 * 
	 * @access public
	 * @return array array di id di aree
	 */
	public function polygons_id() {

		return $this->_polygons_id;

	}

	/**
	 * @brief Restituisce le aree associate alla mappa 
	 * 
	 * @access public
	 * @return array array di oggetti @reg gmapsPolygon
	 */
	public function polygons() {

		return $this->_polygons;

	}


	/**
	 * @brief Associa i punti dati alla mappa 
	 * 
	 * @param mixed $point_ids array di id di punti oppure un unico id
	 * @access public
	 * @return bool il risultato dell'operazione
	 */
	public function addPoints($point_ids) {

		if(!is_array($point_ids)) $points = array($point_ids);

		$values = array();
		foreach($point_ids as $pid) {
			$values[] = "('".$this->id."', '".$pid."')";
		}
		
		$db = db::instance();
		$query = "INSERT INTO ".self::$_tbl_map_point." (map_id, point_id) VALUES ".implode(',', $values).";";

		return $db->actionquery($query);
	}

	/**
	 * @brief Associa i percorsi dati alla mappa 
	 * 
	 * @param mixed $polyline_ids array di id di percorsi oppure un unico id
	 * @access public
	 * @return bool il risultato dell'operazione
	 */
	public function addPolylines($polyline_ids) {

		if(!is_array($polyline_ids)) $points = array($polyline_ids);

		$values = array();
		foreach($polyline_ids as $pid) {
			$values[] = "('".$this->id."', '".$pid."')";
		}
		
		$db = db::instance();
		$query = "INSERT INTO ".self::$_tbl_map_polyline." (map_id, polyline_id) VALUES ".implode(',', $values).";";

		return $db->actionquery($query);
	}

	/**
	 * @brief Associa le aree date alla mappa 
	 * 
	 * @param mixed $polygon_ids array di id di aree oppure un unico id
	 * @access public
	 * @return bool il risultato dell'operazione
	 */
	public function addPolygons($polygon_ids) {

		if(!is_array($polygon_ids)) $points = array($polygon_ids);

		$values = array();
		foreach($polygon_ids as $pid) {
			$values[] = "('".$this->id."', '".$pid."')";
		}
		
		$db = db::instance();
		$query = "INSERT INTO ".self::$_tbl_map_polygon." (map_id, polygon_id) VALUES ".implode(',', $values).";";

		return $db->actionquery($query);
	}


	/**
	 * @brief Disassocia i punti di interesse dati dalla mappa 
	 * 
	 * @param mixed $point_ids array di id di punti oppure un unico id
	 * @access public
	 * @return bool il risultato dell'operazione
	 */
	public function removePoints($point_ids) {

		if(!is_array($point_ids)) $points = array($point_ids);

		$values = array();
		foreach($point_ids as $pid) {
			$values[] = "point_id='".$pid."'";
		}
		
		$db = db::instance();
		$query = "DELETE FROM ".self::$_tbl_map_point." WHERE map_id='".$this->id."' AND (".implode(" OR ", $values).")";

		return $db->actionquery($query);
	}

	/**
	 * @brief Disassocia i percorsi dati dalla mappa 
	 * 
	 * @param mixed $polyline_ids array di id di percorsi oppure un unico id
	 * @access public
	 * @return bool il risultato dell'operazione
	 */
	public function removePolylines($polyline_ids) {

		if(!is_array($polyline_ids)) $points = array($polyline_ids);

		$values = array();
		foreach($polyline_ids as $pid) {
			$values[] = "polyline_id='".$pid."'";
		}
		
		$db = db::instance();
		$query = "DELETE FROM ".self::$_tbl_map_polyline." WHERE map_id='".$this->id."' AND (".implode(" OR ", $values).")";

		return $db->actionquery($query);
	}

	/**
	 * @brief Disassocia le aree date dalla mappa 
	 * 
	 * @param mixed $polyline_ids array di id di aree oppure un unico id
	 * @access public
	 * @return bool il risultato dell'operazione
	 */
	public function removePolygons($polygon_ids) {

		if(!is_array($polygon_ids)) $points = array($polygon_ids);

		$values = array();
		foreach($polygon_ids as $pid) {
			$values[] = "polygon_id='".$pid."'";
		}
		
		$db = db::instance();
		$query = "DELETE FROM ".self::$_tbl_map_polygon." WHERE map_id='".$this->id."' AND (".implode(" OR ", $values).")";

		return $db->actionquery($query);
	}


	/**
	 * @brief Seleziona id di mappe sul database 
	 * 
	 * @param int $instance identificativo dell'istanza di @ref gmaps
	 * @param array $opts array associativo di opzioni:
	 *                          - where: where clauses addizionali 
	 *                          - order: campo di ordinamento dei risultati
	 *                          - limit: limitazione dei risultati
	 * @static
	 * @access public
	 * @return array un array di id dei percorsi ottenuti dalla query
	 */
	public static function get($instance, $opts=null) {

		$where = "instance='".$instance."'".((isset($opts['where']) && $opts['where']) ? " AND ".$opts['where'] : '');
		$order = isset($opts['order']) ? $opts['order'] : 'name';
		$limit = isset($opts['limit']) ? "LIMIT ".$opts['limit'] : '';

		$res = array();

		$db = db::instance();
		$query = "SELECT id FROM ".self::$_tbl_map." WHERE $where ORDER BY $order $limit";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[] = $b['id'];
		}

		return $res;

	}

	/**
	 * @brief Form di inserimento e modifica mappa 
	 * 
	 * @param string $redirect url di reindirizzamento se il salvataggio va a buon fine
	 * @access public
	 * @return string form di inserimento e modifica dati mappa
	 */
	public function form($redirect) {

		if(isset($_POST['submit'])) {
			$gform = new Form('form_map', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				$this->name = cleanVar($_POST, 'name', 'string', '');
				$this->description = cleanVarEditor($_POST, 'description', '');
				$this->width = cleanVar($_POST, 'width', 'string', '');
				$this->height = cleanVar($_POST, 'height', 'string', '');
				$this->updateDbData();
				header("Location: ".$redirect);
				exit();
			}
			else {
				exit(error::errorMessage(array('error'=>1), $_SERVER['QUERY_STRING']));
			}
		}

		if($this->id) {
			$title = _("Modifica mappa");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuova mappa");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_map', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$required = "name,width,height";
		$buffer = $gform->form('', false, $required, array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('name', 'text', $gform->retvar('name', htmlInput($this->name)), _("Nome"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'name'));
		$buffer .= $gform->fcktextarea('description', $gform->retvar('description', htmlInputEditor($this->description)), _("Descrizione"), array('fck_toolbar'=>"Default", 'trnsl'=>true, 'field'=>'description'));
		$buffer .= $gform->cinput('width', 'text', $gform->retvar('width', htmlInput($this->width)), array(_("Larghezza"), _("in px o percentuale<br />es: 500px, 98%")), array('required'=>true, 'maxlength'=>32, 'size'=>6));
		$buffer .= $gform->cinput('height', 'text', $gform->retvar('height', htmlInput($this->height)), array(_("Altezza"), _("in px o percentuale<br />es: 500px, 98%")), array('required'=>true, 'maxlength'=>32, 'size'=>6));
		$buffer .= $gform->cinput('submit', 'submit', $submit, '', array('classField'=>'submit'));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	/**
	 * @brief Elimina la mappa 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 * 
	 */
	public function delete() {

		$db = db::instance();

		// delete point associations
		$query = "DELETE FROM ".self::$_tbl_map_point." WHERE map_id='".$this->id."'";
		$res = $db->actionquery($query);

		// delete polyline associations
		$query = "DELETE FROM ".self::$_tbl_map_polyline." WHERE map_id='".$this->id."'";
		$res = $db->actionquery($query);

		return $this->deleteDbData();

	}

}

?>

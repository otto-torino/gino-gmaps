<?php
/**
 * @file class.gmapsPolyline.php
 * @brief Contiene la definizione ed implementazione della classe gmapsPolyline.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione di percorsi geolocalizzati
 *
 * Campi:
 *
 * - **id**: identificativo percorso
 * - **instance**: identificativo @ref gmaps di appartenenza 
 * - **label**: etichetta
 * - **description**: descrizione
 * - **lat**: latitudini dei punti del percorso separate da virgole
 * - **lng**: longitudini dei punti del percorso separate da virgole
 * - **color**: colore della polyline visualizzata in mappa
 * - **width**: spessore (px) della polyline visualizzata in mappa
 *
 * Proprietà:
 *
 * - **_ctgs_id**: array di id di categorie associate al percorso
 * - **_ctgs**: array di categorie associate al percorso
 * - **_points_id**: array di punti associati al percorso
 * - **_points**: array di id di punti associati al percorso
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsPolyline extends propertyObject {

       	public static $_tbl_polyline = "gmaps_polyline";
       	public static $_tbl_polyline_point = "gmaps_polyline_point";
       	public static $_tbl_polyline_polyline_ctg = "gmaps_polyline_polyline_ctg";

	private $_controller;

	/**
	 * @brief Array di punti (@ref gmapPoint) associati al percorso
	 */
	private $_points, $_points_id;

	/**
	 * @brief Array di categorie (@ref gmapPolylineCtg) associate al percorso
	 */
	private $_ctgs, $_ctgs_id;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsPolyline 
	 * 
	 * @param int $id id del percorso
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsPolyline un oggetto gmapsPolyline
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$_tbl_polyline;
		parent::__construct($id);

		$this->_points = array();
		$this->_points_id = array();
		$query = "SELECT point_id FROM ".self::$_tbl_polyline_point." WHERE polyline_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_points[] = new gmapsPoint($b['point_id'], $this->_controller);	
				$this->_points_id[] = $b['point_id'];	
			}
		}

		$this->_ctgs_id = array();
		$this->_ctgs = array();
		$query = "SELECT ctg_id FROM ".self::$_tbl_polyline_polyline_ctg." WHERE polyline_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_ctgs[] = new gmapsPolylineCtg($b['ctg_id'], $this->_controller);	
				$this->_ctgs_id[] = $b['ctg_id'];	
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

		// delete polyline point association
		$query = "DELETE FROM ".self::$_tbl_polyline_point." WHERE polyline_id IN (SELECT id FROM ".self::$_tbl_polyline." WHERE instance='$instance')";
		$res = $db->actionquery($query);

		// delete polyline ctg association
		$query = "DELETE FROM ".self::$_tbl_polyline_polyline_ctg." WHERE polyline_id IN (SELECT id FROM ".self::$_tbl_polyline." WHERE instance='$instance')";
		$res = $res && $db->actionquery($query);

		// delete polylines
		$query = "DELETE FROM ".self::$_tbl_polyline." WHERE instance='$instance'";
		$res = $res && $db->actionquery($query);

		return $res;

	}

	/**
	 * @brief Seleziona id di percorsi sul database 
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
		$order = isset($opts['order']) ? $opts['order'] : 'label';
		$limit = isset($opts['limit']) ? "LIMIT ".$opts['limit'] : '';

		$res = array();

		$db = db::instance();
		$query = "SELECT id FROM ".self::$_tbl_polyline." WHERE $where ORDER BY $order $limit";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[] = $b['id'];
		}

		return $res;

	}

	/**
	 * @brief Seleziona id di percorsi associati alla categoria data
	 * 
	 * @param int $ctg_id id della categoria
	 * @static
	 * @access public
	 * @return array un array di id dei percorsi associati alla categoria
	 */
	public static function getByCtg($ctg_id) {

		$res = array();

		$db = db::instance();
		$query = "SELECT polyline_id FROM ".self::$_tbl_polyline_polyline_ctg." WHERE ctg_id='$ctg_id'";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[] = $b['polyline_id'];
		}

		return $res;

	}

	/**
	 * @brief Getter della proprietà _points, tutti i punti di interesse collegati al percorso 
	 * 
	 * @access public
	 * @return array array di oggetti @reg gmapsPoint
	 */
	public function points() {

		return $this->_points;

	}

	/**
	 * @brief Getter della proprietà _points_id, tutti gli id dei punti di interesse collegati al percorso 
	 * 
	 * @access public
	 * @return array array di id di punti di interesse
	 */
	public function points_id() {

		return $this->_points_id;

	}

	/**
	 * @brief Getter della proprietà _ctgs, tutte le categorie collegate al percorso 
	 * 
	 * @access public
	 * @return array array di oggetti @reg gmapsPolylineCtg
	 */
	public function ctgs() {

		return $this->_ctgs;

	}

	/**
	 * @brief Associa i punti dati al percorso 
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
		$query = "INSERT INTO ".self::$_tbl_polyline_point." (polyline_id, point_id) VALUES ".implode(',', $values).";";

		return $db->actionquery($query);
	}

	/**
	 * @brief Disassocia i punti di interesse dati al percorso 
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
		$query = "DELETE FROM ".self::$_tbl_polyline_point." WHERE polyline_id='".$this->id."' AND (".implode(" OR ", $values).")";

		return $db->actionquery($query);
	}

	/**
	 * @brief Form di inserimento e modifica percorso 
	 * 
	 * @param string $redirect url di reindirizzamento se il salvataggio va a buon fine
	 * @access public
	 * @return string form di inserimento e modifica dati percorso
	 */
	public function form($redirect) {

		if(isset($_POST['submit'])) {
			$gform = new Form('form_polyline', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				$ctgs_id = cleanVar($_POST, 'ctg', 'array', '');
				$this->label = cleanVar($_POST, 'label', 'string', '');
				$this->lat = cleanVar($_POST, 'lat', 'string', '');
				$this->lng = cleanVar($_POST, 'lng', 'string', '');
				$this->description = cleanVarEditor($_POST, 'description', '');
				$this->color = cleanVar($_POST, 'color', 'string', '');
				$this->width = cleanVar($_POST, 'width', 'int', '');
				$this->updateDbData();
				$this->saveCtgs($ctgs_id);
				header("Location: ".$redirect);
				exit();
			}
			else {
				if(!($_POST['lat'] && $_POST['lng'])) {
					exit(error::errorMessage(array('error'=>_("Rappresentare il percorso sulla mappa seguendo le istruzioni.")), $_SERVER['QUERY_STRING']));
				}
				exit(error::errorMessage(array('error'=>1), $_SERVER['QUERY_STRING']));
			}
		}

		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
		$registry->addJs("http://ajs.otto.to.it/sources/0.1/ajs/ajs.js");

		if($this->id) {
			$title = _("Modifica percorso");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuovo percorso");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_polyline', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$required = "label,ctg,lat,lng";
		$buffer = $gform->form('', false, $required, array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->hidden('lat', $this->lat, array('id'=>'lat'));
		$buffer .= $gform->hidden('lng', $this->lng, array('id'=>'lng'));

		$buffer .= $gform->multipleCheckbox('ctg[]', $gform->retvar('ctg', $this->_ctgs_id), gmapsPolylineCtg::getForSelect($this->instance), _("Categorie"), array('required'=>true));
		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'label'));
		$buffer .= $gform->cell($this->formMap());
		$buffer .= $gform->fcktextarea('description', $gform->retvar('description', htmlInputEditor($this->description)), _("Descrizione"), array('fck_toolbar'=>"Default", 'trnsl'=>true, 'field'=>'description'));
		$buffer .= $gform->cinput('color', 'text', $gform->retvar('color', htmlInput($this->color)), array(_("Colore"), _("codice esadecimale, es. #ff0000")), array('required'=>true, 'maxlength'=>7, 'size'=>7, 'pattern'=>'^#[0-9abcdefABCDEF]{6}$', 'hint'=>_("Inserire il colore in esadecimale")));
		$buffer .= $gform->cinput('width', 'text', $gform->retvar('width', htmlInput($this->width)), array(_("Spessore tracciato"), _("in px")), array('required'=>true, 'maxlength'=>2, 'size'=>2, 'pattern'=>'^[0-9]*$', 'hint'=>_("Inserire un numero intero")));
		$buffer .= $gform->cinput('submit', 'submit', $submit, '', array('classField'=>'submit'));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	/**
	 * @brief Widget per la selezione di un percorso geolocalizzato direttamente su google map 
	 * 
	 * @access private
	 * @return string widget
	 */
	private function formMap() {

		$buffer = "<p>Per geolocalizzare il percorso di interesse utilizzare lo strumento mappa qui sotto. Seguire la seguente procedura:</p>";
		$buffer .= "<ol>";
		$buffer .= "<li>"._("Premere il pulsante 'polyline'")."</li>";
		$buffer .= "<li>"._("Cliccare nel punto desiderato sulla mappa per settare ogni punto del percorso. Se si dispone di un indirizzo si può utilizzare il campo di testo, scrivere l'indirizzo e poi premere 'draw'. E' possibile modificare direttamente il percorso agendo sui controlli.")."</li>";
		$buffer .= "<li>"._("Quando il percorso tracciato corrisponde a quello desiderato permere il pulsante 'export map'.")."</li>";
		$buffer .= "</ol>";
		$buffer .= "<div id=\"map_canvas\" style=\"width:100%; height: 300px;\"></div>";

		$buffer .= "<script>";
		$buffer .= "ajs.use(['ajs.maps.gmapdraw'], function() {

				fillFields = function(data) {
					var lat = [];
					var lng = [];
					for(var i = 0, tot = data.polyline[0].length; i < tot; i++) {
						lat.push(data.polyline[0][i].lat);
						lng.push(data.polyline[0][i].lng);
					}
					$('lat').set('value', lat.join(','));
					$('lng').set('value', lng.join(','));
					alert('".jsVar(_("Il percorso è stato settato correttamente, prosegui con la compilazione del form"))."');
				}
	
				mymap = new ajs.maps.gmapdraw.map('map_canvas', {
					zoom: 10,
					export_map_callback: fillFields,
					tools: {'polyline': { options: { max_items_allowed: 1 }}},
					tips_map_ctrl: null
				});
				mymap.render();

				if($('lat').value && $('lng').value) {
		
					var lat = $('lat').value.split(',');
					var lng = $('lng').value.split(',');

					var parr = [];
					for(var i = 0, tot = lat.length; i < tot; i++) {
						parr.push({lat: lat[i], lng: lng[i]});
					} 
					
					var data = {polyline: [parr]};
					mymap.importMap(data);
					mymap.gmap().setCenter(new google.maps.LatLng(lat[(Math.round(lat.length/2))], lng[(Math.round(lng.length/2))]));
				}


			});"; 
		$buffer .= "</script>";

		return $buffer;

	}

	/**
	 * @brief Associa le categorie date al percorso 
	 * 
	 * @param array $ctgs_id array di id di categorie da associare
	 * @access private
	 * @return bool il risultato dell'operazione
	 */
	private function saveCtgs($ctgs_id) {

		$db = db::instance();
		$query = "DELETE FROM ".self::$_tbl_polyline_polyline_ctg." WHERE polyline_id='".$this->id."'";
		$res = $db->actionquery($query);

		$values = array();
		foreach($ctgs_id as $ctg_id) {
			$values[] = "('".$this->id."', '".$ctg_id."')";
		}
		$query = "INSERT INTO ".self::$_tbl_polyline_polyline_ctg." (polyline_id, ctg_id) VALUES ".implode(',', $values).";";
		$res = $db->actionquery($query);

		return $res;

	}

	/**
	 * @brief Restituisce il codice js necessario a disegnare il percorso sulla mappa data 
	 * 
	 * @param string $map il nome della variabile javascript che referenzia la google map 
	 * @param string $bounds il nome della variabile javascript che referenzia i bounds della mappa 
	 * @access public
	 * @return string codice javascript che disegna la polyline
	 */
	public function gmapCode($map, $bounds) {

		$buffer = "var polyline_path_".$this->id." = new google.maps.MVCArray();";
		$lats = explode(',', $this->lat);		
		$lngs = explode(',', $this->lng);		
		for($i=0; $i<count($lats); $i++) {
			$lat = $lats[$i];
			$lng = $lngs[$i];
			$buffer .= "polyline_path_".$this->id.".push(new google.maps.LatLng($lat, $lng));";
			$buffer .= $bounds.".extend(new google.maps.LatLng($lat, $lng));";
		}
		$buffer .= " var polyline_".$this->id." = new google.maps.Polyline({path: polyline_path_".$this->id.", map:$map});";

		foreach($this->_points as $point) {
			$buffer .= $point->gmapCode($map, $bounds);
		}

		return $buffer;
	}

	/**
	 * @brief Restituisce il percorso sotto forma di oggetto javascript 
	 * 
	 * @param string $map_obj oggetto javascript di tipo Gmap 
	 * @param int $map_id id della mappa @ref gmapsMap 
	 * @access public
	 * @return string oggetto javascript
	 */
	public function jsObject($map_obj, $map_id) {
	
		$db = db::instance();
		
		$buffer = '';

        $categories = array();
        foreach($this->_ctgs as $ctg) {
            $categories[] = jsVar($ctg->ml('name'));
        }

		$fields = "{
				id: '".$this->id."',		
				label: '".jsVar($this->ml('label'))."',		
				categories: '".implode(', ', $categories)."',		
				description: '".jsVar(cutHtmlText(HtmlChars($this->ml('description')), 120, '...', false, false, false, null))."',		
				lat: '".jsVar($this->lat)."',		
				lng: '".jsVar($this->lng)."',
				color: '".$this->color."',
				width: '".$this->width."'
		}";


		$info_url = HOME_FILE.'?pt['.$this->_controller->instanceName().'-infowindow]&polyline_id='.$this->id.'&map_id='.$map_id;

		$buffer .= "new GmapPolyline($fields, [".implode(',', $this->_ctgs_id)."], $map_obj, '$info_url');";

		return $buffer;

	}

	/**
	 * @brief Elimina il percorso 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 * 
	 */
	public function delete() {

		$db = db::instance();

		// delete ctg association
		$query = "DELETE FROM ".self::$_tbl_polyline_polyline_ctg." WHERE polyline_id='".$this->id."'";
		$res = $db->actionquery($query);

		// delete point association
		$query = "DELETE FROM ".self::$_tbl_polyline_point." WHERE polyline_id='".$this->id."'";
		$res = $res && $db->actionquery($query);

		$res = $res && $this->deleteDbData();

		return $res;

	}
}

?>

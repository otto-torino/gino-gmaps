<?php
/**
 * @file class.gmapsPoint.php
 * @brief Contiene la definizione ed implementazione della classe gmapsPoint.
 *
 * @version1.0.1
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione di punti di interesse geolocalizzati
 *
 * Campi:
 *
 * - **id**: identificativo punto di interesse
 * - **insert_date**: datetime inserimento
 * - **last_edit_date**: datetime ultima modifica
 * - **instance**: identificativo @ref gmaps di appartenenza 
 * - **label**: etichetta
 * - **address**: indirizzo
 * - **cap**: cap
 * - **city**: città
 * - **nation**: città
 * - **marker**: marker utilizzato per la visualizzazione
 * - **description**: descrizione
 * - **information**: informazioni
 * - **phone**: telefono
 * - **email**: email
 * - **web**: sito web
 * - **opening_hours**: orari di apertura
 * - **lat**: latitudine
 * - **lng**: longitudine
 * - **updating**: disponibilità aggiornamento attraverso codice
 * - **updating_email**: email di invio codice aggiornamento
 * - **updating_code**: codice aggiornamento
 *
 * Proprietà:
 *
 * - **_ctgs_id**: array di id di categorie associate al punto di interesse
 * - **_ctgs**: array di categorie associate al punto di interesse
 * - **_services_id**: array di id di servizi associati al punto di interesse
 * - **_services**: array di servizi associati al punto di interesse
 * - **_images**: array di immagini associate al punto di interesse
 * - **_videos**: array di video associati al punto di interesse
 * - **_events**: array di eventi associati al punto di interesse
 * - **_attachments**: array di allegati associati al punto di interesse
 * - **_collections**: array di collezioni associate al punto di interesse
 *
 * @version1.0.1
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsPoint extends propertyObject {

       	private static $_tbl_point = "gmaps_point";
       	private static $_tbl_point_image = "gmaps_point_image";
       	private static $_tbl_point_video = "gmaps_point_video";
       	private static $_tbl_point_event = "gmaps_point_event";
       	private static $_tbl_point_attachment = "gmaps_point_attachment";
       	private static $_tbl_point_collection = "gmaps_point_collection";
       	private static $_tbl_point_collection_image = "gmaps_point_collection_image";
       	private static $_tbl_point_service = "gmaps_point_service";
       	private static $_tbl_point_point_ctg = "gmaps_point_point_ctg";
        private static $_tbl_map_point = 'gmaps_map_point';

	private $_controller;

	/**
	 * @brief Array di categorie (@ref gmapsPointCtg) associate al punto di interesse
	 */
	private $_ctgs, $_ctgs_id;

	/**
	 * @brief Array di servizi (@ref gmaspService) associati al punto di interesse
	 */
	private $_services, $_services_id;

	/**
	 * @brief Array di immagini (@ref gmapsImage) associate al punto di interesse
	 */
	private $_images;

	/**
	 * @brief Array di video (@ref gmapsVideo) associati al punto di interesse
	 */
	private $_videos;

	/**
	 * @brief Array di eventi (@ref gmapsEvent) associati al punto di interesse
	 */
	private $_events;

	/**
	 * @brief Array di allegati (@ref gmapsAttachment) associati al punto di interesse
	 */
	private $_attachments;

	/**
	 * @brief Array di collezioni (@ref gmapsCollection) associate al punto di interesse
	 */
	private $_collections;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsPoint 
	 * 
	 * @param int $id id del punto di interesse
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsPoint un oggetto gmapsPoint
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
	
		$this->_tbl_data = self::$_tbl_point;
		parent::__construct($id);

		$this->_ctgs_id = array();
		$this->_ctgs = array();
		$query = "SELECT ctg_id FROM ".self::$_tbl_point_point_ctg." WHERE point_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_ctgs[] = new gmapsPointCtg($b['ctg_id'], $this->_controller);	
				$this->_ctgs_id[] = $b['ctg_id'];	
			}
		}

		$this->_services_id = array();
		$this->_services = array();
		$query = "SELECT service_id FROM ".self::$_tbl_point_service." WHERE point_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_services[] = new gmapsService($b['service_id'], $this->_controller);	
				$this->_services_id[] = $b['service_id'];	
			}
		}

		$this->_images = array();
		$query = "SELECT id FROM ".self::$_tbl_point_image." WHERE point_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_images[] = new gmapsImage($b['id'], $this->_controller);	
			}
		}

		$this->_videos = array();
		$query = "SELECT id FROM ".self::$_tbl_point_video." WHERE point_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_videos[] = new gmapsVideo($b['id'], $this->_controller);	
			}
		}

		$this->_events = array();
		$query = "SELECT id FROM ".self::$_tbl_point_event." WHERE point_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_events[] = new gmapsEvent($b['id'], $this->_controller);	
			}
		}

		$this->_attachments = array();
		$query = "SELECT id FROM ".self::$_tbl_point_attachment." WHERE point_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_attachments[] = new gmapsAttachment($b['id'], $this->_controller);	
			}
		}

		$this->_collections = array();
		$query = "SELECT id FROM ".self::$_tbl_point_collection." WHERE point_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_collections[] = new gmapsCollection($b['id'], $this->_controller);	
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

		// delete point ctg association
		$query = "DELETE FROM ".self::$_tbl_point_point_ctg." WHERE point_id IN (SELECT id FROM ".self::$_tbl_point." WHERE instance='$instance')";
		$res = $db->actionquery($query);

		// delete point service association
		$query = "DELETE FROM ".self::$_tbl_point_service." WHERE point_id IN (SELECT id FROM ".self::$_tbl_point." WHERE instance='$instance')";
		$res = $res && $db->actionquery($query);

		// delete point image association
		$query = "DELETE FROM ".self::$_tbl_point_image." WHERE point_id IN (SELECT id FROM ".self::$_tbl_point." WHERE instance='$instance')";
		$res = $res && $db->actionquery($query);

		// delete point video association
		$query = "DELETE FROM ".self::$_tbl_point_video." WHERE point_id IN (SELECT id FROM ".self::$_tbl_point." WHERE instance='$instance')";
		$res = $res && $db->actionquery($query);

		// delete point events association
		$query = "DELETE FROM ".self::$_tbl_point_event." WHERE point_id IN (SELECT id FROM ".self::$_tbl_point." WHERE instance='$instance')";
		$res = $res && $db->actionquery($query);

		// delete point attachments association
		$query = "DELETE FROM ".self::$_tbl_point_attachment." WHERE point_id IN (SELECT id FROM ".self::$_tbl_point." WHERE instance='$instance')";
		$res = $res && $db->actionquery($query);

		// delete point map association
		$query = "DELETE FROM ".self::$_tbl_map_point." WHERE point_id IN (SELECT id FROM ".self::$_tbl_point." WHERE instance='$instance')";
		$res = $res && $db->actionquery($query);

		// delete points
		$query = "DELETE FROM ".self::$_tbl_point." WHERE instance='$instance'";
		$res = $res && $db->actionquery($query);

		return $res;

	}
	
	/**
	 * @brief Getter della proprietà _ctgs, tutte le categorie collegate al punto di interesse 
	 * 
	 * @access public
	 * @return array array di oggetti @ref gmapsPointCtg
	 */
	public function ctgs() {

		return $this->_ctgs;

	}

	/**
	 * @brief Getter della proprietà _services, tutti i servizi associati al punto di interesse 
	 * 
	 * @access public
	 * @return array array di oggetti @ref gmapsService
	 */
	public function services() {

		return $this->_services;

	}

	/**
	 * @brief Getter della proprietà _images, tutte le immagini associate al punto di interesse 
	 * 
	 * @access public
	 * @return array array di oggetti @ref gmapsImage
	 */
	public function images() {

		return $this->_images;

	}

	/**
	 * @brief Getter della proprietà _videos, tutti i video associati al punto di interesse 
	 * 
	 * @access public
	 * @return array array di oggetti @ref gmapsVideo
	 */
	public function videos() {

		return $this->_videos;

	}

	/**
	 * @brief Getter della proprietà _events, tutti gli eventi associati al punto di interesse 
	 * 
	 * @access public
	 * @return array array di oggetti @ref gmapsEvent
	 */
	public function events() {

		return $this->_events;

	}

	/**
	 * @brief Getter della proprietà _attachments, tutti gli allegati associati al punto di interesse 
	 * 
	 * @access public
	 * @return array array di oggetti @ref gmapsAttachment
	 */
	public function attachments() {

		return $this->_attachments;

	}

	/**
	 * @brief Getter della proprietà _collections, tutte le collezioni associate al punto di interesse 
	 * 
	 * @access public
	 * @return array array di oggetti @ref gmapsCollection
	 */
	public function collections() {

		return $this->_collections;

	}


	/**
	 * @brief Seleziona id di punti di interesse sul database 
	 * 
	 * @param int $instance identificativo dell'istanza di @ref gmaps
	 * @param array $opts array associativo di opzioni:
	 *                          - where: where clauses addizionali 
	 *                          - order: campo di ordinamento dei risultati
	 *                          - limit: limitazione dei risultati
	 * @static
	 * @access public
	 * @return array un array di id dei punti di interesse ottenuti dalla query
	 */
	public static function get($instance, $opts=null) {

		$where = "instance='".$instance."'".((isset($opts['where']) && $opts['where']) ? " AND ".$opts['where'] : '');
		$ctg = gOpt('ctg', $opts, null);
		if($ctg) {
			$where .= " AND id IN (SELECT point_id FROM ".self::$_tbl_point_point_ctg." WHERE ctg_id='".$ctg."')";
		}
		$order = isset($opts['order']) ? $opts['order'] : 'label';
		$limit = isset($opts['limit']) ? "LIMIT ".$opts['limit'] : '';

		$res = array();

		$db = db::instance();
		$query = "SELECT id FROM ".self::$_tbl_point." WHERE $where ORDER BY $order $limit";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[] = $b['id'];
		}

		return $res;

	}

	/**
	 * @brief Seleziona id di punti di interesse associati al marker dato 
	 * 
	 * @param int $marker_id id del marker
	 * @static
	 * @access public
	 * @return array un array di id dei punti di interesse associati al marker dato
	 */
	public static function getByMarker($marker_id) {

		$res = array();

		$db = db::instance();
		$query = "SELECT point_id FROM ".self::$_tbl_point." WHERE marker='$marker_id'";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[] = $b['point_id'];
		}

		return $res;

	}


	/**
	 * @brief Seleziona id di punti di interesse associati al servizio dato 
	 * 
	 * @param int $service_id id del servizio
	 * @static
	 * @access public
	 * @return array un array di id dei punti di interesse associati al servizio dato
	 */
	public static function getByService($service_id) {

		$res = array();

		$db = db::instance();
		$query = "SELECT point_id FROM ".self::$_tbl_point_service." WHERE service='$service_id'";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[] = $b['point_id'];
		}

		return $res;

	}

	/**
	 * @brief Seleziona id di punti di interesse associati alla categoria data
	 * 
	 * @param int $ctg_id id della categoria
	 * @static
	 * @access public
	 * @return array un array di id dei punti di interesse associati alla categoria
	 */
	public static function getByCtg($ctg_id) {

		$res = array();

		$db = db::instance();
		$query = "SELECT point_id FROM ".self::$_tbl_point_point_ctg." WHERE ctg_id='$ctg_id'";
		$a = $db->selectquery($query);
		foreach($a as $b) {
			$res[] = $b['point_id'];
		}

		return $res;

	}

	/**
	 * @brief Form di inserimento e modifica dati punto di interesse 
	 * 
	 * @param string $redirect url di reindirizzamento se il salvataggio va a buon fine
	 * @access public
	 * @return string form di inserimento e modifica dati punto di interesse
	 */
	public function form($redirect) {

		if(isset($_POST['submit'])) {
			$gform = new Form('form_point', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				if(!$this->id) {
					$this->insert_date = date('Y-m-d H:i:s');
				}
				$this->last_edit_date = date('Y-m-d H:i:s');
				$ctgs_id = cleanVar($_POST, 'ctg', 'array', '');
				$services_id = cleanVar($_POST, 'service', 'array', '');
				$this->label = cleanVar($_POST, 'label', 'string', '');
				$this->address = cleanVar($_POST, 'address', 'string', '');
				$this->cap = cleanVar($_POST, 'cap', 'string', '');
				$this->city = cleanVar($_POST, 'city', 'string', '');
				$this->nation = cleanVar($_POST, 'nation', 'int', '');
				$this->lat = cleanVar($_POST, 'lat', 'string', '');
				$this->lng = cleanVar($_POST, 'lng', 'string', '');
				$this->marker = cleanVar($_POST, 'marker', 'int', '');
				$this->description = cleanVarEditor($_POST, 'description', '');
				$this->information = cleanVarEditor($_POST, 'information', '');
				$this->phone = cleanVar($_POST, 'phone', 'string', '');
				$this->email = cleanVar($_POST, 'email', 'string', '');
				$this->web = cleanVar($_POST, 'web', 'string', '');
				$this->opening_hours = cleanVar($_POST, 'web', 'opening_hours', '');
				$this->updating = cleanVar($_POST, 'web', 'updating', '');
				$this->updating_email = cleanVar($_POST, 'web', 'updating_email', '');
				if($this->updating && !$this->updating_email) {
					exit(error::errorMessage(array('error'=>_("Inserire anche una email per l'invio del codice di aggiornamento")), $_SERVER['QUERY_STRING']));
				}
				$this->updateDbData();
				$this->saveCtgs($ctgs_id);
				$this->saveServices($services_id);
				header("Location: ".$redirect);
				exit();
			}
			else {
				if(!($_POST['lat'] && $_POST['lng'])) {
					exit(error::errorMessage(array('error'=>_("Rappresentare il punto sulla mappa seguendo le istruzioni.")), $_SERVER['QUERY_STRING']));
				}
				exit(error::errorMessage(array('error'=>1), $_SERVER['QUERY_STRING']));
			}
		}

		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
		$registry->addJs("http://ajs.otto.to.it/sources/0.1/ajs/ajs.js");

		if($this->id) {
			$title = _("Modifica punto di interesse");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuovo punto di interesse");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_point', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$required = "label,lat,lng,ctg,cap,city,address,nation";
		$buffer = $gform->form('', false, $required, array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->hidden('lat', $this->lat, array('id'=>'lat'));
		$buffer .= $gform->hidden('lng', $this->lng, array('id'=>'lng'));

		$buffer .= $gform->multipleCheckbox('ctg[]', $gform->retvar('ctg', $this->_ctgs_id), gmapsPointCtg::getForSelect($this->instance), _("Categorie"), array('required'=>true));
		$buffer .= $gform->multipleCheckbox('service[]', $gform->retvar('service', $this->_services_id), gmapsService::getForSelect($this->instance), _("Servizi"), array('required'=>false));
		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'label'));
		$buffer .= $gform->cinput('address', 'text', $gform->retvar('address', htmlInput($this->address)), _("Indirizzo"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>false));
		$buffer .= $gform->cinput('cap', 'text', $gform->retvar('cap', htmlInput($this->cap)), _("Cap"), array('required'=>true, 'maxlength'=>16, 'size'=>5, 'trnsl'=>false));
		$buffer .= $gform->cinput('city', 'text', $gform->retvar('city', htmlInput($this->city)), _("Città"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'city'));
		$query = "SELECT id, ".$_SESSION['lngDft']." FROM nation ORDER BY ".$_SESSION['lngDft']." ASC";
		$buffer .= $gform->cselect('nation', $gform->retvar('nation', htmlInput($this->nation)), $query, _("Nazione"), array("required"=>true));
		$buffer .= $gform->cselect('marker', $gform->retvar('marker', htmlInput($this->marker)), gmapsMarker::getForSelect($this->instance), array(_("Marker"), _("lasciare vuoto se si intende visualizzare il marker di default di google")), array("required"=>false));
		$buffer .= $gform->cell($this->formMap());
		$buffer .= $gform->fcktextarea('description', $gform->retvar('description', htmlInputEditor($this->description)), _("Descrizione"), array('fck_toolbar'=>"Default", 'trnsl'=>true, 'field'=>'description'));
		$buffer .= $gform->fcktextarea('information', $gform->retvar('information', htmlInputEditor($this->information)), _("Informazioni"), array('fck_toolbar'=>"Default", 'trnsl'=>true, 'field'=>'information'));

		$buffer .= $gform->cinput('phone', 'text', $gform->retvar('phone', htmlInput($this->phone)), _("Telefono"), array('required'=>false, 'maxlength'=>16, 'size'=>8, 'trnsl'=>false));
		$buffer .= $gform->cinput('email', 'text', $gform->retvar('email', htmlInput($this->email)), _("Email"), array('required'=>false, 'maxlength'=>64, 'size'=>20, 'trnsl'=>false));
		$buffer .= $gform->cinput('web', 'text', $gform->retvar('web', htmlInput($this->web)), _("Web"), array('required'=>false, 'maxlength'=>200, 'size'=>40, 'trnsl'=>false));
		$buffer .= $gform->ctextarea('opening_hours', $gform->retvar('opening_hours', htmlInput($this->opening_hours)), _("Orari di apertura"), array('required'=>false, 'cols'=>60, 'rows'=>4, 'trnsl'=>true, 'field'=>'opening_hours'));

		$buffer .= $gform->cradio('updating', $gform->retvar('updating', htmlInput($this->updating)), array('0'=>_('no'), 1=>_('si')), 0, _("Aggiornamento"), array('required'=>true));
		$buffer .= $gform->cinput('updating_email', 'text', $gform->retvar('updating_email', htmlInput($this->updating_email)), _("Email codice aggiornamento"), array('required'=>false, 'maxlength'=>64, 'size'=>20, 'trnsl'=>false));
		$buffer .= $gform->cinput('submit', 'submit', $submit, '', array('classField'=>'submit'));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	/**
	 * @brief Widget per la selezione di un punto geolocalizzato direttamente su google map 
	 * 
	 * @access private
	 * @return string widget
	 */
	private function formMap() {

		$buffer = "<p>Per geolocalizzare il punto di interesse utilizzare lo strumento mappa qui sotto. Seguire la seguente procedura:</p>";
		$buffer .= "<ol>";
		$buffer .= "<li>"._("Premere il pulsante 'point'")."</li>";
		$buffer .= "<li>"._("Cliccare nel punto desiderato sulla mappa per settare il punto. Una volta comparso il marker è possibile affinare la posizione spostandolo. Se si dispone di un indirizzo si può utilizzare il campo di testo, scrivere l'indirizzo e poi premere 'draw'. Anche in questo caso è poi possibile affinare la posizione in un secondo momento.")."</li>";
		$buffer .= "<li>"._("Quando il marker si trova nella posizione desiderata permere il pulsante 'export map'.")."</li>";
		$buffer .= "</ol>";
		$buffer .= "<div id=\"map_canvas\" style=\"width:100%; height: 300px;\"></div>";

		$buffer .= "<script>";
		$buffer .= "ajs.use(['ajs.maps.gmapdraw'], function() {

				fillFields = function(data) {
					$('lat').set('value', data.point[0].lat.round(10));
					$('lng').set('value', data.point[0].lng.round(10));
					alert('".jsVar(_("Il punto è stato settato correttamente, prosegui con la compilazione del form"))."');
				}
	
				mymap = new ajs.maps.gmapdraw.map('map_canvas', {
					zoom: 10,
					export_map_callback: fillFields,
					tools: {'point': { options: { max_items_allowed: 1 }}},
					tips_map_ctrl: null
				});
				mymap.render();

				if($('lat').value && $('lng').value) {
					var data = {point: [{lat: $('lat').value, lng:$('lng').value}]};
					mymap.importMap(data);
					mymap.gmap().setCenter(new google.maps.LatLng($('lat').value, $('lng').value));
				}


			});"; 
		$buffer .= "</script>";

		return $buffer;

	}

	/**
	 * @brief Restituisce il codice js necessario a disegnare il punto sulla mappa data 
	 * 
	 * @param string $map il nome della variabile javascript che referenzia la google map 
	 * @param string $bounds il nome della variabile javascript che referenzia i bounds della mappa 
	 * @access public
	 * @return string codice javascript che disegna il marker
	 */
	public function gmapCode($map, $bounds) {

		$buffer = "var marker_".$this->id." = new google.maps.Marker({
			position: new google.maps.LatLng(".$this->lat.", ".$this->lng."),
			map: $map
		});";
		$buffer .= $bounds.".extend(new google.maps.LatLng(".$this->lat.", ".$this->lng."));";
		if($this->marker) {
			$marker = new gmapsMarker($this->marker, $this->_controller);
			$buffer .= "marker_".$this->id.".setIcon(new google.maps.MarkerImage('".$marker->iconUrl()."'));";
			if($marker->shadow) {
				$buffer .= "marker_".$this->id.".setShadow(new google.maps.MarkerImage('".$marker->shadowUrl()."'));";
			}
		}

		return $buffer;

	}
	
	/**
	 * @brief Restituisce il punto di interesse sotto forma di oggetto javascript 
	 * 
	 * @param string $map_obj oggetto javascript di tipo Gmap 
	 * @param string $polylines array di id di percorisi ai quali è associato il punto 
	 * @param string $polygons array di id di aree alle quali è associato il punto 
	 * @param int $map_id id della mappa @ref gmapsMap 
	 * @access public
	 * @return string oggetto javascript
	 */
	public function jsObject($map_obj, $polylines, $polygons, $map_id) {
	
		$db = db::instance();
		$nation = $db->getFieldFromId('nation', $_SESSION['lng'], 'id', $this->nation);

		if($this->marker) {
			$marker = new gmapsMarker($this->marker, $this->_controller);
			$marker_icon = $marker->iconUrl();
			$marker_shadow = $marker->shadowUrl();
		}
		else {
			$marker_icon = null;
			$marker_shadow = null;
		}

        $categories = array();
        foreach($this->_ctgs as $ctg) {
            $categories[] = jsVar($ctg->ml('name'));
        }

        $services = array();
        foreach($this->_services as $s) {
            $services[] = jsVar($s->ml('name'));
        }

		$fields = "{
				id: '".$this->id."',		
				label: '".jsVar($this->ml('label'))."',		
				categories: '".implode(', ', $categories)."',		
				services: '".implode(', ', $services)."',		
				description: '".jsVar(cutHtmlText(HtmlChars($this->ml('description')), 120, '...', false, false, false, null))."',		
				address: '".jsVar($this->ml('address'))."',		
				cap: '".jsVar($this->cap)."',		
				city: '".jsVar($this->ml('city'))."',		
				nation: '".jsVar($nation)."',		
				lat: '".jsVar($this->lat)."',		
				lng: '".jsVar($this->lng)."',
				icon: ".($marker_icon ? "'$marker_icon'" : "null").",		
				shadow: ".($marker_shadow ? "'$marker_shadow'" : "null")."		
		}";

		$info_url = HOME_FILE.'?pt['.$this->_controller->instanceName().'-infowindow]&point_id='.$this->id.'&map_id='.$map_id;

		return "new GmapPoint($fields, [".implode(',', $this->_ctgs_id)."], [".implode(',', $polylines)."], [".implode(',', $polygons)."], $map_obj, '$info_url');";
	
	}

	/**
	 * @brief Associa le categorie date al punto di interesse 
	 * 
	 * @param array $ctgs_id array di id di categorie da associare
	 * @access private
	 * @return bool il risultato dell'operazione
	 */
	private function saveCtgs($ctgs_id) {

		$db = db::instance();
		$query = "DELETE FROM ".self::$_tbl_point_point_ctg." WHERE point_id='".$this->id."'";
		$res = $db->actionquery($query);

		$values = array();
		foreach($ctgs_id as $ctg_id) {
			$values[] = "('".$this->id."', '".$ctg_id."')";
		}
		$query = "INSERT INTO ".self::$_tbl_point_point_ctg." (point_id, ctg_id) VALUES ".implode(',', $values).";";
		$res = $db->actionquery($query);

		return $res;

	}

	/**
	 * @brief Associa i servizi dati al punto di interesse 
	 * 
	 * @param array $services_id array di id di servizi da associare
	 * @access private
	 * @return bool il risultato dell'operazione
	 */
	private function saveServices($services_id) {

		$db = db::instance();
		$query = "DELETE FROM ".self::$_tbl_point_service." WHERE point_id='".$this->id."'";
		$res = $db->actionquery($query);

		$values = array();
		foreach($services_id as $service_id) {
			$values[] = "('".$this->id."', '".$service_id."')";
		}
		$query = "INSERT INTO ".self::$_tbl_point_service." (point_id, service_id) VALUES ".implode(',', $values).";";
		$res = $db->actionquery($query);

		return $res;

	}

	/**
	 * @brief Elimina il punto di interesse 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 * 
	 */
	public function delete() {

		$db = db::instance();

		// delete ctg associations
		$query = "DELETE FROM ".self::$_tbl_point_point_ctg." WHERE point_id='".$this->id."'";
		$res = $db->actionquery($query);

		// delete map associations
		$query = "DELETE FROM ".self::$_tbl_map_point." WHERE point_id='".$this->id."'";
		$res = $db->actionquery($query);

		// delete service associations
		$query = "DELETE FROM ".self::$_tbl_point_service." WHERE point_id='".$this->id."'";
		$res = $db->actionquery($query);

		// delete images
		foreach($this->_images as $image) {
			$image->delete();
		}

		// delete videos
		foreach($this->_videos as $video) {
			$video->delete();
		}

		// delete events
		foreach($this->_events as $event) {
			$event->delete();
		}

		// delete attachments
		foreach($this->_attachments as $attachment) {
			$attachment->delete();
		}

		// delete collections
		foreach($this->_collections as $collection) {
			$collection->delete();
		}

		return $this->deleteDbData();

	}

	
}

?>

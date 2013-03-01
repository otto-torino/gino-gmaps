<?php
/**
 * @file class_gmaps.php
 * @brief Contiene la definizione ed implementazione della classe \ref gmaps.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @mainpage Caratteristiche opzioni ed output disponibili
 *
 * gino-gmaps è una classe istanziabile per la gestione di mappe di punti di interesse, percorsi ed aree. 
 * I punti di interesse sono corredati di informazioni aggiuntive, foto, video, allegati, eventi e collezioni.
 * I percorsi e le aree possono essere associati a punti di interesse.
 * Le mappe ottenute possono essere embeddate in iframe su qualunque altro sito. 
 *
 * OPZIONI
 * - Titolo modulo
 * - Mappa di default mostrata se non precisata da url
 * - Campi mostrati nella lista elementi della mappa
 *
 * OUTPUT DISPONIBILI
 * - vista mappa
 * - vista mappa con elenco items
 * - scheda punto di interesse
 * - scheda percorso
 * - scheda area
 *
 */

/**
 * @defgroup gino-gmaps
 * @brief Modulo per la gestione di punti si interesse, percorsi ed aree geolocalizzati
 *
 * Il modulo contiene anche dei javascript, css e file di configurazione
 *
 */

require_once('class.gmapsMap.php');
require_once('class.gmapsPointCtg.php');
require_once('class.gmapsPoint.php');
require_once('class.gmapsService.php');
require_once('class.gmapsPolylineCtg.php');
require_once('class.gmapsPolyline.php');
require_once('class.gmapsPolygonCtg.php');
require_once('class.gmapsPolygon.php');
require_once('class.gmapsImage.php');
require_once('class.gmapsCollection.php');
require_once('class.gmapsCollectionImage.php');
require_once('class.gmapsVideo.php');
require_once('class.gmapsEvent.php');
require_once('class.gmapsAttachment.php');
require_once('class.gmapsMarker.php');

/**
 * @ingroup gino-gmaps
 * @brief Classe interfaccia per la gestione e visualizzazione di punti, percorsi ed aree geolocalizzati.
 *
 * Agisce da controller esponendo i metodi chiamabili direttamente da url per la visualizzazione 
 * di front end e back office. 
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmaps extends AbstractEvtClass {

	/**
	 * @brief Percorso assoluto alla cartella di upload di immagini ed allegati 
	 */
	protected $_data_dir;

	/**
	 * @brief Percorso relativo alla cartella di upload di immagini ed allegati 
	 */
	protected $_data_www;

	/**
	 * @brief Array associativo contenente i valori di default delle opzioni 
	 */
	private $_optionsValue;

	/**
	 * @brief Titolo del modulo
	 */
	private $_title;

	/**
	 * @brief Mappa di default mostrata se non viene passato un id alla funzione @ref gmaps::view()
	 */
	private $_default_map;

    /**
	 * @brief Campi mostrati nella lista degli elementi della mappa
	 */
	private $_list_fields;

	/**
	 * @brief Oggetto di tipo options per la gestione automatica delle opzioni 
	 */
	private $_options;

	/**
	 * @brief Elenco di proprietà delle opzioni per la creazione del form delle opzioni 
	 */
	public $_optionsLabels;

	/**
 	 * @brief Nome della tabella che contiene le mappe 
 	 */
 	private $_tbl_map = 'gmaps_map';

	/**
 	 * @brief Nome della tabella che contiene l'associazione mappe punti di inteersse 
 	 */
 	private $_tbl_map_point = 'gmaps_map_point';

	/**
 	 * @brief Nome della tabella che contiene l'associazione mappe percorsi 
 	 */
 	private $_tbl_map_polyline = 'gmaps_map_polyline';

	/**
 	 * @brief Nome della tabella che contiene l'associazione mappe aree 
 	 */
 	private $_tbl_map_polygon = 'gmaps_map_polygon';

	/**
 	 * @brief Nome della tabella che contiene le categorie dei punti 
 	 */
 	private $_tbl_point_ctg = 'gmaps_point_ctg';
	
	/**
 	 * @brief Nome della tabella che contiene i punti 
 	 */
 	private $_tbl_point = 'gmaps_point';

	/**
 	 * @brief Nome della tabella che contiene l'associazione tra i punti di interesse e le immagini 
 	 */
 	private $_tbl_point_image = 'gmaps_point_image';

	/**
 	 * @brief Nome della tabella che contiene l'associazione tra i punti di interesse e i video 
 	 */
 	private $_tbl_point_video = 'gmaps_point_video';

	/**
 	 * @brief Nome della tabella che contiene l'associazione tra i punti di interesse e gli eventi 
 	 */
 	private $_tbl_point_event = 'gmaps_point_event';

	/**
 	 * @brief Nome della tabella che contiene l'associazione tra i punti di interesse e gli allegati 
 	 */
 	private $_tbl_point_attachment = 'gmaps_point_attachment';

	/**
 	 * @brief Nome della tabella che contiene l'associazione tra i punti di interesse ed i servizi 
 	 */
 	private $_tbl_point_service = 'gmaps_point_service';

	/**
 	 * @brief Nome della tabella che contiene l'associazione tra i punti di interesse e le categorie 
 	 */
 	private $_tbl_point_point_ctg = 'gmaps_point_point_ctg';

	/**
 	 * @brief Nome della tabella che contiene le categorie dei percorsi 
 	 */
 	private $_tbl_polyline_ctg = 'gmaps_polyline_ctg';
	
	/**
 	 * @brief Nome della tabella che contiene i percorsi 
 	 */
	private $_tbl_polyline = 'gmaps_polyline';

	/**
 	 * @brief Nome della tabella che contiene l'associazione tra i percorsi e le categorie 
 	 */
 	private $_tbl_polyline_polyline_ctg = 'gmaps_polyline_polyline_ctg';

	/**
 	 * @brief Nome della tabella che contiene l'associazione punti-percorsi
 	 */
	private $_tbl_polyline_point = 'gmaps_polyline_point';
	
	/*
 	 * @brief Nome della tabella che contiene le categorie delle aree 
 	 */
 	private $_tbl_polygon_ctg = 'gmaps_polygon_ctg';
	
	/**
 	 * @brief Nome della tabella che contiene le aree 
 	 */
	private $_tbl_polygon = 'gmaps_polygon';

	/**
 	 * @brief Nome della tabella che contiene l'associazione tra le aree e le categorie 
 	 */
 	private $_tbl_polygon_polygon_ctg = 'gmaps_polygon_polygon_ctg';

	/**
 	 * @brief Nome della tabella che contiene l'associazione punti-aree
 	 */
	private $_tbl_polygon_point = 'gmaps_polygon_point';

	/**
	 * @brief Nome della tabella che contiene le opzioni 
	 */
	private $_tbl_opt = 'gmaps_opt';

	/**
	 * @brief Nome della tabella che contiene l'associazione utenti-gruppi 
	 */
	private $_tbl_usr = 'gmaps_usr';

	/**
	 * @brief Parametro action letto da url 
	 */
	private $_action;

	/**
	 * @brief Parametro block letto da url 
	 */
	private $_block;

	/**
	 * @brief Numero di item nella lista amministrativa dei punti di interesse 
	 */
	private $_list_point_items;

	/**
	 * @brief icona allegati 
	 */
	private $_icon_attachment;

	/**
	 * @brief icona eventi 
	 */
	private $_icon_event;

	/**
	 * @brief icona immagini 
	 */
	private $_icon_image;

	/**
	 * @brief icona video 
	 */
	private $_icon_video;

	/**
	 * @brief icona collezioni 
	 */
	private $_icon_collection;

	/**
	 * @brief icona elementi mappa 
	 */
	private $_icon_map_element;

	/**
	 * @brief icona punti percorsi 
	 */
	private $_icon_polyline_points;

	/**
	 * @brief icona punti area 
	 */
	private $_icon_polygon_points;


	/**
	 * @brief Costruisce un'istanza di tipo gmaps 
	 * 
	 * @param int $mdlId id dell'istanza
	 * @access public
	 * @return gmaps oggetto di tipo gmaps
	 */
	function __construct($mdlId){

		parent::__construct();

		$this->_instance = $mdlId;
		$this->_instanceName = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $this->_instance);
		$this->_instanceLabel = $this->_db->getFieldFromId($this->_tbl_module, 'label', 'id', $this->_instance);

		$this->_data_dir = $this->_data_dir.$this->_os.$this->_instanceName;
		$this->_data_www = $this->_data_www."/".$this->_instanceName;

		$this->setAccess();

		// Opzioni di default
		$this->_optionsValue = array(
			'title'=>_("Mappe interattive"), 
			'list_fields'=>'label,categories' 
		);
		
		$this->_title = htmlChars($this->setOption('title', array('value'=>$this->_optionsValue['title'], 'translation'=>true)));
		$this->_default_map = htmlChars($this->setOption('default_map', array('value'=>'', 'translation'=>false)));
        $this->_list_fields = $this->setOption('list_fields', array('value'=>$this->_optionsValue['list_fields'], 'translation'=>false));
		
        $list_fields_exp = _("Inserire i campi separati da virgole<br/>Campi disponibili:<ul>
                <li><b>label</b>: etichetta (punti e percorsi)</li>
                <li><b>categories</b>: categorie (punti e percorsi)</li>
                <li><b>address</b>: indirizzo (punti)</li>
                <li><b>cap</b>: cap (punti)</li>
                <li><b>city</b>: città (punti)</li>
                <li><b>services</b>: servizi (punti)</li>
        </ul>");
		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
			"title"=>array('label'=>_("Titolo del modulo"), 'value'=>$this->_optionsValue['title'], 'required'=>false),
			"default_map"=>array('label'=>array(_("Mappa di default"), _("ID della mappa da mostrare nella vista quando non viene passato un id tramite GET")), 'value'=>'', 'required'=>false),
			"list_fields"=>array('label'=>array(_("Campi mostrati nelle liste (mappa full, itinerari, aree)"), $list_fields_exp), 'value'=>$this->_optionsValue['list_fields'], 'required'=>false)
		);
		
		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');

		$this->_list_point_items = 15;

		$this->_icon_attachment = "<img class=\"icon_tooltip\" src=\"".$this->_class_img."/ico_attachment.gif\" alt=\""._("allegati")."\" title=\""._("allegati")."\" />";
		$this->_icon_event = "<img class=\"icon_tooltip\" src=\"".$this->_class_img."/ico_event.gif\" alt=\""._("eventi")."\" title=\""._("eventi")."\" />";
		$this->_icon_image = "<img class=\"icon_tooltip\" src=\"".$this->_class_img."/ico_image.gif\" alt=\""._("immagini")."\" title=\""._("immagini")."\" />";
		$this->_icon_video = "<img class=\"icon_tooltip\" src=\"".$this->_class_img."/ico_video.gif\" alt=\""._("video")."\" title=\""._("video")."\" />";
		$this->_icon_collection = "<img class=\"icon_tooltip\" src=\"".$this->_class_img."/ico_collection.gif\" alt=\""._("collezioni")."\" title=\""._("collezioni")."\" />";
		$this->_icon_map_element = "<img class=\"icon_tooltip\" src=\"".$this->_class_img."/ico_map_element.gif\" alt=\""._("elementi mappa")."\" title=\""._("elementi mappa")."\" />";
		$this->_icon_polyline_points = "<img class=\"icon_tooltip\" src=\"".$this->_class_img."/ico_map_element.gif\" alt=\""._("punti percorso")."\" title=\""._("punti percorso")."\" />";
		$this->_icon_polygon_points = "<img class=\"icon_tooltip\" src=\"".$this->_class_img."/ico_map_element.gif\" alt=\""._("punti area")."\" title=\""._("punti percorso")."\" />";

	}

	/**
	 * @brief Restituisce alcune proprietà della classe utili per la generazione di nuove istanze 
	 * 
	 * @static
	 * @access public
	 * @return array[string]array lista proprietà utilizzate per la creazione di istanze di tipo gmaps
	 */
	public static function getClassElements() {

		return array("tables"=>array('gmaps_map', 'gmaps_map_point', 'gmaps_map_polyline', 'gmaps_point', 'gmaps_point_image', 'gmaps_point_video', 'gmaps_point_event', 'gmaps_point_attachment', 'gmaps_point_collection', 'gmaps_point_collection_image', 'gmaps_point_service', 'gmaps_point_point_ctg', 'gmaps_point_ctg', 'gmaps_polyline', 'gmaps_polyline_point', 'gmaps_polyline_ctg', 'gmaps_polyline_polyline_ctg', 'gmaps_polygon', 'gmaps_polygon_point', 'gmaps_polygon_ctg', 'gmaps_polygon_polygon_ctg', 'gmaps_service', 'gmaps_marker', 'gmaps_opt', 'gmaps_grp', 'gmaps_usr'),
			     "css"=>array('gmaps.css'),
			     "folderStructure"=>array(
				CONTENT_DIR.OS.'gmaps'=>array(
					"service"=>null,
					"point_ctg"=>null,
					"polyline_ctg"=>null,
					"polygon_ctg"=>null,
					"point"=>array(
						'images'=>null,
						'events'=>null,
						'attachments'=>null,
						'collections'=>array(
							"images"=>null
						)
					)
				)	
	     		     )
		      );
	}

	/**
	 * @brief Metodo invocato quando viene eliminata un'istanza di tipo gmaps
	 *
	 * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione di elimazione da database
	 */
	public function deleteInstance() {

		$this->accessGroup('');

		$res = true;

		$res = $res && gmapsMap::deleteInstance($this->_instance);
		$res = $res && gmapsPoint::deleteInstance($this->_instance);
		$res = $res && gmapsPointCtg::deleteInstance($this->_instance);
		$res = $res && gmapsPolyline::deleteInstance($this->_instance);
		$res = $res && gmapsPolylineCtg::deleteInstance($this->_instance);
		$res = $res && gmapsService::deleteInstance($this->_instance);
		$res = $res && gmapsMarker::deleteInstance($this->_instance);

		/*
		 * delete record and translation from the options' table
		 */
		$opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
		language::deleteTranslations($this->_tbl_opt, $opt_id);

		$query = "DELETE FROM ".$this->_tbl_opt." WHERE instance='$this->_instance'";	
		$res = $res && $this->_db->actionquery($query);

		/*
		 * delete group users association
		 */
		$query = "DELETE FROM ".$this->_tbl_usr." WHERE instance='$this->_instance'";	
		$res = $res && $this->_db->actionquery($query);

		/*
		 * delete css files
		 */
		$classElements = $this->getClassElements();
		foreach($classElements['css'] as $css) {
			unlink(APP_DIR.OS.$this->_className.OS.baseFileName($css)."_".$this->_instanceName.".css");
		}

		/*
		 * delete folder structure
		 */
		foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
			$this->deleteFileDir($fld.OS.$this->_instanceName, true);
		}

		return $res;
	}
	
	/**
	 * @brief Definizione dei metodi pubblici che forniscono un output per il front-end 
	 * 
	 * Questo metodo viene letto dal motore di generazione dei layout e dal motore di generazione di voci di menu
	 * per presentare una lista di output associati all'istanza di classe. 
	 * 
	 * @static
	 * @access public
	 * @return array[string]array
	 */
	public static function outputFunctions() {

		$list = array(
			"view" => array("label"=>_("Visualizzazione mappa (aggiungere l'id della mappa: es. &id=1, di default viene caricata la mappa impostata da opzioni)"), "role"=>'1'),
			"viewFull" => array("label"=>_("Visualizzazione mappa con lista elementi (aggiungere l'id della mappa: es. &id=1, di default viene caricata la mappa impostata da opzioni)"), "role"=>'1')
		);

		return $list;
	}

	/**
	 * @brief Returns the instance name 
	 * 
	 * @access public
	 * @return string the instance name
	 */
	public function instanceName() {
		
		return $this->_instanceName;

	}

	/**
	 * @brief Visualizzazione mappa  
	 * 
	 * Viene visualizzata la mappa di default impostata da opzioni oppure quella con ID uguale al valore passato via GET 
	 * 
	 * @access public
	 * @return string visualizzazione mappa
	 */
	public function view() {

		$this->setAccess($this->_access_base);

		$get_id = cleanVar($_GET, 'id', 'int', '');
		$map_id = $get_id ? $get_id : $this->_default_map;
		$map = new gmapsMap($map_id, $this);

		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
		$registry->addJs(SITE_WWW."/app/gmaps/markerclusterer_packed.js");
		$registry->addJs(SITE_WWW."/app/gmaps/infobox.js");
		$registry->addJs(SITE_WWW."/app/gmaps/MooComplete.js");
		$registry->addJs(SITE_WWW."/app/gmaps/ProgressBar.js");
		$registry->addJs(SITE_WWW."/app/gmaps/gmaps.js");
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");
		$registry->addCss(SITE_WWW."/app/gmaps/MooComplete.css");

		$buffer = '';

		$buffer .= "<section id=\"view_gmaps_".$this->_instanceName."\">";
		$buffer .= $this->map($map);
		$buffer .= "</section>";	

		return $buffer;

	}

    /**
	 * @brief Visualizzazione mappacon lista elementi
	 * 
	 * Viene visualizzata la mappa di default impostata da opzioni oppure quella con ID uguale al valore passato via GET 
	 * 
	 * @access public
	 * @return string visualizzazione mappa e lista elementi
	 */
	public function viewFull() {

		$this->setAccess($this->_access_base);

		$get_id = cleanVar($_GET, 'id', 'int', '');
		$map_id = $get_id ? $get_id : $this->_default_map;
		$map = new gmapsMap($map_id, $this);

		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
		$registry->addJs(SITE_WWW."/app/gmaps/markerclusterer_packed.js");
		$registry->addJs(SITE_WWW."/app/gmaps/infobox.js");
		$registry->addJs(SITE_WWW."/app/gmaps/MooComplete.js");
		$registry->addJs(SITE_WWW."/app/gmaps/ProgressBar.js");
		$registry->addJs(SITE_WWW."/app/gmaps/gmaps.js");
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");
		$registry->addCss(SITE_WWW."/app/gmaps/MooComplete.css");

		$buffer = '';

		$buffer .= "<section id=\"viewfull_gmaps_".$this->_instanceName."\">";
		$buffer .= $this->map($map, true);
		$buffer .= "</section>";	

		return $buffer;

	}

	/**
	 * @brief Visualizzazione mappa in iframe 
	 * 
	 * @access public
	 * @return string visualizzazione mappa in iframe
	 */
	public function iframeMap() {

		$this->setAccess($this->_access_base);

		$id = cleanVar($_GET, 'id', 'int', '');
		$map = new gmapsMap($id, $this);

		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
		$registry->addJs(SITE_WWW."/app/gmaps/markerclusterer_packed.js");
		$registry->addJs(SITE_WWW."/app/gmaps/infobox.js");
		$registry->addJs(SITE_WWW."/app/gmaps/MooComplete.js");
		$registry->addJs(SITE_WWW."/app/gmaps/ProgressBar.js");
		$registry->addJs(SITE_WWW."/app/gmaps/gmaps.js");
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");
		$registry->addCss(SITE_WWW."/app/gmaps/MooComplete.css");

		$pub = new pub();

		$buffer = "<!DOCTYPE html>";
		$buffer .= "<html lang=\"".LANG."\">\n";
		$buffer .= "<head>\n";
		$buffer .= "<meta charset=\"utf-8\" />\n";
		$buffer .= "<title>".htmlChars($map->name)."</title>\n";
		$buffer .= "<base href=\"".$pub->getUrl('root').SITE_WWW."/\" />\n";
		$buffer .= $registry->variables('css');
		$buffer .= $registry->variables('js');
		$buffer .= "</head>\n";

		$buffer .= "<body style=\"background: transparent;\">\n";
		$buffer .= "<section style=\"text-align:left; padding: 0; margin: 0; width:".$map->width."; height:".$map->height.";\">";
		$buffer .= $this->map($map);
		$buffer .= "</section>";	
		$buffer .= "</body>\n";
		$buffer .= "</html>\n";

		echo $buffer;
		exit;

	}

	/**
	 * @brief Visualizzazione mappa  
	 * 
	 * @param object $map istanza di @ref gmapsMap
	 * @param bool $list mostrare la lista degli elementi oppure no
	 * @access public
	 * @return void
	 */
	private function map($map, $list = false) {
		
		$buffer = '';

		$gform = new Form('form_map', 'post', false, array('tblLayout'=>false));

		$buffer .= "<nav id=\"gmaps_nav\">";
		$buffer .= "<ul>";
		$buffer .= "<li>";
		$buffer .= _("Help");
		$buffer .= "<ul>";
		$buffer .= "<li data-menu=\"maphelp\">"._("<p>Utilizzare la voce 'Mappa' per modificare il tipo di mappa.<br/>Utilizzare il campo di testo per ricercare punti di interesse, percorsi o aree nella mappa, iniziando a digitare le chiavi di ricerca verranno visualizzati dei suggerimenti, selezionare quello desiderato e premere il bottone 'cerca'. Per tornare alla visualizzazione completa premere il bottone 'tutti'. <br />Se si desidera filtrare per categoria utlizzare l'apposito menu a tendina.</p>")."</li>";
		$buffer .= "</ul>";
		$buffer .= "</li>";
		$buffer .= "<li>";
		$buffer .= _("Mappa");
		$buffer .= "<ul>";
		$buffer .= "<li class=\"link\" data-menu=\"maptype\" data-type=\"hybrid\">"._("Ibrida")."</li>";
		$buffer .= "<li class=\"link selected\" data-menu=\"maptype\" data-type=\"roadmap\">"._("roadmap")."</li>";
		$buffer .= "<li class=\"link\" data-menu=\"maptype\" data-type=\"satellite\">"._("satellite")."</li>";
		$buffer .= "<li class=\"link\" data-menu=\"maptype\" data-type=\"terrain\">"._("terrain")."</li>";
		$buffer .= "</ul>";
		$buffer .= "</li>";
		$buffer .= "<li>";
		$buffer .= "<input type=\"text\" name=\"text_search\" id=\"text_search\" value=\"\" placeholder=\""._("cerca...")."\" />";
		$buffer .= " <input type=\"button\" name=\"button_search\" id=\"button_search\" value=\""._("cerca")."\" />";
		$buffer .= " <input type=\"button\" name=\"button_search_reset\" id=\"button_search_reset\" value=\""._("tutti")."\" />";
		$buffer .= "</li>";

		$point_ctgs = gmapsPointCtg::getForSelect($this->_instance);
		$polyline_ctgs = gmapsPolylineCtg::getForSelect($this->_instance);
		$polygon_ctgs = gmapsPolygonCtg::getForSelect($this->_instance);

		$buffer .= "<li>";
		$buffer .= "<select id=\"ctg_search\">";
		$buffer .= "<option selected>"._("tutte le categorie")."</option>";
		if(count($point_ctgs)) {
			$buffer .= "<optgroup label=\""._("Punti di interesse")."\">";
			foreach($point_ctgs as $id=>$label) {
				$buffer .= "<option value=\"point_".$id."\">".$label."</option>";
			}
			$buffer .= "</optgroup>";
		}
		if(count($polyline_ctgs)) {
			$buffer .= "<optgroup label=\""._("Percorsi")."\">";
			foreach($polyline_ctgs as $id=>$label) {
				$buffer .= "<option value=\"polyline_".$id."\">".$label."</option>";
			}
		}
		if(count($polygon_ctgs)) {
			$buffer .= "<optgroup label=\""._("Aree")."\">";
			foreach($polygon_ctgs as $id=>$label) {
				$buffer .= "<option value=\"polygon_".$id."\">".$label."</option>";
			}
		}
		$buffer .= "</optgroup>";
		$buffer .= "</select>";
		$buffer .= "</li>";
		$buffer .= "</ul>";
		$buffer .= "</nav>";
		$buffer .= "<div id=\"map_canvas\" style=\"width:".$map->width.";height:".$map->height."\"></div>";

        $voices = array();
        foreach(explode(',', $this->_list_fields) as $f) {
            $f = trim($f);
            if(in_array($f, array('label', 'categories', 'address', 'cap', 'city', 'services'))) {
                $voices[] = $f;
            }
        }
        $voices = "'".implode("', '", $voices)."'";

		$buffer .= "<script>";
		$buffer .= "window.addEvent('load', function() {";
		$buffer .= "var gmap = new Gmap($('map_canvas'), {elements_list: ".($list ? 'true' : 'false').", elements_voices: [".$voices."], points_label: '".jsVar(_('Punti di interesse'))."', polylines_label: '".jsVar("Percorsi")."', polygons_label: '"._("Aree")."', empty_search_result: '".jsVar(_("La ricerca non ha prodotto risultati"))."'});";
		$points_ids = $map->points_id();
		$points_polylines = array();
		$points_polygons = array();
		foreach($map->polylines() as $polyline) {
			foreach($polyline->points_id() as $point_id) {
				$points_ids[] = $point_id;
				if(!isset($points_polylines[$point_id]))
					$points_polylines[$point_id] = array($polyline->id);
				else $points_polylines[$point_id][] = $polyline->id;	
			}
		}
		foreach($map->polygons() as $polygon) {
			foreach($polygon->points_id() as $point_id) {
				$points_ids[] = $point_id;
				if(!isset($points_polygons[$point_id]))
					$points_polygons[$point_id] = array($polygon->id);
				else $points_polygons[$point_id][] = $polygon->id;	
			}
		}

		$points_ids = array_unique($points_ids);

		if(count($points_ids)) {
			$points = 'var points = {};';
			foreach($points_ids as $point_id) {
				$point_obj = new gmapsPoint($point_id, $this);
				$point_polylines = isset($points_polylines[$point_id]) ? $points_polylines[$point_id] : array();
				$point_polygons = isset($points_polygons[$point_id]) ? $points_polygons[$point_id] : array();
				$points .= "points.id".$point_obj->id." = ".$point_obj->jsObject('gmap', $point_polylines, $point_polygons, $map->id);
			}
			$buffer .= $points;
			$buffer .= "gmap.addPoints(points);";
		}

		if(count($map->polylines())) {
			$polylines = 'var polylines = {};';
			foreach($map->polylines() as $polyline_obj) {
				$polylines .= "polylines.id".$polyline_obj->id." = ".$polyline_obj->jsObject('gmap', $map->id);
			}
			$buffer .= $polylines;
			$buffer .= "gmap.addPolylines(polylines);";
		}

		if(count($map->polygons())) {
			$polygons = 'var polygons = {};';
			foreach($map->polygons() as $polygon_obj) {
				$polygons .= "polygons.id".$polygon_obj->id." = ".$polygon_obj->jsObject('gmap', $map->id);
			}
			$buffer .= $polygons;
			$buffer .= "gmap.addPolygons(polygons);";
		}

		$buffer .= "gmap.renderMap();";
		$buffer .= "})";
		$buffer .= "</script>";

		return $buffer;
	}

	/**
	 * @brief Metodo chiamabile via ajax per visualizzare il contenuto della infowindow del punto di interesse passato via GET 
	 * 
	 * @access public
	 * @return string il contenuto della infowindow del punto di interesse
	 */
	public function infowindow() {

		$this->setAccess($this->_access_base);

		$point_id = cleanVar($_GET, 'point_id', 'int', '');
		$polyline_id = cleanVar($_GET, 'polyline_id', 'int', '');
		$polygon_id = cleanVar($_GET, 'polygon_id', 'int', '');
		$map_id = cleanVar($_GET, 'map_id', 'int', '');

		if($point_id) {

			$point = new gmapsPoint($point_id, $this);

			$buffer = "<section>";
			$buffer .= "<h1>".htmlCHars($point->ml('label'))."</h1>";

			$nation = $this->_db->getFieldFromId('nation', $_SESSION['lng'], 'id', $point->nation);
			$caddress = htmlChars($point->ml('address').', '.$point->cap.' '.$point->ml('city').', '.$nation);
			$buffer .= "<p><i>".$caddress."</i></p>";

      $remove_images = false;
			if($images = $point->images()) {
				$image = $images[0];
				$buffer .= $image->thumb('left');
        $remove_images = true;
			}
			$buffer .= cutHtmlText(HtmlChars($point->ml('description')), 220, '...', false, false, $remove_images, array('endingPosition'=>'in'));
			$buffer .= "<p><a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array("id"=>$point->id, 'map'=>$map_id))."\">"._("leggi tutto")."</a></p>";
			$buffer .= "<div class=\"null\"></div>";
			$buffer .= "</section>";
		}
		elseif($polyline_id) {
			
			$polyline = new gmapsPolyline($polyline_id, $this);
		
			$buffer = "<section>";
			$buffer .= "<h1>".htmlCHars($polyline->ml('label'))."</h1>";

			$buffer .= cutHtmlText(HtmlChars($polyline->ml('description')), 220, '...', false, false, false, array('endingPosition'=>'in'));
			$buffer .= "<p><a href=\"".$this->_plink->aLink($this->_instanceName, 'itinerary', array("id"=>$polyline->id, 'map'=>$map_id))."\">"._("leggi tutto")."</a></p>";
			$buffer .= "<div class=\"null\"></div>";
			$buffer .= "</section>";
		}
		elseif($polygon_id) {
			
			$polygon = new gmapsPolygon($polygon_id, $this);
		
			$buffer = "<section>";
			$buffer .= "<h1>".htmlCHars($polygon->ml('label'))."</h1>";

			$buffer .= cutHtmlText(HtmlChars($polygon->ml('description')), 220, '...', false, false, false, array('endingPosition'=>'in'));
			$buffer .= "<p><a href=\"".$this->_plink->aLink($this->_instanceName, 'area', array("id"=>$polygon->id, 'map'=>$map_id))."\">"._("leggi tutto")."</a></p>";
			$buffer .= "<div class=\"null\"></div>";
			$buffer .= "</section>";
		}

		return $buffer;

	}

	/**
	 * @brief Scheda completa percorso
	 * 
	 * @access public
	 * @return string scheda del percorso
	 */
	public function itinerary() {

		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
		$registry->addJs(SITE_WWW."/app/gmaps/markerclusterer_packed.js");
		$registry->addJs(SITE_WWW."/app/gmaps/infobox.js");
		$registry->addJs(SITE_WWW."/app/gmaps/MooComplete.js");
		$registry->addJs(SITE_WWW."/app/gmaps/ProgressBar.js");
		$registry->addJs(SITE_WWW."/app/gmaps/gmaps.js");
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");
		$registry->addCss(SITE_WWW."/app/gmaps/MooComplete.css");

		$id = cleanVar($_GET, 'id', 'int', '');
		$map_id = cleanVar($_GET, 'map', 'int', '');

		$polyline = new gmapsPolyline($id, $this);
	
		$link_map = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'viewFull', array('id'=>$map_id))."\">"._("mappa")."</a>";

		$title = $polyline->ml('label');
		$htmlsection = new htmlSection(array('class'=>'public', 'id'=>'itinerary_gmaps_'.$this->_instanceName, 'headerTag'=>'header', 'headerLabel'=>$title, 'headerLinks'=>$link_map));

		$buffer = "<div id=\"itinerary_map\">";
		
		$buffer .= "<nav id=\"gmaps_nav\">";
		$buffer .= "<ul>";
		$buffer .= "<li>";
		$buffer .= _("Help");
		$buffer .= "<ul>";
		$buffer .= "<li data-menu=\"maphelp\">"._("<p>Utilizzare la voce 'Mappa' per modificare il tipo di mappa.<br/>Utilizzare il campo di testo per ricercare punti di interesse o percorsi nella mappa, iniziando a digitare le chiavi di ricerca verranno visualizzati dei suggerimenti, selezionare quello desiderato e premere il bottone 'cerca'. Per tornare alla visualizzazione completa premere il bottone 'tutti'. <br />Se si desidera filtrare per categoria utlizzare l'apposito menu a tendina.</p>")."</li>";
		$buffer .= "</ul>";
		$buffer .= "</li>";
		$buffer .= "<li>";
		$buffer .= _("Mappa");
		$buffer .= "<ul>";
		$buffer .= "<li class=\"link\" data-menu=\"maptype\" data-type=\"hybrid\">"._("Ibrida")."</li>";
		$buffer .= "<li class=\"link selected\" data-menu=\"maptype\" data-type=\"roadmap\">"._("roadmap")."</li>";
		$buffer .= "<li class=\"link\" data-menu=\"maptype\" data-type=\"satellite\">"._("satellite")."</li>";
		$buffer .= "<li class=\"link\" data-menu=\"maptype\" data-type=\"terrain\">"._("terrain")."</li>";
		$buffer .= "</ul>";
		$buffer .= "</li>";
		$buffer .= "<li>";
		$buffer .= "<input type=\"text\" name=\"text_search\" id=\"text_search\" value=\"\" placeholder=\""._("cerca...")."\" />";
		$buffer .= " <input type=\"button\" name=\"button_search\" id=\"button_search\" value=\""._("cerca")."\" />";
		$buffer .= " <input type=\"button\" name=\"button_search_reset\" id=\"button_search_reset\" value=\""._("tutti")."\" />";
		$buffer .= "</li>";

		$buffer .= "<li>";
		$buffer .= "<select id=\"ctg_search\">";
		$buffer .= "<option selected>"._("tutte le categorie")."</option>";
		$buffer .= "<optgroup label=\""._("Punti di interesse")."\">";
		foreach(gmapsPointCtg::getForSelect($this->_instance) as $id=>$label) {
			$buffer .= "<option value=\"point_".$id."\">".$label."</option>";
		}
		$buffer .= "</optgroup>";
		$buffer .= "</select>";
		$buffer .= "</li>";
		$buffer .= "</ul>";
		$buffer .= "</nav>";
		$buffer .= "<div id=\"map_canvas\" class=\"map\"></div>";
		$buffer .= "</div>";	

		$voices = array();
        foreach(explode(',', $this->_list_fields) as $f) {
            $f = trim($f);
            if(in_array($f, array('label', 'categories', 'address', 'cap', 'city', 'services'))) {
                $voices[] = $f;
            }
        }
        $voices = "'".implode("', '", $voices)."'";

		$buffer .= "<script>";
		$buffer .= "window.addEvent('load', function() {";
		$buffer .= "var gmap = new Gmap($('map_canvas'), {elements_list: true, elements_voices: [".$voices."] ,empty_search_result: '".jsVar(_("La ricerca non ha prodotto risultati"))."'});";
		$points_ids = $polyline->points_id();

		if(count($points_ids)) {
			$points = 'var points = {};';
			foreach($points_ids as $point_id) {
				$point_obj = new gmapsPoint($point_id, $this);
				$point_polylines = array($polyline->id);
				$points .= "points.id".$point_obj->id." = ".$point_obj->jsObject('gmap', $point_polylines, array(), $map_id);
			}
			$buffer .= $points;
			$buffer .= "gmap.addPoints(points);";
		}

		$polylines = 'var polylines = {};';
		$polylines .= "polylines.id".$polyline->id." = ".$polyline->jsObject('gmap', $map_id);
		$buffer .= $polylines;
		$buffer .= "gmap.addPolylines(polylines);";
		$buffer .= "gmap.renderMap();";
		$buffer .= "})";
		$buffer .= "</script>";
		$buffer .= htmlChars($polyline->description);

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}			

	/**
	 * @brief Scheda completa area
	 * 
	 * @access public
	 * @return string scheda dell'area
	 */
	public function area() {

		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
		$registry->addJs(SITE_WWW."/app/gmaps/markerclusterer_packed.js");
		$registry->addJs(SITE_WWW."/app/gmaps/infobox.js");
		$registry->addJs(SITE_WWW."/app/gmaps/MooComplete.js");
		$registry->addJs(SITE_WWW."/app/gmaps/ProgressBar.js");
		$registry->addJs(SITE_WWW."/app/gmaps/gmaps.js");
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");
		$registry->addCss(SITE_WWW."/app/gmaps/MooComplete.css");

		$id = cleanVar($_GET, 'id', 'int', '');
		$map_id = cleanVar($_GET, 'map', 'int', '');

		$polygon = new gmapsPolygon($id, $this);
	
		$title = $polygon->ml('label');
		$link_map = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'viewFull', array('id'=>$map_id))."\">"._("mappa")."</a>";

		$htmlsection = new htmlSection(array('class'=>'public', 'id'=>'area_gmaps_'.$this->_instanceName, 'headerTag'=>'header', 'headerLabel'=>$title, 'headerLinks'=>$link_map));

		$buffer = "<div id=\"area_map\">";
		
		$buffer .= "<nav id=\"gmaps_nav\">";
		$buffer .= "<ul>";
		$buffer .= "<li>";
		$buffer .= _("Help");
		$buffer .= "<ul>";
		$buffer .= "<li data-menu=\"maphelp\">"._("<p>Utilizzare la voce 'Mappa' per modificare il tipo di mappa.<br/>Utilizzare il campo di testo per ricercare punti di interesse o aree nella mappa, iniziando a digitare le chiavi di ricerca verranno visualizzati dei suggerimenti, selezionare quello desiderato e premere il bottone 'cerca'. Per tornare alla visualizzazione completa premere il bottone 'tutti'. <br />Se si desidera filtrare per categoria utlizzare l'apposito menu a tendina.</p>")."</li>";
		$buffer .= "</ul>";
		$buffer .= "</li>";
		$buffer .= "<li>";
		$buffer .= _("Mappa");
		$buffer .= "<ul>";
		$buffer .= "<li class=\"link\" data-menu=\"maptype\" data-type=\"hybrid\">"._("Ibrida")."</li>";
		$buffer .= "<li class=\"link selected\" data-menu=\"maptype\" data-type=\"roadmap\">"._("roadmap")."</li>";
		$buffer .= "<li class=\"link\" data-menu=\"maptype\" data-type=\"satellite\">"._("satellite")."</li>";
		$buffer .= "<li class=\"link\" data-menu=\"maptype\" data-type=\"terrain\">"._("terrain")."</li>";
		$buffer .= "</ul>";
		$buffer .= "</li>";
		$buffer .= "<li>";
		$buffer .= "<input type=\"text\" name=\"text_search\" id=\"text_search\" value=\"\" placeholder=\""._("cerca...")."\" />";
		$buffer .= " <input type=\"button\" name=\"button_search\" id=\"button_search\" value=\""._("cerca")."\" />";
		$buffer .= " <input type=\"button\" name=\"button_search_reset\" id=\"button_search_reset\" value=\""._("tutti")."\" />";
		$buffer .= "</li>";

		$buffer .= "<li>";
		$buffer .= "<select id=\"ctg_search\">";
		$buffer .= "<option selected>"._("tutte le categorie")."</option>";
		$buffer .= "<optgroup label=\""._("Punti di interesse")."\">";
		foreach(gmapsPointCtg::getForSelect($this->_instance) as $id=>$label) {
			$buffer .= "<option value=\"point_".$id."\">".$label."</option>";
		}
		$buffer .= "</optgroup>";
		$buffer .= "</select>";
		$buffer .= "</li>";
		$buffer .= "</ul>";
		$buffer .= "</nav>";
		$buffer .= "<div id=\"map_canvas\" class=\"map\"></div>";
		$buffer .= "</div>";	

		$voices = array();
        foreach(explode(',', $this->_list_fields) as $f) {
            $f = trim($f);
            if(in_array($f, array('label', 'categories', 'address', 'cap', 'city', 'services'))) {
                $voices[] = $f;
            }
        }
        $voices = "'".implode("', '", $voices)."'";

		$buffer .= "<script>";
		$buffer .= "window.addEvent('load', function() {";
		$buffer .= "var gmap = new Gmap($('map_canvas'), {elements_list: true, elements_voices: [".$voices."] ,empty_search_result: '".jsVar(_("La ricerca non ha prodotto risultati"))."'});";
		$points_ids = $polygon->points_id();

		if(count($points_ids)) {
			$points = 'var points = {};';
			foreach($points_ids as $point_id) {
				$point_obj = new gmapsPoint($point_id, $this);
				$point_polygons = array($polygon->id);
				$points .= "points.id".$point_obj->id." = ".$point_obj->jsObject('gmap', array(), $point_polygons, $map_id);
			}
			$buffer .= $points;
			$buffer .= "gmap.addPoints(points);";
		}

		$polygons = 'var polygons = {};';
		$polygons .= "polygons.id".$polygon->id." = ".$polygon->jsObject('gmap', $map_id);
		$buffer .= $polygons;
		$buffer .= "gmap.addPolygons(polygons);";
		$buffer .= "gmap.renderMap();";
		$buffer .= "})";
		$buffer .= "</script>";
		$buffer .= htmlChars($polygon->description);

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Scheda del punto di interesse 
	 * 
	 * @access public
	 * @return string scheda del punto di interesse
	 */
	public function point() {

		$registry = registry::instance();

		$id = cleanVar($_GET, 'id', 'int', '');
		$map_id = cleanVar($_GET, 'map', 'int', '');

		$point = new gmapsPoint($id, $this);

		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");
	
		$title = $point->ml('label');
		$link_map = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'viewFull', array('id'=>$map_id))."\">"._("Mappa")."</a>";

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header', 'headerLabel'=>$link_map.' > '.$title));

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>''));	

		$links = array();

		if(count($point->collections())) {
			$link_collections = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array("id"=>$point->id, 'map'=>$map_id, 'block'=>'collections'))."\">"._("Collezioni")."</a>";
			$links[] = $link_collections;
		}
		if(count($point->attachments())) {
			$link_attachments = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array("id"=>$point->id, 'map'=>$map_id, 'block'=>'attachments'))."\">"._("Allegati")."</a>";
			$links[] = $link_attachments;
		}
		if(count($point->events())) {
			$link_events = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array("id"=>$point->id, 'map'=>$map_id, 'block'=>'events'))."\">"._("Eventi")."</a>";
			$links[] = $link_events;
		}
		if(count($point->videos())) {
			$link_videos = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array("id"=>$point->id, 'map'=>$map_id, 'block'=>'videos'))."\">"._("Video")."</a>";
			$links[] = $link_videos;
		}
		if(count($point->images())) {
			$link_images = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array("id"=>$point->id, 'map'=>$map_id, 'block'=>'images'))."\">"._("Immagini")."</a>";
			$links[] = $link_images;
		}
		$link_info = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array("id"=>$point->id, 'map'=>$map_id, 'block'=>'info'))."\">"._("Informazioni")."</a>";
		$links[] = $link_info;
		$link_main = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array("id"=>$point->id, 'map'=>$map_id))."\">"._("Descrizione")."</a>";
		$links[] = $link_main;
		$sel_link = $link_main;

		if($this->_block == 'images') {
			$buffer = $this->pointImages($point);		
			$sel_link = $link_images;
		}
		elseif($this->_block == 'videos') {
			$buffer = $this->pointVideos($point);		
			$sel_link = $link_videos;
		}
		elseif($this->_block == 'events') {
			$buffer = $this->pointEvents($point);		
			$sel_link = $link_events;
		}
		elseif($this->_block == 'attachments') {
			$buffer = $this->pointAttachments($point);		
			$sel_link = $link_attachments;
		}
		elseif($this->_block == 'info') {
			$buffer = $this->pointInfo($point);		
			$sel_link = $link_info;
		}
		elseif($this->_block == 'collections') {
			$buffer = $this->pointCollections($point);		
			$sel_link = $link_collections;
		}
		else {
			$buffer = $this->pointMain($point);		
			$sel_link = $link_main;
		}

		$htmltab->navigationLinks = $links;
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $buffer;
		$buffer = $htmltab->render();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Collezioni associate al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint
	 * @access private
	 * @return string galleria di immagini associate al punto di interesse
	 */
	private function pointCollections($point) {
			
		$registry = registry::instance();
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");

		$collection_id = cleanVar($_GET, 'collection', 'int', '');

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header'));

		if($collection_id) {
			$registry->addJs("http://ajs.otto.to.it/sources/dev/ajs/ajs.js");
			$collection = new gmapsCollection($collection_id, $this);

			$buffer = "<h2>".htmlChars($collection->ml('name'))." - <a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array('id'=>$point->id, 'block'=>'collections'), '')."\">"._("tutte le collezioni")."</a></h2>";
			$buffer .= "<div id=\"img_container\">";
			$buffer .= "</div>";

			$js_images = "var images = [";
			$images = array();
			foreach($collection->images() as $img) {
			$images[] = "{
				thumb: '".$img->thumbPath()."',
				img: '".$img->path()."',
				title: '".jsVar($img->ml('title'))."',
				description: '".jsVar($img->ml('description'))."',
				credits: '".jsVar($img->ml('credits'))."'
				}";
			}
			$js_image = "var images = [".implode(',', $images)."];";

			$buffer .= "<script>";
			$buffer .= $js_image."\n";
			$buffer .= "ajs.use(['ajs.ui.moogallery'], function() {
				var mygallery = new ajs.ui.moogallery('img_container', images);
			})";
			$buffer .= "</script>";
		}
		else {

			if(count($point->collections())) {
				$buffer = "<table class=\"gmaps_collections\">";	
				foreach($point->collections() as $collection) {
					$buffer .= "<tr>";
					$buffer .= "<td><a href=\"".$collection->path()."\" rel=\"lightbox\"><img src=\"".$collection->thumbPath()."\" /></a></td>";
					$buffer .= "<td>";
					$buffer .= "<h2>".htmlChars($collection->ml('name'))."</h2>";
					$buffer .= "<p>".htmlChars($collection->ml('description'))."</p>";
					$buffer .= "<p><a href=\"".$this->_plink->aLink($this->_instanceName, 'point', array('id'=>$point->id, 'block'=>'collections', 'collection'=>$collection->id), '')."\">"._("Vedi gli oggetti")."</a></p>";
					$buffer .= "</td>";
					$buffer .= "</tr>";
				}
				$buffer .= "</table>";
			}
			else {
				$buffer = "<p>"._("Non risultano collezioni pubblicate")."</p>";
			}
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Galleria di immagini associate al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint
	 * @access private
	 * @return string galleria di immagini associate al punto di interesse
	 */
	private function pointImages($point) {

		$registry = registry::instance();
		$registry->addJs("http://ajs.otto.to.it/sources/dev/ajs/ajs.js");
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header'));

		$buffer = "<div id=\"img_container\">";
		$buffer .= "</div>";

		$js_images = "var images = [";
		$images = array();
		foreach($point->images() as $img) {
			$images[] = "{
				thumb: '".$img->thumbPath()."',
				img: '".$img->path()."',
				title: '".jsVar($img->ml('title'))."',
				description: '".jsVar($img->ml('description'))."',
				credits: '".jsVar($img->ml('credits'))."'
			}";
		}
		$js_image = "var images = [".implode(',', $images)."];";

		$buffer .= "<script>";
		$buffer .= $js_image."\n";
		$buffer .= "ajs.use(['ajs.ui.moogallery'], function() {
			var mygallery = new ajs.ui.moogallery('img_container', images);
		})";
		$buffer .= "</script>";

		$htmlsection->content = $buffer;

		return $htmlsection->render();
			

	}

	/**
	 * @brief Galleria di video associati al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint
	 * @access private
	 * @return string galleria di video associati al punto di interesse
	 */
	private function pointVideos($point) {

		$registry = registry::instance();
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header'));

		if(count($point->videos())) {
			$buffer = "<table class=\"gmaps_video\">";	
			foreach($point->videos() as $video) {
				$buffer .= "<tr>";
				$buffer .= "<td>".$video->code."</td>";
				$buffer .= "<td>";
				$buffer .= "<p class=\"title\">".htmlChars($video->ml('title'))."</p>";
				$buffer .= "<p>".htmlChars($video->ml('description'))."</p>";
				if($video->credits) {
					$buffer .= "<p>"._("credits").': '.htmlChars($video->ml('credits'))."</p>";
				}
				$buffer .= "</td>";
				$buffer .= "</tr>";
			}
			$buffer .= "</table>";
		}
		else {
			$buffer = "<p>"._("Non risultano video pubblicati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
			

	}

	/**
	 * @brief Eventi associati al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint
	 * @access private
	 * @return string lista eventi associati al punto di interesse
	 */
	private function pointEvents($point) {

		$registry = registry::instance();
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header'));

		if(count($point->events())) {
			$buffer = "<table class=\"gmaps_event\">";	
			$buffer .= "<tr>";
			$buffer .= "<th class=\"thIcon\"></th>";
			$buffer .= "<th>"._("Data")."</th>";
			$buffer .= "<th>"._("Durata")."</th>";
			$buffer .= "<th>"._("Evento")."</th>";
			$buffer .= "<th>"._("Descizione")."</th>";
			$buffer .= "</tr>";
			foreach($point->events() as $event) {
				$buffer .= "<tr>";
				$buffer .= "<td><a href=\"".$event->imagePath()."\" rel=\"lightbox\"><img src=\"".$event->thumbPath()."\" /></a></td>";
				$buffer .= "<td>".dbDateToDate($event->date, '/')."</td>";
				$buffer .= "<td>".$event->duration."</td>";
				$buffer .= "<td>".htmlChars($event->ml('name'))."</td>";
				$buffer .= "<td>".htmlChars($event->ml('description'))."</td>";
				$buffer .= "</tr>";
			}
			$buffer .= "</table>";
		}
		else {
			$buffer = "<p>"._("Non risultano eventi pubblicati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
			

	}
	
	/**
	 * @brief Allegati associati al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint
	 * @access private
	 * @return string lista allegati associati al punto di interesse
	 */
	private function pointAttachments($point) {

		$registry = registry::instance();
		$registry->addCss(SITE_WWW."/app/gmaps/gmaps_".$this->_instanceName.".css");

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header'));

		if(count($point->attachments())) {
			$buffer = "<table class=\"gmaps_attachment\">";	
			foreach($point->attachments() as $a) {
				$buffer .= "<tr>";
				$buffer .= "<td><a href=\"".$a->filePath()."\">".pub::icon('download')."</a></td>";
				$buffer .= "<td>".$a->filename."</td>";
				$buffer .= "<td>".round($a->size/1000, 1)." Kb</td>";
				$buffer .= "<td>".htmlChars($a->ml('description'))."</td>";
				$buffer .= "</tr>";
			}
			$buffer .= "</table>";
		}
		else {
			$buffer = "<p>"._("Non risultano allegati pubblicati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
			

	}

	/**
	 * @brief Informazioni punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint
	 * @access private
	 * @return string informazioni punto di interesse
	 */
	private function pointInfo($point) {
		
		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header'));

		$buffer = '';

		$categories = array();
		foreach($point->ctgs() as $ctg) {
			$categories[] = htmlChars($ctg->ml('name'));
		}

		$buffer .= "<p><b>"._("Categorie")."</b>: ".implode(', ', $categories)."</p>";

		$services = array();
		foreach($point->services() as $service) {
			$services[] = htmlChars($service->ml('name'));
		}

		if(count($services)) {
			$buffer .= "<p><b>"._("Servizi")."</b>: ".implode(', ', $services)."</p>";
		}

		if($point->phone) {
			$buffer .= "<p><b>"._("Telefono")."</b>: ".$point->phone."</p>";
		}

		if($point->email) {
			$buffer .= "<p><b>"._("Email")."</b>: <a href=\"mailto:".$point->email."\">".$point->email."</a></p>";
		}

		if($point->web) {
			$buffer .= "<p><b>"._("Sito web")."</b>: <a href=\"".$point->web."\">".$point->web."</a></p>";
		}

		$nation = $this->_db->getFieldFromId('nation', $_SESSION['lng'], 'id', $point->nation);
		$buffer .= "<p><b>"._("Indirizzo")."</b>: ".htmlChars($point->ml('address').', '.$point->cap.' '.$point->ml('city').', '.$nation)."</p>";

		$buffer .= htmlChars($point->ml('information'));

		if($point->opening_hours) {
			$buffer .= "<p><b>"._("Orari di apertura")."</b>: ".$point->ml('opening_hours')."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Descrizione punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint
	 * @access private
	 * @return string descrizione punto di interesse
	 */
	private function pointMain($point) {
		
		$title = _("Descrizione");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header'));

		$buffer = '';

		$buffer = htmlChars($point->ml('description'));

		$buffer .= "<div class=\"null\"></div>";

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Punto di ingresso per il backoffice
	 * 
	 * @access public
	 * @return string il back-office del modulo
	 */
	public function manageDoc() {
	
		$this->accessGroup('ALL');
		
		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_instanceLabel));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=permissions\">"._("Permessi")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=options\">"._("Opzioni")."</a>";
		$link_polygon = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon\">"._("Aree")."</a>";
		$link_polygon_ctg = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_ctg\">"._("Ctg A")."</a>";
		$link_polyline = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline\">"._("Percorsi")."</a>";
		$link_polyline_ctg = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_ctg\">"._("Ctg P")."</a>";
		$link_point = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point\">"._("Punti di interesse")."</a>";
		$link_point_ctg = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_ctg\">"._("Ctg PI")."</a>";
		$link_service = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=service\">"._("Servizi")."</a>";
		$link_marker = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=marker\">"._("Markers")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]\">"._("Mappe")."</a>";
		$sel_link = $link_dft;

		if($this->_block == 'options') {
			$buffer = sysfunc::manageOptions($this->_instance, $this->_className);		
			$sel_link = $link_options;
		}
		elseif($this->_block == 'permissions') {
			$buffer = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		elseif($this->_block == 'service') {
			$buffer = $this->manageService();
			$sel_link = $link_service;
		}
		elseif($this->_block == 'marker') {
			$buffer = $this->manageMarker();
			$sel_link = $link_marker;
		}
		elseif($this->_block == 'point_ctg') {
			$buffer = $this->managePointCtg();
			$sel_link = $link_point_ctg;
		}
		elseif($this->_block == 'point_image') {
			$buffer = $this->managePointImage();
			$sel_link = $link_point;
		}
		elseif($this->_block == 'point_video') {
			$buffer = $this->managePointVideo();
			$sel_link = $link_point;
		}
		elseif($this->_block == 'point_event') {
			$buffer = $this->managePointEvent();
			$sel_link = $link_point;
		}
		elseif($this->_block == 'point_attachment') {
			$buffer = $this->managePointAttachment();
			$sel_link = $link_point;
		}
		elseif($this->_block == 'point_collection') {
			$buffer = $this->managePointCollection();
			$sel_link = $link_point;
		}
		elseif($this->_block == 'point') {
			$buffer = $this->managePoint();
			$sel_link = $link_point;
		}
		elseif($this->_block == 'polygon') {
			$buffer = $this->managePolygon();
			$sel_link = $link_polygon;
		}
		elseif($this->_block == 'polygon_point') {
			$buffer = $this->managePolygonPoint();
			$sel_link = $link_polygon;
		}
		elseif($this->_block == 'polygon_ctg') {
			$buffer = $this->managePolygonCtg();
			$sel_link = $link_polygon_ctg;
		}
		elseif($this->_block == 'polyline') {
			$buffer = $this->managePolyline();
			$sel_link = $link_polyline;
		}
		elseif($this->_block == 'polyline_point') {
			$buffer = $this->managePolylinePoint();
			$sel_link = $link_polyline;
		}
		elseif($this->_block == 'polyline_ctg') {
			$buffer = $this->managePolylineCtg();
			$sel_link = $link_polyline_ctg;
		}
		elseif($this->_block == 'map_item') {
			$buffer = $this->manageMapItem();
		}
		else {
			$buffer = $this->manageMap();
		}
			
		$htmltab->navigationLinks = array($link_admin, $link_options, $link_service, $link_polygon_ctg, $link_polygon, $link_polyline_ctg, $link_polyline, $link_point_ctg, $link_point, $link_marker, $link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $buffer;
		return $htmltab->render();

	}

	/**
	 * @brief Back office dei marker utilizzati per segnare punti di interesse sulla mappa
	 * 
	 * @access private
	 * @return string la sezione amministrativa di gestione dei marker
	 */
	private function manageMarker() {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$marker = new gmapsMarker($id, $this);
		$marker->instance = $this->_instance;

		$col1 = $this->listMarker($id);

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col2 = $marker->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=marker");
		}
		elseif($this->_action == 'delete') {
			$res = $marker->delete();
			if($res === 'points') {
				exit(error::errorMessage(array('error'=>_("Il marker è utilizzato da uno o più punti di interesse. Eliminarli o cambiarne il marker e riprovare")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=marker"));
			}
			elseif($res === false) {
				exit(error::errorMessage(array('error'=>_("Impossibile eliminare il marker, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=marker"));
			}
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=marker");
		}
		else {
			$col2 = $this->infoMarker();
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;
	}

	/**
	 * @brief Lista amministrativa dei marker 
	 * 
	 * @param int $sel_id identificativo del marker selezionato 
	 * @access private
	 * @return string Lista dei marker
	 */
	private function listMarker($sel_id) {
	
		$title = _("Markers");
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=marker&action=insert\">".pub::icon('insert', _("nuovo marker"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		$marker_ids = gmapsMarker::get($this->_instance);

		if($tot = count($marker_ids)) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($marker_ids as $marker_id) {
				$marker = new gmapsMarker($marker_id, $this);
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=marker&action=modify&id=".$marker->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare il servizio?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=marker&action=delete&id=".$marker->id."';\">".pub::icon('delete')."</span>";
				$selected = $marker->id == $sel_id ? true : false;
				$buffer .= $htmlList->item(htmlChars($marker->label), array($link_modify, $link_delete), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano markers registrati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Informazioni sull'amministrazione dei servizi 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione dei servizi
	 */
	private function infoMarker() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione dei marker associabili ai punti di interesse");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}


	/**
	 * @brief Back office degli allegati associati ad un punto di interesse
	 * 
	 * @access private
	 * @return string la sezione amministrativa di gestione degli allegati associati ad un punto di interesse
	 */
	private function managePointAttachment() {
		
		$point_id = cleanVar($_REQUEST, 'point_id', 'int', '');
		$point = new gmapsPoint($point_id, $this);
		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$attachment = new gmapsAttachment($id, $this);
		$attachment->point_id = $point->id;

		$col1 = $this->listAttachment($point, $attachment);

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col2 = $attachment->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_attachment&point_id=$point_id");
		}
		elseif($this->_action == 'delete') {
			$res = $attachment->delete();
			if($res === false) {
				exit(error::errorMessage(array('error'=>_("Impossibile eliminare l'allegato, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_attachment&point_id=".$point_id));
			}
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=point_attachment&point_id=".$point_id);
		}
		else {
			$col2 = $this->infoAttachment();
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;
	}

	/**
	 * @brief Back office delle collezioni associati ad un punto di interesse
	 * 
	 * @access private
	 * @return string la sezione amministrativa di gestione delle collezioni associate ad un punto di interesse
	 */
	private function managePointCollection() {
		
		$point_id = cleanVar($_REQUEST, 'point_id', 'int', '');
		$point = new gmapsPoint($point_id, $this);
		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$mng = cleanVar($_REQUEST, 'mng', 'string', '');
		$collection = new gmapsCollection($id, $this);
		$collection->point_id = $point->id;

		$col1 = $this->listCollection($point, $collection);

		if($mng == 'image') {
			$image_id = cleanVar($_REQUEST, 'image_id', 'int', '');
			$image = new gmapsCollectionImage($image_id, $this);
			$image->collection_id = $collection->id;

			if(in_array($this->_action, array('insert_image', 'modify_image'))) {
				$col2 = $image->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&point_id=$collection->point_id&id=$collection->id&mng=image");
			}
			elseif($this->_action == 'delete_image') {
				$res = $image->delete();
				if($res === false) {
					exit(error::errorMessage(array('error'=>_("Impossibile eliminare l'immagine, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&point_id=".$collection->point_id."&id=".$collection->id));
				}
				EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=point_collection&point_id=".$collection->point_id."&id=".$collection->id);
			}
			else {
				$col2 = $this->listCollectionImage($collection);
			}
		}
		else {
			if(in_array($this->_action, array('insert', 'modify'))) {
				$col2 = $collection->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&point_id=$point_id");
			}
			elseif($this->_action == 'delete') {
				$res = $collection->delete();
				if($res === false) {
					exit(error::errorMessage(array('error'=>_("Impossibile eliminare la collezione, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&point_id=".$point_id));
				}
				EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=point_collection&point_id=".$point_id);
			}
			else {
				$col2 = $this->infoCollection();
			}
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;
	}

	/**
	 * @brief Lista amministrativa degli allegati associati al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint 
	 * @param object $sel_attachment allegato (@ref gmapsAttachment) selezionato
	 * @access private
	 * @return string lista degli allegati associati al punto di interesse
	 */
	private function listAttachment($point, $sel_attachment) {
		
		$title = sprintf(_("Allegati del punto di interesse '%s'"), htmlChars($point->label));
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_attachment&point_id=".$point->id."&action=insert\">".pub::icon('insert', _("nuovo allegato"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		if($tot = count($point->attachments())) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($point->attachments() as $attachment) {
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_attachment&action=modify&point_id=".$point->id."&id=".$attachment->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare l'allegato?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_attachment&action=delete&point_id=".$point->id."&id=".$attachment->id."';\">".pub::icon('delete')."</span>";
				$selected = $attachment->id == $sel_attachment->id ? true : false;
				$buffer .= $htmlList->item(htmlChars($attachment->name), array($link_modify, $link_delete), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano allegati registrati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Lista amministrativa delle collezioni associate al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint 
	 * @param object $sel_collection collezione (@ref gmapsCollection) selezionata
	 * @access private
	 * @return string lista delle collezioni associate al punto di interesse
	 */
	private function listCollection($point, $sel_collection) {
		
		$title = sprintf(_("Collezioni del punto di interesse '%s'"), htmlChars($point->label));
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&point_id=".$point->id."&action=insert\">".pub::icon('insert', _("nuova collezione"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		if($tot = count($point->collections())) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($point->collections() as $collection) {
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&action=modify&point_id=".$point->id."&id=".$collection->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare la collezione?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&action=delete&point_id=".$point->id."&id=".$collection->id."';\">".pub::icon('delete')."</span>";
				$link_images = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&action=modify&point_id=".$point->id."&id=".$collection->id."&mng=image\">".$this->_icon_image."</a>";
				$selected = $collection->id == $sel_collection->id ? true : false;
				$buffer .= $htmlList->item(htmlChars($collection->name), array($link_modify, $link_delete, $link_images), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano collezioni registrate")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Lista amministrativa delle immagini associate ad una collezione 
	 * 
	 * @param gmapsCollection $collection istanza di una collezione
	 * @access private
	 * @return lista delle immagini associate ad una collezione
	 */
	private function listCollectionImage($collection) {

		$title = sprintf(_("Immagini della collezione '%s'"), htmlChars($collection->name));
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&point_id=".$collection->point_id."&id=".$collection->id."&mng=image&action=insert_image\">".pub::icon('insert', _("nuova collezione"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		if($tot = count($collection->images())) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($collection->images() as $image) {
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&action=modify_image&point_id=".$collection->point_id."&id=".$collection->id."&mng=image&image_id=".$image->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare l'immagine?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&action=delete_image&point_id=".$collection->point_id."&id=".$collection->id."&mng=image&image_id=".$image->id."';\">".pub::icon('delete')."</span>";
				$buffer .= $htmlList->item(htmlChars($image->title), array($link_modify, $link_delete), false, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano immagini registrate")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Informazioni sull'amministrazione degli allegati 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione degli allegati
	 */
	private function infoAttachment() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione degli allegati associati al punto di interesse");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}

	/**
	 * @brief Informazioni sull'amministrazione delle collezioni 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione delle collezioni
	 */
	private function infoCollection() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione delle collezioni associate al punto di interesse, Ciasuna collezione può contenere un numero variabile di immagini.");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}

	/**
	 * @brief Back office degli eventi associati ad un punto di interesse
	 * 
	 * @access private
	 * @return string la sezione amministrativa di gestione degli eventi associati ad un punto di interesse
	 */
	private function managePointEvent() {
		
		$point_id = cleanVar($_REQUEST, 'point_id', 'int', '');
		$point = new gmapsPoint($point_id, $this);
		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$event = new gmapsEvent($id, $this);
		$event->point_id = $point->id;

		$col1 = $this->listEvent($point, $event);

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col2 = $event->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_event&point_id=$point_id");
		}
		elseif($this->_action == 'delete') {
			$res = $event->delete();
			if($res === false) {
				exit(error::errorMessage(array('error'=>_("Impossibile eliminare l'evento, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_event&point_id=".$point_id));
			}
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=point_event&point_id=".$point_id);
		}
		else {
			$col2 = $this->infoEvent();
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;
	}

	/**
	 * @brief Lista amministrativa degli eventi associati al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint 
	 * @param object $sel_event video (@ref gmapsEvent) selezionato
	 * @access private
	 * @return string lista degli eventi associati al punto di interesse
	 */
	private function listEvent($point, $sel_event) {
		
		$title = sprintf(_("Eventi del punto di interesse '%s'"), htmlChars($point->label));
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_event&point_id=".$point->id."&action=insert\">".pub::icon('insert', _("nuovo evento"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		if($tot = count($point->events())) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($point->events() as $event) {
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_event&action=modify&point_id=".$point->id."&id=".$event->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare l'evento?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_event&action=delete&point_id=".$point->id."&id=".$event->id."';\">".pub::icon('delete')."</span>";
				$selected = $event->id == $sel_event->id ? true : false;
				$buffer .= $htmlList->item(htmlChars($event->name), array($link_modify, $link_delete), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano eventi registrati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Informazioni sull'amministrazione degli eventi 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione degli eventi
	 */
	private function infoEvent() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione degli eventi associati al punto di interesse");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}

	/**
	 * @brief Back office dei video associati ad un punto di interesse
	 * 
	 * @access private
	 * @return string la sezione amministrativa di gestione dei video associati ad un punto di interesse
	 */
	private function managePointVideo() {
		
		$point_id = cleanVar($_REQUEST, 'point_id', 'int', '');
		$point = new gmapsPoint($point_id, $this);
		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$video = new gmapsVideo($id, $this);
		$video->point_id = $point->id;

		$col1 = $this->listVideo($point, $video);

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col2 = $video->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_video&point_id=$point_id");
		}
		elseif($this->_action == 'delete') {
			$res = $video->delete();
			if($res === false) {
				exit(error::errorMessage(array('error'=>_("Impossibile eliminare il video, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_video&point_id=".$point_id));
			}
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=point_video&point_id=".$point_id);
		}
		else {
			$col2 = $this->infoVideo();
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;
	}

	/**
	 * @brief Lista amministrativa dei video associati al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint 
	 * @param object $sel_video video (@ref gmapsVideo) selezionato
	 * @access private
	 * @return string lista dei video associati al punto di interesse
	 */
	private function listVideo($point, $sel_video) {
		
		$title = sprintf(_("Video del punto di interesse '%s'"), htmlChars($point->label));
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_video&point_id=".$point->id."&action=insert\">".pub::icon('insert', _("nuovo video"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		if($tot = count($point->videos())) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($point->videos() as $video) {
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_video&action=modify&point_id=".$point->id."&id=".$video->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare il video?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_video&action=delete&point_id=".$point->id."&id=".$video->id."';\">".pub::icon('delete')."</span>";
				$selected = $video->id == $sel_video->id ? true : false;
				$buffer .= $htmlList->item(htmlChars($video->title), array($link_modify, $link_delete), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano video registrati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Informazioni sull'amministrazione dei video 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione dei video
	 */
	private function infoVideo() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione dei video associati al punto di interesse");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}

	/**
	 * @brief Back office delle immagini associate ad un punto di interesse
	 * 
	 * @access private
	 * @return string la sezione amministrativa di gestione delle immagini associate ad un punto di interesse
	 */
	private function managePointImage() {
		
		$point_id = cleanVar($_REQUEST, 'point_id', 'int', '');
		$point = new gmapsPoint($point_id, $this);
		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$image = new gmapsImage($id, $this);
		$image->point_id = $point->id;

		$col1 = $this->listImage($point, $image);

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col2 = $image->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_image&point_id=$point_id");
		}
		elseif($this->_action == 'delete') {
			$res = $image->delete();
			if($res === false) {
				exit(error::errorMessage(array('error'=>_("Impossibile eliminare l'immagine, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_image&point_id=".$point_id));
			}
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=point_image&point_id=".$point_id);
		}
		else {
			$col2 = $this->infoImage();
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;
	}

	/**
	 * @brief Lista amministrativa delle immagini associate al punto di interesse 
	 * 
	 * @param object $point istanza di @ref gmapsPoint 
	 * @param object $sel_image immagine (@ref gmapsImage) selezionata
	 * @access private
	 * @return string lista delle immagini associate al punto di interesse
	 */
	private function listImage($point, $sel_image) {
		
		$title = sprintf(_("Immagini del punto di interesse '%s'"), htmlChars($point->label));
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_image&point_id=".$point->id."&action=insert\">".pub::icon('insert', _("nuova immagine"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		if($tot = count($point->images())) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($point->images() as $image) {
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_image&action=modify&point_id=".$point->id."&id=".$image->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare l'immagine?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_image&action=delete&point_id=".$point->id."&id=".$image->id."';\">".pub::icon('delete')."</span>";
				$selected = $image->id == $sel_image->id ? true : false;
				$buffer .= $htmlList->item(htmlChars($image->title), array($link_modify, $link_delete), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano immagini registrate")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Informazioni sull'amministrazione delle immagini 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione delle immagini
	 */
	private function infoImage() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione delle immagini associate al punto di interesse");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}

	/**
	 * @brief Back office dei servizi
	 * 
	 * @access private
	 * @return string la sezione amministrativa di gestione dei servizi
	 */
	private function manageService() {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$service = new gmapsService($id, $this);
		$service->instance = $this->_instance;

		$col1 = $this->listService($id);

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col2 = $service->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=service");
		}
		elseif($this->_action == 'delete') {
			$res = $service->delete();
			if($res === 'points') {
				exit(error::errorMessage(array('error'=>_("Il servizio è presente in uno o più punti di interesse. Eliminarli o cambiarne il servizio e riprovare")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=service"));
			}
			elseif($res === false) {
				exit(error::errorMessage(array('error'=>_("Impossibile eliminare il servizio, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=service"));
			}
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=service");
		}
		else {
			$col2 = $this->infoService();
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;

	}

	/**
	 * @brief Lista amministrativa dei servizi 
	 * 
	 * @param int $sel_id identificativo del servizio selezionato 
	 * @access private
	 * @return string Lista dei servizi
	 */
	private function listService($sel_id) {
	
		$title = _("Servizi");
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=service&action=insert\">".pub::icon('insert', _("nuovo servizio"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		$service_ids = gmapsService::get($this->_instance);

		if($tot = count($service_ids)) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($service_ids as $service_id) {
				$service = new gmapsService($service_id, $this);
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=service&action=modify&id=".$service->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare il servizio?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=service&action=delete&id=".$service->id."';\">".pub::icon('delete')."</span>";
				$selected = $service->id == $sel_id ? true : false;
				$buffer .= $htmlList->item(htmlChars($service->name), array($link_modify, $link_delete), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano servizi registrati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Informazioni sull'amministrazione dei servizi 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione dei servizi
	 */
	private function infoService() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione dei servizi associabili ai punti di interesse");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}

	/**
	 * @brief Back-office deglie elementi di una mappa 
	 * 
	 * @access private
	 * @return string la sezione amministrativa degli elementi di una mappa
	 */
	private function manageMapItem() {
		
		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$map = new gmapsMap($id, $this);

		$title = sprintf(_("Punti di interesse, percorsi ed aree associati alla mappa '%s'"), htmlChars($map->name));
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = '';
		
		if(isset($_POST['submit_add'])) {
			$add_point_ids = cleanVar($_POST, 'point_add', 'array', '');
			$res = $map->addPoints($add_point_ids);
			if(count($add_point_ids) && !$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=map_item&id=$id"));
			}

			$add_polylines_ids = cleanVar($_POST, 'polyline_add', 'array', '');
			$res = $map->addPolylines($add_polylines_ids);
			
			if(count($add_polylines_ids) && !$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=map_item&id=$id"));
			}

			$add_polygons_ids = cleanVar($_POST, 'polygon_add', 'array', '');
			$res = $map->addPolygons($add_polygons_ids);
			
			if(count($add_polygons_ids) && !$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=map_item&id=$id"));
			}
			else {
				EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=map_item&id=".$id);
			}
		}
		elseif(isset($_POST['submit_remove'])) {
			$remove_point_ids = cleanVar($_POST, 'point_remove', 'array', '');
			$res = $map->removePoints($remove_point_ids);
			if(count($remove_point_ids) && !$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=map_item&id=$id"));
			}

			$remove_polyline_ids = cleanVar($_POST, 'polyline_remove', 'array', '');
			$res = $map->removePolylines($remove_polyline_ids);
			if(count($remove_polyline_ids) && !$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=map_item&id=$id"));
			}

			$remove_polygon_ids = cleanVar($_POST, 'polygon_remove', 'array', '');
			$res = $map->removePolygons($remove_polygon_ids);
			if(count($remove_polygon_ids) && !$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=map_item&id=$id"));
			}

			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=map_item&id=".$id);
		}

		$points_code = '';
		$polylines_code = '';
		$polygons_code = '';
		$buffer .= "<h2>"._("Punti di interesse, percorsi ed aree presenti")."</h2>";
		if(count($map->points()) || count($map->polylines()) || count($map->polygons())) {
			$gform = new Form('form_remove_map_point', 'post', false, array('tblLayout'=>false));
			$buffer .= $gform->form('', false, '');
			$buffer .= $gform->hidden('id', $id);
			$buffer .= "<table class=\"generic\" style=\"margin-bottom: 10px;\">";			

			if(count($map->points())) {

				$buffer .= "<tr>";
				$buffer .= "<th>Etichetta punto di interesse</th>";
				$buffer .= "<th>Rimuovi</th>";
				$buffer .= "</tr>";
				foreach($map->points() as $point) {
					$points_code .= $point->gmapCode('map', 'bounds');
					$buffer .= "<tr>";
					$buffer .= "<td>".htmlChars($point->label)."</td>";
					$buffer .= "<td><input type=\"checkbox\" name=\"point_remove[]\" value=\"".$point->id."\" /></td>";
					$buffer .= "</tr>";
				}
			}
			if(count($map->polylines())) {

				$buffer .= "<tr>";
				$buffer .= "<th>Etichetta percorso</th>";
				$buffer .= "<th>Rimuovi</th>";
				$buffer .= "</tr>";
				foreach($map->polylines() as $polyline) {
					$polylines_code .= $polyline->gmapCode('map', 'bounds');
					$buffer .= "<tr>";
					$buffer .= "<td>".htmlChars($polyline->label)."</td>";
					$buffer .= "<td><input type=\"checkbox\" name=\"polyline_remove[]\" value=\"".$polyline->id."\" /></td>";
					$buffer .= "</tr>";
				}
			}
			if(count($map->polygons())) {

				$buffer .= "<tr>";
				$buffer .= "<th>Etichetta percorso</th>";
				$buffer .= "<th>Rimuovi</th>";
				$buffer .= "</tr>";
				foreach($map->polygons() as $polygon) {
					$polygons_code .= $polygon->gmapCode('map', 'bounds');
					$buffer .= "<tr>";
					$buffer .= "<td>".htmlChars($polygon->label)."</td>";
					$buffer .= "<td><input type=\"checkbox\" name=\"polygon_remove[]\" value=\"".$polygon->id."\" /></td>";
					$buffer .= "</tr>";
				}
			}
			$buffer .= "</table>";
			$buffer .= "<p>".$gform->input('submit_remove', 'submit', _("rimuovi selezionati"), array('classField'=>'submit'))."</p>";
			$buffer .= $gform->cform();
		}
		else {
			$buffer .= "<p class=\"message\">"._("Non risultano punti di interesse, percorsi o aree associati")."</p>";
		}

		$buffer .= "<h2>Situazione attuale</h2>";
		$buffer .= "<div id=\"map_canvas\" style=\"width:100%; height: 300px;\"></div>";
		$buffer .= "<script>";
		$buffer .= "window.addEvent('load', function() {
			var myopt = {
				center: new google.maps.LatLng(45, 7),
				zoom: 10,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}

			var map = new google.maps.Map($('map_canvas'), myopt);
			var bounds = new google.maps.LatLngBounds();
			".$points_code."
			".$polylines_code."
			".$polygons_code."
			map.fitBounds(bounds);

		})";		
		$buffer .= "</script>";

		$buffer .= "<h2>"._("Ricerca ed aggiunta punti, percorsi ed aree")."</h2>";
	
		$buffer .= $this->formAddMapPolygonPolylinePoint($map);

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
	
	/**
	 * @brief Form per l'aggiunta di punti di interesse, percorsi ed aree ad una mappa
	 * 
	 * @param object $map istanza di @ref gmapsMap 
	 * @access private
	 * @return string il form per l'aggiunta di punti di interesse, percorsi ed aree
	 */
	private function formAddMapPolygonPolylinePoint($map) {
		
		$gform = new Form('form_search_add_map_polygon_polyline_point', 'post', false);
		$buffer = $gform->form('', false, '');

		$buffer .= $gform->cselect('point_ctg', '', gmapsPointCtg::getForSelect($this->_instance), _("Categoria punti di interesse"), array('id'=>'point_ctg'));
		$buffer .= $gform->cinput('point_label', 'text', '', _("Etichetta punti di interesse"), array('maxlength'=>200, 'id'=>'point_label'));
		$buffer .= $gform->cselect('polyline_ctg', '', gmapsPolylineCtg::getForSelect($this->_instance), _("Categoria percorsi"), array('id'=>'polyline_ctg'));
		$buffer .= $gform->cinput('polyline_label', 'text', '', _("Etichetta percorsi"), array('maxlength'=>200, 'id'=>'polyline_label'));
		$buffer .= $gform->cselect('polygon_ctg', '', gmapsPolygonCtg::getForSelect($this->_instance), _("Categoria aree"), array('id'=>'polygon_ctg'));
		$buffer .= $gform->cinput('polygon_label', 'text', '', _("Etichetta aree"), array('maxlength'=>200, 'id'=>'polygon_label'));

		$onclick = "ajaxRequest('post', '".$this->_home."?pt[".$this->_instanceName."-mapPolygonPolylinePointSearch]', 'id=".$map->id."&point_ctg='+$('point_ctg').value+'&point_label='+$('point_label').value+'&polyline_ctg='+$('polyline_ctg').value+'&polyline_label='+$('polyline_label').value+'&polygon_ctg='+$('polygon_ctg').value+'&polygon_label='+$('polygon_label').value, 'map_polygon_polyline_point_search', {load: 'map_polygon_polyline_point_search'})";

		$buffer .= $gform->cinput('submit_search', 'button', _("cerca"), '', array('classField'=>'submit', 'js'=>"onclick=\"$onclick\""));

		$buffer .= $gform->cform();

		$buffer .= "<div id=\"map_polygon_polyline_point_search\" style=\"margin-top: 10px;\"></div>";

		return $buffer;
	}

	/**
	 * @brief Lista di aree, percorsi e punti di interesse ricercati con form di selezione chiamata via ajax 
	 * 
	 * @access public
	 * @return string lista di aree, percorsi e punti di interesse con form di selezione
	 */
	public function mapPolygonPolylinePointSearch() {

		$this->accessGroup('ALL');

		$id = cleanVar($_POST, 'id', 'int', '');
		$point_ctg = cleanVar($_POST, 'point_ctg', 'int', '');
		$point_label = cleanVar($_POST, 'point_label', 'string', '');
		$polyline_ctg = cleanVar($_POST, 'polyline_ctg', 'int', '');
		$polyline_label = cleanVar($_POST, 'polyline_label', 'string', '');
		$polygon_ctg = cleanVar($_POST, 'polygon_ctg', 'int', '');
		$polygon_label = cleanVar($_POST, 'polygon_label', 'string', '');

		$map = new gmapsMap($id, $this);

		$where = array();
		if(count($map->points_id())) {
			$where[] = "id NOT IN (".implode(',', $map->points_id()).")";
		}
		if(count($map->polylines_id())) {
			$nopoints = array();
			foreach($map->polylines() as $polyline) {
				$nopoints = array_merge($nopoints, $polyline->points_id());	
			}
			$where[] = "id NOT IN (".implode(',', $nopoints).")";
		}
		if(count($map->polygons_id())) {
			$nopoints = array();
			foreach($map->polygons() as $polygon) {
				$nopoints = array_merge($nopoints, $polygon->points_id());	
			}
			$where[] = "id NOT IN (".implode(',', $nopoints).")";
		}
		if($point_ctg) {
			$where[] = "id IN (SELECT point_id FROM ".$this->_tbl_point_point_ctg." WHERE ctg_id='$point_ctg')";
		}
		if($point_label) {
			$where[] = "label LIKE '%$point_label%'";
		}

		$point_ids = gmapsPoint::get($this->_instance, array('where'=>implode(" AND ", $where)));		

		$where = array();
		if(count($map->polylines_id())) {
			$where[] = "id NOT IN (".implode(',', $map->polylines_id()).")";
		}
		if($polyline_ctg) {
			$where[] = "id IN (SELECT polyline_id FROM ".$this->_tbl_polyline_polyline_ctg." WHERE ctg_id='$polyline_ctg')";
		}
		if($polyline_label) {
			$where[] = "label LIKE '%$polyline_label%'";
		}

		$polyline_ids = gmapsPolyline::get($this->_instance, array('where'=>implode(" AND ", $where)));

		$where = array();
		if(count($map->polygons_id())) {
			$where[] = "id NOT IN (".implode(',', $map->polygons_id()).")";
		}
		if($polygon_ctg) {
			$where[] = "id IN (SELECT polygon_id FROM ".$this->_tbl_polygon_polygon_ctg." WHERE ctg_id='$polygon_ctg')";
		}
		if($polygon_label) {
			$where[] = "label LIKE '%$polygon_label%'";
		}

		$polygon_ids = gmapsPolygon::get($this->_instance, array('where'=>implode(" AND ", $where)));
		
		$buffer = "<p><b>"._("Punti di interesse, percorsi e aree")."</b></p>";	

		$gform = new Form('form_add_map_polygon_polyline_point', 'post', false, array('tblLayout'=>false));
		$buffer .= $gform->form('', false, '');
		$buffer .= $gform->hidden('id', $id);
		$buffer .= "<table class=\"generic\">";
		if(count($point_ids)) {

			$buffer .= "<tr>";
			$buffer .= "<th>"._("Etichetta punto di interesse")."</th>";
			$buffer .= "<th>"._("Aggiungi")."</th>";
			$buffer .= "</tr>";
			foreach($point_ids as $point_id) {
				$point = new gmapsPoint($point_id, $this);
				$buffer .= "<tr>";
				$buffer .= "<td>".htmlChars($point->label)."</td>"; 
				$buffer .= "<td><input type=\"checkbox\" name=\"point_add[]\" value=\"".$point->id."\" /></td>";
				$buffer .= "</tr>";
			}
		}
		if(count($polyline_ids)) {

			$buffer .= "<tr>";
			$buffer .= "<th>"._("Etichetta percorso")."</th>";
			$buffer .= "<th>"._("Aggiungi")."</th>";
			$buffer .= "</tr>";
			foreach($polyline_ids as $polyline_id) {
				$polyline = new gmapsPolyline($polyline_id, $this);
				$buffer .= "<tr>";
				$buffer .= "<td>".htmlChars($polyline->label)."</td>"; 
				$buffer .= "<td><input type=\"checkbox\" name=\"polyline_add[]\" value=\"".$polyline->id."\" /></td>";
				$buffer .= "</tr>";
			}
		}
		if(count($polygon_ids)) {

			$buffer .= "<tr>";
			$buffer .= "<th>"._("Etichetta area")."</th>";
			$buffer .= "<th>"._("Aggiungi")."</th>";
			$buffer .= "</tr>";
			foreach($polygon_ids as $polygon_id) {
				$polygon = new gmapsPolygon($polygon_id, $this);
				$buffer .= "<tr>";
				$buffer .= "<td>".htmlChars($polygon->label)."</td>"; 
				$buffer .= "<td><input type=\"checkbox\" name=\"polygon_add[]\" value=\"".$polyline->id."\" /></td>";
				$buffer .= "</tr>";
			}
		}
		$buffer .= "</table>";
		$buffer .= "<p>".$gform->input('submit_add', 'submit', _("aggiungi selezionati"), array('classField'=>'submit'))."</p>";
		$buffer .= $gform->cform();


		if(!count($point_ids) && !count($polyline_ids) && !count($polygon_ids)) {
			$buffer = "<p class=\"message\">"._("La ricerca non ha prodotto risultati")."</p>";
		}

		return $buffer;

	}
	
	/**
	 * @brief Back-office delle mappe 
	 * 
	 * @access private
	 * @return string la sezione amministrativa delle mappe
	 */
	private function manageMap() {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$map = new gmapsMap($id, $this);
		$map->instance = $this->_instance;

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col = $map->form($this->_home."?evt[".$this->_instanceName."-manageDoc]");
		}
		elseif($this->_action == 'delete') {
			$map->delete();
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "");
		}
		else {
			$col = $this->listMap();	
		}

		$buffer = "<div>";
		$buffer .= $col;
		$buffer .= "</div>";	

		return $buffer;
	}

	/**
	 * @brief Lista amministrativa delle mappe 
	 * 
	 * @access private
	 * @return string Lista delle mappe
	 */
	private function listMap() {
		
		$title = _("Mappe");
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&action=insert\">".pub::icon('insert', _("nuova mappa"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>array($link_insert), 'headerLabel'=>$title));

		$buffer = '';

		$map_ids = gmapsMap::get($this->_instance, array());
		if($tot = count($map_ids)) {

			$buffer .= "<table class=\"generic\">";
			$buffer .= "<tr>";
			$buffer .= "<th>"._("ID")."</th>";
			$buffer .= "<th>"._("Nome")."</th>";
			$buffer .= "<th>"._("Larghezza")."</th>";
			$buffer .= "<th>"._("Altezza")."</th>";
			$buffer .= "<th class=\"thIcon\"></th>";
			$buffer .= "</tr>";
			foreach($map_ids as $map_id) {
				$map = new gmapsMap($map_id, $this);
				$buffer .= "<tr>";
				$buffer .= "<td>".$map->id."</td>";
				$buffer .= "<td>".htmlChars($map->name)."</td>";
				$buffer .= "<td>".htmlChars($map->width)."</td>";
				$buffer .= "<td>".htmlChars($map->height)."</td>";
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&action=modify&id=".$map->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare la mappa?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&action=delete&id=".$map->id."';\">".pub::icon('delete')."</span>";
				$link_items = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=map_item&id=".$map->id."\">".$this->_icon_map_element."</a>";
				$onclick = "window.myWin = new layerWindow({'title':'"._("Codice embed")."', 'url':'".$this->_home."?pt[".$this->_instanceName."-iframeCode]&id=$map_id', 'width':600});window.myWin.display();";
				$link_link = "<span class=\"link\" onclick=\"$onclick\">".pub::icon('link', _("Codice inserimento iframe"))."</span>";
				$buffer .= "<td class=\"tdIcon\">$link_modify $link_delete $link_items $link_link</td>";
				$buffer .= "</tr>";

			}
			$buffer .= "</table>";

		}
		else {
			$buffer .= "<p class=\"message\">"._("Non risultano mappe registrate")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Restituisce il codice html per visualizzare la mappa in un iframe 
	 * 
	 * @access public
	 * @return string il codice html
	 */
	public function iframeCode() {

		$this->accessGroup('ALL');

		$id = cleanVar($_GET, 'id', 'int', '');
		$map = new gmapsMap($id, $this);

		$buffer = "<p>"._("Copia il codice seguente ed incollalo nella posizione desideratea all'interno del codice html del sito nel quale vuoi includere la mappa. Potrebbe essere necessario ritoccare i valore width ed height in modo da far scomparire eventuali barre di scorrimento.")."</p>";
		$buffer .= "<textarea style=\"width:99%\" rows=\"3\" readonly>";
		$buffer .= "<iframe frameborder=\"0\" width=\"".$map->width."\" height=\"".$map->height."\" src=\"".$this->_url_root.SITE_WWW.'/'.$this->_plink->aLink($this->_instanceName, 'iframeMap', array('id'=>$map->id), '')."\">Your browser doesn't support iframes</iframe>&lt;script type=\"text/javascript\"&gt;function gmaps_chg_parent_url(url) { document.location = url; }&lt;/script&gt;";
		$buffer .= "</textarea>";

		return $buffer;		

	}

	/**
	 * @brief Back-office dei punti di interesse di un percorso 
	 * 
	 * @access private
	 * @return string la sezione amministrativa dei punti di interesse di un percorso
	 */
	private function managePolylinePoint() {
		
		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$polyline = new gmapsPolyline($id, $this);

		$title = sprintf(_("Punti di interesse associati al percorso '%s'"), htmlChars($polyline->label));
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = '';

		if(isset($_POST['submit_add'])) {
			$add_point_ids = cleanVar($_POST, 'point_add', 'array', '');
			$res = $polyline->addPoints($add_point_ids);
			if(!$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_point&id=$id"));
			}
			else {
				EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=polyline_point&id=".$id);
			}
		}
		elseif(isset($_POST['submit_remove'])) {
			$remove_point_ids = cleanVar($_POST, 'point_remove', 'array', '');
			$res = $polyline->removePoints($remove_point_ids);
			if(!$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_point&id=$id"));
			}
			else {
				EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=polyline_point&id=".$id);
			}
		}

		$buffer .= "<h2>"._("Punti presenti")."</h2>";

		if(count($polyline->points())) {
			
			$gform = new Form('form_remove_polyline_point', 'post', false, array('tblLayout'=>false));
			$buffer .= $gform->form('', false, '');
			$buffer .= $gform->hidden('id', $id);
			$buffer .= "<table class=\"generic\" style=\"margin-bottom: 10px;\">";			
			$buffer .= "<tr>";
			$buffer .= "<th>Etichetta</th>";
			$buffer .= "<th>Rimuovi</th>";
			$buffer .= "</tr>";
			foreach($polyline->points() as $point) {
				$buffer .= "<tr>";
				$buffer .= "<td>".htmlChars($point->label)."</td>";
				$buffer .= "<td><input type=\"checkbox\" name=\"point_remove[]\" value=\"".$point->id."\" /></td>";
				$buffer .= "</tr>";
			}
			$buffer .= "</table>";
			$buffer .= "<p>".$gform->input('submit_remove', 'submit', _("rimuovi selezionati"), array('classField'=>'submit'))."</p>";
			$buffer .= $gform->cform();
		}
		else {
			$buffer .= "<p class=\"message\">"._("Non risultano punti di interesse associati")."</p>";
		}

		$buffer .= "<h2>"._("Situazione attuale")."</h2>";
		$buffer .= "<div id=\"map_canvas\" style=\"width:100%; height: 300px;\"></div>";
		$buffer .= "<script>";
		$buffer .= "window.addEvent('load', function() {
			var myopt = {
				center: new google.maps.LatLng(45, 7),
				zoom: 10,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}

			var map = new google.maps.Map($('map_canvas'), myopt);
			var bounds = new google.maps.LatLngBounds();
			".$polyline->gmapCode('map', 'bounds')."
			map.fitBounds(bounds);

		})";		
		$buffer .= "</script>";


		$buffer .= "<h2>"._("Ricerca ed aggiunta punti")."</h2>";

		$buffer .= $this->formAddPolylinePoint($polyline);
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	/**
	 * @brief Back-office dei punti di interesse di un'area 
	 * 
	 * @access private
	 * @return string la sezione amministrativa dei punti di interesse di un'area
	 */
	private function managePolygonPoint() {
		
		$registry = registry::instance();
		$registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$polygon = new gmapsPolygon($id, $this);

		$title = sprintf(_("Punti di interesse associati all'area '%s'"), htmlChars($polygon->label));
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = '';

		if(isset($_POST['submit_add'])) {
			$add_point_ids = cleanVar($_POST, 'point_add', 'array', '');
			$res = $polygon->addPoints($add_point_ids);
			if(!$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_point&id=$id"));
			}
			else {
				EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=polygon_point&id=".$id);
			}
		}
		elseif(isset($_POST['submit_remove'])) {
			$remove_point_ids = cleanVar($_POST, 'point_remove', 'array', '');
			$res = $polygon->removePoints($remove_point_ids);
			if(!$res) {
				exit(error::errorMessage(array('error'=>_("Si è verificato un errore, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_point&id=$id"));
			}
			else {
				EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=polygon_point&id=".$id);
			}
		}

		$buffer .= "<h2>"._("Punti presenti")."</h2>";

		if(count($polygon->points())) {
			
			$gform = new Form('form_remove_polygon_point', 'post', false, array('tblLayout'=>false));
			$buffer .= $gform->form('', false, '');
			$buffer .= $gform->hidden('id', $id);
			$buffer .= "<table class=\"generic\" style=\"margin-bottom: 10px;\">";			
			$buffer .= "<tr>";
			$buffer .= "<th>Etichetta</th>";
			$buffer .= "<th>Rimuovi</th>";
			$buffer .= "</tr>";
			foreach($polygon->points() as $point) {
				$buffer .= "<tr>";
				$buffer .= "<td>".htmlChars($point->label)."</td>";
				$buffer .= "<td><input type=\"checkbox\" name=\"point_remove[]\" value=\"".$point->id."\" /></td>";
				$buffer .= "</tr>";
			}
			$buffer .= "</table>";
			$buffer .= "<p>".$gform->input('submit_remove', 'submit', _("rimuovi selezionati"), array('classField'=>'submit'))."</p>";
			$buffer .= $gform->cform();
		}
		else {
			$buffer .= "<p class=\"message\">"._("Non risultano punti di interesse associati")."</p>";
		}

		$buffer .= "<h2>"._("Situazione attuale")."</h2>";
		$buffer .= "<div id=\"map_canvas\" style=\"width:100%; height: 300px;\"></div>";
		$buffer .= "<script>";
		$buffer .= "window.addEvent('load', function() {
			var myopt = {
				center: new google.maps.LatLng(45, 7),
				zoom: 10,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}

			var map = new google.maps.Map($('map_canvas'), myopt);
			var bounds = new google.maps.LatLngBounds();
			".$polygon->gmapCode('map', 'bounds')."
			map.fitBounds(bounds);

		})";		
		$buffer .= "</script>";


		$buffer .= "<h2>"._("Ricerca ed aggiunta punti")."</h2>";

		$buffer .= $this->formAddPolygonPoint($polygon);
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	/**
	 * @brief Form per l'aggiunta di punti di interesse ad un percorso
	 * 
	 * @param object $polyline istanza di @ref gmapsPolyline 
	 * @access private
	 * @return string il form per l'aggiunta di punti di interesse
	 */
	private function formAddPolylinePoint($polyline) {
		
		$gform = new Form('form_search_add_polyline_point', 'post', false);
		$buffer = $gform->form('', false, '');

		$buffer .= $gform->cselect('point_ctg', '', gmapsPointCtg::getForSelect($this->_instance), _("Categoria"), array('id'=>'point_ctg'));
		$buffer .= $gform->cinput('point_label', 'text', '', _("Etichetta"), array('maxlength'=>200, 'id'=>'point_label'));

		$onclick = "ajaxRequest('post', '".$this->_home."?pt[".$this->_instanceName."-polylinePointSearch]', 'id=".$polyline->id."&ctg='+$('point_ctg').value+'&label='+$('point_label').value, 'polyline_point_search', {load: 'polyline_point_search'})";

		$buffer .= $gform->cinput('submit_search', 'button', _("cerca"), '', array('classField'=>'submit', 'js'=>"onclick=\"$onclick\""));

		$buffer .= $gform->cform();

		$buffer .= "<div id=\"polyline_point_search\" style=\"margin-top: 10px;\"></div>";

		return $buffer;
	}

	/**
	 * @brief Form per l'aggiunta di punti di interesse ad un'area
	 * 
	 * @param object $polygon istanza di @ref gmapsPolygon 
	 * @access private
	 * @return string il form per l'aggiunta di punti di interesse
	 */
	private function formAddPolygonPoint($polygon) {
		
		$gform = new Form('form_search_add_polygon_point', 'post', false);
		$buffer = $gform->form('', false, '');

		$buffer .= $gform->cselect('point_ctg', '', gmapsPointCtg::getForSelect($this->_instance), _("Categoria"), array('id'=>'point_ctg'));
		$buffer .= $gform->cinput('point_label', 'text', '', _("Etichetta"), array('maxlength'=>200, 'id'=>'point_label'));

		$onclick = "ajaxRequest('post', '".$this->_home."?pt[".$this->_instanceName."-polygonPointSearch]', 'id=".$polygon->id."&ctg='+$('point_ctg').value+'&label='+$('point_label').value, 'polygon_point_search', {load: 'polygon_point_search'})";

		$buffer .= $gform->cinput('submit_search', 'button', _("cerca"), '', array('classField'=>'submit', 'js'=>"onclick=\"$onclick\""));

		$buffer .= $gform->cform();

		$buffer .= "<div id=\"polygon_point_search\" style=\"margin-top: 10px;\"></div>";

		return $buffer;
	}


	/**
	 * @brief Lista di punti di interesse ricercati con form di selezione chiamata via ajax 
	 * 
	 * @access public
	 * @return string lista di punti di interesse con form di selezione
	 */
	public function polylinePointSearch() {

		$this->accessGroup('ALL');

		$id = cleanVar($_POST, 'id', 'int', '');
		$ctg = cleanVar($_POST, 'ctg', 'int', '');
		$label = cleanVar($_POST, 'label', 'string', '');

		$polyline = new gmapsPolyline($id, $this);

		$where = array();
		if(count($polyline->points_id())) {
			$where[] = "id NOT IN (".implode(',', $polyline->points_id()).")";
		}
		if($label) {
			$where[] = "label LIKE '%$label%'";
		}

		$point_ids = gmapsPoint::get($this->_instance, array('where'=>implode(" AND ", $where), 'ctg'=>$ctg));		

		if(count($point_ids)) {

			$gform = new Form('form_add_polyline_point', 'post', false, array('tblLayout'=>false));
			$buffer = $gform->form('', false, '');
			$buffer .= $gform->hidden('id', $id);
		
			$buffer .= "<table class=\"generic\">";
			$buffer .= "<tr>";
			$buffer .= "<th>"._("Etichetta")."</th>";
			$buffer .= "<th>"._("Aggiungi")."</th>";
			$buffer .= "</tr>";
			foreach($point_ids as $point_id) {
				$point = new gmapsPoint($point_id, $this);
				$buffer .= "<tr>";
				$buffer .= "<td>".htmlChars($point->label)."</td>"; 
				$buffer .= "<td><input type=\"checkbox\" name=\"point_add[]\" value=\"".$point->id."\" /></td>";
				$buffer .= "</tr>";
			}
			$buffer .= "<table>";

			$buffer .= "<p>".$gform->input('submit_add', 'submit', _("aggiungi selezionati"), array('classField'=>'submit'))."</p>";
			$buffer .= $gform->cform();

		}
		else {
			$buffer = "<p class=\"message\">"._("La ricerca non ha prodotto risultati")."</p>";
		}

		return $buffer;

	}

	/**
	 * @brief Lista di punti di interesse ricercati con form di selezione chiamata via ajax 
	 * 
	 * @access public
	 * @return string lista di punti di interesse con form di selezione
	 */
	public function polygonPointSearch() {

		$this->accessGroup('ALL');

		$id = cleanVar($_POST, 'id', 'int', '');
		$ctg = cleanVar($_POST, 'ctg', 'int', '');
		$label = cleanVar($_POST, 'label', 'string', '');

		$polygon = new gmapsPolygon($id, $this);

		$where = array();
		if(count($polygon->points_id())) {
			$where[] = "id NOT IN (".implode(',', $polygon->points_id()).")";
		}
		if($label) {
			$where[] = "label LIKE '%$label%'";
		}

		$point_ids = gmapsPoint::get($this->_instance, array('where'=>implode(" AND ", $where), 'ctg'=>$ctg));		

		if(count($point_ids)) {

			$gform = new Form('form_add_polygon_point', 'post', false, array('tblLayout'=>false));
			$buffer = $gform->form('', false, '');
			$buffer .= $gform->hidden('id', $id);
		
			$buffer .= "<table class=\"generic\">";
			$buffer .= "<tr>";
			$buffer .= "<th>"._("Etichetta")."</th>";
			$buffer .= "<th>"._("Aggiungi")."</th>";
			$buffer .= "</tr>";
			foreach($point_ids as $point_id) {
				$point = new gmapsPoint($point_id, $this);
				$buffer .= "<tr>";
				$buffer .= "<td>".htmlChars($point->label)."</td>"; 
				$buffer .= "<td><input type=\"checkbox\" name=\"point_add[]\" value=\"".$point->id."\" /></td>";
				$buffer .= "</tr>";
			}
			$buffer .= "<table>";

			$buffer .= "<p>".$gform->input('submit_add', 'submit', _("aggiungi selezionati"), array('classField'=>'submit'))."</p>";
			$buffer .= $gform->cform();

		}
		else {
			$buffer = "<p class=\"message\">"._("La ricerca non ha prodotto risultati")."</p>";
		}

		return $buffer;

	}

	/**
	 * @brief Back-office dei percorsi 
	 * 
	 * @access private
	 * @return string la sezione amministrativa dei percorsi
	 */
	private function managePolyline() {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$start = cleanVar($_GET, 'start', 'int', '');
		$polyline = new gmapsPolyline($id, $this);
		$polyline->instance = $this->_instance;

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col = $polyline->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline&start=$start");
		}
		elseif($this->_action == 'delete') {
			// delete polyline and all associations
			$polyline->delete();
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=polyline&start=$start");
		}
		else {
			$col = $this->listPolyline();	
		}

		$buffer = "<div>";
		$buffer .= $col;
		$buffer .= "</div>";	

		return $buffer;
	}

	/**
	 * @brief Back-office delle aree 
	 * 
	 * @access private
	 * @return string la sezione amministrativa delle aree
	 */
	private function managePolygon() {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$start = cleanVar($_GET, 'start', 'int', '');
		$polygon = new gmapsPolygon($id, $this);
		$polygon->instance = $this->_instance;

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col = $polygon->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon&start=$start");
		}
		elseif($this->_action == 'delete') {
			// delete polyline and all associations
			$polygon->delete();
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=polygon&start=$start");
		}
		else {
			$col = $this->listPolygon();	
		}

		$buffer = "<div>";
		$buffer .= $col;
		$buffer .= "</div>";	

		return $buffer;
	}


	/**
	 * @brief Lista amministrativa dei percorsi 
	 * 
	 * @access private
	 * @return string Lista dei percorsi
	 */
	private function listPolyline() {
		
		$title = _("Percorsi");
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline&action=insert\">".pub::icon('insert', _("nuovo percorso"))."</a>";
		$onclick = "myreveal.toggle();";
		$link_search = "<span class=\"link\" onclick=\"".$onclick."\">".pub::icon('search')."</span>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>array($link_insert, $link_search), 'headerLabel'=>$title));

		$formsearch = $this->adminSearchPolyline();

		$buffer = "<div id=\"admin_search_polyline\" style=\"display:".((isset($_SESSION['apl_search']) && $_SESSION['apl_search']) ? "block" : "none")."; border:1px solid #ddd; background: #eee; padding: 10px; padding-top: 0; margin-bottom: 10px;\">";
		$buffer .= "<p>Le ricerche effettuate rimangono in sessione</p>";
		$buffer .= $formsearch;
		$buffer .= "</div>";
		$buffer .= "<script>";
		$buffer .= "myreveal = new Fx.Reveal('admin_search_polyline');";
		$buffer .= "</script>";

		$polyline_ids = gmapsPolyline::get($this->_instance, array('where'=>$_SESSION['apl_where']));
		if($tot = count($polyline_ids)) {

			$plist = new PageList(2, $tot, 'array');
			$start = $plist->start();
			$end = min($start + 2, $tot);

			$buffer .= "<table class=\"generic\">";
			$buffer .= "<tr>";
			$buffer .= "<th>"._("Categorie")."</th>";
			$buffer .= "<th>"._("Etichetta")."</th>";
			$buffer .= "<th>"._("Colore")."</th>";
			$buffer .= "<th>"._("Spessore")."</th>";
			$buffer .= "<th class=\"thIcon\"></th>";
			$buffer .= "</tr>";
			for($i = $start; $i < $end; $i++) {
				$polyline_id = $polyline_ids[$i];
				$polyline = new gmapsPolyline($polyline_id, $this);
				$ctg = new gmapsPolylineCtg($polyline->ctg, $this);
				$buffer .= "<tr>";
				$buffer .= "<td>";
				$ctgs_string = '';
				foreach($polyline->ctgs() as $ctg) {
					$ctgs_string .= htmlChars($ctg->name).", ";
				}
				$buffer .= substr($ctgs_string, 0, -2);
				$buffer .= "</td>";
				$buffer .= "<td>".htmlChars($polyline->label)."</td>";
				$buffer .= "<td>".htmlChars($polyline->color)."</td>";
				$buffer .= "<td>".htmlChars($polyline->width)."px</td>";
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline&action=modify&id=".$polyline->id."&start=".$start."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare il percorso?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline&action=delete&id=".$polyline->id."&start=".$start."';\">".pub::icon('delete')."</span>";
				$link_points = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_point&id=".$polyline->id."&start=".$start."\">".$this->_icon_polyline_points."</a>";
				$buffer .= "<td class=\"tdIcon\">$link_modify $link_delete $link_points</td>";
				$buffer .= "</tr>";

			}
			$buffer .= "</table>";

			$htmlsection->footer = "<p>".$plist->listReferenceGINO($this->_plink->aLink($this->_instanceName, 'manageDoc', '', "block=polyline", array("basename"=>false)))."</p>";

		}
		else {
			$buffer .= "<p class=\"message\">"._("Non risultano percorsi registrati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Lista amministrativa delle aree 
	 * 
	 * @access private
	 * @return string Lista delle aree
	 */
	private function listPolygon() {
		
		$title = _("Aree");
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon&action=insert\">".pub::icon('insert', _("nuova area"))."</a>";
		$onclick = "myreveal.toggle();";
		$link_search = "<span class=\"link\" onclick=\"".$onclick."\">".pub::icon('search')."</span>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLinks'=>array($link_insert, $link_search), 'headerLabel'=>$title));

		$formsearch = $this->adminSearchPolygon();

		$buffer = "<div id=\"admin_search_polygon\" style=\"display:".((isset($_SESSION['apg_search']) && $_SESSION['apg_search']) ? "block" : "none")."; border:1px solid #ddd; background: #eee; padding: 10px; padding-top: 0; margin-bottom: 10px;\">";
		$buffer .= "<p>Le ricerche effettuate rimangono in sessione</p>";
		$buffer .= $formsearch;
		$buffer .= "</div>";
		$buffer .= "<script>";
		$buffer .= "myreveal = new Fx.Reveal('admin_search_polygon');";
		$buffer .= "</script>";

		$polygon_ids = gmapsPolygon::get($this->_instance, array('where'=>$_SESSION['apl_where']));
		if($tot = count($polygon_ids)) {

			$plist = new PageList(2, $tot, 'array');
			$start = $plist->start();
			$end = min($start + 2, $tot);

			$buffer .= "<table class=\"generic\">";
			$buffer .= "<tr>";
			$buffer .= "<th>"._("Categorie")."</th>";
			$buffer .= "<th>"._("Etichetta")."</th>";
			$buffer .= "<th>"._("Colore")."</th>";
			$buffer .= "<th>"._("Spessore")."</th>";
			$buffer .= "<th class=\"thIcon\"></th>";
			$buffer .= "</tr>";
			for($i = $start; $i < $end; $i++) {
				$polygon_id = $polygon_ids[$i];
				$polygon = new gmapsPolygon($polygon_id, $this);
				$ctg = new gmapsPolygonCtg($polygon->ctg, $this);
				$buffer .= "<tr>";
				$buffer .= "<td>";
				$ctgs_string = '';
				foreach($polygon->ctgs() as $ctg) {
					$ctgs_string .= htmlChars($ctg->name).", ";
				}
				$buffer .= substr($ctgs_string, 0, -2);
				$buffer .= "</td>";
				$buffer .= "<td>".htmlChars($polygon->label)."</td>";
				$buffer .= "<td>".htmlChars($polygon->color)."</td>";
				$buffer .= "<td>".htmlChars($polygon->width)."px</td>";
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon&action=modify&id=".$polygon->id."&start=".$start."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare l'area?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon&action=delete&id=".$polygon->id."&start=".$start."';\">".pub::icon('delete')."</span>";
				$link_points = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_point&id=".$polygon->id."&start=".$start."\">".$this->_icon_polygon_points."</a>";
				$buffer .= "<td class=\"tdIcon\">$link_modify $link_delete $link_points</td>";
				$buffer .= "</tr>";

			}
			$buffer .= "</table>";

			$htmlsection->footer = "<p>".$plist->listReferenceGINO($this->_plink->aLink($this->_instanceName, 'manageDoc', '', "block=polygon", array("basename"=>false)))."</p>";

		}
		else {
			$buffer .= "<p class=\"message\">"._("Non risultano aree registrate")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}


	/**
	 * @brief Form di ricerca percorsi in area amministrativa 
	 * 
	 * @access private
	 * @return string il form di ricerca percorsi in area amministrativa
	 */
	private function adminSearchPolyline() {

		if(!isset($_SESSION['apl_search'])) {
			$_SESSION['apl_ctg'] = 0;
			$_SESSION['apl_label'] = '';
			$_SESSION['apl_where'] = '';
		}

		if(isset($_POST['submit_search'])) {
			$_SESSION['apl_ctg'] = cleanVar($_POST, 'apl_ctg', 'int', '');
			$_SESSION['apl_label'] = cleanVar($_POST, 'apl_label', 'string', '');

			$where = array();

			if($_SESSION['apl_ctg']) $where[] = "id IN (SELECT polyline_id FROM ".$this->_tbl_polyline_polyline_ctg." WHERE ctg_id='".$_SESSION['apl_ctg']."')";
			if($_SESSION['apl_label']) $where[] = "label LIKE '%".$_SESSION['apl_label']."%'";

			$_SESSION['apl_where'] = implode(" AND ", $where);

			if($_SESSION['apl_ctg'] || $_SESSION['apl_label']) $_SESSION['apl_search'] = true;
			else $_SESSION['apl_search'] = false;
		}
		elseif(isset($_POST['submit_reset'])) {
			$_SESSION['apl_ctg'] = 0;
			$_SESSION['apl_label'] = '';
			$_SESSION['apl_where'] = '';
			$_SESSION['apl_search'] = false;
		}
		
		$gform = new Form('form_admin_search_polyline', 'post', false);
		$buffer = $gform->form('', false, '');

		$buffer .= $gform->cselect('apl_ctg', $_SESSION['apl_ctg'], gmapsPolylineCtg::getForSelect($this->_instance), _("Categoria"), array('required'=>true));
		$buffer .= $gform->cinput('apl_label', 'text', $_SESSION['apl_label'], _("Etichetta"), array('required'=>true, 'maxlength'=>200));
		$reset = $gform->input('submit_reset', 'submit', _("azzera"), array('classField'=>'submit'));
		$buffer .= $gform->cinput('submit_search', 'submit', _("cerca"), '', array('classField'=>'submit', 'text_add'=>" $reset"));

		$buffer .= $gform->cform();

		return $buffer;
	}

	/**
	 * @brief Form di ricerca aree in area amministrativa 
	 * 
	 * @access private
	 * @return string il form di ricerca percorsi in area amministrativa
	 */
	private function adminSearchPolygon() {

		if(!isset($_SESSION['apl_search'])) {
			$_SESSION['apg_ctg'] = 0;
			$_SESSION['apg_label'] = '';
			$_SESSION['apg_where'] = '';
		}

		if(isset($_POST['submit_search'])) {
			$_SESSION['apg_ctg'] = cleanVar($_POST, 'apg_ctg', 'int', '');
			$_SESSION['apg_label'] = cleanVar($_POST, 'apg_label', 'string', '');

			$where = array();

			if($_SESSION['apg_ctg']) $where[] = "id IN (SELECT polygon_id FROM ".$this->_tbl_polygon_polygon_ctg." WHERE ctg_id='".$_SESSION['apg_ctg']."')";
			if($_SESSION['apg_label']) $where[] = "label LIKE '%".$_SESSION['apg_label']."%'";

			$_SESSION['apg_where'] = implode(" AND ", $where);

			if($_SESSION['apg_ctg'] || $_SESSION['apg_label']) $_SESSION['apg_search'] = true;
			else $_SESSION['apg_search'] = false;
		}
		elseif(isset($_POST['submit_reset'])) {
			$_SESSION['apg_ctg'] = 0;
			$_SESSION['apg_label'] = '';
			$_SESSION['apg_where'] = '';
			$_SESSION['apg_search'] = false;
		}
		
		$gform = new Form('form_admin_search_polygon', 'post', false);
		$buffer = $gform->form('', false, '');

		$buffer .= $gform->cselect('apg_ctg', $_SESSION['apg_ctg'], gmapsPolygonCtg::getForSelect($this->_instance), _("Categoria"), array('required'=>true));
		$buffer .= $gform->cinput('apg_label', 'text', $_SESSION['apg_label'], _("Etichetta"), array('required'=>true, 'maxlength'=>200));
		$reset = $gform->input('submit_reset', 'submit', _("azzera"), array('classField'=>'submit'));
		$buffer .= $gform->cinput('submit_search', 'submit', _("cerca"), '', array('classField'=>'submit', 'text_add'=>" $reset"));

		$buffer .= $gform->cform();

		return $buffer;
	}


	/**
	 * @brief Back-office dei punti di interesse 
	 * 
	 * @access private
	 * @return string la sezione amministrativa dei punti di interesse
	 */
	private function managePoint() {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$start = cleanVar($_GET, 'start', 'int', '');
		$point = new gmapsPoint($id, $this);
		$point->instance = $this->_instance;

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col = $point->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point&start=$start");
		}
		elseif($this->_action == 'delete') {
			$point->delete();
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=point&start=$start");
		}
		else {
			$col = $this->listPoint();	
		}

		$buffer = "<div>";
		$buffer .= $col;
		$buffer .= "</div>";	

		return $buffer;
	}

	/**
	 * @brief Lista amministrativa dei punti di interesse 
	 * 
	 * @access private
	 * @return string Lista dei punti di interesse
	 */
	private function listPoint() {

		$title = _("Punti di interesse");
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point&action=insert\">".pub::icon('insert', _("nuovo punto di interesse"))."</a>";
		$onclick = "myreveal.toggle();";
		$link_search = "<span class=\"link\" onclick=\"".$onclick."\">".pub::icon('search')."</span>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>array($link_insert, $link_search), 'headerLabel'=>$title));

		$formsearch = $this->adminSearchPoint();

		$buffer = "<div id=\"admin_search_point\" style=\"display:".((isset($_SESSION['ap_search']) && $_SESSION['ap_search']) ? "block" : "none")."; border:1px solid #ddd; background: #eee; padding: 10px; padding-top: 0; margin-bottom: 10px;\">";
		$buffer .= "<p>Le ricerche effettuate rimangono in sessione</p>";
		$buffer .= $formsearch;
		$buffer .= "</div>";
		$buffer .= "<script>";
		$buffer .= "myreveal = new Fx.Reveal('admin_search_point');";
		$buffer .= "</script>";

		$point_ids = gmapsPoint::get($this->_instance, array('where'=>$_SESSION['ap_where']));
		if($tot = count($point_ids)) {

			$plist = new PageList($this->_list_point_items, $tot, 'array');
			$start = $plist->start();
			$end = min($start + $this->_list_point_items, $tot);

			$buffer .= "<table class=\"generic\">";
			$buffer .= "<tr>";
			$buffer .= "<th>"._("Categorie")."</th>";
			$buffer .= "<th>"._("Etichetta")."</th>";
			$buffer .= "<th>"._("Latitudine")."</th>";
			$buffer .= "<th>"._("Longitudine")."</th>";
			$buffer .= "<th class=\"thIcon\" style=\"width: 160px;\"></th>";
			$buffer .= "</tr>";
			for($i = $start; $i < $end; $i++) {
				$point_id = $point_ids[$i];
				$point = new gmapsPoint($point_id, $this);
				$ctg = new gmapsPointCtg($point->ctg, $this);
				$buffer .= "<tr>";
				$buffer .= "<td>";
				$ctgs_string = '';
				foreach($point->ctgs() as $ctg) {
					$ctgs_string .= htmlChars($ctg->name).', ';
				}
				$buffer .= substr($ctgs_string, 0, -2);
				$buffer .= "</td>";
				$buffer .= "<td>".htmlChars($point->label)."</td>";
				$buffer .= "<td>".htmlChars(round($point->lat, 4))."</td>";
				$buffer .= "<td>".htmlChars(round($point->lng, 4))."</td>";
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point&action=modify&id=".$point->id."&start=".$start."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare il punto di interesse?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point&action=delete&id=".$point->id."&start=".$start."';\">".pub::icon('delete')."</span>";
				$link_images = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_image&point_id=".$point->id."\">".$this->_icon_image."</a>";
				$link_videos = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_video&point_id=".$point->id."\">".$this->_icon_video."</a>";
				$link_events = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_event&point_id=".$point->id."\">".$this->_icon_event."</a>";
				$link_attachments = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_attachment&point_id=".$point->id."\">".$this->_icon_attachment."</a>";
				$link_collections = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_collection&point_id=".$point->id."\">".$this->_icon_collection."</a>";
				$buffer .= "<td class=\"tdIcon\">$link_modify $link_delete $link_images $link_videos $link_events $link_attachments $link_collections</td>";
				$buffer .= "</tr>";
				
			}
			$buffer .= "</table>";
		
			$htmlsection->footer = "<p>".$plist->listReferenceGINO($this->_plink->aLink($this->_instanceName, 'manageDoc', '', "block=point", array("basename"=>false)))."</p>";

		}
		else {
			$buffer .= "<p class=\"message\">"._("Non risultano punti di interesse registrati")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	/**
	 * @brief Form di ricerca punti di interesse in area amministrativa 
	 * 
	 * @access private
	 * @return string il form di ricerca punti di interesse in area amministrativa
	 */
	private function adminSearchPoint() {

		if(!isset($_SESSION['ap_search'])) {
			$_SESSION['ap_ctg'] = 0;
			$_SESSION['ap_label'] = '';
			$_SESSION['ap_where'] = '';
		}

		if(isset($_POST['submit_search'])) {
			$_SESSION['ap_ctg'] = cleanVar($_POST, 'ap_ctg', 'int', '');
			$_SESSION['ap_label'] = cleanVar($_POST, 'ap_label', 'string', '');

			$where = array();

			if($_SESSION['ap_ctg']) $where[] = "id IN (SELECT point_id FROM ".$this->_tbl_point_point_ctg." WHERE ctg_id='".$_SESSION['ap_ctg']."')";
			if($_SESSION['ap_label']) $where[] = "label LIKE '%".$_SESSION['ap_label']."%'";

			$_SESSION['ap_where'] = implode(" AND ", $where);

			if($_SESSION['ap_ctg'] || $_SESSION['ap_label']) $_SESSION['ap_search'] = true;
			else $_SESSION['ap_search'] = false;
		}
		elseif(isset($_POST['submit_reset'])) {
			$_SESSION['ap_ctg'] = 0;
			$_SESSION['ap_label'] = '';
			$_SESSION['ap_where'] = '';
			$_SESSION['ap_search'] = false;
		}
		
		$gform = new Form('form_admin_search_point', 'post', false);
		$buffer = $gform->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point", false, '');

		$buffer .= $gform->cselect('ap_ctg', $_SESSION['ap_ctg'], gmapsPointCtg::getForSelect($this->_instance), _("Categoria"), array('required'=>true));
		$buffer .= $gform->cinput('ap_label', 'text', $_SESSION['ap_label'], _("Etichetta"), array('required'=>true, 'maxlength'=>200));
		$reset = $gform->input('submit_reset', 'submit', _("azzera"), array('classField'=>'submit'));
		$buffer .= $gform->cinput('submit_search', 'submit', _("cerca"), '', array('classField'=>'submit', 'text_add'=>" $reset"));

		$buffer .= $gform->cform();

		return $buffer;
	}

	/**
	 * @brief Back-office delle categorie dei percorsi 
	 * 
	 * @access private
	 * @return string la sezione amministrativa delle categorie dei percorsi
	 */
	private function managePolylineCtg() {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$ctg = new gmapsPolylineCtg($id, $this);
		$ctg->instance = $this->_instance;

		$col1 = $this->listPolylineCtg($id);

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col2 = $ctg->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_ctg");
		}
		elseif($this->_action == 'delete') {
			$res = $ctg->delete();
			if($res === 'polylines') {
				exit(error::errorMessage(array('error'=>_("La categoria contiene dei percorsi, eliminarli o cambiarne la categoria e riprovare")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_ctg"));
			}
			elseif($res === false) {
				exit(error::errorMessage(array('error'=>_("Impossibile eliminare la categoria, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_ctg"));
			}
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=polyline_ctg");
		}
		else {
			$col2 = $this->infoPolylineCtg();
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;

	}

	/**
	 * @brief Back-office delle categorie delle aree 
	 * 
	 * @access private
	 * @return string la sezione amministrativa delle categorie delle aree
	 */
	private function managePolygonCtg() {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$ctg = new gmapsPolygonCtg($id, $this);
		$ctg->instance = $this->_instance;

		$col1 = $this->listPolygonCtg($id);

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col2 = $ctg->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_ctg");
		}
		elseif($this->_action == 'delete') {
			$res = $ctg->delete();
			if($res === 'polygons') {
				exit(error::errorMessage(array('error'=>_("La categoria contiene delle aree, eliminarle o cambiarne la categoria e riprovare")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_ctg"));
			}
			elseif($res === false) {
				exit(error::errorMessage(array('error'=>_("Impossibile eliminare la categoria, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_ctg"));
			}
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=polygon_ctg");
		}
		else {
			$col2 = $this->infoPolygonCtg();
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;

	}

	/**
	 * @brief Lista amministrativa delle categorie dei percorsi 
	 * 
	 * @access private
	 * @return string Lista delle categorie dei percorsi
	 */
	private function listPolylineCtg($sel_id) {
	
		$title = _("Categorie");
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_ctg&action=insert\">".pub::icon('insert', _("nuova categoria"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		$ctg_ids = gmapsPolylineCtg::get($this->_instance);

		if($tot = count($ctg_ids)) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($ctg_ids as $ctg_id) {
				$ctg = new gmapsPolylineCtg($ctg_id, $this);
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_ctg&action=modify&id=".$ctg->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare la categoria?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polyline_ctg&action=delete&id=".$ctg->id."';\">".pub::icon('delete')."</span>";
				$selected = $ctg->id == $sel_id ? true : false;
				$buffer .= $htmlList->item(htmlChars($ctg->name), array($link_modify, $link_delete), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano categorie registrate")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Lista amministrativa delle categorie delle aree 
	 * 
	 * @access private
	 * @return string Lista delle categorie delle aree
	 */
	private function listPolygonCtg($sel_id) {
	
		$title = _("Categorie");
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_ctg&action=insert\">".pub::icon('insert', _("nuova categoria"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		$ctg_ids = gmapsPolygonCtg::get($this->_instance);

		if($tot = count($ctg_ids)) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($ctg_ids as $ctg_id) {
				$ctg = new gmapsPolygonCtg($ctg_id, $this);
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_ctg&action=modify&id=".$ctg->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare la categoria?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=polygon_ctg&action=delete&id=".$ctg->id."';\">".pub::icon('delete')."</span>";
				$selected = $ctg->id == $sel_id ? true : false;
				$buffer .= $htmlList->item(htmlChars($ctg->name), array($link_modify, $link_delete), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano categorie registrate")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}


	/**
	 * @brief Informazioni sull'amministrazione delle categorie dei percorsi 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione delle categorie dei percorsi
	 */
	private function infoPolylineCtg() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione delle categorie dei percorsi");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}

	/**
	 * @brief Informazioni sull'amministrazione delle categorie delle aree 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione delle categorie delle aree
	 */
	private function infoPolygonCtg() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione delle categorie delle aree");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}


	/**
	 * @brief Back-office delle categorie dei punti di interesse 
	 * 
	 * @access private
	 * @return string la sezione amministrativa delle categorie dei punti di interesse
	 */
	private function managePointCtg() {

		$id = cleanVar($_REQUEST, 'id', 'int', '');
		$ctg = new gmapsPointCtg($id, $this);
		$ctg->instance = $this->_instance;

		$col1 = $this->listPointCtg($id);

		if(in_array($this->_action, array('insert', 'modify'))) {
			$col2 = $ctg->form($this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_ctg");
		}
		elseif($this->_action == 'delete') {
			$res = $ctg->delete();
			if($res === 'points') {
				exit(error::errorMessage(array('error'=>_("La categoria contiene dei punti di interesse, eliminarli o cambiarne la categoria e riprovare")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_ctg"));
			}
			elseif($res === false) {
				exit(error::errorMessage(array('error'=>_("Impossibile eliminare la categoria, contattare l'amministratore del sistema.")), $this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_ctg"));
			}
			EvtHandler::HttpCall($this->_home, $this->_instanceName."-manageDoc", "block=point_ctg");
		}
		else {
			$col2 = $this->infoPointCtg();
		}

		$buffer = "<div class=\"vertical_1\">";
		$buffer .= $col1;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"vertical_2\">";
		$buffer .= $col2;
		$buffer .= "</div>";	

		$buffer .= "<div class=\"null\"></div>";

		return $buffer;

	}

	/**
	 * @brief Lista amministrativa delle categorie dei punti di interesse 
	 * 
	 * @access private
	 * @return string Lista delle categorie di punti di interesse
	 */
	private function listPointCtg($sel_id) {
	
		$title = _("Categorie");
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_ctg&action=insert\">".pub::icon('insert', _("nuova categoria"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLinks'=>$link_insert, 'headerLabel'=>$title));

		$ctg_ids = gmapsPointCtg::get($this->_instance);

		if($tot = count($ctg_ids)) {
			$htmlList = new htmlList(array("numItems"=>$tot, "separator"=>true));
			$buffer  = $htmlList->start();
			foreach($ctg_ids as $ctg_id) {
				$ctg = new gmapsPointCtg($ctg_id, $this);
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_ctg&action=modify&id=".$ctg->id."\">".pub::icon('modify')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirm('".jsVar(_("Sicuro di voler eliminare la categoria?"))."')) location.href='".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=point_ctg&action=delete&id=".$ctg->id."';\">".pub::icon('delete')."</span>";
				$selected = $ctg->id == $sel_id ? true : false;
				$buffer .= $htmlList->item(htmlChars($ctg->name), array($link_modify, $link_delete), $selected, true);
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p class=\"message\">"._("Non risultano categorie registrate")."</p>";
		}

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Informazioni sull'amministrazione delle categorie dei punti di interesse 
	 * 
	 * @access private
	 * @return string Informazioni sull'amministrazione delle categorie dei punti di interesse
	 */
	private function infoPointCtg() {

		$title = _("Informazioni");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$buffer = _("Gestione delle categorie dei punti di interesse");

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}

}


?>

<?php
/**
 * @file class.gmapsPolygonCtg.php
 * @brief Contiene la definizione ed implementazione della classe gmapsPolygonCtg.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

require_once('class.gmapsCtg.php');

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione di categorie di aree geolocalizzate
 *
 * Campi:
 *
 * - **id**: identificativo area
 * - **instance**: identificativo @ref gmaps di appartenenza 
 * - **name**: nome categoria
 * - **description**: descrizione
 * - **icon**: icona
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsPolygonCtg extends gmapsCtg {

	protected static $_tbl = 'gmaps_polygon_ctg';

	private $_controller;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsPolylineCtg 
	 * 
	 * @param int $id id del percorso
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsPolylineCtg un oggetto gmapsPolylineCtg
	 */
	function __construct($id, $instance) {
	
		$this->_controller = $instance;
		parent::__construct($id, "gmaps_polygon_ctg");
		$this->_data_www = CONTENT_WWW.'/gmaps/'.$this->_controller->instanceName().'/polygon_ctg';
		$this->_data_dir = CONTENT_DIR.OS.'gmaps'.OS.$this->_controller->instanceName().OS.'polygon_ctg';
	}

	/**
	 * @brief Elimina la categoria 
	 * 
	 * @access public
	 * @return mixed il risultato dell'operazione
	 *               - 'polygons': la categoria contiene aree e non può essere eliminata
	 *               - false: errore nell'eliminazione
	 *               - true: eliminazione effettuata
	 */
	public function delete() {

		$polygons = gmapsPolygon::getByCtg($this->id);

		if(count($polygons)) {
			return 'polygons';
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

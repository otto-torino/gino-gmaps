<?php
/**
 * @file class.gmapsPolylineCtg.php
 * @brief Contiene la definizione ed implementazione della classe gmapsPolylineCtg.
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
 * @brief Classe per la gestione di categorie di percorsi geolocalizzati
 *
 * Campi:
 *
 * - **id**: identificativo percorso
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
class gmapsPolylineCtg extends gmapsCtg {

	protected static $_tbl = 'gmaps_polyline_ctg';

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
		parent::__construct($id, "gmaps_polyline_ctg");
		$this->_data_www = CONTENT_WWW.'/gmaps/'.$this->_controller->instanceName().'/polyline_ctg';
		$this->_data_dir = CONTENT_DIR.OS.'gmaps'.OS.$this->_controller->instanceName().OS.'polyline_ctg';
	}

	/**
	 * @brief Elimina la categoria 
	 * 
	 * @access public
	 * @return mixed il risultato dell'operazione
	 *               - 'polylines': la categoria contiene percorsi e non può essere eliminata
	 *               - false: errore nell'eliminazione
	 *               - true: eliminazione effettuata
	 */
	public function delete() {

		$polylines = gmapsPolyline::getByCtg($this->id);

		if(count($polylines)) {
			return 'polylines';
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

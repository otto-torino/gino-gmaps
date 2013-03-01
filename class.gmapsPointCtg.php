<?php
/**
 * @file class.gmapsPointCtg.php
 * @brief Contiene la definizione ed implementazione della classe gmapsPointCtg.
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
 * @brief Classe per la gestione di categorie di punti di interesse geolocalizzati
 *
 * Campi:
 *
 * - **id**: identificativo punto di interesse
 * - **instance**: identificativo @ref gmaps di appartenenza 
 * - **name**: nome categoris
 * - **description**: descrizione
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsPointCtg extends gmapsCtg {

	protected static $_tbl = 'gmaps_point_ctg';

	private $_controller;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsPointCtg 
	 * 
	 * @param int $id id del punto di interesse
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsPointCtg un oggetto gmapsPointCtg
	 */
	function __construct($id, $instance) {
	
		$this->_controller = $instance;
		parent::__construct($id, "gmaps_point_ctg");

		$this->_data_www = CONTENT_WWW.'/gmaps/'.$this->_controller->instanceName().'/point_ctg';
		$this->_data_dir = CONTENT_DIR.OS.'gmaps'.OS.$this->_controller->instanceName().OS.'point_ctg';
	}
	
	/**
	 * @brief Elimina la categoria 
	 * 
	 * @access public
	 * @return mixed il risultato dell'operazione
	 *               - 'points': la categoria contiene punti di interesse e non può essere eliminata
	 *               - false: errore nell'eliminazione
	 *               - true: eliminazione effettuata
	 */
	public function delete() {

		$points = gmapsPoint::getByCtg($this->id);

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

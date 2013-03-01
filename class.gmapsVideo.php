<?php
/**
 * @file class.gmapsVideo.php
 * @brief Contiene la definizione ed implementazione della classe gmapsVideo.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione di video associati ai punti di interesse (link a youtube)
 *
 * Campi:
 *
 * - **id**: identificativo punto di interesse
 * - **point_id**: identificativo @ref gmapsPoint di appartenenza 
 * - **title**: titolo categoria
 * - **description**: descrizione
 * - **credits**: credits
 * - **link**: link alla risorsa youtube
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsVideo extends propertyObject {

	private static $_tbl_video = 'gmaps_point_video';

	private $_controller;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsVideo 
	 * 
	 * @param int $id id del servizio
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsVideo un oggetto gmapsVideo
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$_tbl_video;
		parent::__construct($id);
	}

	/**
	 * @brief Metodo setter per la proprietà point_id 
	 * 
	 * @param int $value identificativo dell'istanza @ref gmapsPoint 
	 * @access public
	 * @return bool true
	 */
	public function setPoint_id($value) {

		if($this->_p['point_id']!=$value && !in_array('point_id', $this->_chgP)) $this->_chgP[] = 'point_id';
		$this->_p['point_id'] = $value;
		return true;

	}

	/**
	 * @brief Form di inserimento e modifica video 
	 * 
	 * @param string $redirect url di reindirizzamento se il salvataggio va a buon fine
	 * @access public
	 * @return string form di inserimento e modifica video
	 */
	public function form($redirect) {

		if(isset($_POST['submit'])) {
			$gform = new Form('form_video', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				$this->title = cleanVar($_POST, 'title', 'string', '');
				$this->description = cleanVar($_POST, 'description', 'string', '');
				$this->credits = cleanVar($_POST, 'credits', 'string', '');
				$this->code = cleanVar($_POST, 'code', 'string', '');

				$this->updateDbData();

				header("Location: ".$redirect);
				exit();
			}
			else {
				error::errorMessage(array('error'=>1), $_SERVER['QUERY_STRING']);
				exit();
			}
		}

		if($this->id) {
			$title = _("Modifica video");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuovo video");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_video', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$buffer = $gform->form('', true, 'title', array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('title', 'text', $gform->retvar('title', htmlInput($this->title)), _("Titolo"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'title'));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array('cols'=>40, 'rows'=>4, 'trnsl'=>true, 'field'=>'description'));
		$buffer .= $gform->ctextarea('credits', $gform->retvar('credits', htmlInput($this->credits)), _("Credits"), array('cols'=>40, 'rows'=>2, 'trnsl'=>true, 'field'=>'credits'));
		$buffer .= $gform->ctextarea('code', $gform->retvar('code', htmlInput($this->code)), array(_("Codice embed"), _('Inserire interamente il codice embed fornito dalla piattaforma di streaming')), array('cols'=>40, 'rows'=>2, 'trnsl'=>true, 'field'=>'credits'));

		$buffer .= $gform->cinput('submit', 'submit', $submit, '', array('classField'=>'submit'));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Elimina l'immagine (da db e filesystem) 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 * 
	 */
	public function delete() {

		return $this->deleteDbData();

	}


}

?>

<?php
/**
 * @file class.gmapsEvent.php
 * @brief Contiene la definizione ed implementazione della classe gmapsEvent.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione di eventi associati ai punti di interesse
 *
 * Campi:
 *
 * - **id**: identificativo punto di interesse
 * - **insert_date**: datetime inserimento
 * - **point_id**: identificativo @ref gmapsPoint di appartenenza 
 * - **name**: nome evento
 * - **description**: descrizione
 * - **date**: data evento
 * - **duration**: durata evento
 * - **image**: immagine
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsEvent extends propertyObject {

	private static $_tbl_event = 'gmaps_point_event';

	private $_controller, $_data_www, $_data_dir, $_extension_media, $_prefix_img, $_prefix_thumb;

	/**
	 * @brief larghezza di ridimensionamento dell'immagine uploadata 
	 */
	private $_img_width = 600;

	/**
	 * @brief larghezza di ridimensionamento della thumb generata dall'immagine uploadata 
	 */
	private $_thumb_width = 100;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsEvent 
	 * 
	 * @param int $id id del servizio
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsVideo un oggetto gmapsEvent
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$_tbl_event;
		parent::__construct($id);

		$this->_extension_media = array('jpg', 'jpeg', 'png');
		$this->_prefix_img = 'img_';
		$this->_prefix_thumb = 'thumb_';
		$this->_data_www = CONTENT_WWW.'/gmaps/'.$this->_controller->instanceName().'/point/events';
		$this->_data_dir = CONTENT_DIR.OS.'gmaps'.OS.$this->_controller->instanceName().OS.'point'.OS.'events';
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
	 * @brief Form di inserimento e modifica evento 
	 * 
	 * @param string $redirect url di reindirizzamento se il salvataggio va a buon fine
	 * @access public
	 * @return string form di inserimento e modifica evento
	 */
	public function form($redirect) {

		if(isset($_POST['submit'])) {
			$gform = new Form('form_event', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				if(!$this->id) {
					$this->insert_date = date("Y-m-d H:i:s");
				}
				$this->name = cleanVar($_POST, 'name', 'string', '');
				$this->description = cleanVar($_POST, 'description', 'string', '');
				$this->duration = cleanVar($_POST, 'duration', 'string', '');
				$this->date = dateToDbDate(cleanVar($_POST, 'date', 'string', ''), '/');

				$this->updateDbData();

				$old_image = cleanVar($_POST, 'old_image', 'string', '');

				$res = $gform->manageFile('image', $old_image, true, $this->_extension_media, $this->_data_dir.OS, preg_replace("#action=insert#", 'action=modify&id='.$this->id, $_SERVER['QUERY_STRING']), $this->_tbl_data, 'image', 'id', $this->id, array('prefix_file'=>$this->_prefix_img, 'prefix_thumb'=>$this->_prefix_thumb, 'width'=>$this->_img_width, 'thumb_width'=>$this->_thumb_width));

				header("Location: ".$redirect);
				exit();
			}
			else {
				error::errorMessage(array('error'=>1), $_SERVER['QUERY_STRING']);
				exit();
			}
		}

		if($this->id) {
			$title = _("Modifica evento");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuovo evento");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_event', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$buffer = $gform->form('', true, 'name,date,duration', array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('name', 'text', $gform->retvar('name', htmlInput($this->name)), _("Nome"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'name'));
		$buffer .= $gform->cinput_date('date', $gform->retvar('date', dbDateToDate($this->date, '/')), _("Data"), array('required'=>true));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array('cols'=>40, 'rows'=>4, 'trnsl'=>true, 'field'=>'description'));
		$duration = $gform->retvar('duration', htmlInput($this->duration));
		$buffer .= $gform->cinput('duration', 'text', $duration, array(_("Durata"), _("giorni")), array('required'=>true, 'maxlength'=>200, 'size'=>40));
		$buffer .= $gform->cfile('image', $this->image, _("Immagine"), array("required"=>true, "extensions"=>$this->_extension_media, "del_check"=>true, "preview"=>true, "previewSrc"=>$this->_data_www.'/'.$this->_prefix_img.$this->image));

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

		@unlink($this->_data_dir.OS.$this->_prefix_img.$this->image);
		@unlink($this->_data_dir.OS.$this->_prefix_thumb.$this->image);

		return $this->deleteDbData();

	}

	/**
	 * @brief Path all'immagine
	 * 
	 * @access public
	 * @return string path immagine
	 */
	public function imagePath() {

		return $this->_data_www."/".$this->_prefix_img.$this->image;

	}

	/**
	 * @brief Path alla thumb dell'immagine
	 * 
	 * @access public
	 * @return string path thumb immagine
	 */
	public function thumbPath() {

		return $this->_data_www."/".$this->_prefix_thumb.$this->image;

	}



}

?>

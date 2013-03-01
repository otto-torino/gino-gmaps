<?php
/**
 * @file class.gmapsAttachment.php
 * @brief Contiene la definizione ed implementazione della classe gmapsAttachment.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione di allegati associati ai punti di interesse
 *
 * Campi:
 *
 * - **id**: identificativo punto di interesse
 * - **insert_date**: datetime inserimento
 * - **point_id**: identificativo @ref gmapsPoint di appartenenza 
 * - **name**: nome allegato
 * - **description**: descrizione
 * - **size**: dimensione allegato
 * - **filename**: file
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsAttachment extends propertyObject {

	private static $_tbl_event = 'gmaps_point_attachment';

	private $_controller, $_data_www, $_data_dir, $_extension_attachment;

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

		$this->_extension_attachment = array('doc', 'docx', 'pdf', 'ppt', 'pptx', 'xlsx', 'xls', 'odt', 'ods', 'odp', 'zip');
		$this->_data_www = CONTENT_WWW.'/gmaps/'.$this->_controller->instanceName().'/point/attachments';
		$this->_data_dir = CONTENT_DIR.OS.'gmaps'.OS.$this->_controller->instanceName().OS.'point'.OS.'attachments';
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
	 * @brief Metodo setter per la proprietà size 
	 * 
	 * @param int $value dimensione file 
	 * @access public
	 * @return bool true
	 */
	public function setSize($value) {

		if($this->_p['size']!=$value && !in_array('size', $this->_chgP)) $this->_chgP[] = 'size';
		$this->_p['size'] = $value;
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
			$gform = new Form('form_attachment', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				if(!$this->id) {
					$this->insert_date = date("Y-m-d H:i:s");
				}
				$this->name = cleanVar($_POST, 'name', 'string', '');
				$this->description = cleanVar($_POST, 'description', 'string', '');

				$this->updateDbData();

				$old_filename = cleanVar($_POST, 'old_filename', 'string', '');

				$res = $gform->manageFile('filename', $old_filename, false, $this->_extension_attachment, $this->_data_dir.OS, preg_replace("#action=insert#", 'action=modify&id='.$this->id, $_SERVER['QUERY_STRING']), $this->_tbl_data, 'filename', 'id', $this->id, array('check_type'=>false));

				$this->size = $_FILES['filename']['size'];
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
			$title = _("Modifica allegato");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuovo allegato");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_attachment', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$buffer = $gform->form('', true, 'name', array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('name', 'text', $gform->retvar('name', htmlInput($this->name)), _("Nome"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'name'));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array('cols'=>40, 'rows'=>4, 'trnsl'=>true, 'field'=>'description'));
		$buffer .= $gform->cfile('filename', $this->filename, _("File"), array("required"=>true, "extensions"=>$this->_extension_attachment, "del_check"=>true, "preview"=>false));

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

		@unlink($this->_data_dir.OS.$this->filename);

		return $this->deleteDbData();

	}

	/**
	 * @brief Path del file
	 * 
	 * @access public
	 * @return string path file
	 */
	public function filePath() {
		
		return $this->_data_www."/".$this->filename;

	}

}

?>

<?php
/**
 * @file class.gmapsImage.php
 * @brief Contiene la definizione ed implementazione della classe gmapsImage.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione di immagini associate ai punti di interesse
 *
 * Campi:
 *
 * - **id**: identificativo punto di interesse
 * - **point_id**: identificativo @ref gmapsPoint di appartenenza 
 * - **title**: titolo categoria
 * - **description**: descrizione
 * - **credits**: credits
 * - **filename**: nome del file
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsImage extends propertyObject {

	private static $_tbl_image = 'gmaps_point_image';

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
	 * @brief Costruisce un'istanza di tipo gmapsImage 
	 * 
	 * @param int $id id del servizio
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsImage un oggetto gmapsImage
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$_tbl_image;
		parent::__construct($id);

		$this->_extension_media = array('jpg', 'jpeg', 'png');
		$this->_prefix_img = 'img_';
		$this->_prefix_thumb = 'thumb_';
		$this->_data_www = CONTENT_WWW.'/gmaps/'.$this->_controller->instanceName().'/point/images';
		$this->_data_dir = CONTENT_DIR.OS.'gmaps'.OS.$this->_controller->instanceName().OS.'point'.OS.'images';
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
	 * @brief Form di inserimento e modifica immagine 
	 * 
	 * @param string $redirect url di reindirizzamento se il salvataggio va a buon fine
	 * @access public
	 * @return string form di inserimento e modifica immagine
	 */
	public function form($redirect) {

		if(isset($_POST['submit'])) {
			$gform = new Form('form_image', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				$this->title = cleanVar($_POST, 'title', 'string', '');
				$this->description = cleanVar($_POST, 'description', 'string', '');
				$this->credits = cleanVar($_POST, 'credits', 'string', '');

				$this->updateDbData();

				$old_filename = cleanVar($_POST, 'old_filename', 'string', '');

				$res = $gform->manageFile('filename', $old_filename, true, $this->_extension_media, $this->_data_dir.OS, preg_replace("#action=insert#", 'action=modify&id='.$this->id, $_SERVER['QUERY_STRING']), $this->_tbl_data, 'filename', 'id', $this->id, array('prefix_file'=>$this->_prefix_img, 'prefix_thumb'=>$this->_prefix_thumb, 'width'=>$this->_img_width, 'thumb_width'=>$this->_thumb_width));

				header("Location: ".$redirect);
				exit();
			}
			else {
				error::errorMessage(array('error'=>1), $_SERVER['QUERY_STRING']);
				exit();
			}
		}

		if($this->id) {
			$title = _("Modifica immagine");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuova immagine");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_image', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$buffer = $gform->form('', true, 'title', array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('title', 'text', $gform->retvar('title', htmlInput($this->title)), _("Titolo"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'title'));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array('cols'=>40, 'rows'=>4, 'trnsl'=>true, 'field'=>'description'));
		$buffer .= $gform->ctextarea('credits', $gform->retvar('credits', htmlInput($this->credits)), _("Credits"), array('cols'=>40, 'rows'=>2, 'trnsl'=>true, 'field'=>'credits'));

		$buffer .= $gform->cfile('filename', $this->filename, _("File"), array("required"=>true, "extensions"=>$this->_extension_media, "del_check"=>true, "preview"=>true, "previewSrc"=>$this->_data_www.'/'.$this->_prefix_img.$this->filename));
		$buffer .= $gform->cinput('submit', 'submit', $submit, '', array('classField'=>'submit'));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * @brief Immagine thumb come tag img
	 * 
	 * @access public
	 * @return string immagine thumb
	 */
	public function thumb($css_class) {
		
		return "<img class=\"".$css_class."\" src=\"".$this->_data_www."/".$this->_prefix_thumb.$this->filename."\" alt=\"".preg_replace("#\"#", "'", htmlChars($this->ml('title')))."\" />";

	}

	/**
	 * @brief Immagine come tag img
	 * 
	 * @access public
	 * @return string immagine
	 */
	public function img($css_class = '', $title = null, $description=null) {
		
		return "<img class=\"".$css_class."\" src=\"".$this->_data_www."/".$this->_prefix_img.$this->filename."\" alt=\"".preg_replace("#\"#", "'", htmlChars($this->ml('title')))."\" />";

	}

	/**
	 * @brief Path alla thumb
	 * 
	 * @access public
	 * @return string path thumb immagine
	 */
	public function thumbPath() {
		
		return $this->_data_www."/".$this->_prefix_thumb.$this->filename;

	}

	/**
	 * @brief Path all'immagine
	 * 
	 * @access public
	 * @return string path immagine
	 */
	public function path() {
		
		return $this->_data_www."/".$this->_prefix_img.$this->filename;

	}

	/**
	 * @brief Elimina l'immagine (da db e filesystem) 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 * 
	 */
	public function delete() {

		@unlink($this->_data_dir.OS.$this->_prefix_img.$this->filename);
		@unlink($this->_data_dir.OS.$this->_prefix_thumb.$this->filename);

		return $this->deleteDbData();

	}


}

?>

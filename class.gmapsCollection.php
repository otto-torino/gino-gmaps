<?php
/**
 * @file class.gmapsCollection.php
 * @brief Contiene la definizione ed implementazione della classe gmapsCollection.
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */

/**
 * @ingroup gino-gmaps
 * @brief Classe per la gestione di collezioni associate ai punti di interesse
 *
 * Campi:
 *
 * - **id**: identificativo punto di interesse
 * - **point_id**: identificativo @ref gmapsPoint di appartenenza 
 * - **name**: nome collezione
 * - **description**: descrizione
 * - **image**: nome del file immagine
 *
 * Proprietà:
 *
 * - **_images**: array di immagini associate alla collezione
 *
 * @version 1.0
 * @copiright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 *
 */
class gmapsCollection extends propertyObject {

	private static $_tbl_collection = 'gmaps_point_collection';
	private static $_tbl_collection_image = 'gmaps_point_collection_image';

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
	 * @brief Array di immagini (@ref gmapsCollectionImage) associate alla collezione
	 */
	private $_images;

	/**
	 * @brief Costruisce un'istanza di tipo gmapsCollection 
	 * 
	 * @param int $id id del servizio
	 * @param object $instance the @ref gmaps instance
	 * @access public
	 * @return gmapsCollection un oggetto gmapsCollection
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$_tbl_collection;
		parent::__construct($id);

		$this->_extension_media = array('jpg', 'jpeg', 'png');
		$this->_prefix_img = 'img_';
		$this->_prefix_thumb = 'thumb_';
		$this->_data_www = CONTENT_WWW.'/gmaps/'.$this->_controller->instanceName().'/point/collections';
		$this->_data_dir = CONTENT_DIR.OS.'gmaps'.OS.$this->_controller->instanceName().OS.'point'.OS.'collections';

		$this->_images = array();
		$query = "SELECT id FROM ".self::$_tbl_collection_image." WHERE collection_id='".$this->id."'";
		$a = $this->_db->selectquery($query);
		if(count($a)) {
			foreach($a as $b) {
				$this->_images[] = new gmapsCollectionImage($b['id'], $this->_controller);	
			}
		}
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
	 * @brief Getter della proprietà _images, tutte le immagini associate alla collezione 
	 * 
	 * @access public
	 * @return array array di oggetti @ref gmapsCollectionImage
	 */
	public function images() {

		return $this->_images;

	}

	/**
	 * @brief Form di inserimento e modifica collezione 
	 * 
	 * @param string $redirect url di reindirizzamento se il salvataggio va a buon fine
	 * @access public
	 * @return string form di inserimento e modifica collezione
	 */
	public function form($redirect) {

		if(isset($_POST['submit'])) {
			$gform = new Form('form_collection', 'post', false, array('verifyToken'=>true));
			$gform->save('dataform');
			$req_error = $gform->arequired();	
			if(!$req_error) {
				$this->name = cleanVar($_POST, 'name', 'string', '');
				$this->description = cleanVar($_POST, 'description', 'string', '');

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
			$title = _("Modifica collezione");
			$submit = _("modifica");
		}
		else {
			$title = _("Nuova collezione");
			$submit= _("inserisci");
		}
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title));

		$gform = new Form('form_collection', 'post', true, array('trnsl_table'=>$this->_tbl_data, 'trnsl_id'=>$this->id));
		$gform->load('dataform');
		$buffer = $gform->form('', true, 'name', array('generateToken'=>true));
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('name', 'text', $gform->retvar('name', htmlInput($this->name)), _("Nome"), array('required'=>true, 'maxlength'=>200, 'size'=>40, 'trnsl'=>true, 'field'=>'name'));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array('cols'=>40, 'rows'=>4, 'trnsl'=>true, 'field'=>'description'));

		$buffer .= $gform->cfile('image', $this->image, _("Immagine"), array("required"=>false, "extensions"=>$this->_extension_media, "del_check"=>true, "preview"=>true, "previewSrc"=>$this->_data_www.'/'.$this->_prefix_img.$this->image));
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

		return "<img class=\"".$css_class."\" src=\"".$this->_data_www."/".$this->_prefix_thumb.$this->image."\" alt=\"".preg_replace("#\"#", "'", htmlChars($this->ml('name')))."\" />";

	}

	/**
	 * @brief Immagine come tag img
	 * 
	 * @access public
	 * @return string immagine
	 */
	public function img($css_class = '', $title = null, $description=null) {

		return "<img class=\"".$css_class."\" src=\"".$this->_data_www."/".$this->_prefix_img.$this->image."\" alt=\"".preg_replace("#\"#", "'", htmlChars($this->ml('name')))."\" />";

	}

	/**
	 * @brief Path alla thumb
	 * 
	 * @access public
	 * @return string path thumb immagine
	 */
	public function thumbPath() {

		return $this->_data_www."/".$this->_prefix_thumb.$this->image;

	}

	/**
	 * @brief Path all'immagine
	 * 
	 * @access public
	 * @return string path immagine
	 */
	public function path() {

		return $this->_data_www."/".$this->_prefix_img.$this->image;

	}

	/**
	 * @brief Elimina l'immagine (da db e filesystem) 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 * 
	 */
	public function delete() {

		// delete images
		foreach($this->_images as $image) {
			$image->delete();
		}

		@unlink($this->_data_dir.OS.$this->_prefix_img.$this->image);
		@unlink($this->_data_dir.OS.$this->_prefix_thumb.$this->image);

		return $this->deleteDbData();

	}


}

?>

<?php
/**
 * @file class.PointImage.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gmaps.PointImage
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */

namespace Gino\App\Gmaps;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un'immagine associata ad un punto di interesse
 *
 * @version 0.1.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class PointImage extends \Gino\Model
{
    public static $table = 'gmaps_point_image';

    private static $_extension_img = array('jpg', 'jpeg', 'png');

    /**
     * @brief Costruttore
     *
     * @param int $id id del record
     * @param \Gino\App\Gmaps\gmaps $instance istanza di Gino.App.Gmaps.gmaps
     * @return istanza di Gino.App.Gmaps.PointImage
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'description' => _('Descrizione'),
            'file' => _('File'),
        );

        parent::__construct($id);

        $this->_model_label = _('Immagine');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     *
     * @return nome
     */
    function __toString()
    {
        return (string) $this->name;
    }

    /**
     * @brief Url immagine
     * @return url
     */
    public function getUrl()
    {
        return $this->_controller->getBaseTypePath('point_img').'/'.$this->file;
    }

    /**
     * @brief Definizione della struttura del modello
     *
     * @see Gino.Model::structure()
     * @param $id id dell'istanza
     *
     * @return array, struttura del modello
     */
    public function structure($id)
    {
        $structure = parent::structure($id);

        $base_path = $this->_controller->getBaseTypeAbsPath('point_img');

        $structure['file'] = new \Gino\ImageField(array(
            'name' => 'file',
            'model' => $this,
            'required' => TRUE,
            'extensions' => self::$_extension_img,
            'resize' => FALSE,
            'path' => $base_path,
        ));

        $structure['point_id']->setWidget('hidden');
        $structure['category_id']->setWidget('hidden');


        return $structure;
    }

}

<?php
/**
 * @file class.Marker.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gmaps.Marker
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */

namespace Gino\App\Gmaps;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un marker personalizzato
 *
 * @version 0.1.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class Marker extends \Gino\Model
{
    public static $table = 'gmaps_marker';

    private static $_extension_img = array('jpg', 'jpeg', 'png');

    /**
     * @brief Costruttore
     *
     * @param int $id id del record
     * @param \Gino\App\Gmaps\gmaps $instance istanza di Gino.App.Gmaps.gmaps
     * @return istanza di Gino.App.Gmaps.Marker
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'icon' => _('Icona'),
        );

        parent::__construct($id);

        $this->_model_label = _('Marker');
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
     * @brief Url icona
     * @return url
     */
    public function iconUrl()
    {
        return $this->_controller->getBaseTypePath('marker_icon').'/'.$this->icon;
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

        $base_path = $this->_controller->getBaseTypeAbsPath('marker_icon');

        $structure['icon'] = new \Gino\ImageField(array(
            'name' => 'icon',
            'model' => $this,
            'extensions' => self::$_extension_img,
            'resize' => FALSE,
            'path' => $base_path,
        ));



        return $structure;
    }

}

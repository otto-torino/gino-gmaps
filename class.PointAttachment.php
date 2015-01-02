<?php
/**
 * @file class.PointAttachment.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gmaps.PointAttachment
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */

namespace Gino\App\Gmaps;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un allegato associato ad un punto di interesse
 *
 * @version 0.1.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class PointAttachment extends \Gino\Model
{
    public static $table = 'gmaps_point_attachment';

    private static $_extension_file = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'odt', 'txt', 'zip', 'rar', 'png', 'jpg', 'bmp', 'wav', 'mp3', '3gp');

    /**
     * @brief Costruttore
     *
     * @param int $id id del record
     * @param \Gino\App\Gmaps\gmaps $instance istanza di Gino.App.Gmaps.gmaps
     * @return istanza di Gino.App.Gmaps.PointAttachment
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'description' => _('Descrizione'),
            'file' => array(_('Allegato'), _('Estensioni permesse: ').implode(',', self::$_extension_file)),
            'filesize' => _('Dimensioni'),
        );

        parent::__construct($id);

        $this->_model_label = _('Allegato');
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
     * @brief File url
     * @return url
     */
    public function getUrl()
    {
        return $this->_controller->getBaseTypePath('point_attachment').'/'.$this->file;
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

        $base_path = $this->_controller->getBaseTypeAbsPath('point_attachment');

        $structure['file'] = new \Gino\FileField(array(
            'name'=>'file',
            'model'=>$this,
            'extensions'=>self::$_extension_file,
            'path'=>$base_path,
            'required'=>TRUE,
            'check_type'=>FALSE,
            'filesize_field' => 'filesize'
        ));

        $structure['filesize']->setWidget('hidden');
        $structure['point_id']->setWidget('hidden');
        $structure['category_id']->setWidget('hidden');


        return $structure;
    }

}

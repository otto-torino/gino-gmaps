<?php
/**
 * @file class.Map.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gmaps.Map
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */

namespace Gino\App\Gmaps;

/**
 * @brief Classe di tipo Gino.Model che rappresenta una mappa interattiva contente punti, percorsi ed aree
 *
 * @version 0.1.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class Map extends \Gino\Model
{
    public static $table = 'gmaps_map',
                  $table_points = 'gmaps_map_point',
                  $table_paths = 'gmaps_map_path',
                  $table_areas = 'gmaps_map_area';

    /**
     * @brief Costruttore
     *
     * @param int $id id del record
     * @param \Gino\App\Gmaps\gmaps $instance istanza di Gino.App.Gmaps.gmaps
     * @return istanza di Gino.App.Gmaps.Map
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'slug' => array(_('Slug'), _('url parlante')),
            'description' => _('Descrizione'),
            'width' => array(_('Larghezza'), _('Inserire lo stile completo: 500px | 100%')),
            'height' => array(_('Altezza'), _('Inserire lo stile completo: 300px | 100%')),
            // m2m
            'points' => _('Punti di interesse'),
            'paths' => _('Percorsi'),
            'areas' => _('Aree'),
        );

        parent::__construct($id);

        $this->_model_label = _('Mappa');
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
     * @brief Url vista mappa
     * @return url
     */
    public function getUrl()
    {
        return $this->_registry->router->link($this->_controller->getInstanceName(), 'map', array('id' => $this->slug));
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

        $structure['slug'] = new \Gino\SlugField(array(
            'name'=>'slug',
            'model'=>$this,
            'required'=>true,
            'autofill'=>'name',
        ));

        $structure['points'] = new PointManyToManyField(array(
            'name' => 'points',
            'model' => $this,
            'm2m' => '\Gino\App\Gmaps\Point',
            'm2m_where' => 'instance=\''.$this->_controller->getInstance().'\'',
            'm2m_controller' => $this->_controller,
            'join_table' => self::$table_points,
            'add_related' => TRUE,
            'add_related_url' => $this->_controller->linkAdmin(array(), 'block=point&insert=1')
        ));

        $structure['paths'] = new \Gino\ManyToManyField(array(
            'name' => 'paths',
            'model' => $this,
            'm2m' => '\Gino\App\Gmaps\Path',
            'm2m_where' => 'instance=\''.$this->_controller->getInstance().'\'',
            'm2m_controller' => $this->_controller,
            'join_table' => self::$table_paths,
            'add_related' => TRUE,
            'add_related_url' => $this->_controller->linkAdmin(array(), 'block=path&insert=1')
        ));

        $structure['areas'] = new \Gino\ManyToManyField(array(
            'name' => 'areas',
            'model' => $this,
            'm2m' => '\Gino\App\Gmaps\Area',
            'm2m_where' => 'instance=\''.$this->_controller->getInstance().'\'',
            'm2m_controller' => $this->_controller,
            'join_table' => self::$table_areas,
            'add_related' => TRUE,
            'add_related_url' => $this->_controller->linkAdmin(array(), 'block=area&insert=1')
        ));

        return $structure;
    }

}

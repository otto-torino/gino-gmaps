<?php
/**
 * @file class.Area.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gmaps.Area
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */

namespace Gino\App\Gmaps;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un'area
 *
 * @version 0.1.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class Area extends \Gino\Model
{
    public static $table = 'gmaps_area',
                  $table_categories = 'gmaps_area_category',
                  $table_points = 'gmaps_area_point';

    /**
     * @brief Costruttore
     *
     * @param int $id id del record
     * @param \Gino\App\Gmaps\gmaps $instance istanza di Gino.App.Gmaps.gmaps
     * @return istanza di Gino.App.Gmaps.Area
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'slug' => array(_('Slug'), _('url parlante')),
            'description' => _('Descrizione'),
            'lat' => _('Latitudine'),
            'lng' => _('Longitudine'),
            'color' => array(_('Colore riempimento'), _('Utilizzare un formato css valido')),
            'width' => _('Spessore tracciato (px)'),
            // m2m
            'categories' => _('Categorie'),
            'points' => _('Punti di interesse'),
        );

        parent::__construct($id);

        $this->_model_label = _('Area');
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
     * @brief Array js di nomi di categorie associate
     * @return string
     */
    public function jsCtgArray()
    {
        $ctgs_names = array();
        foreach($this->categories as $ctg_id) {
            $ctg = new Category($ctg_id, $this->_controller);
            $ctgs_names[] = \Gino\jsVar($ctg->ml('name'));
        }
        return count($ctgs_names) ? "['".implode("','", $ctgs_names)."']" : "[]";
    }

    /**
     * @brief Url vista dettaglio
     * @return url
     */
    public function getUrl()
    {
        return $this->_registry->router->link($this->_controller->getInstanceName(), 'area', array('id' => $this->slug));
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

        // m2m

        $structure['categories'] = new \Gino\ManyToManyField(array(
            'name' => 'categories',
            'model' => $this,
            'm2m' => '\Gino\App\Gmaps\Category',
            'm2m_where' => 'instance=\''.$this->_controller->getInstance().'\'',
            'm2m_controller' => $this->_controller,
            'join_table' => self::$table_categories,
            'add_related' => TRUE,
            'add_related_url' => $this->_controller->linkAdmin(array(), 'block=category&insert=1')
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

        return $structure;
    }

}

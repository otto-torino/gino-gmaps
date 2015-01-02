<?php
/**
 * @file class.Point.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gmaps.Point
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */

namespace Gino\App\Gmaps;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un punto di interesse
 *
 * @version 0.1.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class Point extends \Gino\Model
{
    public static $table = 'gmaps_point',
                  $table_categories = 'gmaps_point_category',
                  $table_services = 'gmaps_point_service';

    /**
     * @brief Costruttore
     *
     * @param int $id id del record
     * @param \Gino\App\Gmaps\gmaps $instance istanza di Gino.App.Gmaps.gmaps
     * @return istanza di Gino.App.Gmaps.Point
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'slug' => array(_('Slug'), _('url parlante')),
            'address' => _('Indirizzo'),
            'cap' => _('Cap'),
            'city' => _('Città'),
            'nation' => _('Nazione'),
            'marker' => _('Marker'),
            'lat' => _('Latitudine'),
            'lng' => _('Longitudine'),
            'description' => _('Descrizione'),
            'phone' => _('Telefono'),
            'email' => _('Email'),
            'web' => _('Sito web'),
            // m2m
            'categories' => _('Categorie'),
            'services' => _('Servizi'),
            // m2mt
            'images' => _('Immagini'),
            'videos' => _('Video'),
            'attachments' => _('Allegati'),
        );

        parent::__construct($id);

        $this->_model_label = _('Punto di interesse');
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
     * @brief Url vista dettaglio
     * @return url
     */
    public function getUrl()
    {
        return $this->_registry->router->link($this->_controller->getInstanceName(), 'point', array('id' => $this->slug));
    }

    /**
     * @brief Indirizzo completo
     * @return indirizzo, string
     */
    public function address()
    {
        if(!$this->address and !$this->cap and !$this->city and !$this->nation) {
            return '';
        }

        $registry = \Gino\Registry::instance();
        $rows = $this->_db->select($registry->request->session->lng, TBL_NATION, "id='".$this->nation."'");
        $nation = $rows[0][$registry->request->session->lng];

        if($this->address and $this->cap and $this->city and $this->nation) {
            return sprintf('%s, %s %s, %s', $this->address, $this->cap, $this->city, $nation);
        }

        if($this->address and $this->cap and $this->city) {
            return sprintf('%s, %s %s', $this->address, $this->cap, $this->city);
        }

        if($this->address and $this->cap) {
            return sprintf('%s, %s', $this->address, $this->cap);
        }

        if($this->address and $this->city) {
            return sprintf('%s, %s', $this->address, $this->city);
        }

        if($this->address) {
            return sprintf('%s', $this->address);
        }

        if($this->cap and $this->city and $this->nation) {
            return sprintf('%s %s, %s', $this->city, $this->cap, $nation);
        }

        if($this->city and $this->nation) {
            return sprintf('%s, %s', $this->city, $nation);
        }

        if($this->city) {
            return sprintf('%s', $this->city);
        }

        if($this->nation) {
            return sprintf('%s', $nation);
        }

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
     * @brief Array js di nomi di categorie associate
     * @return string
     */
    public function ctgArray()
    {
        $ctgs_names = array();
        foreach($this->categories as $ctg_id) {
            $ctg = new Category($ctg_id, $this->_controller);
            $ctgs_names[] = \Gino\htmlChars($ctg->ml('name'));
        }
        return $ctgs_names;
    }

    /**
     * @brief Url icona marker
     * @return url
     */
    public function iconUrl()
    {
        if($this->marker) {
            $marker = new Marker($this->marker, $this->_controller);
            return $marker->iconUrl();
        }

        return '';
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

        $request = \Gino\Http\Request::instance();
        $selection = sprintf('id, %s', $request->session->lngDft);
        $rows = $this->_db->select($selection, TBL_NATION);
        $data_nations = array();
        foreach($rows as $row) {
            $data_nations[$row['id']] = \Gino\htmlChars($row[$request->session->lngDft]);
        }
        $structure['nation'] = new \Gino\EnumField(array(
            'name' => 'nation',
            'widget' => 'select',
            'model' => $this,
            'required' => FALSE,
            'enum' => $data_nations
        ));

        $structure['marker'] = new \Gino\ForeignKeyField(array(
            'name' => 'marker',
            'model' => $this,
            'required' => FALSE,
            'foreign'=>'\Gino\App\Gmaps\Marker',
            'foreign_where'=>'instance=\''.$this->_controller->getInstance().'\'',
            'foreign_controller'=>$this->_controller,
            'add_related' => TRUE,
            'add_related_url' => $this->_controller->linkAdmin(array(), 'block=marker&insert=1')
        ));

        $structure['email'] = new \Gino\EmailField(array(
            'name' => 'email',
            'model' => $this,
            'required' => FALSE,
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

        $structure['services'] = new \Gino\ManyToManyField(array(
            'name' => 'services',
            'model' => $this,
            'm2m' => '\Gino\App\Gmaps\Service',
            'm2m_where' => 'instance=\''.$this->_controller->getInstance().'\'',
            'm2m_controller' => $this->_controller,
            'join_table' => self::$table_services,
            'add_related' => TRUE,
            'add_related_url' => $this->_controller->linkAdmin(array(), 'block=service&insert=1')
        ));

        // m2mt

        $structure['images'] = new \Gino\ManyToManyThroughField(array(
            'name'=>'images',
            'model'=>$this,
            'm2m'=>'\Gino\App\Gmaps\PointImage',
            'm2m_controller'=>$this->_controller,
            'controller'=>$this->_controller
        ));

        $structure['videos'] = new \Gino\ManyToManyThroughField(array(
            'name'=>'videos',
            'model'=>$this,
            'm2m'=>'\Gino\App\Gmaps\PointVideo',
            'm2m_controller'=>$this->_controller,
            'controller'=>$this->_controller
        ));

        $structure['attachments'] = new \Gino\ManyToManyThroughField(array(
            'name'=>'attachments',
            'model'=>$this,
            'm2m'=>'\Gino\App\Gmaps\PointAttachment',
            'm2m_controller'=>$this->_controller,
            'controller'=>$this->_controller,
            'remove_fields' => array('filesize')
        ));

        return $structure;
    }

}

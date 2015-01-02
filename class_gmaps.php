<?php
/**
 * @file class_gmaps.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gmaps.gmaps
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */

/**
 * @namespace Gino.App.Gmaps
 * @description Namespace dell'applicazione Gmaps
 */
namespace Gino\App\Gmaps;

use \Gino\View;

require_once('class.Category.php');
require_once('class.Service.php');
require_once('class.Marker.php');
require_once('class.Point.php');
require_once('class.PointImage.php');
require_once('class.PointVideo.php');
require_once('class.PointAttachment.php');
require_once('class.Path.php');
require_once('class.Area.php');
require_once('class.Map.php');

require_once('class.PointManyToManyField.php');

/**
 * @brief Classe di tipo Gino.Controller del modulo Gmaps
 *
 * @version 0.1.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class gmaps extends \Gino\Controller
{

    private $_default_map_id;
    private $_tbl_opt;

    /**
     * @brief Costruttore
     *
     * @param int $instance_id id istanza
     * @return \Gino\App\Gmaps\gmaps istanza di Gino.App.Gmaps.gmaps
     */
    public function __construct($instance_id)
    {
        parent::__construct($instance_id);

        //options
        $this->_tbl_opt = 'gmaps_opt';
        $this->_default_map_id = $this->setOption('default_map_id');
        $this->_options = \Gino\Loader::load('Options', array($this));
        $this->_optionsLabels = array(
            'default_map_id'=>array(_('Mappa di default'), _('ID della mappa da mostrare quando non viene passato un id tramite GET'))
        );
    }

    /**
     * @brief Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
     *
     * @return lista delle proprietà utilizzate per la creazione di istanze di tipo events (tabelle, css, viste, folders)
     */
    public static function getClassElements()
    {
        return array(
            "tables"=>array(
                'gmaps_opt',
                'gmaps_category',
                'gmaps_service',
                'gmaps_marker',
                'gmaps_point',
                'gmaps_point_image',
                'gmaps_point_video',
                'gmaps_point_attachment',
                'gmaps_point_category',
                'gmaps_point_service',
                'gmaps_path',
                'gmaps_path_category',
                'gmaps_path_point',
                'gmaps_area_category',
                'gmaps_area_point',
                'gmaps_map',
                'gmaps_map_point',
                'gmaps_map_path',
                'gmaps_map_area'
            ),
            "css"=>array(
                'gmaps.css',
            ),
            "views" => array(
                'map.php' => _('Mappa'),
                'point.php' => _('Dettaglio punto di interesse'),
                'path.php' => _('Dettaglio percorso'),
                'area.php' => _('Dettaglio area'),
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'gmaps'=> array(
                    'point' => array(
                        'img' => null,
                        'video' => null,
                        'attachment' => null
                    ),
                    'service' => array(
                        'icon' => null
                    ),
                    'marker' => array(
                        'icon' => null
                    )
                )
            ),
        );
    }

    /**
     * @brief Metodo invocato quando viene eliminata un'istanza di tipo gmaps
     *
     * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory
     * @return TRUE
     */
    public function deleteInstance()
    {

        $this->requirePerm('can_admin');

        /** delete models */
        Category::deleteInstance($this);
        Service::deleteInstance($this);
        Marker::deleteInstance($this);
        Point::deleteInstance($this);
        Path::deleteInstance($this);
        Area::deleteInstance($this);
        Map::deleteInstance($this);

        /** delete opzioni */
        $opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
        \Gino\Translation::deleteTranslations($this->_tbl_opt, $opt_id);
        $result = $this->_db->delete($this->_tbl_opt, "instance=".$this->_instance);

        /** delete css files */
        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            unlink(APP_DIR.OS.$this->_class_name.OS.\Gino\baseFileName($css)."_".$this->_instance_name.".css");
        }

        /** eliminazione views */
        foreach($classElements['views'] as $k => $v) {
            unlink($this->_view_dir.OS.\Gino\baseFileName($k)."_".$this->_instance_name.".php");
        }

        /** delete folder structure */
        foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
            \Gino\deleteFileDir($fld.OS.$this->_instance_name, TRUE);
        }

        return TRUE;
    }

    /**
     * @brief Metodi pubblici disponibili per inserimento in layout (non presenti nel file gmaps.ini) e menu (presenti nel file gmaps.ini)
     *
     * @return lista metodi NOME_METODO => array('label' => LABEL, 'permissions' = PERMISSIONS)
     */
    public static function outputFunctions()
    {
        $list = array(
            "map" => array("label"=>_("Mappa"), "permissions"=>array()),
            "point" => array("label"=>_("Dettaglio punto di interesse"), "permissions"=>array()),
            "path" => array("label"=>_("Dettaglio percorso"), "permissions"=>array()),
            "area" => array("label"=>_("Dettaglio area"), "permissions"=>array()),
        );

        return $list;
    }

    /**
     * @brief Percorso assoluto alle directory di upload
     * @param string $type tipo contenuto (point_img|point_video|point_attachment|service_icon|marker_icon)
     * @return path assoluto
     */
    public function getBaseTypeAbsPath($type)
    {
        if($type == 'point_img') return sprintf('%s%spoint%simg', $this->_data_dir, OS, OS);
        if($type == 'point_attachment') return sprintf('%s%spoint%sattachment', $this->_data_dir, OS, OS);
        if($type == 'point_video') return sprintf('%s%spoint%svideo', $this->_data_dir, OS, OS);
        if($type == 'service_icon') return sprintf('%s%sservice%sicon', $this->_data_dir, OS, OS);
        if($type == 'marker_icon') return sprintf('%s%smarker%sicon', $this->_data_dir, OS, OS);

        return null;
    }

    /**
     * @brief Percorso relativo alle directory di upload
     *
     * @param string $type tipo contenuto (point_img|point_video|point_attachment|service_icon|marker_icon)
     * @return path relativo
     */
    public function getBaseTypePath($type)
    {
        if($type == 'point_img') return sprintf('%s/point/img', $this->_data_www);
        if($type == 'point_attachment') return sprintf('%s/point/attachment', $this->_data_www);
        if($type == 'point_video') return sprintf('%s/point/video', $this->_data_www);
        if($type == 'service_icon') return sprintf('%s/service/icon', $this->_data_www);
        if($type == 'marker_icon') return sprintf('%s/marker/icon', $this->_data_www);

        return null;

    }

    /**
     * @brief Mappa interattiva
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function map(\Gino\Http\Request $request)
    {

        $this->_registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
        $this->_registry->addJs($this->_class_www.'/ProgressBar.js');
        $this->_registry->addJs($this->_class_www.'/markerclusterer_packed.js');
        $this->_registry->addJs($this->_class_www.'/gmaps.js');

        $this->_registry->addCss($this->_class_www.'/gmaps_'.$this->_instance_name.'.css');

        $slug = \Gino\cleanVar($request->GET, 'id', 'string');
        if($slug) {
            $map = Map::getFromSlug($slug, $this);
        }
        else {
            $map = new Map($this->_default_map_id, $this);
        }

        if(!$map->id) {
            throw new \Gino\Exception\Exception404();
        }

        $view = new View($this->_view_dir, 'map_'.$this->_instance_name);
        $dict = array(
            'map' => $map,
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Dettaglio punto di interesse
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function point(\Gino\Http\Request $request)
    {

        $this->_registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
        $this->_registry->addJs($this->_class_www.'/moogallery.js');
        $this->_registry->addCss($this->_class_www.'/gmaps_'.$this->_instance_name.'.css');
        $this->_registry->addCss($this->_class_www.'/moogallery.css');

        $slug = \Gino\cleanVar($request->GET, 'id', 'string');
        $point = Point::getFromSlug($slug, $this);

        if(!$point->id) {
            throw new \Gino\Exception\Exception404();
        }

        $view = new View($this->_view_dir, 'point_'.$this->_instance_name);
        $dict = array(
            'point' => $point
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Dettaglio percorso
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function path(\Gino\Http\Request $request)
    {
        $this->_registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
        $this->_registry->addJs($this->_class_www.'/ProgressBar.js');
        $this->_registry->addJs($this->_class_www.'/markerclusterer_packed.js');
        $this->_registry->addJs($this->_class_www.'/gmaps.js');

        $this->_registry->addCss($this->_class_www.'/gmaps_'.$this->_instance_name.'.css');

        $slug = \Gino\cleanVar($request->GET, 'id', 'string');
        $path = Path::getFromSlug($slug, $this);

        if(!$path->id) {
            throw new \Gino\Exception\Exception404();
        }

        $view = new View($this->_view_dir, 'path_'.$this->_instance_name);
        $dict = array(
            'path' => $path
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();

    }

    /**
     * @brief Dettaglio area
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function area(\Gino\Http\Request $request)
    {
        $this->_registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
        $this->_registry->addJs($this->_class_www.'/ProgressBar.js');
        $this->_registry->addJs($this->_class_www.'/markerclusterer_packed.js');
        $this->_registry->addJs($this->_class_www.'/gmaps.js');

        $this->_registry->addCss($this->_class_www.'/gmaps_'.$this->_instance_name.'.css');

        $slug = \Gino\cleanVar($request->GET, 'id', 'string');
        $area = Area::getFromSlug($slug, $this);

        if(!$area->id) {
            throw new \Gino\Exception\Exception404();
        }

        $view = new View($this->_view_dir, 'area_'.$this->_instance_name);
        $dict = array(
            'area' => $area
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia amministrazione modulo
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageDoc(\Gino\Http\Request $request)
    {
        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string');

        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_options = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=options'), _('Opzioni'));
        $link_category = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=category'), _('Categorie'));
        $link_service = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=service'), _('Servizi'));
        $link_marker = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=marker'), _('Markers'));
        $link_point = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=point'), _('Punti di interesse'));
        $link_path = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=path'), _('Percorsi'));
        $link_area = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=area'), _('Aree'));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Mappe'));
        $sel_link = $link_dft;

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'options') {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        elseif($block == 'category') {
            $backend = $this->manageCategory($request);
            $sel_link = $link_category;
        }
        elseif($block == 'service') {
            $backend = $this->manageService($request);
            $sel_link = $link_service;
        }
        elseif($block == 'marker') {
            $backend = $this->manageMarker($request);
            $sel_link = $link_marker;
        }
        elseif($block == 'point') {
            $backend = $this->managePoint($request);
            $sel_link = $link_point;
        }
        elseif($block == 'path') {
            $backend = $this->managePath($request);
            $sel_link = $link_path;
        }
        elseif($block == 'area') {
            $backend = $this->manageArea($request);
            $sel_link = $link_area;
        }
        else {
            $backend = $this->manageMap($request);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $links_array = array($link_frontend, $link_options, $link_service, $link_category, $link_marker, $link_area, $link_path, $link_point, $link_dft);

        \Gino\Loader::import('module', 'ModuleInstance');
        $module_instance = \Gino\App\Module\ModuleInstance::getFromName($this->getInstanceName());

        $view = new View(null, 'tab');
        $dict = array(
          'title' => \Gino\htmlChars($module_instance->label),
          'links' => $links_array,
          'selected_link' => $sel_link,
          'content' => $backend
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione Categorie
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function manageCategory(\Gino\Http\Request $request)
    {
        $admin_table = \Gino\Loader::load('AdminTable', array($this, array()));

        $backend = $admin_table->backoffice(
            'Category',
            array(), // display options
            array(), // form options
            array()  // fields options
        );

        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione Servizi
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function manageService(\Gino\Http\Request $request)
    {
        $admin_table = \Gino\Loader::load('AdminTable', array($this, array()));

        $backend = $admin_table->backoffice(
            'Service',
            array(), // display options
            array(), // form options
            array()  // fields options
        );

        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione Markers personalizzati
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function manageMarker(\Gino\Http\Request $request)
    {
        $admin_table = \Gino\Loader::load('AdminTable', array($this, array()));

        $backend = $admin_table->backoffice(
            'Marker',
            array(), // display options
            array(), // form options
            array()  // fields options
        );

        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione punti di interesse
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function managePoint(\Gino\Http\Request $request)
    {
        $edit = \Gino\cleanVar($request->GET, 'edit', 'int');
        $insert = \Gino\cleanVar($request->GET, 'insert', 'int');

        $admin_table = \Gino\Loader::load('AdminTable', array($this, array()));

        if(function_exists('curl_version')) {
            $form_description = _('Le thumbnail dei video sono recuperate automaticamente da youtube/vimeo se non viene caricato un file nel campo thumb.');
            $thumb_required = false;
        }
        else {
            $form_description = _('Le thumbnail dei video non possono essere recuperate automaticamente da youtube/vimeo. Per abilitare tale funzionalità è necessario aggiungere il supporto alle funzioni curl del php.');
            $thumb_required = true;
        }

        $buffer = '';
        // js map
        if($insert or $edit) {
            \Gino\Loader::import('class', '\Gino\Javascript');
            $buffer .= \Gino\Javascript::abiMapLib();
            $buffer .= "<script type=\"text/javascript\">";
            $buffer .= "function convert() {
                    var addressConverter = new AddressToPointConverter('map_coord', 'lat', 'lng', $('map_address').value, {'canvasPosition':'over'});
                    addressConverter.showMap();
                }\n";
            $buffer .= "</script>";
            $onclick = "onclick=\"Asset.javascript('http://maps.google.com/maps/api/js?sensor=true&callback=convert')\"";
            $gform = \Gino\Loader::load('Form', array('', '', ''));
            $convert_button = $gform->input('map_coord', 'button', _("converti"), array("id"=>"map_coord", "js"=>$onclick));

            $add_cell = array(
                'lat'=>array(
                    'name' => _('geolocalization'),
                    'field' => $gform->cinput('map_address', 'text', '', array(_("Indirizzo localizzazione evento"), _("es: torino, via mazzini 37<br />utilizzare 'converti' per calcolare latitudine e longitudine")), array("size"=>40, "maxlength"=>200, "id"=>"map_address", "text_add"=>"<p>".$convert_button."</p>"))
                )
            );
        }
        else {
            $add_cell= array();
        }

        $fieldsets = array(
            _('Informazioni') => array('id', 'name', 'slug', 'description', 'phone', 'email', 'web'),
            _('Associazioni') => array('categories', 'services'),
            _('Localizzazione') => array('address', 'cap', 'city', 'nation', 'geolocalization', 'lat', 'lng', 'marker'),
            _('Media/Allegati') => array('images', 'videos', 'attachments'),
        );

        $backend = $admin_table->backoffice(
            'Point',
            array(
                'list_display' => array('name', 'categories', 'city', 'email', 'web')
            ), // display options
            array(
                'f_upload' => TRUE,
                'form_description' => "<p>".$form_description."</p>",
                'addCell' => $add_cell,
                'removeFields' => array('filesize'),
                'fieldsets' => $fieldsets
            ), // form options
            array(
                'description' => array(
                    'widget' => 'editor',
                    'notes' => FALSE,
                    'img_preview' => TRUE,
                ),
                'lat' => array(
                    'id' => 'lat'
                ),
                'lng' => array(
                    'id' => 'lng'
                )
            )  // fields options
        );

        return (is_a($backend, '\Gino\Http\Response')) ? $backend : $buffer.$backend;
    }

    /**
     * @brief Interfaccia di amministrazione Percorsi
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function managePath(\Gino\Http\Request $request)
    {
        $edit = \Gino\cleanVar($request->GET, 'edit', 'int');
        $insert = \Gino\cleanVar($request->GET, 'insert', 'int');

        $admin_table = \Gino\Loader::load('AdminTable', array($this, array()));

        $form_description = '<p>'._('Utilizzare la mappa per geolocalizzare il percorso.').'</p>';
        $form_description .= '<ul>';
        $form_description .= '<li>'._('Cliccare il bottone \'polyline\'').'</li>';
        $form_description .= '<li>'._('Disegnare il percorso').'</li>';
        $form_description .= '<li>'._('Cliccare il bottone \'export map\' per compilare automaticamente i campi \'latitudine\' e \'longitudine\'').'</li>';
        $form_description .= '</ul>';


        $buffer = '';
        if($insert or $edit) {
            $this->_registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
            $this->_registry->addJs($this->_class_www.'/moomapdrawer.js');
            $this->_registry->addCss($this->_class_www.'/moomapdrawer.css');
            $this->_registry->addJs($this->_class_www.'/admin.js');

            $import_json = '';
            if($edit) {
                $id = \Gino\cleanVar($request->GET, 'id', 'int');
                $path = new Path($id, $this);
                $coords = array();
                $lats = explode(',', $path->lat);
                $lngs = explode(',', $path->lng);
                for($i = 0, $l = count($lats); $i < $l; $i++) {
                    $coords[] = array('lat' => $lats[$i], 'lng' => $lngs[$i]);
                }
                $import_json = json_encode($coords);
            }

            // carica la mappa per la creazione del percorso
            $buffer .= "<script>window.addEvent('load', function() { gino.gmaps.admin.pathMap('".$import_json."'); });</script>";
        }

        $fieldsets = array(
            _('Informazioni') => array('id', 'name', 'slug', 'description'),
            _('Associazioni') => array('categories'),
            _('Localizzazione') => array('lat', 'lng', 'points'),
            _('Stile') => array('color', 'width'),
        );

        $backend = $admin_table->backoffice(
            'Path',
            array(
                'list_display' => array('name', 'categories', 'color', 'width')
            ), // display options
            array(
                'form_description' => $form_description,
                'fieldsets' => $fieldsets
            ), // form options
            array(
                'description' => array(
                    'widget' => 'editor',
                    'notes' => FALSE,
                    'img_preview' => TRUE,
                ),
                'lat' => array(
                    'id' => 'lat'
                ),
                'lng' => array(
                    'id' => 'lng'
                )
            )  // fields options
        );

        return (is_a($backend, '\Gino\Http\Response')) ? $backend : $buffer.$backend;
    }

    /**
     * @brief Interfaccia di amministrazione Aree
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function manageArea(\Gino\Http\Request $request)
    {
        $edit = \Gino\cleanVar($request->GET, 'edit', 'int');
        $insert = \Gino\cleanVar($request->GET, 'insert', 'int');

        $admin_table = \Gino\Loader::load('AdminTable', array($this, array()));

        $form_description = '<p>'._('Utilizzare la mappa per geolocalizzare l\'area.').'</p>';
        $form_description .= '<ul>';
        $form_description .= '<li>'._('Cliccare il bottone \'polygon\'').'</li>';
        $form_description .= '<li>'._('Disegnare l\'area').'</li>';
        $form_description .= '<li>'._('Cliccare il bottone \'export map\' per compilare automaticamente i campi \'latitudine\' e \'longitudine\'').'</li>';
        $form_description .= '</ul>';


        $buffer = '';
        if($insert or $edit) {
            $this->_registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
            $this->_registry->addJs($this->_class_www.'/moomapdrawer.js');
            $this->_registry->addCss($this->_class_www.'/moomapdrawer.css');
            $this->_registry->addJs($this->_class_www.'/admin.js');

            $import_json = '';
            if($edit) {
                $id = \Gino\cleanVar($request->GET, 'id', 'int');
                $area = new Area($id, $this);
                $coords = array();
                $lats = explode(',', $area->lat);
                $lngs = explode(',', $area->lng);
                for($i = 0, $l = count($lats); $i < $l; $i++) {
                    $coords[] = array('lat' => $lats[$i], 'lng' => $lngs[$i]);
                }
                $import_json = json_encode($coords);
            }

            // carica la mappa per la creazione del percorso
            $buffer .= "<script>window.addEvent('load', function() { gino.gmaps.admin.areaMap('".$import_json."'); });</script>";
        }

        $fieldsets = array(
            _('Informazioni') => array('id', 'name', 'slug', 'description'),
            _('Associazioni') => array('categories'),
            _('Localizzazione') => array('lat', 'lng', 'points'),
            _('Stile') => array('color', 'width'),
        );

        $backend = $admin_table->backoffice(
            'Area',
            array(
                'list_display' => array('name', 'categories', 'color', 'width')
            ), // display options
            array(
                'form_description' => $form_description,
                'fieldsets' => $fieldsets
            ), // form options
            array(
                'description' => array(
                    'widget' => 'editor',
                    'notes' => FALSE,
                    'img_preview' => TRUE,
                ),
                'lat' => array(
                    'id' => 'lat'
                ),
                'lng' => array(
                    'id' => 'lng'
                )
            )  // fields options
        );

        return (is_a($backend, '\Gino\Http\Response')) ? $backend : $buffer.$backend;
    }


    /**
     * @brief Interfaccia di amministrazione Mappe
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function manageMap(\Gino\Http\Request $request)
    {
        $edit = \Gino\cleanVar($request->GET, 'edit', 'int');
        $insert = \Gino\cleanVar($request->GET, 'insert', 'int');

        $admin_table = \Gino\Loader::load('AdminTable', array($this, array()));

        $buffer = '';
        if($insert or $edit) {
            $this->_registry->addJs("http://maps.googleapis.com/maps/api/js?key=AIzaSyArAE-uBvCZTRaf_eaFn4umUdESmoUvoxM&sensor=true");
            $this->_registry->addJs($this->_class_www.'/admin.js');

            $import_json = '';
            if($edit) {
            }

            // carica la mappa per la creazione del percorso
            $buffer .= "<script>window.addEvent('load', function() { gino.gmaps.admin.mapMap('".$import_json."', '".$this->getInstanceName()."'); });</script>";
        }


        $fieldsets = array(
            _('Informazioni') => array('id', 'name', 'slug', 'description'),
            _('Dimensioni') => array('width', 'height'),
            _('Elementi') => array('points', 'paths', 'areas'),
        );

        $backend = $admin_table->backoffice(
            'Map',
            array(
                'list_display' => array('name', 'width', 'height', array('member' => 'getUrl', 'label' => _('Url')))
            ), // display options
            array(
                'fieldsets' => $fieldsets
            ), // form options
            array(
                'description' => array(
                    'widget' => 'editor',
                    'notes' => FALSE,
                    'img_preview' => TRUE,
                ),
            )  // fields options
        );

        return (is_a($backend, '\Gino\Http\Response')) ? $backend : $buffer.$backend;
    }

    /**
     * @brief Json con le coordinate di una forma (path, area) e dei punti associati
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.ResponseJson
     */
    public function shapeJson(\Gino\Http\Request $request)
    {
        $id = \Gino\cleanVar($request->POST, 'id', 'int');
        $shape = \Gino\cleanVar($request->POST, 'shape', 'string');

        if($shape == 'path') {
            $obj = new Path($id, $this);
        }
        else if($shape == 'area') {
            $obj = new Area($id, $this);
        }
        else {
            throw new \Gino\Exception\Exception404();
        }

        $dict = array(
            'shape' => array(),
            'points' => array()
        );
        $lats = explode(',', $obj->lat);
        $lngs = explode(',', $obj->lng);
        for($i = 0, $l = count($lats); $i < $l; $i++) {
            $dict['shape'][] = array('lat' => $lats[$i], 'lng' => $lngs[$i]);
        }
        foreach($obj->points as $point_id) {
            $point = new Point($point_id, $this);
            $dict['points'][] = array('id' => $point->id, 'lat' => $point->lat, 'lng' => $point->lng);
        }

        $response = \Gino\Loader::load('http/ResponseJson', array($dict), '\Gino\Http');
        return $response;
    }

}

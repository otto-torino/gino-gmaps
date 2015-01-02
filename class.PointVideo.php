<?php
/**
 * @file class.PointVideo.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gmaps.PointVideo
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */

namespace Gino\App\Gmaps;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un video associato ad un punto di interesse
 *
 * @version 0.1.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class PointVideo extends \Gino\Model
{
    public static $table = 'gmaps_point_video';

    private static $_platforms = array('1' => 'youtube', 2 => 'vimeo'),
                   $_extension_img = array('jpg', 'jpeg', 'png');

    /**
     * @brief Costruttore
     *
     * @param int $id id del record
     * @param \Gino\App\Gmaps\gmaps $instance istanza di Gino.App.Gmaps.gmaps
     * @return istanza di Gino.App.Gmaps.PointVideo
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('Nome'),
            'description' => _('Descrizione'),
            'platform' => _('Piattaforma'),
            'code' => _('Codice'),
            'width'=>_("Larghezza (px)"),
            'height'=>_("Lunghezza (px)"),
            'thumb' => _('Thumbnail'),
        );

        parent::__construct($id);

        $this->_model_label = _('Video');
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
     * @brief Nome piattaforma
     * @return piattaforma (youtube|vimeo)
     */
    public function platform()
    {
        return self::$_platforms[$this->platform];
    }

    /**
     * @brief Url thumb
     * @return url
     */
    public function getThumbUrl()
    {
        return $this->_controller->getBaseTypePath('point_video').'/'.$this->thumb;
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

        $enum = self::$_platforms;
        $structure['platform'] = new \Gino\EnumField(array(
            'name' => 'platform',
            'required' > TRUE,
            'model' => $this,
            'enum' => $enum
        ));

        $base_path = $this->_controller->getBaseTypeAbsPath('point_video');
        $required = !!function_exists('curl_version');
        $structure['thumb'] = new \Gino\ImageField(array(
            'name' => 'thumb',
            'required' > $required,
            'model' => $this,
            'extensions' => self::$_extension_img,
            'resize' => FALSE,
            'path' => $base_path,
        ));

        $structure['point_id']->setWidget('hidden');
        $structure['category_id']->setWidget('hidden');


        return $structure;
    }

    /**
     * @brief Salva il modello su db
     * @see Gino.Model::save()
     * @description Il metodo estende quello della classe @ref Gino.Model per eseguire il download della thumb direttamente da youtube o vimeo
     * @return TRUE
     */
    public function save() {

        parent::save();

        if(!$this->thumb and function_exists('curl_version')) {

            $path_to_file = $this->_controller->getBaseTypeAbsPath('point_video').OS.'thumb_video_'.$this->id.'.jpg';

            if($this->platform == 1) {
                $url = "http://img.youtube.com/vi/".$this->code."/hqdefault.jpg";
                $this->grabImage($url, $path_to_file);
                $this->thumb = 'thumb_video_'.$this->id.'.jpg';
                parent::save();
            }
            else {
                $info_url = "http://vimeo.com/api/v2/video/".$this->code.".php";
                $ch = curl_init ($info_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                $raw = curl_exec($ch);
                curl_close ($ch);
                $hash = unserialize($raw);
                $url = $hash[0]['thumbnail_large'];
                $this->grabImage($url, $path_to_file);
                $this->thumb = 'thumb_video_'.$this->id.'.jpg';
                parent::save();
            }
        }

        return TRUE;
    }

    /**
     * @brief Salva una immagine da url esterno
     * @param string $url url immagine
     * @param string $saveto percorso di destinazione
     * @return void
     */
    private function grabImage($url, $saveto){
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $raw=curl_exec($ch);
        curl_close ($ch);
        if(file_exists($saveto)){
            unlink($saveto);
        }
        $fp = fopen($saveto,'x');
        fwrite($fp, $raw);
        fclose($fp);
    }


}

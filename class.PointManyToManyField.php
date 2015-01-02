<?php
/**
 * @file class.PointManyToManyField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gmaps.PointManyToManyField
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */

namespace Gino\App\Gmaps;

/**
 * @brief Classe di tipo Gino.ManyToManyField per associazione di punti di interesse
 * @description Permette di aggiungere data-attributes con le informazioni di latitudine
 *              e longitudine dei punti utili per creare un'anteprima in mappa in js
 *
 * @version 0.1.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 */
class PointManyToManyField extends \Gino\ManyToManyField {

    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        $db = \Gino\db::instance();
        if($this->_m2m_controller) {
            $m2m = new $this->_m2m(null, $this->_m2m_controller);
        }
        else {
            $m2m = new $this->_m2m(null);
        }
        $rows = $db->select('id', $m2m->getTable(), $this->_m2m_where, array('order' => $this->_m2m_order));
        $enum = array();
        $selected_part = array();
        $not_selected_part = array();
        $this->_value = $this->_model->{$this->_name};
        foreach($rows as $row) {
            if($this->_m2m_controller) {
                $m2m = new $this->_m2m($row['id'], $this->_m2m_controller);
            }
            else {
                $m2m = new $this->_m2m($row['id']);
            }
            //$enum[$row['id']] = (string) $m2m;
            if(is_array($this->_value) and in_array($row['id'], $this->_value)) {
                $selected_part[$row['id']] = array('label' => (string) $m2m, 'lat' => $m2m->lat, 'lng' => $m2m->lng);
            }
            else {
                $not_selected_part[$row['id']] = array('label' => (string) $m2m, 'lat' => $m2m->lat, 'lng' => $m2m->lng);
            }
        }

        $enum = $selected_part + $not_selected_part;

        $this->_enum = $enum;
        $this->_name .= "[]";


        $buffer = "<div class=\"form-row\">";
        $buffer .= "<label for=\"points[]\">".$this->_label."</label>";
        $buffer .= "<div class=\"form-multicheck\">\n";
        $buffer .= "<table class=\"table table-hover table-striped table-bordered\">\n";

        $i = 0;
        $data = $enum;
        if(sizeof($data)>0) {
            $buffer .= "<thead>";
            if(sizeof($data) > 10) {
                    $buffer .= "<tr>";
                    $buffer .= "<th class=\"light\">"._("Filtra")."</th>";
                    $buffer .= "<th class=\"light\"><input type=\"text\" class=\"no-check no-focus-padding\" size=\"6\" onkeyup=\"gino.filterMulticheck($(this), $(this).getParents('.form-multicheck')[0])\" /></th>";
                    $buffer .= "</tr>";
            }
            $buffer .= "<tr>";
            $buffer .= "<th class=\"light\">"._("Seleziona tutti/nessuno")."</th>";
            $buffer .= "<th style=\"text-align: right\" class=\"light\"><input type=\"checkbox\" onclick=\"gino.checkAll($(this), $(this).getParents('.form-multicheck')[0]);\" /></th>";
            $buffer .= "</tr>";
            $buffer .= "</thead>";
            foreach($data as $k=>$arr)
            {
                $v = $arr['label'];
                $check = in_array($k, $this->_value)? "checked=\"checked\"": "";
                $value_name = $v;
                $value_name = \Gino\htmlChars($value_name);

                $buffer .= "<tr>\n";

                $checkbox = "<input type=\"checkbox\" data-lat=\"".$arr['lat']."\" data-lng=\"".$arr['lng']."\" name=\"$this->_name\" value=\"$k\" $check";
                $checkbox .= " />";

                $buffer .= "<td>$value_name</td>";
                $buffer .= "<td style=\"text-align:right\">$checkbox</td>";

                $buffer .= "</tr>\n";

                $i++;
            }
            $buffer .= "</table>\n";
        }
        else $buffer .= "<tr><td>"._("non risultano scelte disponibili")."</td></tr>";

        if($this->_add_related) {
            $options['add_related'] = array(
                'title' => _('inserisci').' '.$m2m->getModelLabel(),
                'id' => 'add_'.$this->_name,
                'url' => $this->_add_related_url
            );
        }

        $buffer .= "</table>\n";
        $buffer .= "</div>\n";

        if(isset($options['add_related'])) {
            $title = $options['add_related']['title'];
            $id = $options['add_related']['id'];
            $url = $options['add_related']['url'];
            $buffer .= " <a target=\"_blank\" href=\"".$url."\" onclick=\"return gino.showAddAnotherPopup($(this))\" id=\"".$id."\" class=\"fa fa-plus-circle form-addrelated\" title=\"".\Gino\attributeVar($title)."\"></a>";
        }
        $buffer .= "</div>\n";

        return $buffer;
    }

}

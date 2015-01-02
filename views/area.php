<?php
/**
* @file area.php
* @brief Template per la vista dettaglio area
*
* Variabili disponibili:
* - area: \Gino\App\Gmaps\Area. istanza di Gino.App.Gmaps.Area
*
* @version 0.1.0
* @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Gmaps; ?>
<? //@cond no-doxygen ?>
<section>
    <h1><?= \Gino\htmlChars($area->ml('name')); ?></h1>
    <?= \Gino\htmlChars($area->ml('description')) ?>
    <div id="map-canvas" style="width: 100%;height:250px;"></div>
    <? $points = array(); ?>
    <script>
        function initialize() {

            var points = [];
            var areas = [];

            var map = new gmaps.Map({
                filter_label: 'filtra',
                show_progress_bar: true,
                filter: false,
                filter_thematisms: true,
                filter_thematisms_label: '<?= _('Categoria') ?>',
            });

            areas.push(new gmaps.Area({
                lat: '<?= $area->lat ?>',
                lng: '<?= $area->lng ?>',
            }, {
                fill_opacity: '0.6',
                fill_color: '<?= $area->color ?>',
            }, {
                name: '<?= \Gino\jsVar($area->ml('name')) ?>',
                read_all_url: '<?= $area->getUrl() ?>',
                thematisms: <?= $area->jsCtgArray() ?>
            }));
            <? foreach($area->points as $point_id): ?>
                <? $point = new Point($point_id, $area->getController()); ?>
                <? $points[] = $point_id; ?>
                points.push(new gmaps.Point({
                    lat: '<?= $point->lat ?>',
                    lng: '<?= $point->lng ?>',
                }, {
                    icon: '<?= $point->iconUrl() ?>'
                }, {
                    name: '<?= \Gino\jsVar($point->ml('name')) ?>',
                    read_all_url: '<?= $point->getUrl() ?>',
                    thematisms: Array.combine(<?= $point->jsCtgArray() ?>, <?= $area->jsCtgArray() ?>)
                }));
            <? endforeach ?>

            map.addGroup('areas', '<?= _('Aree') ?>', areas);
            map.addGroup('points', '<?= _('Punti') ?>', points);

            map.render('map-canvas');
        }
        google.maps.event.addDomListener(window, 'load', initialize);
    </script>

</section>
<? // @endcond ?>

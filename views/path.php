<?php
/**
* @file path.php
* @brief Template per la vista dettaglio percorso
*
* Variabili disponibili:
* - path: \Gino\App\Gmaps\Path. istanza di Gino.App.Gmaps.Path
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
    <h1><?= \Gino\htmlChars($path->ml('name')); ?></h1>
    <?= \Gino\htmlChars($path->ml('description')) ?>
    <div id="map-canvas" style="width: 100%;height:250px;"></div>
    <? $points = array(); ?>
    <script>
        function initialize() {

            var points = [];
            var paths = [];

            var map = new gmaps.Map({
                filter_label: 'filtra',
                show_progress_bar: true,
                filter: false,
                filter_thematisms: true,
                filter_thematisms_label: '<?= _('Categoria') ?>',
            });

            paths.push(new gmaps.Path({
                lat: '<?= $path->lat ?>',
                lng: '<?= $path->lng ?>',
            }, {
                stroke_color: '<?= $path->color ?>',
                stroke_width: '<?= $path->width ?>',
            }, {
                name: '<?= \Gino\jsVar($path->ml('name')) ?>',
                read_all_url: '<?= $path->getUrl() ?>',
                thematisms: <?= $path->jsCtgArray() ?>
            }));
            <? foreach($path->points as $point_id): ?>
                <? $point = new Point($point_id, $path->getController()); ?>
                <? $points[] = $point_id; ?>
                points.push(new gmaps.Point({
                    lat: '<?= $point->lat ?>',
                    lng: '<?= $point->lng ?>',
                }, {
                    icon: '<?= $point->iconUrl() ?>'
                }, {
                    name: '<?= \Gino\jsVar($point->ml('name')) ?>',
                    read_all_url: '<?= $point->getUrl() ?>',
                    thematisms: Array.combine(<?= $point->jsCtgArray() ?>, <?= $path->jsCtgArray() ?>)
                }));
            <? endforeach ?>

            map.addGroup('paths', '<?= _('Percorsi') ?>', paths);
            map.addGroup('points', '<?= _('Punti') ?>', points);

            map.render('map-canvas');
        }
        google.maps.event.addDomListener(window, 'load', initialize);
    </script>

</section>
<? // @endcond ?>

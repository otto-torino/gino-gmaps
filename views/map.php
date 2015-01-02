<?php
/**
* @file map.php
* @brief Template per la vista mappa
*
* Variabili disponibili:
* - map: \Gino\App\Gmaps\Map istanza di Gino.App.Gmaps.Map
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
    <h1><?= \Gino\htmlChars($map->ml('name')) ?></h1>
    <?= \Gino\htmlChars($map->ml('description')) ?>
    <div id="map-canvas" style="width: <?= $map->width ?>;height:<?= $map->height ?>"></div>
    <? $points = array(); ?>
    <script>
        function initialize() {

            var points = [];
            var paths = [];
            var areas = [];
            var points_mid = {};

            var map = new gmaps.Map({
                filter_label: 'filtra',
                show_progress_bar: true,
                filter: false,
                filter_thematisms: true,
                filter_thematisms_label: '<?= _('Categoria') ?>',
            });

            <? foreach($map->areas as $area_id): ?>
                <? $area = new Area($area_id, $map->getController()); ?>
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
                    <? $point = new Point($point_id, $map->getController()); ?>
                    <? $points[] = $point_id; ?>
                    points_mid[<?= $point_id ?>] = new gmaps.Point({
                        lat: '<?= $point->lat ?>',
                        lng: '<?= $point->lng ?>',
                    }, {
                        icon: '<?= $point->iconUrl() ?>'
                    }, {
                        name: '<?= \Gino\jsVar($point->ml('name')) ?>',
                        read_all_url: '<?= $point->getUrl() ?>',
                        thematisms: Array.combine(<?= $point->jsCtgArray() ?>, <?= $area->jsCtgArray() ?>)
                    });
                <? endforeach ?>
            <? endforeach ?>

            <? foreach($map->paths as $path_id): ?>
                <? $path = new Path($path_id, $map->getController()); ?>
                paths.push(new gmaps.Path({
                    lat: '<?= $path->lat ?>',
                    lng: '<?= $path->lng ?>',
                }, {
                    fill_opacity: '0.6',
                    stroke_color: '<?= $path->color ?>',
                    stroke_width: '<?= $path->width ?>'
                }, {
                    name: '<?= \Gino\jsVar($path->ml('name')) ?>',
                    read_all_url: '<?= $path->getUrl() ?>',
                    thematisms: <?= $path->jsCtgArray() ?>
                }));
                <? foreach($path->points as $point_id): ?>
                    <? $point = new Point($point_id, $map->getController()); ?>
                    <? if(in_array($point_id, $points)): ?>
                        points_mid[<?= $point_id ?>].fields.thematisms = Array.combine(points_mid[<?= $point_id ?>].fields.thematisms, <?= $path->jsCtgArray() ?>);
                    <? else: ?>
                        <? $points[] = $point_id; ?>
                        points_mid[<?= $point_id ?>] = new gmaps.Point({
                            lat: '<?= $point->lat ?>',
                            lng: '<?= $point->lng ?>',
                        }, {
                            icon: '<?= $point->iconUrl() ?>'
                        }, {
                            name: '<?= \Gino\jsVar($point->ml('name')) ?>',
                            read_all_url: '<?= $point->getUrl() ?>',
                            thematisms: Array.combine(<?= $point->jsCtgArray() ?>, <?= $path->jsCtgArray() ?>)
                        });
                    <? endif ?>
                <? endforeach ?>
            <? endforeach ?>

            <? foreach($map->points as $point_id): ?>
                <? $point = new Point($point_id, $map->getController()); ?>
                <? if(!in_array($point_id, $points)): ?>
                    <? $points[] = $point_id; ?>
                    points_mid[<?= $point_id ?>] = new gmaps.Point({
                        lat: '<?= $point->lat ?>',
                        lng: '<?= $point->lng ?>',
                    }, {
                        icon: '<?= $point->iconUrl() ?>'
                    }, {
                        name: '<?= \Gino\jsVar($point->ml('name')) ?>',
                        read_all_url: '<?= $point->getUrl() ?>',
                        thematisms: <?= $point->jsCtgArray() ?>
                    });
                <? endif ?>
            <? endforeach ?>

            for(prop in points_mid) {
                if(points_mid.hasOwnProperty(prop)) {
                    points.push(points_mid[prop]);
                }
            }

            map.addGroup('areas', '<?= _('Aree') ?>', areas);
            map.addGroup('paths', '<?= _('Percorsi') ?>', paths);
            map.addGroup('points', '<?= _('Punti') ?>', points);

            map.render('map-canvas');
        }
        google.maps.event.addDomListener(window, 'load', initialize);
    </script>

</section>
<? // @endcond ?>

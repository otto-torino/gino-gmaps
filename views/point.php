<?php
/**
* @file point.php
* @brief Template per la vista dettaglio punto di interesse
*
* Variabili disponibili:
* - point: \Gino\App\Gmaps\Point istanza di Gino.App.Gmaps.Point
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
    <div class="row">
        <div class="col-md-3 col-xs-12">
            <div id="map_canvas" style="width: 100%;height: 250px;margin-top: 30px; margin-bottom: 20px;"></div>
            <? if($point->categories): ?>
                <p><span class="fa fa-tag"></span> <?= implode(', ', $point->ctgArray()) ?></p>
            <? endif ?>
            <? if($point->phone): ?>
                <p><span class="fa fa-phone"></span> <?= $point->phone ?></p>
            <? endif ?>
            <? if($point->email): ?>
                <p><span class="fa fa-envelope"></span> <a href="mailto:<?= $point->email ?>"><?= $point->email ?></a></p>
            <? endif ?>
            <? if($point->web): ?>
                <p><span class="fa fa-globe"></span> <a href="<?= $point->web ?>"><?= $point->web ?></a></p>
            <? endif ?>
        </div>
        <div class="col-md-9 col xs 12">
            <h1><?= \Gino\htmlChars($point->ml('name')) ?></h1>
            <? if($point->address()): ?>
                <p><?= $point->address() ?></p>
            <? endif ?>
            <?= \Gino\htmlChars($point->ml('description')) ?>
            <? if($point->services): ?>
                <h2><?= _('Servizi') ?></h2>
                <? foreach($point->services as $service_id): ?>
                    <? $service = new Service($service_id, $point->getController()); ?>
                    <img class="icon icon-tooltip" src="<?= $service->iconUrl() ?>" alt="<?= \Gino\jsVar($service->name) ?>" title="<?= \Gino\jsVar($service->name) ?>"/>
                <? endforeach ?>
            <? endif ?>
            <? if(count($point->images) or count($point->videos)): ?>
                <h2><?= _('Media') ?></h2>
                <div id="moogallery"></div>
                <script>
                var mg_instance = new moogallery('moogallery', [
                    <? foreach($point->images as $image_id): ?>
                        <? $pimage = new PointImage($image_id, $point->getController()); ?>
                        <? $image = new \Gino\GImage(\Gino\absolutePath($pimage->getUrl())); $thumb = $image->thumb(100, 100); ?>
                        {
                            thumb: '<?= $thumb->getPath() ?>',
                            img: '<?= $pimage->getUrl() ?>',
                            title: '<?= \Gino\jsVar($pimage->ml('name')) ?>',
                            description: '<?= \Gino\jsVar($pimage->ml('description')) ?>',
                        },
                    <? endforeach ?>
                    <? foreach($point->videos as $video_id): ?>
                        <? $pvideo = new PointVideo($video_id, $point->getController()); ?>
                        <? $image = new \Gino\GImage(\Gino\absolutePath($pvideo->getThumbUrl())); $thumb = $image->thumb(100, 100); ?>
                        {
                            thumb: '<?= $thumb->getPath() ?>',
                            <?= $pvideo->platform() ?>: '<?= $pvideo->code ?>',
                            title: '<?= \Gino\jsVar($pvideo->ml('name')) ?>',
                            description: '<?= \Gino\jsVar($pvideo->ml('description')) ?>',
                            video_width: '<?= $pvideo->width ?>',
                            video_height: '<?= $pvideo->height ?>',
                        },
                    <? endforeach ?>
                ]);
                </script>
            <? endif ?>
            <? if(count($point->attachments)): ?>
                <h2><?= _('Allegati') ?></h2>
                <ul class="attachments">
                    <? foreach($point->attachments as $attachment_id): ?>
                    <? $pattachment = new PointAttachment($attachment_id, $point->getController()); ?>
                    <li class="<?= \Gino\extensionFile($pattachment->file) ?>">
                        <a target="_blank" href="<?= $pattachment->getUrl() ?>"><?= $pattachment->ml('name') ?></a> (<?= ($pattachment->filesize / 1000).' Kb' ?>)
                        <? if($pattachment->description): ?>
                        <br /><p><?= $pattachment->ml('description') ?></p>
                        <? endif ?>
                    </li>
                    <? endforeach ?>
                </ul>
            <? endif ?>
        </div>
    </div>
    <script type="text/javascript">
        var mapOptions = {
            center: new google.maps.LatLng(<?= $point->lat ?>, <?= $point->lng ?>),
            zoom: 12,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map($('map_canvas'), mapOptions);
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(<?= $point->lat ?>, <?= $point->lng ?>),
            map: map
        });
    </script>
</section>
<? // @endcond ?>

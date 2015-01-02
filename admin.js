"use strict";

var gino = gino || {};
gino.gmaps = gino.gmaps || {};
gino.gmaps.admin = {};

gino.gmaps.admin.MapController = function(shape) {
    this.shape = shape;
    this.markers = {};
    this.addElements = function() {
        var map_el = new Element('div#map-' + shape).setStyles({
            width: '100%',
            height: '300px'
        });
        var info_el = new Element('p#info_tool');
        var form_row = new Element('div.form-row')
            .inject($('lat').getParent('.form-row'), 'before')
            .adopt(info_el, map_el);
    };
    this.exportData = function(data) {
        var lats = [];
        var lngs = [];
        data[this.shape][0].each(function(coord) {
            lats.push(coord.lat);
            lngs.push(coord.lng);
        });

        $('lat').value = lats.join(',');
        $('lng').value = lngs.join(',');
    };
    this.createMap = function() {
        var tools = {};
        tools[this.shape] = {
            options: {
                max_items_allowed: 1
            }
        };
        this.mymap = new moomapdrawer.map('map-' + this.shape, {
            tools: tools,
            tips_map_ctrl: 'info_tool',
            export_map_callback: this.exportData.bind(this)
        });
        this.mymap.render();
    };
    this.importData = function(json) {
        if(json != '') {
            var import_data = {};
            import_data[this.shape] = [JSON.decode(json)];
            this.mymap.importMap(import_data);
        }
    };
    this.updateMapPoints = function() {
        var self = this;
        $$('input[type=checkbox][name^=points]').each(function(checkbox) {
            if(checkbox.checked && typeof self.markers[checkbox.value] == 'undefined') {
                self.markers[checkbox.value] = new google.maps.Marker({
                    position: new google.maps.LatLng(checkbox.get('data-lat'), checkbox.get('data-lng')),
                    map: self.mymap.gmap()
                });
            }
            else if(!checkbox.checked && typeof self.markers[checkbox.value] != 'undefined') {
                self.markers[checkbox.value].setMap(null);
                delete self.markers[checkbox.value];
            }
        });
    };
    this.init = function(json) {
        this.addElements();
        this.createMap();
        this.importData(json);
        this.updateMapPoints();
        $$('.form-multicheck input[type=checkbox]').addEvent('click', this.updateMapPoints.bind(this));
    };
}

gino.gmaps.admin.pathMap = function(import_json) {
    var map_controller = new gino.gmaps.admin.MapController('polyline');
    map_controller.init(import_json);
}

gino.gmaps.admin.areaMap = function(import_json) {
    var map_controller = new gino.gmaps.admin.MapController('polygon');
    map_controller.init(import_json);
}

/**
 * Mappa area amministrativa, sezione mappe
 */
gino.gmaps.admin.mapMap = function(import_json, instance_name) {
    var areas = {};
    var paths = {};
    var points = {};

    var map_canvas = new Element('div#map').setStyles({
        width: '100%',
        height: '300px'
    });
    var preview_button = new Element('input[type=button]', {
        value: 'anteprima',
        style: 'margin-top: 10px; display: block;'
    }).addEvent('click', function() { updateMap();  });
    var form_row = new Element('div.form-row')
        .inject($$('label[for^=points]')[0].getParent('.form-row'), 'before')
        .adopt(map_canvas, preview_button);

    var map = new google.maps.Map(map_canvas, {
        center: new google.maps.LatLng('45', '7'),
        zoom: 9
    });

    var updateMap = function() {
        var loader = new gino.Loader();
        loader.show();

        var latlngbound = new google.maps.LatLngBounds();

        var fitBounds = function() {
            map.fitBounds(latlngbound);
        };

        // areas
        var showArea = function(id) {
            areas[id].shape.setMap(map);
            areas[id].shape.getPath().forEach(function(element, index) {
                latlngbound.extend(element);
            })
            areas[id].points.each(function(id) {
                points[id].setMap(map);
                points[id].counter++;
            })
        }
        var hideArea = function(id) {
            if(areas[id]) {
                areas[id].shape.setMap(null);
                areas[id].points.each(function(id) {
                    points[id].counter--;
                    if(points[id].counter == 0) points[id].setMap(null);
                })
            }
        }

        $$('input[name^=areas]').each(function(area) {
            if(area.checked && !areas[area.value]) {
                gino.jsonRequest('post', instance_name + '/shapeJson/', 'shape=area&id=' + area.value, function(response) { 
                    var spoints = [];
                    for(var i = 0, l = response.shape.length; i < l; i++) {
                        spoints.push(new google.maps.LatLng(response.shape[i].lat, response.shape[i].lng));
                    }
                    areas[area.value] = {};
                    areas[area.value].points = [];
                    areas[area.value].shape = new google.maps.Polygon({
                        paths: new google.maps.MVCArray(spoints)
                    });
                    for(var i = 0, l = response.points.length; i < l; i++) {
                        areas[area.value].points.push(response.points[i].id);
                        if(!points[response.points[i].id]) {
                            points[response.points[i].id] = new google.maps.Marker({
                                position: new google.maps.LatLng(response.points[i].lat, response.points[i].lng)
                            });
                            points[response.points[i].id].counter = 0;
                        }
                    }
                    showArea(area.value);
                    fitBounds();
                });
            }
            else if(area.checked) {
                showArea(area.value);
            }
            else {
                hideArea(area.value);
            }
        });
        // paths
        var showPath = function(id) {
            paths[id].shape.setMap(map);
            paths[id].shape.getPath().forEach(function(element, index) {
                latlngbound.extend(element);
            })
            paths[id].points.each(function(id) {
                points[id].setMap(map);
                points[id].counter++;
            })
        }
        var hidePath = function(id) {
            if(paths[id]) {
                paths[id].shape.setMap(null);
                paths[id].points.each(function(id) {
                    points[id].counter--;
                    if(points[id].counter == 0) points[id].setMap(null);
                })
            }
        }

        $$('input[name^=paths]').each(function(path) {
            if(path.checked && !paths[path.value]) {
                gino.jsonRequest('post', instance_name + '/shapeJson/', 'shape=path&id=' + path.value, function(response) { 
                    var spoints = [];
                    for(var i = 0, l = response.shape.length; i < l; i++) {
                        spoints.push(new google.maps.LatLng(response.shape[i].lat, response.shape[i].lng));
                    }
                    paths[path.value] = {};
                    paths[path.value].points = [];
                    paths[path.value].shape = new google.maps.Polyline({
                        path: new google.maps.MVCArray(spoints)
                    });
                    for(var i = 0, l = response.points.length; i < l; i++) {
                        paths[path.value].points.push(response.points[i].id);
                        if(!points[response.points[i].id]) {
                            points[response.points[i].id] = new google.maps.Marker({
                                position: new google.maps.LatLng(response.points[i].lat, response.points[i].lng)
                            });
                            points[response.points[i].id].counter = 0;
                        }
                    }
                    showPath(path.value);
                    fitBounds();
                });
            }
            else if(path.checked) {
                showPath(path.value);
            }
            else {
                hidePath(path.value);
            }
        });
        // points
        var showPoint = function(id) {
            points[id].setMap(map);
            points[id].counter++;
            latlngbound.extend(points[id].getPosition());
        }
        var hidePoint = function(id) {
            if(points[id]) {
                points[id].counter--;
                if(points[id].counter == 0) points[id].setMap(null);
            }
        }

        $$('input[name^=points]').each(function(point) {
            if(point.checked && !points[point.value]) {
                points[point.value] = new google.maps.Marker({
                    position: new google.maps.LatLng(point.get('data-lat'), point.get('data-lng'))
                });
                points[point.value].counter = 0;
                showPoint(point.value);
                fitBounds();
            }
            else if(point.checked) {
                showPoint(point.value);
            }
            else {
                hidePoint(point.value);
            }
        });

        fitBounds();
        loader.remove();
    }

    updateMap();
}

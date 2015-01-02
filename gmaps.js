var gmaps = {};
gmaps.meta = {
    version: '0.1'
}


/*
 * Requires ProgressBar.js, markerclusterer_packed.js
 */

//use new maps rendering
google.maps.visualRefresh = true

// global object exporting oproperties and methods used by all other classes
gmaps.Global = {
    d_infowindow: null,
    /**
     * @summary Sets the current displayed infowindow
     * @memberof gmaps.Global
     * @param {maps.google.InfoWindow} [infowindow] the displayed infowindow
     */
    setDisplayedInfowindow: function(infowindow) {
        this.d_infowindow = infowindow;
    },
    /**
     * @summary Closes the current displayed infowindow
     * @memberof gmaps.Global
     */
    closeDisplayedInfowindow: function() {
        if(this.d_infowindow) {
            this.d_infowindow.close();
            this.d_infowindow = null;
        }
    }
}

//primitive class for map's items
gmaps.Item = new Class({
    Implements: [Options, Events],
    options: {
    },
    /**
     * @summary Primitive map item class
     * @classdesc This is a primitive class extended by all map item type classes. Provides methods usefull for every item type.
     *                        Do not instantiate directly this class
     * @constructs gmaps.Item
     * @param {Object} [coords=undefined] The coordinates object
     * @param {String} [coords.type=undefined] The shape type: point | area | polyline
     * @param {Number|String} [coords.lat=undefined] The point latitude or the array of all polygon's vertex latitudes
     * @param {Number|String} [coords.lng=undefined] The point longitude or the array of all polygon's vertex longitudes
     * @param {Object} [style=undefined] Provides style information for the shape
     * @param {String} [style.icon=undefined] The url of the icon to use as the point marker
     * @param {String} [style.fill_color=undefined] The hex color to use to fill the polygon
     * @param {Number} [style.fill_opacity=undefined] The decimal value for the polygon opacity
     * @param {String} [style.stroke_color=undefined] The hex color to use to stroke the polygon / polyline
     * @param {Number} [style.stroke_width=5] Stroke width polyline
     * @param {Object} [options] Generic options
     *
     */
    initialize: function(coords, style, fields, options) {
        this.setOptions(options);
        // fields used for infowindow content
        this.fields = fields;
        // prepare infowindow
        this.setInfoWindow();
        //set the shape (marker, polygon, polyline)
        this.setShape(coords, style);
    },
    /**
     * @summary Returns the item label
     * @memberof gmaps.Item.prototype
     * @return {String}
     */
    label: function() {
        return this.fields.name;
    },
    /**
     * @summary Returns the coordinates to put the shape in the center of the map
     */
    center: function() {
        if(this.shape_type == 'point') {
            return this.shape.getPosition();
        }
        else if(this.shape_type == 'area' || this.shape_type == 'polyline') {
            return this.shape.getPath().getAt(0);
        }
    },
    /**
     * @summary Method used to set the infowindow and its content. Such method should be overridden by all subclasses.
     * @abstract
     * @memberof gmaps.Item.prototype
     */
    setInfoWindow: function() {
        // override
    },
    /**
     * @summary Creates the item shape (marker, polygon or polyline). Attaches the click event on a point to open its infowindow. Polygon's infowindows are
     *                    managed through a click on map event handler
     * @memberof gmaps.Item.prototype
     */
    setShape: function(coords, style) {
        if(coords.type == 'point') {
            this.shape_type = 'point';
            this.shape = new google.maps.Marker({
                position: new google.maps.LatLng(coords.lat, coords.lng)
            })
            // custom marker icon ?
            if(style) {
                this.shape.setIcon(style.icon);
            }
            //infowindow
            var self = this;
            google.maps.event.addListener(this.shape, 'click', function(evt) {
                self.openInfoWindow();
            })
        }
        else if(coords.type == 'area') {
            this.shape_type = 'area';
            var lats = coords.lat.split(',');
            var lngs = coords.lng.split(',');

            var apath = [];

            for(var i = 0; i < lats.length; i++) {
                var latlng = new google.maps.LatLng(lats[i], lngs[i]);
                apath.push(latlng);
            }

            var path = new google.maps.MVCArray(apath);

            this.shape = new google.maps.Polygon({
                paths: path,
                clickable: false,
            });
            //now a bit of custom style?
            if(style) {
                this.shape.setOptions({
                    fillColor: style.fill_color,
                    fillOpacity: style.fill_opacity.replace(',', '.'),
                    strokeColor: style.stroke_color
                })
            }
        }
        else if(coords.type == 'polyline') {
            this.shape_type = 'polyline';
            var lats = coords.lat.split(',');
            var lngs = coords.lng.split(',');

            var apath = [];

            for(var i = 0; i < lats.length; i++) {
                var latlng = new google.maps.LatLng(lats[i], lngs[i]);
                apath.push(latlng);
            }

            var path = new google.maps.MVCArray(apath);

            this.shape = new google.maps.Polyline({
                path: path,
                clickable: true,
            });
            //now a bit of custom style?
            if(style) {
                this.shape.setOptions({
                    strokeColor: style.stroke_color,
                    strokeWeight: style.stroke_width
                });
            }
            else {
                this.shape.setOptions({
                    strokeWeight: 5
                });
            }
            //infowindow
            var self = this;
            google.maps.event.addListener(this.shape, 'click', function(evt) {
                self.openInfoWindow();
            })
        }
        else {
            throw new Error('item type {item_type} not supported'.substitute({item_type: coords.type}));
        }
    },
    /**
     * @summary Checks if the click over the map was inside the polygon
     * @memberof gmaps.Item.prototype
     * @param {google.maps.LatLng} [latlng] the LatLng clicked point
     * @return {Boolean}
     */
    clicked: function(latlng) {
        if(this.shape_type == 'area') {
            return this.shape.containsLatLng(latlng);
        }
    },
    /**
     * @summary Opens the polygon infowindow in the clicked point
     * @memberof gmaps.Item.prototype
     * @param {google.maps.Event} [evt] the event object
     */
    openInfoWindow: function(evt) {
        gmaps.Global.closeDisplayedInfowindow();
        if(this.shape_type == 'point') {
            this.infowindow.open(this.shape.getMap(), this.shape);
        }
        else if(this.shape_type == 'area') {
            evt ? this.infowindow.setPosition(evt.latLng) : this.infowindow.setPosition(this.shape.getPath().getAt(0));
            this.infowindow.open(this.shape.getMap());
        }
        else if(this.shape_type == 'polyline') {
            evt ? this.infowindow.setPosition(evt.latLng) : this.infowindow.setPosition(this.shape.getPath().getAt(0));
            this.infowindow.open(this.shape.getMap());
        }
        gmaps.Global.setDisplayedInfowindow(this.infowindow);
    },
    /**
     * @summary Extends the map bounds to fit the shape point/s
     * @memberof gmaps.Item.prototype
     * @param {google.maps.LatLngBounds} [bounds] the LatLngBounds object
     */
    extendBounds: function(bounds) {
        if(this.shape_type == 'point') {
            bounds.extend(this.shape.getPosition());
        }
        else if(this.shape_type == 'area') {
            this.shape.getPath().forEach(function(element, index) {
                bounds.extend(element);
            }.bind(this))
        }
        else if(this.shape_type == 'polyline') {
            this.shape.getPath().forEach(function(element, index) {
                bounds.extend(element);
            }.bind(this))
        }
    }
})

// point items class
gmaps.Point = new Class({
    Extends: gmaps.Item,
    options: {},
    /**
     * @summary Point item class
     * @classdesc This class represents a map place. See the parent class for parameters explaination
     * @constructs gmaps.Point
     * @see gmaps.Item.initialize
     */
    initialize: function(coords, style, fields, options) {
        coords.type = 'point';
        this.parent(coords, style, fields, options);
    },
    /**
     * @summary Sets the place infowindow
     * @memberof gmaps.Point.prototype
     */
    setInfoWindow: function() {
        this.infowindow = new google.maps.InfoWindow({
            content: '<p><b><a href="{readall_url}">{name}</a></b></p>'.substitute({name: this.fields.name, readall_url: this.fields.read_all_url})
        });
    }
})

// path items class
gmaps.Path = new Class({
    Extends: gmaps.Item,
    options: {},
    /**
     * @summary Step item class
     * @classdesc This class represents a map path. See the parent class for params explaination
     * @constructs gmaps.Path
     * @see gmaps.Item.initialize
     */
    initialize: function(coords, style, fields, options) {
        coords.type = 'polyline';
        this.parent(coords, style, fields, options);
    },
    /**
     * @summary Sets the place infowindow
     * @memberof gmaps.Path.prototype
     */
    setInfoWindow: function() {
        this.infowindow = new google.maps.InfoWindow({
            content: '<p><b><a href="{readall_url}">{name}</a></b></p>'.substitute({name: this.fields.name, readall_url: this.fields.read_all_url})
        });
    }
})

// service item class
gmaps.Area = new Class({
    Extends: gmaps.Item,
    options: {},
    /**
     * @summary Area item class
     * @classdesc This class represents a map area. See the parent class for params explaination
     * @constructs gmaps.Area
     * @see gmaps.Item.initialize
     */
    initialize: function(coords, style, fields, options) {
        coords.type = 'area';
        this.parent(coords, style, fields, options);
    },
    /**
     * @summary Sets the service infowindow
     * @memberof gmaps.Service.prototype
     */
    setInfoWindow: function() {
        this.infowindow = new google.maps.InfoWindow({
            content: '<p><b><a href="{readall_url}">{name}</a></b></p>'.substitute({name: this.fields.name, readall_url: this.fields.read_all_url})
        });
    }
})

// map class
gmaps.Map = new Class({

    Implements: [Options, Events],
    options: {
        center: [45, 7],
        zoom: 11,
        filter_label: 'filtra',
        filter_thematisms_label: 'tutti i tematismi',
        show_progress_bar: true,
        filter: true,
        filter_thematisms: false, //categoria
        panel: true
    },
    /**
     * @summary Map class
     * @classdesc This class represents the map. Provides methods to render it, update and manage the interface.
     * @constructs gmaps.Map
     * @params {Object} [options=undefined] The map options
     * @params {Array} [options.center=Array(45, 7)] The map init center
     * @params {Array} [options.zoom=11] The map init zoom level
     * @params {String} [options.filter_label='filtra'] The filter label
     * @params {Boolean} [options.show_progress_bar=true] Whether or not to show the progress bar when initializing the map
     * @params {Boolean} [options.filter=true] Whether or not to show the group filter
     * @params {Boolean} [options.filter_thematisms=true] Whether or not to show the thematisms filter
     */
    initialize: function(options) {
        this.setOptions(options);
        this.cluster = null;
        this.groups = {};
        this.selected_group = null;
        this.selected_thematism = null;
    },
    setCenter: function(latlng) {
        this.map.setCenter(latlng);
    },
    /**
     * @summary Adds a group of items to the map. Items are instances of gmaps.Item subclasses
     * @memberof gmaps.Map.prototype
     * @params {String} [name] the name of the group used as a key
     * @params {String} [label] the label of the group
     * @params {Array} [items] the array of items
     */
    addGroup: function(name, label, items) {
        this.groups[name] = {label: label, items: items};
    },
    /**
     * @summary Adds items to a group. Items are instances of gmaps.Item subclasses
     * @memberof gmaps.Map.prototype
     * @params {String} [group_name] the name of the group
     * @params {Array} [items] the array of items
     */
    addGroupItems: function(group_name, items) {
        this.groups[group_name].items = this.groups[group_name].items.append(items);
    },
    /**
     * @summary Renders the map in the given canvas
     * @memberof gmaps.Map.prototype
     * @params {String|Element} [canvas] the canvas id or the element itself
     */
    render: function(canvas) {
        this.canvas = document.id(canvas);
        this.canvas_container = new Element('div.maps-canvas_container')
            .setStyles({position: 'relative', overflow: 'hidden'})
            .inject(this.canvas, 'before').adopt(this.canvas);
        this.initProgressBar();

        this.progress_bar.next(5, 'initializing environment');

        // create the panel
        this.progress_bar.next(10, 'creating panel');
        if(this.options.panel) {
            this.setPanel();
        }

        var mapOptions = {
            center: new google.maps.LatLng(this.options.center[0], this.options.center[1]),
            zoom: this.options.zoom,
            mapTypeControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER
            },
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        this.progress_bar.next(40, 'creating map...');

        this.map = new google.maps.Map(this.canvas, mapOptions);

        this.progress_bar.next(50, 'init cluster...');
        this.setCluster();

        this.progress_bar.next(60, 'adding items...');

        this.updateItems();

        this.progress_bar.next(98, 'ending...');

        google.maps.event.addDomListener(this.map, 'click', this.clickHandler.bind(this), false);
        var streetview = this.map.getStreetView();
        var self = this;
        google.maps.event.addListener(streetview, 'visible_changed', function() {
                if(streetview.getVisible()) {
                        var close_streetview_button = new Element('span#streetview-close').setStyles({
                                position: 'absolute',
                                bottom: '5px',
                                left: '5px',
                                'z-index': 1000,
                                background: '#000',
                                'text-align': 'center',
                                'line-height': '16px',
                                width: '18px',
                                height: '18px',
                                'border-radius': '50%',
                                color: '#fff',
                                'font-weight': 'bold',
                                cursor: 'pointer'
                        }).set('text', 'x').addEvent('click', function() { streetview.setVisible(false); });
                        close_streetview_button.inject(self.canvas_container, 'bottom');
                }
                else {
                        if(typeOf($('streetview-close')) == 'element') {
                                $('streetview-close').dispose();
                        }
                }
        });

        google.maps.event.addListenerOnce(this.map, 'idle', function(){
            (function(){
                this.progress_bar.next(100, 'complete');
            }.bind(this)).delay(3000);
        }.bind(this));

    },
    /**
     * @summary Initializes the progress bar
     * @memberof gmaps.Map.prototype
     */
    initProgressBar: function() {

        var canvas_coordinates = this.canvas.getCoordinates();
        this.loading_layer = new Element('div', {'class': 'maps-loading_layer'}).setStyles({
            top: canvas_coordinates.top + 'px',
            left: canvas_coordinates.left + 'px',
            width: canvas_coordinates.width + 'px',
            height: canvas_coordinates.height + 'px',
            display: this.options.show_progress_bar ? 'block' : 'none'
        });
        this.loading_layer.inject(document.body);

        var self = this;
        this.progress_bar = new progressBar({	
            container: this.loading_layer,
            speed: 500,
            chain: true,
            displayPercentage: true,
            displayText: true,
            onComplete: function() {
                myeff = new Fx.Tween(self.loading_layer, {property: 'opacity'})
                myeff.start(0.9, 0).chain(function() { self.loading_layer.dispose(); });
            }
        });

    },
    /**
     * @summary Initializes the cluster
     * @memberof gmaps.Map.prototype
     */
    setCluster: function() {
        this.cluster = new MarkerClusterer(this.map);
        this.cluster.setGridSize(40);
    },
    /**
     * @summary Creates the map panel to filter/view points
     * @memberof gmaps.Map.prototype
     */
    setPanel: function() {

        this.panel = new gmaps.Panel(this, {
            width: '250px', 
            height: this.canvas.getStyle('height')
        });

        this.panel.inject(this.canvas_container, 'bottom');

    },
    updateMap: function() {

        group = null;
        thematism = null;

        if(typeOf(document.id('filter_group')) != 'null') {
            group = document.id('filter_group').value;
        }

        if(typeOf(document.id('filter_thematism')) != 'null') {
            thematism = document.id('filter_thematism').value;
        }

        this.panel.emptyContent();
        this.selected_group = group ? group : null;
        this.selected_thematism = thematism ? thematism : null;
        this.updateItems();
    },
    /**
     * @summary Updates the items visualization over the map
     * @memberof gmaps.Map.prototype
     */
    updateItems: function() {

        var thematisms_filter = [];

        this.bounds = new google.maps.LatLngBounds(); 
        this.cluster.clearMarkers();

        var self = this;
        // group filter
        if(self.options.filter) {
            var select = new Element('select#filter_group').adopt(new Element('option[value=]').set('text', this.options.filter_label)).addEvent('change', function() {
                self.updateMap();
            });
        }

        Object.each(this.groups, function(group, key) {
            if(self.options.filter) {
                var option = new Element('option[value='+key+']').set('text', group.label);
                self.panel.addFilter(select);
            }
            if(!self.selected_group || self.selected_group == key) {
                if(self.options.panel) {
                    self.panel.addList(key, group.label);
                    if(self.options.filter && self.selected_group == key) {
                        option.set('selected', 'selected');
                    }
                }
            }
            if(self.options.filter) {
                option.inject(select);
            }
            group.items.each(function(item) {

                if(self.options.filter_thematisms) {
                    thematisms_filter.combine(item.fields.thematisms);
                }

                if(
                    (!self.selected_group && !self.selected_thematism) ||
                    (!self.selected_thematism && self.selected_group == key) ||
                    (!self.selected_group && item.fields.thematisms.indexOf(self.selected_thematism) != -1) ||
                    (self.selected_group == key && item.fields.thematisms.indexOf(self.selected_thematism) != -1)
                ) {
                    item.shape.setMap(self.map);
                    if(item.shape_type == 'point') {
                        self.cluster.addMarker(item.shape);
                    }
                    item.extendBounds(self.bounds);
                    var li_item = new Element('span.link').set('text', '› ' + item.label()).addEvent('click', function() {
                        self.map.setZoom(16);
                        self.setCenter(item.center());
                        item.openInfoWindow();
                    });
                    if(self.options.panel) {
                        self.panel.addListItem(key, li_item);
                    }
                }
                else {
                    item.shape.setMap(null);
                }
            })
        });

        // thematism filter
        if(this.options.filter_thematisms && thematisms_filter.length) {
            thematisms_filter.sort();
            var t_select = new Element('select#filter_thematism').adopt(new Element('option[value=]').set('text', this.options.filter_thematisms_label)).addEvent('change', function() {
                self.updateMap();
            });
            thematisms_filter.each(function(t) {
                var option = new Element('option[value='+ t +']').set('text', t).inject(t_select);
                if(self.selected_thematism == t) option.setProperty('selected', 'selected');
            })
            this.panel.addFilter(t_select);
        }

        this.map.fitBounds(this.bounds);
        var zoomChangeBoundsListener = google.maps.event.addListenerOnce(this.map, 'bounds_changed', function(event) {
            if(self.map.getZoom() > 14) {
                self.map.setZoom(14);
            }
            google.maps.event.removeListener(zoomChangeBoundsListener)
        });

        this.cluster.repaint();
    },
    /**
     * @summary Event handler for the click event over the map. Used to open polygon's infowindows.
     * @memberof gmaps.Map.prototype
     * @param {google.maps.Event} [evt] the event object
     */
    clickHandler: function(evt) {
        Object.each(this.groups, function(group, key) {
            group.items.each(function(item) {
                if(item.shape_type == 'area' && item.clicked(evt.latLng)) {
                    item.openInfoWindow(evt);
                }
            })
        })
    }

});

//maps panel class
gmaps.Panel = new Class({
    Implements: [Options],
    options: {
        width: '200px',
        height: '100%'
    },
    /**
     * @summary Maps Panel Class
     * @classdesc Represents the left side panel which lists all visible points with the possibility to filter them
     * @constructs gmaps.Panel
     * @param {Object} [options] some options
     * @param {Number} [options.width] the panel width
     * @param {Number} [options.height] the panel height
     */
    initialize: function(map, options) {
        this.map = map;
        this.setOptions(options);
        this.panel_container = new Element('div.maps-panel').setStyles({
            position: 'absolute',
            right: 0,
            top: 0,
            height: this.options.height,
            width: this.options.width,
            'z-index': 4,
        });
        this.panel_controllers = new Element('div.maps-panel_controllers').inject(this.panel_container);
        this.panel_content = new Element('div.maps-panel_content').inject(this.panel_container);
        this.panel_filters = new Element('div.maps-panel_filters').inject(this.panel_content);
        this.panel_lists = new Element('div.maps-panel_lists').inject(this.panel_content);

        this.panel_container.setStyle('overflow', 'auto');

        this.is_open = true;
        this.toggle_fx = new Fx.Tween(this.panel_container, {property: 'right'});
        this.initControllers();

        this.lists = {};

        this.toggle();
    },
    /**
     * @summary Initializes the panel controllers
     * @memberof gmaps.Panel.prototype
     */
    initControllers: function() {
        var fn = Browser.ie ? function() { setTimeout(this.toggle.bind(this), 500); }.bind(this) : this.toggle.bind(this);
        this.controller = new Element('div.toggle.expanded')
            .addEvent('click', fn)
            .inject(this.panel_controllers);

        var fn = function() {
            this.map.canvas_container.toggleClass('fullscreen');
            this.fullscreen_controller.toggleClass('expanded');
            google.maps.event.trigger(this.map.map,'resize');
        }.bind(this);
        this.fullscreen_controller = new Element('div.ctrl-fullscreen')
            .addEvent('click', fn)
            .inject(this.panel_controllers);

        if(!Browser.Platform.ios && !Browser.Platform.android) {
            this.panel_container.addEvent('mouseover', function() {
                if(!this.is_open) {
                    this.toggle();
                }
            }.bind(this));
        }
    },
    /**
     * @summary Empties the panel contents
     * @memberof gmaps.Panel.prototype
     */
    emptyContent: function() {
        this.panel_filters.empty();
        this.panel_lists.empty();
    },
    /**
     * @summary Toggles the panel
     * @memberof gmaps.Panel.prototype
     */
    toggle: function() {
        var self = this;
        if(this.is_open) {
            if(typeof this.myscrollable != 'undefined') {
                this.myscrollable.terminate();
            }
            this.panel_content.fade('out');
            this.toggle_fx.start(-(this.options.width.toInt()-26)).chain(function() {
                self.controller.removeClass('expanded').addClass('collapsed');
                self.is_open = false;
            });
        }
        else {
            this.panel_content.fade('in');
            this.toggle_fx.start(0).chain(function() {
                self.controller.removeClass('collapsed').addClass('expanded');

                self.panel_container.setStyle('overflow', 'auto');
            });
            this.is_open = true;
        }
    },
    /**
     * @summary Injects the panel in the given element at the given position
     * @memberof gmaps.Panel.prototype
     * @param {Element} [element] the element where the panel is inserted
     * @param {String} [position] the position where the panel has to be inserted
     */
    inject: function(element, position) {
        this.panel_container.inject(element, position);
    },
 /**
     * @summary Adds a list to the panel
     * @memberof gmaps.Panel.prototype
     * @param {String} [name] the name of the list, used as a key
     * @param {String} [label] the label of the list
     */
    addFilter: function(input) {
        this.panel_filters.adopt(input);
    },
    /**
     * @summary Adds a list to the panel
     * @memberof gmaps.Panel.prototype
     * @param {String} [name] the name of the list, used as a key
     * @param {String} [label] the label of the list
     */
    addList: function(name, label) {
        var list_title = new Element('h2').set('text', label);
        var list = new Element('ul');
        this.lists[name] = {title: list_title, list: list};
        this.panel_lists.adopt(this.lists[name].title, this.lists[name].list);
    },
    /**
     * @summary Adds items to a list
     * @memberof ggmaps.Panel.prototype
     * @param {String} [list_name] the name of the list
     * @param {Array} [items] the items (Elements) to insert in li elements
     */
    addListItem: function(list_name, item) {
        this.lists[list_name].list.adopt(new Element('li').adopt(item));
    }
})

/**
 * Starting from here some methods which extends the polygon prototype in order to check if a given google.maps.LatLng point 
 * is contained in the polygon
 */
if (!google.maps.Polygon.prototype.getBounds) {
    google.maps.Polygon.prototype.getBounds = function(latLng) {
        var bounds = new google.maps.LatLngBounds();
        var paths = this.getPaths();
        var path;
        
        for (var p = 0; p < paths.getLength(); p++) {
            path = paths.getAt(p);
            for (var i = 0; i < path.getLength(); i++) {
                bounds.extend(path.getAt(i));
            }
        }

        return bounds;
    }
}

// Polygon containsLatLng - method to determine if a latLng is within a polygon
google.maps.Polygon.prototype.containsLatLng = function(latLng) {
    // Exclude points outside of bounds as there is no way they are in the poly
    var bounds = this.getBounds();

    if(bounds != null && !bounds.contains(latLng)) {
        return false;
    }

    // Raycast point in polygon method
    var inPoly = false;

    var numPaths = this.getPaths().getLength();
    for(var p = 0; p < numPaths; p++) {
        var path = this.getPaths().getAt(p);
        var numPoints = path.getLength();
        var j = numPoints-1;

        for(var i=0; i < numPoints; i++) {
            var vertex1 = path.getAt(i);
            var vertex2 = path.getAt(j);

            if (vertex1.lng() < latLng.lng() && vertex2.lng() >= latLng.lng() || vertex2.lng() < latLng.lng() && vertex1.lng() >= latLng.lng()) {
                if (vertex1.lat() + (latLng.lng() - vertex1.lng()) / (vertex2.lng() - vertex1.lng()) * (vertex2.lat() - vertex1.lat()) < latLng.lat()) {
                    inPoly = !inPoly;
                }
            }

            j = i;
        }
    }

    return inPoly;
}

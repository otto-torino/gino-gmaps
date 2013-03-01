GmapPoint = new Class({

	Implements: [Options],
	initialize: function(fields, categories, polylines, polygons, map, info_url) {
		// db fields
		this.fields = fields;
		// map
		this.map = map;
		// visibility
		this.visible = false;
		// google map marker
		this.marker = null;
		// infowindow
		this.infowindow = null;
		// categories
		this.categories = categories;
		// polylines
		this.polylines = polylines;
		// polygons
		this.polygons = polygons;
		// latlng google maps object
		this.latlng = new google.maps.LatLng(this.fields.lat, this.fields.lng);

		// initialize marker
		this.initMarker();
		// initialize infowindow
		this.initInfowindow();
		//load infowindow async
		this.asyncLoad(info_url);
	},
	setVisible: function(visible) {
		this.visible = visible;
	},
	initMarker: function() {
		this.marker = new google.maps.Marker({
			position: new google.maps.LatLng(this.fields.lat, this.fields.lng), 
			visible: false,
			map: this.map.googlemap()
		});
		if(this.fields.icon) {
			var icon = new google.maps.MarkerImage(this.fields.icon);
			var shadow = this.fields.shadow ? new google.maps.MarkerImage(this.fields.shadow) : null;
			this.marker.setIcon(icon);
			if(shadow) {
				this.marker.setShadow(shadow);
			}
		}
	},
	initInfowindow: function() {
		var iw_opt = {
			content: null,
	 		disableAutoPan: false,
	 		pixelOffset: new google.maps.Size(-160, 0),
	 		closeBoxMargin: "10px 8px 2px 2px",
	 		closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
	 		infoBoxClearance: new google.maps.Size(1, 1),
	 		isHidden: false,
	 		pane: "floatPane",
	 		enableEventPropagation: false
		};
		
		this.infowindow = new InfoBox(iw_opt);		

		google.maps.event.addListener(this.marker, 'click', function(id, e) {
			this.openInfowindow();
		}.bind(this));
	},
	setAsMapCenter: function() {
		this.map.googlemap().setCenter(this.latlng);
	},
	openInfowindow: function() {
		this.map.closeOpenedInfowindow();
		this.infowindow.open(this.map.googlemap(), this.marker);
		this.map.setOpenedInfowindow(this.infowindow);
	},
	update: function() {

		var visible = true;

		if(this.map.active_point_id && this.map.active_point_id != this.fields.id) {
			visible = false;
		}
		else if(this.map.active_point_ctg && this.categories.indexOf(this.map.active_point_ctg) == -1) {
			visible = false;
		}
		else if(this.map.active_polyline_id && this.polylines.indexOf(this.map.active_polyline_id) == -1) {
			visible = false
		}
		else if(this.map.active_polyline_ctg) {
			visible = false;
			this.polylines.each(function(polyline_id) {
				if(this.map.polylines['id' + polyline_id].categories.indexOf(this.map.active_polyline_ctg) != -1) {
					visible = true;
				}	
			}.bind(this))
		}
		else if(this.map.active_polygon_id && this.polygons.indexOf(this.map.active_polygon_id) == -1) {
			visible = false
		}
		else if(this.map.active_polygon_ctg) {
			visible = false;
			this.polygons.each(function(polygon_id) {
				if(this.map.polygons['id' + polygon_id].categories.indexOf(this.map.active_polygon_ctg) != -1) {
					visible = true;
				}	
			}.bind(this))
		}

		this.setVisible(visible);

		if(this.visible) {
			this.map.addMoocomplete(this.fields.label, 'point_' + this.fields.id);
			this.map.cluster.addMarker(this.marker);
			this.map.bounds.extend(this.latlng);
		}
		this.marker.setVisible(this.visible);

	},
	asyncLoad: function(url) {
		var myRequest = new Request({
    			url: url,
    			method: 'get',
    			onSuccess: function(responseText){
				result = this.parseAnchors(responseText);
				this.infowindow.setContent(result);
    			}.bind(this),
    			onFailure: function(){
				throw new Error('Info window load error');
    			}
		}).send();
	},
	parseAnchors: function(text) {

		var is_in_iframe = (window.location != window.parent.location) ? true : false;

		if(!is_in_iframe) return text;
	
		var hidden = new Element('div').setStyle('display', 'none').inject(document.body);
		hidden.set('html', text);

		hidden.getElements('a').each(function(anchor) {

			anchor.href = "javascript:parent.gmaps_chg_parent_url('" + anchor.href +"')";

		});	

		var new_text = hidden.get('html');

		hidden.dispose();

		return new_text;
	
	}

});

GmapPolyline = new Class({

	Implements: [Options],
	initialize: function(fields, categories, map, info_url) {
		// db fields
		this.fields = fields;
		// map
		this.map = map;
		// visibility
		this.visible = false;
		// google map polyline
		this.polyline = null;
		// infowindow
		this.infowindow = null;
		// categories
		this.categories = categories;

		this.lats = this.fields.lat.split(',');
		this.lngs = this.fields.lng.split(',');

		// initialize polyline
		this.initPolyline();
		// initialize infowindow
		this.initInfowindow();
		//load infowindow async
		this.asyncLoad(info_url);
	},
	setVisible: function(visible) {
		this.visible = visible;
	},
	initPolyline: function() {

		this.path = new google.maps.MVCArray();
		for(var i = 0; i < this.lats.length; i++) {
			this.path.push(new google.maps.LatLng(this.lats[i], this.lngs[i]));
		}

		this.polyline = new google.maps.Polyline({
			path: this.path, 
			strokeColor: this.fields.color,
			strokeWeight: this.fields.width,
			visible: false,
			map: this.map.googlemap()
		});

		this.reference_marker = new google.maps.Marker({
			position: this.path.getAt(0),
			map: this.map.googlemap(),
			visible: false
		});
	
	},
	initInfowindow: function() {
		var iw_opt = {
			content: null,
	 		disableAutoPan: false,
	 		pixelOffset: new google.maps.Size(-160, 0),
	 		closeBoxMargin: "10px 8px 2px 2px",
	 		closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
	 		infoBoxClearance: new google.maps.Size(1, 1),
	 		isHidden: false,
	 		pane: "floatPane",
	 		enableEventPropagation: false
		};
		
		this.infowindow = new InfoBox(iw_opt);		

		google.maps.event.addListener(this.polyline, 'click', function(id, e) {
			this.openInfowindow();
		}.bind(this));
	},
	openInfowindow: function() {
		this.map.closeOpenedInfowindow();
		this.infowindow.open(this.map.googlemap(), this.reference_marker);
		this.map.setOpenedInfowindow(this.infowindow);
	},
	update: function() {

		var visible = true;

		if(this.map.active_point_id || this.map.active_point_ctg || this.map.active_polygon_id || this.map.active_polygon_ctg) {
			visible = false;
		}
		if(this.map.active_polyline_id && this.map.active_polyline_id != this.fields.id) {
			visible = false;
		}
		else if(this.map.active_polyline_ctg && this.categories.indexOf(this.map.active_polyline_ctg) == -1) {
			visible = false;
		}

		this.setVisible(visible);

		if(this.visible) {
			this.map.addMoocomplete(this.fields.label, 'polyline_' + this.fields.id);
			this.path.forEach(function(element, index) {
				this.map.bounds.extend(element);
			}.bind(this))
		}
		this.polyline.setVisible(this.visible);

	},
	asyncLoad: function(url) {
		var myRequest = new Request({
    			url: url,
    			method: 'get',
    			onSuccess: function(responseText){
				var result = this.parseAnchors(responseText);
				this.infowindow.setContent(result);
    			}.bind(this),
    			onFailure: function(){
				throw new Error('Info window load error');
    			}
		}).send();
	},
	parseAnchors: function(text) {

		var is_in_iframe = (window.location != window.parent.location) ? true : false;

		if(!is_in_iframe) return text;
	
		var hidden = new Element('div').setStyle('display', 'none').inject(document.body);
		hidden.set('html', text);

		hidden.getElements('a').each(function(anchor) {
			anchor.href = "javascript:parent.gmaps_chg_parent_url('" + anchor.href +"')";
		});	

		var new_text = hidden.get('html');

		hidden.dispose();

		return new_text;
	
	}
});

GmapPolygon = new Class({

	Implements: [Options],
	initialize: function(fields, categories, map, info_url) {
		// db fields
		this.fields = fields;
		// map
		this.map = map;
		// visibility
		this.visible = false;
		// google map polyline
		this.polygon = null;
		// infowindow
		this.infowindow = null;
		// categories
		this.categories = categories;

		this.lats = this.fields.lat.split(',');
		this.lngs = this.fields.lng.split(',');

		// initialize polyline
		this.initPolygon();
		// initialize infowindow
		this.initInfowindow();
		//load infowindow async
		this.asyncLoad(info_url);
	},
	setVisible: function(visible) {
		this.visible = visible;
	},
	initPolygon: function() {

		this.path = new google.maps.MVCArray();
		for(var i = 0; i < this.lats.length; i++) {
			this.path.push(new google.maps.LatLng(this.lats[i], this.lngs[i]));
		}

		this.polygon = new google.maps.Polygon({
			path: this.path, 
			fillColor: this.fields.color,
			strokeColor: this.fields.color,
			strokeWeight: this.fields.width,
			visible: false,
			map: this.map.googlemap()
		});

		this.reference_marker = new google.maps.Marker({
			position: this.path.getAt(0),
			map: this.map.googlemap(),
			visible: false
		});
	
	},
	initInfowindow: function() {
		var iw_opt = {
			content: null,
	 		disableAutoPan: false,
	 		pixelOffset: new google.maps.Size(-160, 0),
	 		closeBoxMargin: "10px 8px 2px 2px",
	 		closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
	 		infoBoxClearance: new google.maps.Size(1, 1),
	 		isHidden: false,
	 		pane: "floatPane",
	 		enableEventPropagation: false
		};
		
		this.infowindow = new InfoBox(iw_opt);		

		google.maps.event.addListener(this.polygon, 'click', function(id, e) {
			this.openInfowindow();
		}.bind(this));
	},
	openInfowindow: function() {
		this.map.closeOpenedInfowindow();
		this.infowindow.open(this.map.googlemap(), this.reference_marker);
		this.map.setOpenedInfowindow(this.infowindow);
	},
	update: function() {

		var visible = true;

		if(this.map.active_point_id || this.map.active_point_ctg || this.map.active_polyline_id || this.map.active_polyline_ctg) {
			visible = false;
		}
		if(this.map.active_polygon_id && this.map.active_polygon_id != this.fields.id) {
			visible = false;
		}
		else if(this.map.active_polygon_ctg && this.categories.indexOf(this.map.active_polygon_ctg) == -1) {
			visible = false;
		}

		this.setVisible(visible);

		if(this.visible) {
			this.map.addMoocomplete(this.fields.label, 'polygon_' + this.fields.id);
			this.path.forEach(function(element, index) {
				this.map.bounds.extend(element);
			}.bind(this))
		}
		this.polygon.setVisible(this.visible);

	},
	asyncLoad: function(url) {
		var myRequest = new Request({
    			url: url,
    			method: 'get',
    			onSuccess: function(responseText){
					var result = this.parseAnchors(responseText);
					this.infowindow.setContent(result);
    			}.bind(this),
    			onFailure: function(){
					throw new Error('Info window load error');
    			}
		}).send();
	},
	parseAnchors: function(text) {

		var is_in_iframe = (window.location != window.parent.location) ? true : false;

		if(!is_in_iframe) return text;
	
		var hidden = new Element('div').setStyle('display', 'none').inject(document.body);
		hidden.set('html', text);

		hidden.getElements('a').each(function(anchor) {
			anchor.href = "javascript:parent.gmaps_chg_parent_url('" + anchor.href +"')";
		});	

		var new_text = hidden.get('html');

		hidden.dispose();

		return new_text;
	
	}
});

Gmap = new Class({

	Implements: [Options],
	options: {
		elements_list: false,
		elements_voices: ['label'],
		points_label: 'Punti di interesse',
		polylines_label: 'Percorsi',
		polygons_label: 'Aree',
		empty_search_result: 'no items found'
	},
	initialize: function(canvas, options) {

		this.canvas = canvas;
		this.points = {};
		this.cluster = null;
		this.opened_infowindow = null;
		this.polylines = {};
		this.polygons = {};
		this.active_point_ctg = null;
		this.active_point_id = null;
		this.active_polyline_ctg = null;
		this.active_polyline_id = null;
		this.active_polygon_ctg = null;
		this.active_polygon_id = null;

		// map bounds
		this.bounds = null;

		this.setProgressBar();
		this.progress_bar.next(5, 'initializing environment');

		this.progress_bar.next(12, 'setting options');
		this.setOptions(options);
		this.progress_bar.next(16, 'preparing auto complete search');
		this.moocomplete_list = [];
		this.moocomplete_list_id = [];
		this.moocomplete = new MooComplete('text_search', {
			list: this.moocomplete_list, 
			mode: 'text',
			size: 8
		});

		this.progress_bar.next(28, 'adding menu events');
		this.addMenuEvents();	

		this.progress_bar.next(50, 'initializing google map');
		this.initMap();

		this.progress_bar.next(55, 'setting map clusterer');
		this.setCluster();
		
	},
	setProgressBar: function() {

		var canvas_coordinates = this.canvas.getCoordinates();
		this.loading_layer = new Element('div', {'class': 'loading_layer'}).setStyles({
			top: canvas_coordinates.top + 'px',
			left: canvas_coordinates.left + 'px',
			width: canvas_coordinates.width + 'px',
			height: canvas_coordinates.height + 'px'
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
	initMap: function() {

		var map_opt = {
			center: new google.maps.LatLng(45, 7),
			zoom: 10,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.LARGE,
				position: google.maps.ControlPosition.RIGHT_CENTER
			},
			panControl: false,
			mapTypeControl: false
		}

		this.map = new google.maps.Map(this.canvas, map_opt);

		var pano = this.map.getStreetView();
		google.maps.event.addListener(pano, 'visible_changed', function() {
			if(pano.getVisible()) {
				$('gmaps_nav').setStyle('display', 'none');
			}
			else {
				$('gmaps_nav').setStyle('display', 'block');
			}
		});
	},
	setCluster: function() {

		this.cluster = new MarkerClusterer(this.map);
		this.cluster.setGridSize(40);
		
	},
	googlemap: function() {
		return this.map;
	},
	addMenuEvents: function() {

		$$('li[data-menu=maphelp]')[0].getParent('ul').setStyle('width', Math.round(this.canvas.getCoordinates().width*0.8) + 'px');

		this.map_types = {
			'hybrid': google.maps.MapTypeId.HYBRID,
			'roadmap': google.maps.MapTypeId.ROADMAP,
			'satellite': google.maps.MapTypeId.SATELLITE,
			'terrain': google.maps.MapTypeId.TERRAIN
		}

		$('gmaps_nav').setStyle('width', this.canvas.getStyle('width'));
		$('gmaps_nav').addEvent('click', function(e) {
			if(e.target.getProperty('data-menu')=='maptype') {
				$$('li[data-menu=maptype]').removeClass('selected');
				e.target.addClass('selected');
				this.map.setMapTypeId(this.map_types[e.target.getProperty('data-type')]);
			}
		}.bind(this));

		$('button_search').addEvent('click', function() {
			this.active_point_id = null;
			this.active_polyline_id = null;
			this.active_polygon_id = null;
			var index = this.moocomplete_list.indexOf($('text_search').value);
			if(index == -1) {
				alert(this.options.empty_search_result);
				return null;
			}
			var string_id = this.moocomplete_list_id[index];
			if(string_id.test(/^point_/)) {
				var id = string_id.substring(6).toInt();
				this.active_point_id = id; 
				this.updateMap();
				//this.points['id' + id].setAsMapCenter();
				this.points['id' + id].openInfowindow();
			}
			else if(string_id.test(/^polyline_/)) {
				var id = string_id.substring(9).toInt();
				this.active_polyline_id = id; 
				this.updateMap();
				this.polylines['id' + id].openInfowindow();
			}
			else if(string_id.test(/^polygon_/)) {
				var id = string_id.substring(8).toInt();
				this.active_polygon_id = id; 
				this.updateMap();
				this.polygons['id' + id].openInfowindow();
			}

		}.bind(this));

		$('button_search_reset').addEvent('click', function() {
			this.resetTextSearch();
			this.updateMap();
		}.bind(this));

		$('ctg_search').addEvent('change', function(e) {
			var value = $('ctg_search').get('value');
			this.active_point_ctg = null;	
			this.active_polyline_ctg = null;	
			this.active_polygon_ctg = null;	
			this.resetTextSearch();

			if(value.test(/^point_/)) {
				this.active_point_ctg = value.substring(6).toInt();	
			}
			else if(value.test(/^polyline_/)){
				this.active_polyline_ctg = value.substring(9).toInt();	
			}
			else if(value.test(/^polygon_/)){
				this.active_polygon_ctg = value.substring(8).toInt();	
			}
			this.updateMap();
		}.bind(this));

	},
	resetTextSearch: function() {
		$('text_search').value = '';
		this.active_point_id = null;
		this.active_polyline_id = null;
		this.active_polygon_id = null;
		if(this.opened_infowindow) {
			this.opened_infowindow.close();
		}
	},
	addPoints: function(points) {
		Object.append(this.points, points);
	}, 
	removePoint: function(id) {
		delete this.points['id' + id];
	},
	addPolylines: function(polylines) {
		Object.append(this.polylines, polylines);
	}, 
	removePolyline: function(id) {
		delete this.polylines['id' + id];
	},
	addPolygons: function(polygons) {
		Object.append(this.polygons, polygons);
	}, 
	removePolygon: function(id) {
		delete this.polygons['id' + id];
	},
	renderMap: function() {

		this.progress_bar.next(58, 'updating map items');
		this.progress_bar.next(65, 'adding points to cluster');
        if(this.options.elements_list) {
            this.renderList();
        }
        this.updateMap(); 

		this.progress_bar.next(98, 'ending...');
		google.maps.event.addListenerOnce(this.map, 'idle', function(){
			(function(){
				this.progress_bar.next(100, 'complete');
			}.bind(this)).delay(3000);
		}.bind(this));
	},
	updateMap: function() {

		// empty moocomplete list
		this.emptyMoocomplete();

		this.bounds = new google.maps.LatLngBounds(); 

		this.cluster.clearMarkers();

		Object.each(this.points, function(point_obj, key, object) {
			point_obj.update();
		}.bind(this))

		Object.each(this.polylines, function(polyline_obj, key, object) {
			polyline_obj.update();
		}.bind(this))

		Object.each(this.polygons, function(polygon_obj, key, object) {
			polygon_obj.update();
		}.bind(this))

		if(this.bounds.isEmpty()) {
			alert(this.options.empty_search_result);
		}
		else {
			this.map.fitBounds(this.bounds);
			this.cluster.repaint();
			if(this.active_point_id) {
				this.map.setZoom(12);
			}
		}

        if(this.options.elements_list) {
            this.updateList();
        }

	},	
    renderList: function() {

        var map_coord = this.canvas.getCoordinates();
        this.list_container = new Element('div', { style: 'width:' + map_coord.width + 'px' });
        this.list_container.table = new Element('table', { 'class': 'generic' }).inject(this.list_container);
        
    },
    updateList: function() {

        this.list_container.table.empty();

        var i = 0;                    
        Object.each(this.points, function(point_obj, key, object) {
            if(point_obj.visible) {
                if(!i) {
                    var tr = new Element('tr');
                    var th = new Element('th', { colspan: this.options.elements_voices.length }).set('html', this.options.points_label).inject(tr);
                    this.list_container.table.adopt(tr);
                }
                var tr = new Element('tr'); 
                this.listRow(tr, point_obj);
                i++;
            }
        }.bind(this))
        
        var i = 0;                    
        Object.each(this.polylines, function(polyline_obj, key, object) {
            if(polyline_obj.visible) {
                if(!i) {
                    var tr = new Element('tr');
                    var th = new Element('th', { colspan: this.options.elements_voices.length }).set('html', this.options.polylines_label).inject(tr);
                    this.list_container.table.adopt(tr);
                }
                var tr = new Element('tr');
                this.listRow(tr, polyline_obj);
                i++;
            }
        }.bind(this))

		var i = 0;                    
        Object.each(this.polygons, function(polygon_obj, key, object) {
            if(polygon_obj.visible) {
                if(!i) {
                    var tr = new Element('tr');
                    var th = new Element('th', { colspan: this.options.elements_voices.length }).set('html', this.options.polygons_label).inject(tr);
                    this.list_container.table.adopt(tr);
                }
                var tr = new Element('tr');
                this.listRow(tr, polygon_obj);
                i++;
            }
        }.bind(this))

        this.list_container.inject(this.canvas, 'after');

    },
    listRow: function(tr, obj) {

        for(var i = 0; i < this.options.elements_voices.length; i++) {
            field = this.options.elements_voices[i];
            var td = new Element('td');
            var voice = new Element('span').set('html', typeof obj.fields[field] != 'undefined' ? obj.fields[field] : '');
            td.adopt(voice);
            if(!i) {
                voice.addClass('link').addEvent('click', function() {
                   obj.openInfowindow(); 
                }.bind(this));
            }
            tr.adopt(td);
        }

        tr.inject(this.list_container.table);
    },
	emptyMoocomplete: function() {
		this.moocomplete_list.empty();
		this.moocomplete_list_id.empty();
	},
	addMoocomplete: function(label, id) {
		this.moocomplete_list.push(label);
		this.moocomplete_list_id.push(id);
		// set new moocomplete list
		this.moocomplete.setList(this.moocomplete_list);
	},
	setOpenedInfowindow: function(infowindow) {
		this.opened_infowindow = infowindow;
	},
	closeOpenedInfowindow: function() {
		if(this.opened_infowindow) {
			this.opened_infowindow.close();
		}
	},
	goToUrl: function() {
		parent.gmaps_chg_parent_url(url);
	}

});

mooGallery = new Class({

	Implements: [Options, Events],
	options: {
		onComplete: function() {}
	},
	initialize: function(container, images_opt, options) {
		
		this.container = typeOf(container)=='element' ? container : $(container);
		this.container.setStyle('padding', '0');
		this.images_opt = images;
		this.setOptions(options);

		this.images = [];
		this.thumbs = [];

		this.max_z_index = this.getMaxZindex();

		this.table = new Element('table', {'class': 'moogallery'}).inject(this.container);
		this.tr = new Element('tr').inject(this.table);
		this.tr_width = 0;
		this.container_width = this.container.getCoordinates().width;

		this.addEvent('image_rendered', function(img_opt_index) {
			if(typeOf(this.images_opt[img_opt_index]) != 'null') {
				this.renderImage(this.images_opt[img_opt_index]);
			}
			else {
				this.fireEvent('complete');
			}
		});

		this.renderImage(this.images_opt[0]);
	},
	renderImage: function(img_opt) {

		var img = new Image();
		var thumb = new Image();
		this.setTip(thumb, img_opt);
		this.setLightbox(thumb, img_opt);

		img.src = img_opt.img;
		thumb.src = img_opt.thumb;

		this.images.push(img);
		this.thumbs.push(thumb);

		img.onload = function() {
			var td = new Element('td');
			td.inject(this.tr);
			thumb.inject(td);
			if(this.table.getCoordinates().width >= this.container_width) {
				console.log(this.container_width);
				td.dispose();
				this.tr = new Element('tr').inject(this.table);
				td.inject(this.tr);
			}
			this.fireEvent('image_rendered', this.images_opt.indexOf(img_opt)+1)
		}.bind(this);
		
	},
	setTip: function(thumb, img_opt) {

		var tip_container = new Element('div', {'class': 'moogallery_tip'});
		tip_container.set('html', '<b>' + img_opt.title + '</b>');
		thumb.addEvents({
			'mousemove': function(e) {
				tip_container.setStyles({
					position: 'absolute',
					top: (e.page.y + 10) + 'px',
					left: (e.page.x + 10) + 'px',
					'z-index': this.max_z_index++
				});
				tip_container.inject(document.body);
	
			}.bind(this),
			'mouseout': function(e) {
				tip_container.dispose();
			}
		});

	},
	setLightbox: function(thumb, img_opt) {

		thumb.addEvent('click', function() {
			this.renderOverlay(this.renderLightbox.bind(this, [thumb, img_opt]));
		}.bind(this));

	},
	renderOverlay: function(chain_callback) {

		var docDim = document.getScrollSize();

		this.overlay = new Element('div', {'class': 'moogallery_overlay'});
		this.overlay.setStyles({
			position: 'absolute',
			top: 0,
			left: 0,
			'z-index': this.max_z_index++,
			width: docDim.x,
			height: docDim.y,
			opacity: 0
		});
		this.overlay.inject(document.body);
		var myfx = new Fx.Tween(this.overlay, {'property': 'opacity'});
		myfx.start(0, 0.9).chain(chain_callback);
	},
	renderLightbox: function(thumb, img_opt) {

		this.lightbox_container = new Element('div.moogallery_lightbox_container').setStyles({
			'visibility': 'hidden',
			'position': 'absolute'
		});

		this.renderLightboxContainer(img_opt);

	},
	renderLightboxContainer: function(img_opt) {

		var img = this.images[this.images_opt.indexOf(img_opt)];

		var img_info = new Element('div.moogallery_lightbox_info');
		var img_info_title = new Element('p.moogallery_lightbox_info_title').set('text', img_opt.title);

		var img_info_description_text = typeOf(img_opt.description) === 'null' ? '' : img_opt.description;
		var img_info_description = new Element('div.moogallery_lightbox_info_description').set('text', img_info_description_text);

		var img_info_credits_text = typeOf(img_opt.credits) === 'null' ? '' : 'credits:' + img_opt.credits;
		var img_info_credits = new Element('p.moogallery_lightbox_info_credits').set('text', img_info_credits_text);

		img_info.adopt(img_info_title, img_info_description, img_info_credits);

		this.lightbox_container.adopt(img);

		this.lightbox_container.inject(document.body);

		var vp = this.getViewport();

		this.lightbox_container.setStyles({
			'width': img.getCoordinates().width + 'px'
		});

		this.lightbox_container.adopt(img_info);

		var lightbox_container_coord = this.lightbox_container.getCoordinates();

		this.lightbox_container.setStyles({
			'top': (vp.cY - lightbox_container_coord.height/2) + 'px',
			'left': (vp.cX - lightbox_container_coord.width/2) + 'px',
			'visibility': 'visible',
			'z-index': this.max_z_index++
		});
	},
	getMaxZindex: function() {
		var maxZ = 0;
		$$('body *').each(function(el) {
			if(el.getStyle('z-index').toInt()) maxZ = Math.max(maxZ, el.getStyle('z-index').toInt());
		});

		return maxZ;
	},
	getViewport: function() {
		
		var width, height, left, top, cX, cY;

		// the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
		if (typeof window.innerWidth != 'undefined') {
			width = window.innerWidth,
			height = window.innerHeight
		}
		// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
		else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth !='undefined' && document.documentElement.clientWidth != 0) {
			width = document.documentElement.clientWidth,
			height = document.documentElement.clientHeight
		}

		top = typeof self.pageYOffset != 'undefined' 
			? self.pageYOffset 
			: (document.documentElement && document.documentElement.scrollTop)
				? document.documentElement.scrollTop
				: document.body.clientHeight;

		left = typeof self.pageXOffset != 'undefined' 
			? self.pageXOffset 
			: (document.documentElement && document.documentElement.scrollTop)
				? document.documentElement.scrollLeft
				: document.body.clientWidth;

		cX = left + width/2;
		cY = top + height/2;

		return {'width':width, 'height':height, 'left':left, 'top':top, 'cX':cX, 'cY':cY};
	}

});

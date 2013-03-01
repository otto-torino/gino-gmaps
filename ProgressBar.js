var progressBar = new Class({
	
	// implements
	Implements: [Options, Chain, Events],
	
	// options
	options: {
		container: document.body,
		boxID:'progress-bar-box-id',
		percentageID:'progress-bar-percentage-id',
		displayPercentageID:'progress-bar-display-percentage-id',
		displayTextID:'progress-bar-display-text-id',
		displayPercentage: true,
		displayText: true,
		speed:10,
		step:1,
		allowMore: false,
		chain: true,
		onComplete: function(){}
	},

	// initialization
	initialize: function(options) {

		//set options
		this.setOptions(options);

		//quick container
		this.options.container = document.id(this.options.container);

		//creates elements
		this.createElements();

		// initializes animation object
		this.initAnimation();
		
		// change event fired when a percentage change ends
		this.addEvent('change', this.change.bind(this), true);

		// call directly callChain when adding changes?
		this.wait_changed = false;
	},
		   
	// function called when the percentage change is complete
	change: function(to) {
		// if the stack is empty then call directly callChain next time
		if(this.callChain() === false) this.wait_changed = false;
	},
 
	//creates the box and percentage elements
	createElements: function() {

		var box = new Element('div', { 
			id:this.options.boxID 
		});

		var perc = new Element('div', { 
			id:this.options.percentageID, 
			'style':'width:0px;' 
		});

		perc.inject(box);
		box.inject(this.options.container);

		if(this.options.displayPercentage) { 
			var text_percentage = new Element('div', { 
				id:this.options.displayPercentageID 
			});
	
			text_percentage.inject(this.options.container);

		}

		if(this.options.displayText) { 

			var text = new Element('div', { 
				id:this.options.displayTextID 
			});
			text.inject(this.options.container, 'top');
		}
		
	},

	// moves the percentage from its current state to desired percentage
	next: function(to, text) {

		// add animation to chain
		this.chain(this.animate.bind(this, [to, text]));
		// if first call or chaining stack is empty execute callChain
		if(!this.wait_changed || !this.options.chain) {
			// execute other next chain functions after change event is fired
			this.wait_changed = true;
			this.callChain();
		}
	},

	// initializes the animation object
	initAnimation: function() {

		var self = this;
		this.animation = new Fx.Morph(document.id(self.options.percentageID), {
			duration: this.options.speed,
			link:'cancel',
			onComplete: function() {
				self.fireEvent('change',[self.to]);
				if(self.to >= 100) {
					self.fireEvent('complete',[self.to]);
				}
			}

		});
	},

	// animates the change in percentage
	animate: function(go, text) {

		var run = false;
		var self = this;
		if(!self.options.allowMore && go > 100) { 
			go = 100; 
		}
		self.to = go.toInt();
		
		if(self.options.displayPercentage) { 
			document.id(self.options.displayPercentageID).set('text', self.to + '%'); 
		}

		if(self.options.displayText) { 
			document.id(self.options.displayTextID).set('text', text || ''); 
		}

		this.animation.start({
			width: self.calculate(go)
		});
	},

	// calculates width in pixels from percentage
	calculate: function(percentage) {
		return (document.id(this.options.boxID).getCoordinates().width * (percentage / 100)).toInt();
	},

});

/*
 * imgViewer
 * 
 *
 * Copyright (c) 2013 Wayne Mogg
 * Licensed under the MIT license.
 */
;(function($) {
	$.widget("wgm.imgViewer", {
		options: {
			zoomStep: 0.1,
			zoom: 1,
			zoomMax: 2,
			onClick: null,
			onUpdate: null
		},

		_create: function() {
			var transparentPNG = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACklEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg==";

			var self = this;
			if (!this.element.is("img")) {
				$.error('imgviewer plugin can only be applied to img elements');
			}
			self.img = self.element[0];
			var $img = $(self.img);
			/*	
			 This is the current level of image zoom - it is always >=1
			 1 means *the entire image is visible
			 values > 1 indicate how much the image is magnified
			 */
			self.zoom = 1;
			self.dragging = false;

			$img.one("load", function() {
				// Determine the size of the image ie at zoom = 1
				var width = $img.width();
				var height = $img.height();
				var boxWidth = $img.parent().width();
				var boxHeight = $img.parent().height();

				if ($img.attr("width") && $img.attr("width").indexOf("%")!==-1) {
					self.autoWidth = true;
					self.autoWidthPercent = parseInt($img.attr("width"),10);
				}
				if ($img.attr("height") && $img.attr("height").indexOf("%")!==-1) {
					self.autoHeight = true;
					self.autoHeightPercent = parseInt($img.attr("height"),10);
				}

				self.bgWidth = width;
				self.bgHeight = height;
				self.bgAspect = width/height;
				self.bgCenter = {
					x: width/2,
					y: height/2
				};
				self.offsetBorder = {
					x: parseInt($img.css('border-left-width'),10),
					y: parseInt($img.css('border-top-width'),10)
				};
				self.offsetPadding = {
					x: parseInt($img.css('padding-left'),10),
					y: parseInt($img.css('padding-top'),10)
				};
				self.offset = $img.offset();

				//self.offsetPadding.x += (boxWidth - width)  / 2;

				$img.css({
					background: "url("+self.img.src+") 0 0 no-repeat",
					backgroundSize: width+'px '+height+'px',
					backgroundPosition: self.offsetPadding.x+'px '+ self.offsetPadding.y+'px'
				});

				self.img.width = boxWidth;
				self.img.height = boxHeight;
				self.img.src = transparentPNG;
			});


			$img.mousewheel(function(event, delta) {
				event.preventDefault();
				self.options.zoom += delta * self.options.zoomStep;

				if (self.options.zoom < 1) {
					self.options.zoom = 1;
				}
				else if (self.options.zoom > self.options.zoomMax) {
					self.options.zoom = self.options.zoomMax;
				}

				self.update();
			});

			$img.mousedown( function(e) {
				e.preventDefault();
				var last = e;
				$img.mousemove( function(e) {
					e.preventDefault();
					self.dragging = true;
					self.bgCenter.x = self.bgCenter.x - (e.pageX - last.pageX)/self.options.zoom;
					self.bgCenter.y = self.bgCenter.y - (e.pageY - last.pageY)/self.options.zoom;
					last = e;
					self.update();
				});
				function endDrag(e) {
					e.preventDefault();
					setTimeout(function() {	self.dragging = false; }, 0);
					$img.unbind("mousemove");
				}
				$img.one("mouseleave", endDrag);
				$img.one("mouseup", endDrag);
			});

			$img.click(function(e) {
				if (!self.dragging) {
					self._trigger("onClick", e, self);
				}
			});

			$(window).resize(function() {
				// Window resize doesn't change the part of the image visible
				// It does change the current zoomed image size and the size at zoom=1

				console.log(self.autoWidth, self.autoHeight);

				if (self.autoWidth || self.autoHeight ) {
					var imgWidth = $img.parent().width();
					var imgHeight = $img.parent().height();
					var viewWidth = $img.parent().width();
					var viewHeight = $img.parent().height();
					if (self.autoWidth && self.autoHeight) {
						viewWidth = viewWidth * self.autoWidthPercent/100;
						viewHeight = viewHeight * self.autoHeightPercent/100;
					} else if (self.autoWidth) {
						viewWidth = viewWidth * self.autoWidthPercent/100;
						viewHeight = viewWidth/self.bgAspect;
					} else	if (self.autoHeight) {
						viewHeight = viewHeight * self.autoHeightPercent/100;
						viewWidth = viewHeight*self.bgAspect;
					}
					self.bgCenter.x = ($img.parent().width() - viewWidth)/self.bgWidth;
					self.bgCenter.y = ($img.parent().height() - viewHeight)/self.bgHeight;
					self.img.width = $img.parent().width();
					self.img.height = $img.parent().height();
					self.bgHeight = viewHeight;
					self.bgWidth = viewWidth;
					self.offset = $img.offset();
					self.update();
				}
			});
		},

		destroy: function() {
			var $img = $(this.img);
			$img.unbind("click");
			$img.unmousewheel();
			$(window).unbind("resize");
			$.Widget.prototype.destroy.call(this);
		},

		_setOption: function(key, value) {
			switch(key) {
				case 'zoom':
					if (parseFloat(value) < 1 || isNaN(parseFloat(value))) {
						return;
					}

					if (value < 1) {
						value = 1;
					}
					else if (value > this.options.zoomMax) {
						value = this.options.zoomMax;
					}

					this.update();
					break;
				case 'zoomStep':
					if (parseFloat(value) <= 0 ||  isNaN(parseFloat(value))) {
						return;
					}
					break;
			}
			var version = $.ui.version.split('.');
			if (version[0] > 1 || version[1] > 8) {
				this._super(key, value);
			} else {
				$.Widget.prototype._setOption.apply(this, arguments);
			}
		},

		imgToCursor: function(relx, rely) {
			if ( relx >= 0 && relx <= 1 && rely >= 0 && rely <=1 ) {
				var zLeft = this.bgWidth/2-this.bgCenter.x*this.options.zoom;
				var zTop =  this.bgHeight/2-this.bgCenter.y*this.options.zoom;
				var x = relx * this.bgWidth*this.options.zoom + this.offset.left + this.offsetPadding.x+ this.offsetBorder.x + zLeft;
				var y = rely * this.bgHeight*this.options.zoom + this.offset.top + this.offsetPadding.y+ this.offsetBorder.y + zTop;
				return { pageX: Math.round(x), pageY: Math.round(y) };
			} else {
				return {};
			}
		},

		cursorToImg: function(pageX, pageY) {
			// Return the relative image coordinate corresponding to the given page pixel location
			// The returned coordinates will be between 0 and 1
			var zLeft = this.bgWidth/2-this.bgCenter.x*this.options.zoom;
			var zTop =  this.bgHeight/2-this.bgCenter.y*this.options.zoom;
			var x = (pageX - this.offset.left - this.offsetPadding.x - this.offsetBorder.x - zLeft)/(this.bgWidth*this.options.zoom);
			var y = (pageY - this.offset.top  - this.offsetPadding.y - this.offsetBorder.y - zTop)/(this.bgHeight*this.options.zoom);
			if (x>=0 && x<=1 && y>=0 && y<=1) {
				return {relx: x, rely: y};
			} else {
				return {};
			}
		},

		update: function() {
			var $img = $(this.img),
				width = this.bgWidth,
				height = this.bgHeight,
				zoom = this.options.zoom,
				half_width = width/2,
				half_height = height/2;

			var zTop, zLeft, zWidth, zHeight;
			if (zoom <= 1) {
				zTop = 0;
				zLeft = 0;
				zWidth = width;
				zHeight = height;
				this.bgCenter = {
					x: half_width,
					y: half_height
				};
				this.options.zoom = 1;
			} else {
				zTop = half_height - this.bgCenter.y * zoom;
				zLeft = half_width - this.bgCenter.x * zoom;
				zWidth = Math.round(width * zoom);
				zHeight = Math.round(height * zoom);
				if (zLeft > 0) {
					this.bgCenter.x = half_width/zoom;
					zLeft=0;
				} else if (zLeft+zWidth < width) {
					this.bgCenter.x = width - half_width/zoom ;
					zLeft = width - zWidth;
				}
				if (zTop > 0) {
					this.bgCenter.y = half_height/zoom;
					zTop = 0;
				} else if (zTop + zHeight < height) {
					this.bgCenter.y = height - half_height/zoom;
					zTop = height - zHeight;
				}
			}
			zLeft = Math.round(zLeft + this.offsetPadding.x);
			zTop = Math.round(zTop + this.offsetPadding.y);
			$img.css({
				backgroundSize: zWidth + 'px ' + zHeight + 'px',
				backgroundPosition: zLeft + 'px ' + zTop + 'px'
			});
			this._trigger("onUpdate", null, this);
		}
	});
})(jQuery);
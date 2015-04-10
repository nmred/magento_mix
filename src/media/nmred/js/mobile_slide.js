jQuery.fn.newMobileSlide = function(options) {
    function testTransform3d() {
        var supported = false;
        var div = jQuery('<div style="position:relative">Transform Test</div>');
        jQuery('body').append(div);
        div.css({
            'transform': "translate3d(3px,0,0)",
            '-moz-transform': "translate3d(3px,0,0)",
            '-webkit-transform': "translate3d(3px,0,0)",
            '-o-transform': "translate3d(3px,0,0)",
            '-ms-transform': "translate3d(3px,0,0)"
        });
        supported = (div.offset().left - jQuery(div[0].offsetParent).offset().left === 3);
        div.empty().remove();
        return supported
    }

    function testTransform() {
        var supported = false;
        var div = jQuery('<div style="position:relative">Transform Test</div>');
        jQuery('body').append(div);
        div.css({
            'transform': "translate(3px,0)",
            '-moz-transform': "translate(3px,0)",
            '-webkit-transform': "translate(3px,0)",
            '-o-transform': "translate(3px,0)",
            '-ms-transform': "translate(3px,0)"
        });
        supported = (div.offset().left - jQuery(div[0].offsetParent).offset().left === 3);
        div.empty().remove();
        return supported
    }
    var supportTransform3d = testTransform3d(),
        supportTransform = testTransform(),
        isAndroid = (/android/gi).test(navigator.appVersion),
        gv1 = supportTransform3d ? 'translate3d(' : 'translate(',
        gv2 = supportTransform3d ? ',0)' : ')',
        resizeEvent = ('onorientationchange' in window) ? 'orientationchange' : 'resize',
        iwidth = window.innerWidth;
    touch = {};
    jQuery.touchSlider = function(container, options) {
        jQuery.extend(this, {
            container: null,
            displaySlide: null,
            contentSlide: null,
            panels: null,
            trigger: null,
            curTriggerClass: 'current',
            linkedButton: null,
            contentWidth: 0,
            imgWidth: 320,
            left: 0,
            visible: 1,
            margin: 0,
            curIndex: 0,
            stepsSlide: true,
            steps: 1,
            distance: 0,
            duration: 300,
            len: 5,
            loop: false,
            play: false,
            interval: 3000,
            useTransform: !isAndroid,
            imgWidthChange: false,
            maxImgWidth: 640,
            visibleIntChange: false,
            lazy: '.lazyimg',
            lazyIndex: 1,
            callback: null,
            prev: null,
            next: null,
            activePnCls: 'none'
        }, options);
        this.findEl() && this.init() && this.increaseEvent()
    };
    jQuery.extend(jQuery.touchSlider.prototype, {
        reset: function(options) {
            jQuery.extend(this, options || {});
            this.init()
        },
        findEl: function() {
            var container = this.container = jQuery(this.container);
            if (!container.length) {
                return null
            }
            this.displaySlide = this.displaySlide && container.find(this.displaySlide) || container.children().first();
            if (!this.displaySlide.length) {
                return null
            }
            this.contentSlide = this.contentSlide && container.find(this.contentSlide) || this.contentSlide.children().first();
            if (!this.contentSlide.length) {
                return null
            }
            this.panels = this.contentSlide.children();
            this.trigger = this.trigger && container.find(this.trigger);
            this.linkedButton = this.linkedButton && container.find(this.linkedButton);
            this.prev = this.prev && container.find(this.prev);
            this.next = this.next && container.find(this.next);
            return this
        },
        triggerInit: function() {
            var trigger = this.trigger;
            if (trigger && trigger.length) {
                var temp = '',
                    childstu = trigger.children();
                if (!childstu.length) {
                    for (var i = 0; i < this.pages; i++) {
                        temp += '<span class="bg' + (i == this.curIndex ? " " + this.curTriggerClass + "" : "") + '"></span>'
                    }
                    trigger.html(temp)
                }
                this.triggers = trigger.children();
                this.triggerSel = this.triggers[this.curIndex]
            } else {
                this.hasTrigger = false
            }
            return this
        },
        cssReset: function() {
            this.displayWidth = this.displaySlide.width();
            if (this.resize && this.imgWidthChange) {
                this.contentSlide.css('width', '1200%')
            }
            var margin = this.margin,
                contentWidth = 0,
                imgWidth = this.imgWidth = this.imgWidthChange ? this.displaySlide.width() : this.imgWidth,
                outWidth = this.outWidth = imgWidth + margin,
                len = this.len = this.panels.length;
            this.visible = parseInt(this.displayWidth / outWidth);
            this.steps = Math.min(this.steps, this.visible);
            this.pages = Math.ceil(len / this.steps);
            this.distance = this.steps * outWidth;
            this.container.find('li').each(function(n, item) {
                jQuery(item).css('margin-right', margin + 'px');
                jQuery(item).css('width', imgWidth)
            });
            this.container.find('img').each(function(n, item) {
                // Fix -- prevent auto rezising pictures with fitted height
                //if (jQuery(item).height() != jQuery(item).parent('li').height()) {
                //    jQuery(item).css('width', imgWidth);
                //}
            });
            this.panels.each(function(n, item) {
                contentWidth += outWidth
            });
            this.contentWidth = contentWidth;
            this.triggerInit();
            this._maxLeft = this.displayWidth - this.contentWidth;
            return this
        },
        cssResize: function() {
            this.resize = true;
            var that = this;
            setTimeout(function() {
                if (iwidth == window.innerWidth || that.displaySlide.width() == 0) {
                    return this
                } else {
                    iwidth = window.innerWidth;
                    that.init()
                }
            }, isAndroid ? 500 : 0);
            return this
        },
        init: function() {
            this.cssReset();
            var contentWidth = this.contentWidth,
                displaySlide = this.displaySlide,
                contentSlide = this.contentSlide,
                panels = this.panels,
                useTransform = this.useTransform = supportTransform ? this.useTransform : false;
            if (useTransform) {
                displaySlide.css({
                    '-webkit-transform': 'translate3d(0,0,0)'
                });
                contentSlide.css({
                    '-webkit-backface-visibility': 'hidden'
                });
                contentSlide.css({
                    '-webkit-transform': gv1 + '0,0' + gv2
                })
            }
            if (this.visible > 1) {
                this.loop = false
            }
            this.updateArrow();
            if (Math.ceil(this.len / this.visible) <= 1) {
                this.trigger && this.trigger.hide()
            }
            if (this.loop) {
                if (!this.resize) {
                    contentSlide.append(panels[0].cloneNode(true));
                    var lastp = panels[this.len - 1].cloneNode(true);
                    contentSlide.append(lastp)
                }
                contentWidth += this.outWidth * 2;
                this.contentSlide.children(':last')[0].style.cssText += 'position:relative;left:' + (-contentWidth) + 'px;'
            }
            contentSlide.css('width', contentWidth + 'px');
            this.contentWidth = contentWidth;
            this._loopMaxLeft = this.displayWidth - this.contentWidth + this.outWidth;
            if (this.imgWidthChange) {
                this.left = -this.curIndex * this.outWidth
            } else if (this.left < this._maxLeft) {
                this._maxLeft > 0 ? (this.left = 0) : (this.left = this._maxLeft)
            }
            this.setCoord(this.contentSlide, this.left);
            return this
        },
        increaseEvent: function() {
            var that = this,
                _display = that.container[0],
                prev = that.prev,
                next = that.next,
                triggers = that.triggers;
            if (_display.addEventListener) {
                _display.addEventListener('touchstart', that, false);
                _display.addEventListener('touchmove', that, false);
                _display.addEventListener('touchend', that, false);
                _display.addEventListener('webkitTransitionEnd', that, false);
                _display.addEventListener('msTransitionEnd', that, false);
                _display.addEventListener('oTransitionEnd', that, false);
                _display.addEventListener('transitionend', that, false)
            }
            window.addEventListener(resizeEvent, that, false);
            if (that.play) {
                that.begin()
            }
            if (prev && prev.length) {
                prev.click(function(e) {
                    that.leftSlide = -(that.curIndex - 1) * that.outWidth;
                    that.backward.call(that, e)
                })
            }
            if (next && next.length) {
                next.click(function(e) {
                    that.forward.call(that, e)
                })
            }
            if (triggers) {
                triggers.each(function(n, item) {
                    jQuery(item).click(function() {
                        that.slideTo(-n * that.outWidth)
                    })
                })
            }
        },
        handleEvent: function(e) {
            switch (e.type) {
                case 'touchstart':
                    this.start(e);
                    break;
                case 'touchmove':
                    this.move(e);
                    break;
                case 'touchend':
                case 'touchcancel':
                    this.end(e);
                    break;
                case 'webkitTransitionEnd':
                case 'msTransitionEnd':
                case 'oTransitionEnd':
                case 'transitionend':
                    this.transitionEnd(e);
                    break;
                case resizeEvent:
                    this.cssResize();
                    break
            }
        },
        start: function(e) {
            var et = e.touches[0];
            this._start = new Date().getTime();
            this._movestart = undefined;
            this._disX = 0;
            this._disY = 0;
            this._coord = {
                x: et.pageX,
                y: et.pageY
            }
        },
        move: function(e) {
            if (e.touches.length > 1 || e.scale && e.scale !== 1) return;
            var et = e.touches[0],
                initLeft = this.left,
                style = this.contentSlide[0].style,
                tmleft;
            this._disX = et.pageX - this._coord.x;
            this._disY = et.pageY - this._coord.y;
            if (typeof this._movestart == 'undefined') {
                this._movestart = (this._movestart || Math.abs(this._disX) < Math.abs(this._disY))
            }
            if (!this._movestart) {
                e.preventDefault();
                this.stop();
                tmleft = initLeft + this._disX;
                style.webkitTransitionDuration = style.MozTransitionDuration = style.msTransitionDuration = style.OTransitionDuration = style.transitionDuration = 0;
                this.setCoord(this.contentSlide, tmleft)
            }
        },
        end: function(e) {
            if (!this._movestart) {
                this._end = new Date().getTime();
                touch.speed = this.speed(this._disX, this._disY, (this._end - this._start));
                var Slide = this.left + (this._disX > 0 ? this.distance : -this.distance),
                    _Slide = this.left + (this._disX > 0 ? touch.speed * this.duration : -touch.speed * this.duration);
                this.leftSlide = (this.stepsSlide ? Slide : _Slide);
                if (this._disX < -5) {
                    e.preventDefault();
                    this.forward()
                } else if (this._disX > 5) {
                    e.preventDefault();
                    this.backward()
                } else {
                    this.setCoord(this.contentSlide, this.left)
                }
            }
        },
        speed: function(disX, disY, time) {
            return Math.sqrt(Math.pow(Math.abs(disX), 2) + Math.pow(Math.abs(disY), 2)) / time
        },
        backward: function(e) {
            if (e && e.preventDefault) {
                e.preventDefault()
            }
            var leftSlide = this.leftSlide;
            if (leftSlide > 0 && !this.loop) {
                this.left = 0
            } else if (this.loop && leftSlide > this.distance) {} else {
                this.left = leftSlide
            }
            this.slideTo(this.left)
        },
        forward: function(e) {
            if (e && e.preventDefault) {
                e.preventDefault()
            }
            var leftSlide = this.leftSlide;
            if (leftSlide < this._maxLeft && !this.loop && this._maxLeft <= 0) {
                this.left = this._maxLeft
            } else if (this.loop && leftSlide < this._loopMaxLeft) {} else if (this._maxLeft > 0) {
                this.left = 0
            } else {
                this.left = leftSlide
            }
            this.slideTo(this.left)
        },
        setCoord: function(obj, x) {
            this.useTransform && obj.css("-webkit-transform", gv1 + x + 'px,0' + gv2) || obj.css("left", x)
        },
        slideTo: function(leftSlide, duration) {
            var contentSlide = this.contentSlide,
                style = contentSlide[0].style,
                scrollx = this.left = leftSlide;
            duration = (this.stepsSlide && touch.speed && Math.ceil(this.distance / touch.speed) < this.duration) ? Math.ceil(this.distance / touch.speed) : this.duration;
            style.webkitTransitionDuration = style.MozTransitionDuration = style.msTransitionDuration = style.OTransitionDuration = style.transitionDuration = (duration || this.duration) + 'ms';
            this.setCoord(contentSlide, scrollx);
            if (!this.loop || (this.left != this.distance && this.left != this._loopMaxLeft)) {
                this.curIndex = Math.ceil(Math.abs(this.left) / this.outWidth)
            }
            if (this.linkedButton) {
                this.linkedButton.attr('rel', this.curIndex);
            }
            this.update();
            this.updateArrow()
        },
        transitionEnd: function() {
            var contentSlide = this.contentSlide,
                style = contentSlide[0].style;
            if (this.loop && (this.left > 0 || this.left < this._maxLeft)) {
                if (this.left < this._maxLeft) {
                    this.left = 0
                } else if (this.left > 0) {
                    this.left = this._maxLeft
                }
                this.curIndex = Math.ceil(Math.abs(this.left) / this.outWidth);
                this.setCoord(contentSlide, this.left);
                this.update();
                this.updateArrow()
            }
            style.webkitTransitionDuration = style.MozTransitionDuration = style.msTransitionDuration = style.OTransitionDuration = style.transitionDuration = 0;
            if (!this.loop && this.left == this._maxLeft) {
                this.stop();
                this.play = false
            } else {
                this.begin()
            }
        },
        update: function() {
            var triggers = this.triggers,
                cls = this.curTriggerClass,
                curIndex = this.curIndex;
            if (triggers && triggers[curIndex]) {
                this.triggerSel && (jQuery(this.triggerSel).removeClass(cls));
                jQuery(triggers[curIndex]).addClass(cls);
                this.triggerSel = triggers[curIndex]
            }
        },
        updateArrow: function() {
            var prev = this.prev,
                next = this.next;
            if (!prev || !prev.length || !next || !next.length) return;
            if (this.loop) return;
            var cur = this.curIndex,
                cls = this.activePnCls;
            cur <= 0 && prev.addClass(cls) || prev.removeClass(cls);
            cur >= this._maxpage && next.addClass(cls) || next.removeClass(cls)
        },
        begin: function() {
            var that = this;
            this.leftSlide = -(this.curIndex + 1) * this.outWidth;
            if (that.play && !that._playTimer) {
                that.stop();
                that._playTimer = setInterval(function() {
                    that.forward()
                }, that.interval)
            }
        },
        stop: function() {
            var that = this;
            if (that.play && that._playTimer) {
                clearInterval(that._playTimer);
                that._playTimer = null
            }
        }
    });
    new jQuery.touchSlider(this, options)
};

jQuery.zoomImgLayer = function(widthEl, heightEl) {
    if ((widthEl / heightEl) > parseFloat(window.innerWidth / window.innerHeight)) {
        var displayHeight = (heightEl / widthEl) * window.innerWidth;
        var padTop = parseInt(window.innerHeight - displayHeight) / 2;
        jQuery('.zoom-img').width(window.innerWidth + 'px');
        jQuery('.zoom-img').height('auto');
        jQuery('.zoom-img').css('margin-top', padTop + 'px')
    } else {
        jQuery('.zoom-img').width('100%');
        jQuery('.zoom-img').css('margin-top', '0')
    }
};


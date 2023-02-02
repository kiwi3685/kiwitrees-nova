/**
 * Kahikatoa Theme
 *
 *
 * Copyright (c) 2010 - 2019 worse is better UG
 * Licensed under the MIT License.
 */

Galleria.addTheme({
	name: 'kahikatoa',
	version: 1.6,
	author: 'kiwitrees.net',
	css: 'galleria.kahikatoa.min.css',
	defaults: {
        transition: 'fade',
        transitionSpeed: 500,
        imageCrop: false,
        thumbCrop: 'height',
        idleMode: 'hover',
        idleSpeed: 500,
        fullscreenTransition: false,

		_toggleInfo: true,
		_showTooltip: true
	},

    init: function(options) {

        Galleria.requires(1.60, 'This version of Classic theme requires Galleria 1.60 or later');

        // add some elements
        this.addElement('info-link','info-close');
        this.append({
            'info' : ['info-link','info-close']
        });

        this.addElement('bar','fullscreen','play','progress').append({
            'stage' : 'progress',
            'container': 'bar',
            'bar'   : ['fullscreen','play','thumbnails-container']
        }).prependChild( 'stage', 'info' ).appendChild( 'container', 'tooltip' );

        // copy the scope
        var gallery = this,
            document = window.document,
            lang = options._locale,
            canvSupport = ( 'getContext' in document.createElement('canvas') );

        // cache some stuff
        var info = this.$('info-link,info-close,info-text'),
            touch = Galleria.TOUCH;

        // toggle info
        if ( options._toggleInfo === true ) {
            info.bind( 'click:fast', function() {
                info.toggle();
            });
        } else {
            info.show();
            this.$('info-link, info-close').hide();
        }

        // bind some stuff
        this.bind('thumbnail', function(e) {

            if (! touch ) {
                // fade thumbnails
                $(e.thumbTarget).css('opacity', 0.6).parent().hover(function() {
                    $(this).not('.active').children().stop().fadeTo(100, 1);
                }, function() {
                    $(this).not('.active').children().stop().fadeTo(400, 0.6);
                });

                if ( e.index === this.getIndex() ) {
                    $(e.thumbTarget).css('opacity',1);
                }
            } else {
                $(e.thumbTarget).css('opacity', this.getIndex() ? 1 : 0.6).bind('click:fast', function() {
                    $(this).css( 'opacity', 1 ).parent().siblings().children().css('opacity', 0.6);
                });
            }
        });

        this.bind( 'play', function() {
            this.$( 'play' ).addClass( 'pause' );
            this.$( 'progress' ).show();

        }).bind( 'pause', function() {
            this.$( 'play' ).removeClass( 'pause' );
            this.$( 'progress' ).hide();

        }).bind( 'loadstart', function(e) {
            if ( !e.cached ) {
                this.$( 'loader' ).show();
            }

        }).bind( 'loadfinish', function(e) {
           this.$('loader').fadeOut(200);

        });

        this.$( 'fullscreen' ).on('click:fast', function(e) {
            e.preventDefault();
            gallery.toggleFullscreen();

        });

        this.$( 'play' ).on('click:fast', function(e) {

         e.preventDefault();
            gallery.playToggle();
            gallery.play(3000);

        });

    }

});

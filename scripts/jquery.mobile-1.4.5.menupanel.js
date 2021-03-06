$.widget( "mobile.menupanel", $.mobile.panel, {
    _transitionClose: true,

    _handleClick: function( e ) {
  		var link,
  			panelId = this.element.attr( "id" );

  		if ( e.currentTarget.href.split( "#" )[ 1 ] === panelId && panelId !== undefined ) {

  			e.preventDefault();
  			link = $( e.target );
  			if ( link.hasClass( "ui-btn" ) ) {
  				link.addClass( $.mobile.activeBtnClass );
  				this.element.one( "menupanelopen menupanelclose", function() {
  					link.removeClass( $.mobile.activeBtnClass );
  				});
  			}
  			this.toggle();
  		}
  	},

    _bindPageEvents: function() {
  		var self = this;

  		this.document
  			// Close the panel if another panel on the page opens
  			.on( "panelbeforeopen", function( e ) {
  				if ( self._open && e.target !== self.element[ 0 ] ) {
  					self.close();
  				}
  			})
  			// On escape, close? might need to have a target check too...
  			.on( "keyup.panel", function( e ) {
  				if ( e.keyCode === 27 && self._open ) {
  					self.close();
  				}
  			});
  		if ( !this._parentPage && this.options.display !== "overlay" ) {
  			this._on( this.document, {
  				"pageshow": function() {
  					this._openedPage = null;
  					this._getWrapper();
  				}
  			});
  		}
  		// Clean up open panels after page hide
  		if ( self._parentPage ) {
  			this.document.on( "pagehide", ":jqmData(role='page')", function() {
  				if ( self._open ) {
  					self.close( true );
  				}
  			});
  		} else {
  			this.document.on( "pagebeforehide", function() {
  				if ( self._open && self.options._transitionClose ) {
  					self.close( true );
  				}
  			});
        this.document.on( "pagebeforeshow", function(event, data) {
            if ( ! self.options._transitionClose ) {
                var $page = $(event.target),
                    classes = self.options.classes,
                    wrapper = $page.find( "." + classes.pageWrapper );

                if (self._open == true) {
                    if ( wrapper.length === 0 ) {
                        wrapper = $page.children( ".ui-header:not(.ui-header-fixed), .ui-content:not(.ui-popup), .ui-footer:not(.ui-footer-fixed)" )
                            .wrapAll( "<div class='" + classes.pageWrapper + "'></div>" )
                            .parent();
                    }
                    wrapper
                        .addClass( self._pageContentOpenClasses )
                        .addClass( classes.pageContentPrefix + "-open" );

                    // Handle fixed headers
                    var $header = $page.find('[data-role="header"][data-position="fixed"]');
                    if( $header.length > 0 )
                    {
                      $header = $header.first();
                      $header.addClass("ui-panel-animate ui-panel-page-content-position-left ui-panel-page-content-display-reveal ui-panel-page-content-open");
                    }

                    // Set data
                    self._page().jqmRemoveData( "panel" );
                    $page.jqmData( "panel", "open" );

                } else {
                    wrapper
                        .removeClass( self._pageContentOpenClasses )
                        .removeClass( classes.pageContentPrefix + "-open" );
                }
            }
        });

  		}
  	}
});

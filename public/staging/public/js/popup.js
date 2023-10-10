class Popup {
    constructor(url, callback, options) {
        var _this = this;
        this.id = '_' + Math.random().toString(36).substr(2, 9);
        this.options = {};
        this.popup = $('.popup[id='+this.id+']');
        this.loadingHtml = '<div class="popup-loading"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
        this.data = {};
        this.backs = [];
        
        // url
        if (typeof(url) !== 'undefined') {
            this.url = url;
        }
        
        // callback
        if (typeof(callback) !== 'undefined') {
            this.callback = callback;
        }
        
        // options
        if (typeof(options) !== 'undefined') {
            this.options = options;

            // data            
            if (typeof(options.data) !== 'undefined') {
                this.data = options.data;
            }
        }
        
        if (!this.popup.length) {
            var popup = $('<div class="popup" id="'+this.id+'">').html('');
            $('body').append(popup);
            
            this.popup = popup;
            this.loading();
        }
        this.popup.css('display', 'none');
        
        //// click outside to close
        $(".popup").click(function(e){
           if(e.target != this) return; // only continue if the target itself has been clicked
           // this section only processes if the .nav > li itself is clicked.
           Popup.hide();
        });

        // onclose popup
        if(this.options.onclose != null) {
            this.onclose = this.options.onclose;
        }
    }
    
    show() {
        this.popup.fadeIn();
        $('html').css('overflow', 'hidden');
    }
    
    hide() {
        this.popup.fadeOut();
        $('html').css('overflow', 'auto');

        // onclose
        if (this.onclose != null) {
            this.onclose();
        }

        // clear backs
        this.backs = [];

        if (typeof(this.onHide) !== 'undefined') {
            this.onHide();
        }
    }
    
    loading() {
        this.popup.prepend(this.loadingHtml);
        this.popup.addClass('popup-is-loading');
    }

    loaded() {
        // apply js for new content
        this.applyJs();
        
        // remove loading effects
        this.popup.find('.popup-loading').remove();        
        this.popup.removeClass('popup-is-loading');
    }
    
    static hide() {
        $('.popup').fadeOut();
        $('html').css('overflow', 'auto');
    }
    
    applyJs() {
        var _this = this;
        
        // init js
        initJs(_this.popup);
        
        // set back button
        // back button
        if (_this.backs.length > 1) {
            _this.popup.find('.back').on('click', function() {
                var backing = _this.backs.pop();
                _this.load(backing.url, backing.callback, $.extend({}, backing.data, {back: true}));
            });
        } else {
            _this.popup.find('.back').on('click', function() {                    
                _this.hide();
            });
        }
        
        // click close button
        _this.popup.find(".close").click(function(){
            _this.hide();
        });
    }
    
    load(url, callback, data) {
        //alert(url);
        var _this = this;
        
        if (typeof(url) !== 'undefined') {
            if (typeof(data) == 'undefined' || !data.back) {
                this.backs.push({
                    url: this.url,
                    callback: this.callback,
                    data: this.data,
                });
            }

            this.url = url;
        }
        
        if (typeof(callback) !== 'undefined' && callback !== null) {
            this.callback = callback;
        }

        var formData = _this.data;
        if (typeof(data) !== 'undefined') {
            formData = $.extend({}, data, formData);
        }
        
        this.loading();
        
        this.show();
        //alert(_this.url);
        $.ajax({
            url: _this.url,
            type: 'GET',
            dataType: 'html',
            data: formData,
        }).done(function(response) {
            _this.popup.html(response);
            
            if (typeof(_this.callback) !== 'undefined') {
                _this.callback();
            }
            
            // after load
            _this.loaded();

            // // apply js
            // _this.applyJs();
        }).fail(function(jqXHR, textStatus, errorThrown){
            // for debugging
            alert(errorThrown);
            document.write(jqXHR.responseText);
        });
    }
    
    loadHtml(html) {
        var _this = this;
        
        _this.popup.html(html);
        
        // after load
        _this.loaded();

        // // apply js
        // _this.applyJs();
    }
}
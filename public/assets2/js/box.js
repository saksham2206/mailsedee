class Box {
    constructor(selector, url, callback) {
        this.box = selector;
        this.loadingHtml = '<div class="box-loading"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
        
        if (typeof(url) !== 'undefined') {
            this.url = url;
        }
        
        if (typeof(callback) !== 'undefined') {
            this.callback = callback;
        }
    }
    
    loading() {
        if (!this.box.find('.box-loading').length) {
            this.box.prepend(this.loadingHtml);
        }
        
        this.box.addClass('box-is-loading');
    }
    
    loaded() {
        // apply js for new content
        this.applyJs();
        
        // remove loading effects
        this.box.find('.box-loading').remove();        
        this.box.removeClass('box-is-loading');
    }
    
    applyJs() {
        var _this = this;
        
        // init js
        initJs(_this.box);
    }
    
    load(url, callback) {
        var _this = this;
       // alert(_this.url);
        if (typeof(url) !== 'undefined') {
            this.url = url;
        }
        
        if (typeof(callback) !== 'undefined') {
            this.callback = callback;
        }
        
        this.loading();
        
        $.ajax({
            url: _this.url,
            type: 'GET',
            dataType: 'html',
        }).always(function(response) {
            _this.box.html(response);
            
            if (typeof(_this.callback) !== 'undefined') {
                _this.callback();
            }
            
            // scroll top
            _this.box.animate({scrollTop: 0});
            
            // done
            _this.loaded();
        });
    }
    
    loadHtml(html) {
        var _this = this;
        
        _this.box.html(html);
        
        _this.box.animate({scrollTop: 0});
        
        // done
        _this.loaded();
    }
}
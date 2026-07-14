var WebpConverter = function(config) {
    config = config || {};
    WebpConverter.superclass.constructor.call(this, config);
};
Ext.extend(WebpConverter, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}
});
Ext.reg('webpconverter', WebpConverter);
WebpConverter = new WebpConverter();

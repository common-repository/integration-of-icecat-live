/**
 * 
 */
tinymce.PluginManager.add('iclvshtcd', function(editor, url) {
    // Add a button that opens a window
    editor.addButton('iclvshtcd', {
        title: 'Icecat Live shortcode',
        icon: 'nswiclv',
        onclick: function() {
            // Open dialog
            var valuesDefault = {brand:'', mpn:'', barcode:''};
            var acceptedLangs = {};
            if( typeof nswiclv_product != 'undefined' ){
                valuesDefault.brand = nswiclv_product.brand;
                valuesDefault.mpn = nswiclv_product.mpn;
                valuesDefault.barcode = nswiclv_product.barcode;
                valuesDefault.langs = nswiclv_product.languages;
            }
            if( typeof nswiclv_languages != 'undefined' ){
            	acceptedLangs = nswiclv_languages;
            }
            editor.windowManager.open({
                title: 'Icecat Live shortcode',
                body: [
                    {type: 'textbox', name: 'brand', label: 'Brand', value: valuesDefault.brand},
                    {type: 'textbox', name: 'mpn', label: 'MPN', value: valuesDefault.mpn},
                    {type: 'textbox', name: 'barcode', label: 'Barcode', value: valuesDefault.barcode},
                    {type: 'textbox', name: 'icid', label: 'Icecat ID'},
                    {type: 'listbox', name: 'lang', label: 'Accepted langs', values: acceptedLangs}
                ],
                onsubmit: function(e) {
                    // Insert content when the window form is submitted
                    var content = '[nswiclv ';
                    if( e.data.brand.length ){
                        content += ' brand="'+ e.data.brand +'"';
                    }
                    if( e.data.mpn.length ){
                        content += ' mpn="'+ e.data.mpn +'"';
                    }
                    if( e.data.barcode.length ){
                        content += ' barcode="'+ e.data.barcode +'"';
                    }
                    if( e.data.icid.length ){
                        content += ' icid="'+ e.data.icid +'"';
                    }
                    if( e.data.lang.length ){
                        content += ' lang="'+ e.data.lang +'"';
                    }
                    content += ']';
                    editor.insertContent(content);
                }
            });
        }
    });

    return {
        getMetadata: function () {
            return  {
                name: "Icecat Live shortcode",
            };
        }
    };
});
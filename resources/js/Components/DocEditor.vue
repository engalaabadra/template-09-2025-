<template>
    <label :for="name" class="text-base px-2 my-2 font-bold inline-block dark:text-primary-100"
            :class="{'p-error':form['errors']??false?form['errors'][name]:false,'p-error':form_error??false,'required':required}">{{
            label
        }}
    </label>
    <div class="row">
        <div class="document-editor__toolbar"></div>
    </div>
    <div class="row row-editor">
        <div class="editor-container">
            <textarea :id="`editor-${_elmId}`" v-model="form[name]"></textarea>
        </div>
    </div>
</template>

<script>

export default {
    name: "DocEditor",
    data(){
        return {
            _elmId: this.elmId || (function(length) {
                let result = '';
                const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                const charactersLength = characters.length;
                let counter = 0;
                while (counter < length) {
                    result += characters.charAt(Math.floor(Math.random() * charactersLength));
                    counter += 1;
                }
                return result;
            })(10),
            editor: null
        }
    },
    watch: {
        orientation(newVal) {
            if (this.editor && CKEDITOR.instances[`editor-${this._elmId}`]) {
                // Update the body class to reflect the new orientation
                CKEDITOR.instances[`editor-${this._elmId}`].document.getBody().setAttribute('class', `document-editor ${newVal}`);
            }
        }
    },
    props:{
        form: Object,
        name: null,
        elmId: null,
        label: String,
        required: Boolean,
        live: false,
        height: {
            type: Number,
            default: 800
        },
        orientation: {
            type: String,
            default: 'portrait'
        }
    },
    mounted() {
        this.editor = CKEDITOR.replace( `editor-${this._elmId}`,{
            language: 'ar',

            // An array of stylesheets to style the WYSIWYG area.
            // Note: it is recommended to keep your own styles in a separate file in order to make future updates painless.
            contentsCss: [ 'https://cdn.ckeditor.com/4.8.0/full-all/contents.css', '/css/doc-style.css?v=1.4' ],

            // This is optional, but will let us define multiple different styles for multiple editors using the same CSS file.
            bodyClass: `document-editor ${this.orientation}`,
            htmlStyle: 'color:red',
            // Make the editing area bigger than default.
            height: this.height,
            stylesSet: [
                { name: 'إطار خارجي', element: 'span', attributes: { 'class': 'with-border' } },
            ]
        } );
        CKEDITOR.instances[`editor-${this._elmId}`].on('change', () => this.form[this.name] = this.editor.getData());
        /*DecoupledDocumentEditor
            .create(document.querySelector('.editor'), {})
            .then(editor => {
                window.editor = editor;
                editor.model.document.on( 'change:data', () => {
                    if(this.live) {
                        this.form[this.name] = editor.getData();
                    }
                });
                // Set a custom container for the toolbar.
                const toolbar = document.querySelector('.document-editor__toolbar');
                if(toolbar.children.length === 0) {
                    toolbar.appendChild(editor.ui.view.toolbar.element);
                }
                document.querySelector('.ck-toolbar').classList.add('ck-reset_all');
            })
            .catch(error => {
                   console.error(error);
            });*/
    }
}
</script>

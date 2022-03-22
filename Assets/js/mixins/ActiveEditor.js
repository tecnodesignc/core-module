export default {
    methods: {
        getCurrentEditor() {
            const configuredEditor = window.EncoreCMS.editor;
            if (configuredEditor === undefined || configuredEditor === 'ckeditor') {
                return 'ckeditor';
            }
            if (configuredEditor === 'simplemde') {
                return 'markdown-editor';
            }
            return configuredEditor;
        },
    },
};

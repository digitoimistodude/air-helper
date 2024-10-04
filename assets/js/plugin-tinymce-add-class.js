/* eslint-disable no-undef, no-unused-vars */
(function addClassesToWysiwygElements() {
  tinymce.create('tinymce.plugins.addClass', {
    init(editor, url) {
      editor.on('NodeChange', () => {
        const elements = editor.dom.select('ul, ol, blockquote');

        elements.forEach((element) => {
          if (!editor.dom.hasClass(element, 'wysiwyg')) {
            editor.dom.addClass(element, 'wysiwyg');
          }
        });
      });
    },
  });

  tinymce.PluginManager.add('addClass', tinymce.plugins.addClass);
}());

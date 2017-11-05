(function() {
    class TranslationEditor extends HTMLElement {
        constructor() {
            super();

            var editor = function() {
                if (! document.getElementById('translation-editor')) {
                    var editorElement = document.createElement('div');

                    editorElement.id = 'translation-editor';
                    editorElement.classList.add('translation-editor__modal');
                    editorElement.innerHTML = '<div class="translation-editor__dialog">' +
                            '<form id="translation-editor-form" class="translation-editor__body">' +
                                '<label for="translation-editor-source">Source text:</label>' +
                                '<textarea id="translation-editor-source" rows="6" readonly></textarea>' +
                                '<label for="translation-editor-translation">Translation:</label>' +
                                '<textarea id="translation-editor-translation" rows="6"></textarea>' +
                            '</form>' +
                            '<div class="translation-editor__footer">' +
                                '<button type="button" class="translation-editor__default">Cancel</button> ' +
                                '<button type="button" class="translation-editor__primary">Save</button>' +
                            '</div>' +
                        '</div>';

                    editorElement.addEventListener('click', function () {
                        editor().classList.remove('in');
                    });

                    editorElement.querySelector('.translation-editor__default').addEventListener('click', function () {
                        editor().classList.remove('in');
                    });

                    editorElement.querySelector('.translation-editor__primary').addEventListener('click', function () {
                        var form = new FormData(document.getElementById('translation-editor-form'));

                        window.fetch('{baseRoute}/translation', {
                                method: 'POST',
                                body: form
                            })
                            .then(function () {
                                editor().classList.remove('in');
                            });
                    });

                    editorElement.querySelector('.translation-editor__dialog').addEventListener('click', function (e) {
                        e.stopPropagation();
                    });

                    document.body.appendChild(editorElement);
                }

                return document.getElementById('translation-editor');
            };

            this.addEventListener('click', function (e) {
                if (! e.altKey) {
                    return;
                }

                var target = e.target,
                    locale = target.getAttribute('locale'),
                    path   = target.getAttribute('path');

                editor().classList.add('in');

                document.getElementById('translation-editor-source').value = 'Loading...';
                document.getElementById('translation-editor-translation').value = 'Loading...';

                window.fetch(encodeURI('{baseRoute}/translation?locale='+ locale +'&path=' + path))
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function (json) {
                        document.getElementById('translation-editor-source').value = json.source;
                        document.getElementById('translation-editor-translation').value = json.translation;
                    });
            });
        }
    }

    customElements.define('translation-editor', TranslationEditor);

    var toggleHighlight = function(e) {
        document.body.classList.toggle('translation-editor__highlight', e.altKey);
    };

    document.addEventListener('keydown', toggleHighlight);
    document.addEventListener('keyup', toggleHighlight);
})();

import { EditorView, basicSetup } from 'codemirror';
import { EditorState } from '@codemirror/state';
import { html } from '@codemirror/lang-html';

export function initializeCodeMirror(element, initialContent = '', onUpdate = null) {
    const state = EditorState.create({
        doc: initialContent,
        extensions: [
            basicSetup,
            html(),
            EditorView.lineWrapping,
            EditorView.updateListener.of((update) => {
                if (update.docChanged && onUpdate) {
                    onUpdate(update.state.doc.toString());
                }
            })
        ]
    });

    const view = new EditorView({
        state,
        parent: element
    });

    return {
        view,
        getValue: () => view.state.doc.toString(),
        setValue: (content) => {
            view.dispatch({
                changes: {
                    from: 0,
                    to: view.state.doc.length,
                    insert: content
                }
            });
        },
        destroy: () => view.destroy()
    };
}

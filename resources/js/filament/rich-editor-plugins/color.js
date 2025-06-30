import Color from '@tiptap/extension-color'
import TextStyle from '@tiptap/extension-text-style'

export default (Alpine) => {
    Alpine.data('richEditorComponent', (
        {
            state,
            readOnly,
            placeholder,
            toolbarButtons,
            uploadsDisk,
            uploadsDirectory,
            uploadsVisibility,
            isTribalkit,
            isProdContent,
        }
    ) => {
        return {
            ...Alpine.store('richEditor').default({
                state,
                readOnly,
                placeholder,
                toolbarButtons,
                uploadsDisk,
                uploadsDirectory,
                uploadsVisibility,
                isTribalkit,
                isProdContent
            }),
            init() {
                this.instance().setOptions({
                    extensions: [
                        ...this.instance().options.extensions,
                        Color,
                        TextStyle
                    ]
                })

                this.$watch('state', (newState) => {
                    if (this.instance().getHTML() !== newState) {
                        this.instance().commands.setContent(newState, false)
                    }
                })
            },
        }
    })
} 
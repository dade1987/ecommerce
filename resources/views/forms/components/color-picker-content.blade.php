<div x-data="{
    color: '#000000',
    applyColor() {
        const richEditor = this.$el.closest('.fi-fo-rich-editor').__x;
        richEditor.execute(
            (view) => {
                view.dispatch(view.state.tr.setSelection(view.state.selection))
            },
            (view) => {
                view.chain().focus().setColor(this.color).run()
            }
        );
        $dispatch('close-modal', { id: 'filament-forms-modal-color' });
    }
}">
    <div class="p-4">
        <label for="color-picker" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Scegli un colore') }}</label>
        <input type="color" id="color-picker" x-model="color" class="mt-1 block w-full">
    </div>

    <div class="fi-modal-footer p-4 bg-gray-100 dark:bg-gray-800 rounded-b-lg">
        <div class="flex items-center justify-end gap-x-3">
             <x-filament::button color="primary" @click="applyColor()">
                {{ __('Applica Colore') }}
            </x-filament::button>
            <x-filament::button color="gray" @click="$dispatch('close-modal', { id: 'filament-forms-modal-color' })">
                {{ __('Annulla') }}
            </x-filament::button>
        </div>
    </div>
</div> 
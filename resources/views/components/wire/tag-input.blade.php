{{-- OmniaCSS tag input bound to a Livewire array property through Alpine ($wire.entangle). Props: model (required), live, placeholder, suggestions (list), label.
     Enter or comma adds a tag, Backspace on an empty field removes the last one. --}}
@props(['model', 'live' => false, 'placeholder' => 'Add tag...', 'suggestions' => [], 'label' => 'Add tag'])
<div wire:ignore {{ $attributes }} x-data="{
    tags: $wire.entangle({{ \Illuminate\Support\Js::from($model) }}){{ $live ? '.live' : '' }},
    draft: '',
    add(value) {
        value = (value ?? this.draft).trim();
        if (value && !this.tags.includes(value)) this.tags = [...this.tags, value];
        this.draft = '';
    },
    remove(index) { this.tags = this.tags.filter((_, i) => i !== index); }
}">
    <div class="oc-tag-input">
        <template x-for="(tag, index) in tags" :key="tag">
            <span class="oc-tag"><span x-text="tag"></span> <button class="oc-tag-remove" type="button" x-on:click="remove(index)" :aria-label="'Remove ' + tag">&times;</button></span>
        </template>
        <input type="text" x-model="draft" placeholder="{{ $placeholder }}" aria-label="{{ $label }}"
               x-on:keydown.enter.prevent="add()" x-on:keydown.comma.prevent="add()"
               x-on:keydown.backspace="if (!draft) tags = tags.slice(0, -1)">
    </div>
    @if (count($suggestions))
        <div class="oc-tag-suggestions">
            @foreach ($suggestions as $suggestion)
                <button type="button" x-on:click="add({{ \Illuminate\Support\Js::from($suggestion) }})">{{ $suggestion }}</button>
            @endforeach
        </div>
    @endif
</div>

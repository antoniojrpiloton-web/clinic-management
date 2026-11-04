@props(['id', 'title', 'size' => null])
<div
    {{ $attributes->merge(['class' => 'modal fade', 'id' => $id, 'tabindex' => '-1', 'aria-hidden' => 'true']) }}
    aria-labelledby="{{ $id }}-label"
>
    <div @class(['modal-dialog modal-dialog-centered', "modal-{$size}" => $size])>
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h2 class="modal-title h6 fw-semibold mb-0" id="{{ $id }}-label">{{ $title }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>


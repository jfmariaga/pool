
@props(['id', 'title', 'footer','size' => 'lg'])

<div wire:ignore.self class="modal fade text-left" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel17" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-{{ $size }}" role="document">
        <div class="modal-content box-shadow-2 bg_white">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel17">
                    {{ $title }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if ( isset( $footer ) && $footer )
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

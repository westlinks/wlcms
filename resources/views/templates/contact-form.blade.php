<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">
@push('styles')
<style>
    .contact-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }
    
    @media (max-width: 768px) {
        .contact-layout {
            grid-template-columns: 1fr;
        }
    }
    
    .contact-form-wrapper {
        background: #fff;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .contact-info {
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    
    .contact-info h3 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .form-embed {
        margin-top: 1.5rem;
    }
</style>
@endpush

<div class="container">
    <div class="contact-layout">
        {{-- Form Section --}}
        <div class="contact-form-wrapper">
            {{-- Introduction Text --}}
            @if(isset($zones['intro']) && !empty($zones['intro']))
            <div class="intro-text" style="margin-bottom: 1.5rem;">
                {!! $zones['intro'] !!}
            </div>
            @endif

            {{-- Contact Form --}}
            <div class="form-embed">
                @if(isset($zones['form']))
                    @php
                        $formData = $zones['form'];
                    @endphp
                    
                    @if(isset($formData['type']) && $formData['type'] === 'embed')
                        {!! $formData['embed_code'] ?? '' !!}
                    @elseif(isset($formData['form_id']))
                        {{-- Built-in or custom form rendering --}}
                        <div class="form-placeholder">
                            <p>Form ID: {{ $formData['form_id'] }}</p>
                            <p><em>Form will be rendered here when form system is implemented.</em></p>
                        </div>
                    @else
                        <div class="form-placeholder">
                            <p><em>No form configured. Please configure a form in the template settings.</em></p>
                        </div>
                    @endif
                @else
                    <div class="form-placeholder">
                        <p><em>Form will appear here once configured.</em></p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Contact Information Sidebar --}}
        <aside class="contact-info">
            @if(isset($zones['contact_info']) && !empty($zones['contact_info']))
                {!! $zones['contact_info'] !!}
            @else
                <h3>Contact Information</h3>
                <p>Add your contact details here.</p>
            @endif
        </aside>
    </div>
</div>
</x-dynamic-component>

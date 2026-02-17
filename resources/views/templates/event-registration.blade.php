<x-dynamic-component :component="$layout ?? 'wlcms::layouts.base'">
<div class="container">
    <article class="main-content event-registration-template">
        <h1 class="page-title">{{ $contentItem->title }}</h1>

        {{-- Content switches based on registration_status setting --}}
        @php
            $status = $settings['registration_status'] ?? 'upcoming';
        @endphp

        @if($status === 'upcoming')
            {{-- Pre-Registration Content --}}
            <div class="content-zone pre-registration-zone">
                {!! $zones['pre_registration'] ?? '' !!}
            </div>
        @elseif($status === 'open')
            {{-- Active Event Content --}}
            <div class="content-zone active-event-zone">
                {!! $zones['active_event'] ?? '' !!}
            </div>

            @if(!empty($settings['registration_link']))
                <div class="registration-cta" style="margin-top: 2rem; text-align: center;">
                    <a href="{{ $settings['registration_link'] }}" 
                       class="btn btn-primary" 
                       style="display: inline-block; padding: 12px 32px; background: #4F46E5; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        Register Now
                    </a>
                </div>
            @endif
        @else
            {{-- Closed Event Content --}}
            <div class="content-zone closed-event-zone">
                {!! $zones['closed_event'] ?? '' !!}
            </div>
        @endif

        @if(!empty($settings['event_date']))
            <div class="event-date" style="margin-top: 2rem; padding: 1rem; background: #F3F4F6; border-radius: 8px; text-align: center;">
                <strong>Event Date:</strong> {{ \Carbon\Carbon::parse($settings['event_date'])->format('F j, Y') }}
            </div>
        @endif
    </article>
</div>
</x-dynamic-component>

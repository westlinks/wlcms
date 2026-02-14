@extends('wlcms::layouts.base')

@push('styles')
<style>
    .sidebar-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
    }
    
    @media (max-width: 768px) {
        .sidebar-layout {
            grid-template-columns: 1fr;
        }
    }
    
    .sidebar {
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    
    .sidebar h3 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="sidebar-layout">
        {{-- Main Content --}}
        <article class="main-content">
            <div class="content-zone">
                {!! $zones['content'] ?? '' !!}
            </div>
        </article>

        {{-- Sidebar --}}
        <aside class="sidebar">
            @if(isset($zones['sidebar']) && !empty($zones['sidebar']))
                {!! $zones['sidebar'] !!}
            @else
                <h3>Quick Links</h3>
                <p>Sidebar content goes here.</p>
            @endif
        </aside>
    </div>
</div>
@endsection

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $contentItem->title ?? config('app.name', 'WLCMS') }}</title>

    {{-- SEO Meta Tags --}}
    @if($contentItem->excerpt ?? false)
    <meta name="description" content="{{ $contentItem->excerpt }}">
    @endif

    @if(isset($meta['keywords']))
    <meta name="keywords" content="{{ $meta['keywords'] }}">
    @endif

    {{-- Open Graph / Social Media --}}
    @if(isset($meta['og_title']))
    <meta property="og:title" content="{{ $meta['og_title'] }}">
    @endif

    @if(isset($meta['og_description']))
    <meta property="og:description" content="{{ $meta['og_description'] }}">
    @endif

    @if(isset($meta['og_image']))
    <meta property="og:image" content="{{ $meta['og_image'] }}">
    @endif

    {{-- Styles --}}
    @stack('styles')
    
    {{-- Template-specific styles --}}
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .page-header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .page-excerpt {
            font-size: 1.125rem;
            color: #6b7280;
        }
        
        .main-content {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .breadcrumbs {
            list-style: none;
            display: flex;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        .breadcrumbs li:not(:last-child)::after {
            content: '/';
            margin-left: 0.5rem;
        }
        
        .breadcrumbs a {
            color: #3b82f6;
            text-decoration: none;
        }
        
        .breadcrumbs a:hover {
            text-decoration: underline;
        }
    </style>

    @stack('head-scripts')
</head>
<body>
    {{-- Optional: Site Header/Navigation --}}
    @if(config('wlcms.templates.show_header', false))
    <header class="site-header">
        <div class="container">
            <nav>
                {{-- Navigation will go here --}}
            </nav>
        </div>
    </header>
    @endif

    {{-- Page Header --}}
    @unless(isset($hidePageHeader) && $hidePageHeader)
    <div class="page-header">
        <div class="container">
            {{-- Breadcrumbs --}}
            @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
            <ul class="breadcrumbs">
                @foreach($breadcrumbs as $crumb)
                    <li>
                        @if($crumb['url'])
                        <a href="{{ $crumb['url'] }}">{{ $crumb['title'] }}</a>
                        @else
                        {{ $crumb['title'] }}
                        @endif
                    </li>
                @endforeach
            </ul>
            @endif

            <h1 class="page-title">{{ $contentItem->title ?? 'Page Title' }}</h1>
            
            @if($contentItem->excerpt ?? false)
            <p class="page-excerpt">{{ $contentItem->excerpt }}</p>
            @endif
        </div>
    </div>
    @endunless

    {{-- Main Content Area --}}
    <main class="page-content">
        {{ $slot }}
    </main>

    {{-- Optional: Site Footer --}}
    @if(config('wlcms.templates.show_footer', false))
    <footer class="site-footer">
        <div class="container">
            {{-- Footer content will go here --}}
        </div>
    </footer>
    @endif

    {{-- Scripts --}}
    @stack('scripts')
</body>
</html>

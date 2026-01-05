<?php

namespace Westlinks\Wlcms\View\Components;

use Illuminate\View\Component;

class AdminLayout extends Component
{
    public string $title;
    public string $pageTitle;

    public function __construct(string $title = 'WLCMS Admin', string $pageTitle = '')
    {
        $this->title = $title;
        $this->pageTitle = $pageTitle;
    }

    public function render()
    {
        // Check if we should use embedded mode (content-only)
        if (config('wlcms.layout.mode') === 'embedded') {
            $hostLayout = config('wlcms.layout.host_layout');
            
            if ($hostLayout) {
                // Pure content-only mode - no package navigation/layout
                return view('wlcms::components.content-only', [
                    'customLayout' => $hostLayout,
                    'title' => $this->title,
                    'pageTitle' => $this->pageTitle
                ]);
            }
        }

        // Use package's own admin layout with full navigation (renamed component)
        return view('wlcms::components.wlcms-admin-layout');
    }
}
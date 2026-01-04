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
        // Check if we should use embedded mode
        if (config('wlcms.layout.mode') === 'embedded') {
            $customLayout = config('wlcms.layout.custom_layout');
            
            if ($customLayout) {
                // Use host application's layout component
                return view('wlcms::components.embedded-wrapper', [
                    'customLayout' => $customLayout
                ]);
            }
        }

        // Use package's own admin layout
        return view('wlcms::components.admin-layout');
    }
}
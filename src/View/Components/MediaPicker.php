<?php

namespace Westlinks\Wlcms\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class MediaPicker extends Component
{
    public string $field;
    public ?string $label;
    public ?string $value;
    public ?string $type;
    public bool $required;
    public ?string $helpText;
    public bool $multiple;
    public string $uniqueId;
    
    /**
     * Create a new component instance.
     *
     * @param string $field The form field name
     * @param string|null $label The label text
     * @param string|null $value The current value (path or comma-separated paths)
     * @param string|null $type Filter by media type (image, video, document)
     * @param bool $required Whether the field is required
     * @param string|null $helpText Help text to display
     * @param bool $multiple Whether to allow multiple selection
     */
    public function __construct(
        string $field,
        ?string $label = null,
        ?string $value = null,
        ?string $type = null,
        bool $required = false,
        ?string $helpText = null,
        bool $multiple = false
    ) {
        $this->field = $field;
        $this->label = $label;
        $this->value = $value;
        $this->type = $type;
        $this->required = $required;
        $this->helpText = $helpText;
        $this->multiple = $multiple;
        $this->uniqueId = 'media-picker-' . Str::random(8);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('wlcms::components.media-picker');
    }
}

<?php

namespace JobMetric\PackageCore\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Throwable;

class BooleanStatus extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string|null $value = null,
    )
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @throws Throwable
     */
    public function render(): View|Closure|string
    {
        return $this->view('package-core::components.boolean-status');
    }

}

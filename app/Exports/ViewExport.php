<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class ViewExport implements FromView
{
    protected string $view;
    protected array $data;

    public function __construct(string $view, array $data = [])
    {
        $this->view = $view;
        $this->data = $data;
    }

    public function view(): View
    {
        return view($this->view, $this->data);
    }
}

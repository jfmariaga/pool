<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // en layout data pasamos las prop
        return view('livewire.dashboard.dashboard')
            ->layoutData(['section' => 'Dashboard']);
    }
}

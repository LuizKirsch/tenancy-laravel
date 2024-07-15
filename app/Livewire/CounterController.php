<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Counter;

class CounterController extends Component
{
    public $count = 1;
    public $counter;

    public function mount()
    {
        $this->counter = Counter::first();
        
        if (!$this->counter) {
            $this->counter = Counter::create(['count' => $this->count]);
        } else {
            $this->count = $this->counter->count;
        }
    }

    protected function rules()
    {
        return [
            'count' => 'required|integer|min:0',
        ];
    }

    public function increment()
    {
        $this->validate(); 

        $this->count++;
        $this->counter->update(['count' => $this->count]);
    }

    public function decrement()
    {
        $this->validate();

        $this->count--;
        $this->counter->update(['count' => $this->count]);
    }

    public function render()
    {
        return view('livewire.counter');
    }
}

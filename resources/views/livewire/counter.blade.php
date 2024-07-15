<div>
    <h1>{{ $count }}</h1>

    <!-- Exibir erro se houver -->
    @error('count')
        <div class="text-red-500">{{ $message }}</div>
    @enderror

    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
    <div wire:loading> 
        Saving post...
    </div>
</div>
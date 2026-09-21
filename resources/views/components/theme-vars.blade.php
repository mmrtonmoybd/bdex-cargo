{{-- Values are sanitised in ThemeVars before they reach this block. --}}
<style>
    :root {
        @foreach ($tokens as $name => $value)
            {!! $name !!}: {!! $value !!};
        @endforeach
    }
</style>

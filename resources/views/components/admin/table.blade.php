@props(['headers' => []])

<div class="card flush">
    <div class="table-scroll">
        <table class="table">
            @if(count($headers) > 0)
                <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

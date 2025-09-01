<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Report' }}</title>
    <style>
        @page {
            size: A4 {{ $orientation ?? 'portrait' }};
            margin: 18mm 12mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1 {
            margin-bottom: 8px;
            font-size: 18px;
        }

        .meta {
            margin-bottom: 8px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px 8px;
            vertical-align: top;
        }

        tr {
            page-break-inside: avoid;
        }

        .right {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <h1>{{ $title ?? 'Report' }}</h1>
    <div class="meta">
        Generated: {{ $generatedAt->format('Y-m-d H:i') }}
        @if (!empty($filters))
            <br>Filters:
            @foreach ($filters as $k => $v)
                @if ($v !== null && $v !== '')
                    <strong>{{ $k }}</strong>=<span>{{ $v }}</span>
                @endif
            @endforeach
        @endif
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($columns as $col)
                    <th class="{{ $col['nowrap'] ?? false ? 'nowrap' : '' }}">{{ $col['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    @foreach ($columns as $col)
                        <td class="{{ $col['class'] ?? '' }}">
                            @if (isset($col['callback']) && is_callable($col['callback']))
                                {!! $col['callback']($row, $i) !!}
                            @else
                                {{ $row[$col['key']] ?? '' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

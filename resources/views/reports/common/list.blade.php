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
    <div style="text-align: center; margin-bottom: 20px; font-family: 'DejaVu Sans', sans-serif;">
        <h1 style="margin: 0; font-size: 22px; color: #1a202c;">PROVITA GROUP</h1>
        <p style="margin: 4px 0; font-size: 14px; color: #4a5568;">
            Provita Tower, House #21, Road #35, Gulshan-02, Dhaka 1212
        </p>
        <p style="margin: 2px 0; font-size: 14px; color: #4a5568;">
            Phone: +880248811872 | Email: hrm@provitagroupbd.com
        </p>
        <hr style="border: 1px solid #e2e8f0; margin-top: 8px;">
    </div>
    <h1>{{ $title ?? 'Report' }}</h1>

    <div class="meta">
        Generated: {{ $generatedAt->format('Y-m-d H:i') }}
        {{-- @if (!empty($filters))
            <br>Filters:
            @foreach ($filters as $k => $v)
                @if ($v !== null && $v !== '')
                    <strong>{{ $k }}</strong>=<span>{{ $v }}</span>
                @endif
            @endforeach
        @endif --}}
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

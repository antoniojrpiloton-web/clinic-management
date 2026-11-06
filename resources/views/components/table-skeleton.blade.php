@props(['cols' => 7, 'rows' => 8])
@for ($r = 0; $r < $rows; $r++)
    <tr class="dt-skeleton-row">
        @for ($c = 0; $c < $cols; $c++)
            <td>
                @if ($c === 0)
                    <span class="dt-skeleton-bar" style="width: 75%;"></span>
                @elseif ($c === 1)
                    <span class="dt-skeleton-bar" style="width: 85%;"></span>
                    <span class="dt-skeleton-bar sm"></span>
                @elseif ($c === $cols - 1)
                    <span class="dt-skeleton-btn"></span>
                @elseif ($c % 3 === 0)
                    <span class="dt-skeleton-pill"></span>
                @else
                    <span class="dt-skeleton-bar" style="width: {{ 60 + (($r + $c) % 3) * 12 }}%;"></span>
                @endif
            </td>
        @endfor
    </tr>
@endfor

